<?php
// Prevent any output before JSON headers  
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Register error handler for API requests (only for errors, not warnings)
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Only handle fatal errors, not warnings or notices
    if (!in_array($errno, [E_WARNING, E_NOTICE, E_DEPRECATED, E_STRICT, E_USER_WARNING, E_USER_NOTICE])) {
        // Always output JSON for API requests
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline]);
        exit;
    }
    return false; // Let normal error handling continue for warnings/notices
});

// Register shutdown handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['success' => false, 'message' => 'Fatal error: ' . $error['message'] . ' in ' . basename($error['file']) . ':' . $error['line']]);
        exit;
    }
});

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require_once '../../../../config/database.php';

// Get database connection
$conn = sms_get_db_connection();
if (!$conn) {
	header('Content-Type: application/json', true, 500);
	echo json_encode(['success' => false, 'message' => 'Database connection failed']);
	exit;
}

// Verify user exists and has Cashier role
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;

$isAjax = (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) || 
          (isset($_POST['action'])) || 
          (isset($_GET['action']));

if (!$userId && !$username) {
	if ($isAjax) {
		header('Content-Type: application/json', true, 401);
		echo json_encode(['success' => false, 'message' => 'Unauthorized - please login']);
		exit;
	}
	header('Location: ../../../../login.php');
	exit;
}

// Query users table to verify credentials and role
$userStmt = $conn->prepare("
	SELECT u.user_id, u.username, r.role_name
	FROM users u
	LEFT JOIN roles r ON u.role_id = r.role_id
	WHERE (u.user_id = ? OR u.username = ?) AND u.is_active = 1
");

if (!$userStmt) {
	header('Content-Type: application/json', true, 500);
	echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
	exit;
}

// Ensure correct types for bind_param
$userIdInt = intval($userId);
$usernameStr = strval($username);
$userStmt->bind_param('is', $userIdInt, $usernameStr);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userRow = $userResult->fetch_assoc()) {
	// Verify user has Cashier role
	if ($userRow['role_name'] !== 'Cashier') {
		if ($isAjax) {
			header('Content-Type: application/json', true, 401);
			echo json_encode(['success' => false, 'message' => 'Unauthorized - Cashier role required']);
			exit;
		}
		header('Location: ../../../../login.php');
		exit;
	}
	// Update session with verified data
	$_SESSION['user_id'] = $userRow['user_id'];
	$_SESSION['username'] = $userRow['username'];
	$_SESSION['user_role'] = 'cashier';
	$loggedInUser = $userRow['username'];
} else {
	// User not found or not active
	if ($isAjax) {
		header('Content-Type: application/json', true, 401);
		echo json_encode(['success' => false, 'message' => 'Unauthorized - user not found']);
		exit;
	}
	header('Location: ../../../../login.php');
	exit;
}
$userStmt->close();

// Handle AJAX search request for application by reference number or student number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'search_application') {
    header('Content-Type: application/json');
    
    $searchKey = strtoupper(trim($_POST['reference'] ?? ''));
    if (empty($searchKey)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reference number or student number is required']);
        exit;
    }

    $conn = sms_get_db_connection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    // Get application details with subjects, linked student record, and fees.
    $stmt = $conn->prepare("
        SELECT 
            a.application_id,
            a.reference_id,
            a.admission_type,
            a.status AS application_status,
            s.student_id,
            s.student_number,
            api.first_name,
            api.last_name,
            api.middle_name,
            sel.year_level,
            c.course_name,
            rn.reference_number
        FROM applications a
        JOIN applicant_personal_info api ON a.application_id = api.application_id
        LEFT JOIN selection sel ON a.selection_id = sel.selection_id
        LEFT JOIN courses c ON sel.course_id = c.course_id
        JOIN reference_numbers rn ON a.reference_id = rn.reference_id
        LEFT JOIN students s ON s.application_id = a.application_id
        WHERE (rn.reference_number = ? OR s.student_number = ?)
          AND a.status IN ('Validated', 'Enrolled')
        LIMIT 1
    ");
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param('ss', $searchKey, $searchKey);
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Query execution error: ' . $stmt->error]);
        $stmt->close();
        exit;
    }
    
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $application_id = $row['application_id'];
        $studentId = isset($row['student_id']) ? intval($row['student_id']) : 0;
        $full_name = trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
        $course_year = $row['course_name'] . ' - Year ' . $row['year_level'];

        // Get selected subjects with prices
        $subjectsStmt = $conn->prepare("
            SELECT s.subject_id, s.subject_code, s.subject_name, s.units, s.price
            FROM subject_preselection_details spd
            JOIN subject_preselection sp ON spd.preselection_id = sp.preselection_id
            JOIN subjects s ON spd.subject_id = s.subject_id
            WHERE sp.application_id = ?
        ");
        $subjectsStmt->bind_param('i', $application_id);
        $subjectsStmt->execute();
        $subjectsResult = $subjectsStmt->get_result();
        
        $subjects = [];
        $totalSubjectCost = 0;
        $totalUnits = 0;
        
        while ($subject = $subjectsResult->fetch_assoc()) {
            $subjects[] = $subject;
            $totalSubjectCost += $subject['price'];
            $totalUnits += $subject['units'];
        }

        // Get fees
        $feesStmt = $conn->prepare("SELECT fee_id, fee_name, amount FROM fees WHERE is_active = 1");
        $feesStmt->execute();
        $feesResult = $feesStmt->get_result();
        
        $fees = [];
        $totalFees = 0;
        
        while ($fee = $feesResult->fetch_assoc()) {
            $fees[] = $fee;
            $totalFees += $fee['amount'];
        }

        $grandTotal = $totalSubjectCost + $totalFees;

        $paymentStatusStmt = $conn->prepare("
            SELECT
                COALESCE(SUM(amount), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN payment_status = 'Full' THEN 1 ELSE 0 END), 0) AS full_count
            FROM payments
            WHERE reference_id = ?
               OR (student_id IS NOT NULL AND student_id = ?)
        ");

        $totalPaid = 0;
        $hasPaidStatus = false;
        if ($paymentStatusStmt) {
            $paymentStatusStmt->bind_param('ii', $row['reference_id'], $studentId);
            $paymentStatusStmt->execute();
            $paymentStatusResult = $paymentStatusStmt->get_result();
            if ($paymentStatusRow = $paymentStatusResult->fetch_assoc()) {
                $totalPaid = floatval($paymentStatusRow['total_paid'] ?? 0);
                $hasPaidStatus = intval($paymentStatusRow['full_count'] ?? 0) > 0;
            }
            $paymentStatusStmt->close();
        }

        $assessmentStmt = $conn->prepare("
            SELECT total_tuition, total_miscellaneous, total_amount, paid_amount, status
            FROM assessment
            WHERE student_id = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        if ($assessmentStmt) {
            $assessmentStmt->bind_param('i', $studentId);
            $assessmentStmt->execute();
            $assessmentResult = $assessmentStmt->get_result();
            if ($assessmentRow = $assessmentResult->fetch_assoc()) {
                $totalSubjectCost = floatval($assessmentRow['total_tuition'] ?? $totalSubjectCost);
                $totalFees = floatval($assessmentRow['total_miscellaneous'] ?? $totalFees);
                $grandTotal = floatval($assessmentRow['total_amount'] ?? $grandTotal);
                $totalPaid = max($totalPaid, floatval($assessmentRow['paid_amount'] ?? 0));
            }
            $assessmentStmt->close();
        }

        $discountAmount = 0.00;
        $netAssessment = $grandTotal;
        $discountStmt = $conn->prepare("
            SELECT discount_amount, net_assessment
            FROM student_discounts
            WHERE student_id = ?
              AND status = 'Approved'
            ORDER BY id DESC
            LIMIT 1
        ");
        if ($discountStmt) {
            $discountStmt->bind_param('i', $studentId);
            $discountStmt->execute();
            $discountResult = $discountStmt->get_result();
            if ($discountRow = $discountResult->fetch_assoc()) {
                $discountAmount = floatval($discountRow['discount_amount'] ?? 0);
                $netAssessment = floatval($discountRow['net_assessment'] ?? $grandTotal);
            }
            $discountStmt->close();
        }

        $discountAmount = max(0, min($discountAmount, $grandTotal));
        $netAssessment = max(0, min($netAssessment, $grandTotal));
        if ($discountAmount > 0) {
            $netAssessment = max(0, $grandTotal - $discountAmount);
        }

        $isFullyPaid = $hasPaidStatus || ($totalPaid >= $netAssessment && $netAssessment > 0);
        $currentStatus = $isFullyPaid ? 'Paid' : ($row['application_status'] ?? 'Validated');
        $remainingBalance = max(0, $netAssessment - $totalPaid);

        echo json_encode([
            'success' => true,
            'data' => [
                'application_id' => $application_id,
                'student_id' => $studentId,
                'student_number' => $row['student_number'],
                'reference_id' => $row['reference_id'],
                'reference_number' => $row['reference_number'],
                'student_name' => $full_name,
                'admission_type' => $row['admission_type'],
                'course_year' => $course_year,
                'subjects' => $subjects,
                'total_units' => $totalUnits,
                'total_subject_cost' => $totalSubjectCost,
                'fees' => $fees,
                'total_fees' => $totalFees,
                'grand_total' => $grandTotal,
                'discount_amount' => $discountAmount,
                'net_assessment' => $netAssessment,
                'total_paid' => $totalPaid,
                'remaining_balance' => $remainingBalance,
                'is_fully_paid' => $isFullyPaid,
                'current_status' => $currentStatus
            ]
        ]);
    } else {
        // Reference number not found - provide helpful error message
        $debugStmt = $conn->prepare("SELECT reference_number FROM reference_numbers WHERE reference_number LIKE ? LIMIT 5");
        if ($debugStmt) {
            $searchLike = $searchKey . '%';
            $debugStmt->bind_param('s', $searchLike);
            $debugStmt->execute();
            $debugResult = $debugStmt->get_result();
            $suggestions = [];
            while ($row = $debugResult->fetch_assoc()) {
                $suggestions[] = $row['reference_number'];
            }
            $debugStmt->close();
            
            if (!empty($suggestions)) {
                $message = 'No application found for the given reference number or student number.';
            } else {
                $message = 'No application found for the given reference number or student number.';
            }
        } else {
            $message = 'No application found for the given reference number or student number.';
        }
        
        echo json_encode(['success' => false, 'message' => $message]);
    }
    $stmt->close();
    exit;
}

// Handle payment submission (JSON request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['action'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing action']);
        exit;
    }

    if ($data['action'] === 'submit_payment') {
        $conn = sms_get_db_connection();
        if (!$conn) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed']);
            exit;
        }
        $referenceNumber = strtoupper(trim(strval($data['reference_number'] ?? '')));
        if (empty($referenceNumber)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Reference number is required']);
            exit;
        }

        $referenceLookup = $conn->prepare("
            SELECT rn.reference_id, a.application_id, s.student_id, s.student_number
            FROM reference_numbers rn
            INNER JOIN applications a ON a.reference_id = rn.reference_id
            LEFT JOIN students s ON s.application_id = a.application_id
            WHERE rn.reference_number = ?
              AND a.status IN ('Validated', 'Enrolled')
            LIMIT 1
        ");
        if (!$referenceLookup) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database query error']);
            exit;
        }
        $referenceLookup->bind_param('s', $referenceNumber);
        $referenceLookup->execute();
        $referenceResult = $referenceLookup->get_result();

        if (!($referenceRow = $referenceResult->fetch_assoc())) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No validated or enrolled record found for this reference number']);
            $referenceLookup->close();
            exit;
        }

        $referenceId = intval($referenceRow['reference_id']);
        $applicationId = intval($referenceRow['application_id'] ?? 0);
        $studentId = intval($referenceRow['student_id'] ?? 0);
        $referenceLookup->close();

        if ($studentId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No student record is linked to this application yet. Please make sure the student.application_id is connected first.'
            ]);
            exit;
        }

        $discountAmount = 0.00;
        $discountStmt = $conn->prepare("
            SELECT discount_amount
            FROM student_discounts
            WHERE student_id = ?
              AND status = 'Approved'
            ORDER BY id DESC
            LIMIT 1
        ");
        if ($discountStmt) {
            $discountStmt->bind_param('i', $studentId);
            $discountStmt->execute();
            $discountResult = $discountStmt->get_result();
            if ($discountRow = $discountResult->fetch_assoc()) {
                $discountAmount = floatval($discountRow['discount_amount'] ?? 0);
            }
            $discountStmt->close();
        }

        $existingPaid = 0;
        $paidCheckStmt = $conn->prepare("
            SELECT
                COUNT(CASE WHEN payment_status = 'Full' THEN 1 END) AS full_count,
                COALESCE(SUM(amount), 0) AS total_paid
            FROM payments
            WHERE reference_id = ?
               OR (student_id IS NOT NULL AND student_id = ?)
        ");
        if ($paidCheckStmt) {
            $paidCheckStmt->bind_param('ii', $referenceId, $studentId);
            $paidCheckStmt->execute();
            $paidCheckResult = $paidCheckStmt->get_result();
            $paidCheckRow = $paidCheckResult ? $paidCheckResult->fetch_assoc() : null;
            $paidCheckStmt->close();

            $existingPaid = floatval($paidCheckRow['total_paid'] ?? 0);
            $requestedGrandTotal = floatval($data['grand_total'] ?? 0);
            $netPayableForCheck = max(0, $requestedGrandTotal - $discountAmount);
            if (intval($paidCheckRow['full_count'] ?? 0) > 0 || $existingPaid >= $netPayableForCheck) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'This reference number is already paid. Additional payment is not allowed.']);
                exit;
            }
        }

        // Generate receipt number
        $now = new DateTime();
        $receiptNo = 'RCP-' . $now->format('YmdHis') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));

        // Determine payment status
        $amountPaid = floatval($data['amount_paid'] ?? 0);
        $grandTotal = floatval($data['grand_total'] ?? 0);
        $totalTuition = floatval($data['total_subject_cost'] ?? 0);
        $totalFees = floatval($data['total_fees'] ?? 0);
        $discountAmount = max(0, min($discountAmount, $grandTotal));
        $netPayable = max(0, $grandTotal - $discountAmount);
        $remainingBeforePayment = max(0, $netPayable - $existingPaid);

        if ($grandTotal <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Grand total must be greater than zero.']);
            exit;
        }

        if ($amountPaid > $remainingBeforePayment) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Amount paid cannot be greater than the remaining balance.']);
            exit;
        }

        $paymentStatus = ($amountPaid >= $remainingBeforePayment) ? 'Full' : 'Partial';

        $conn->begin_transaction();

        $insertStmt = $conn->prepare("
            INSERT INTO payments (student_id, amount, payment_status, payment_method, receipt_number, reference_id, payment_date)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        if (!$insertStmt) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database prepare error']);
            exit;
        }
        
        $paymentMethod = 'Cash';

        if (!$insertStmt->bind_param('idsssi', $studentId, $amountPaid, $paymentStatus, $paymentMethod, $receiptNo, $referenceId)) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Parameter binding error: ' . $insertStmt->error]);
            $insertStmt->close();
            exit;
        }
        
        if ($insertStmt->execute()) {
            $insertStmt->close();

            $assessmentLookupStmt = $conn->prepare("
                SELECT id
                FROM assessment
                WHERE student_id = ?
                ORDER BY id DESC
                LIMIT 1
            ");
            if (!$assessmentLookupStmt) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to prepare assessment lookup.']);
                exit;
            }

            $assessmentLookupStmt->bind_param('i', $studentId);
            $assessmentLookupStmt->execute();
            $assessmentLookupResult = $assessmentLookupStmt->get_result();
            $assessmentRow = $assessmentLookupResult ? $assessmentLookupResult->fetch_assoc() : null;
            $assessmentLookupStmt->close();

            if ($assessmentRow) {
                $assessmentId = intval($assessmentRow['id']);
                $updateAssessmentStmt = $conn->prepare("
                    UPDATE assessment
                    SET total_tuition = ?,
                        total_miscellaneous = ?,
                        total_amount = ?
                    WHERE id = ?
                ");
                if (!$updateAssessmentStmt) {
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare assessment update.']);
                    exit;
                }

                $updateAssessmentStmt->bind_param('dddi', $totalTuition, $totalFees, $grandTotal, $assessmentId);
                if (!$updateAssessmentStmt->execute()) {
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to update assessment: ' . $updateAssessmentStmt->error]);
                    $updateAssessmentStmt->close();
                    exit;
                }
                $updateAssessmentStmt->close();
            } else {
                $insertAssessmentStmt = $conn->prepare("
                    INSERT INTO assessment (student_id, total_tuition, paid_amount, total_miscellaneous, total_amount, status)
                    VALUES (?, ?, 0, ?, ?, 'Unpaid')
                ");
                if (!$insertAssessmentStmt) {
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare assessment insert.']);
                    exit;
                }

                $insertAssessmentStmt->bind_param('iddd', $studentId, $totalTuition, $totalFees, $grandTotal);
                if (!$insertAssessmentStmt->execute()) {
                    $conn->rollback();
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Failed to create assessment: ' . $insertAssessmentStmt->error]);
                    $insertAssessmentStmt->close();
                    exit;
                }
                $insertAssessmentStmt->close();
            }

            $totalPaidStmt = $conn->prepare("
                SELECT COALESCE(SUM(amount), 0) AS total_paid
                FROM payments
                WHERE reference_id = ?
                   OR (student_id IS NOT NULL AND student_id = ?)
            ");
            if (!$totalPaidStmt) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to prepare payment total lookup.']);
                exit;
            }

            $totalPaidStmt->bind_param('ii', $referenceId, $studentId);
            $totalPaidStmt->execute();
            $totalPaidResult = $totalPaidStmt->get_result();
            $totalPaidRow = $totalPaidResult ? $totalPaidResult->fetch_assoc() : null;
            $totalPaidStmt->close();

            $updatedPaidAmount = floatval($totalPaidRow['total_paid'] ?? 0);
            $assessmentStatus = 'Unpaid';
            if ($updatedPaidAmount >= $grandTotal) {
                $assessmentStatus = 'Paid';
            } elseif ($updatedPaidAmount > 0) {
                $assessmentStatus = 'Partially Paid';
            }

            $syncAssessmentStmt = $conn->prepare("
                UPDATE assessment
                SET paid_amount = ?, status = ?
                WHERE student_id = ?
            ");
            if (!$syncAssessmentStmt) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to prepare assessment payment sync.']);
                exit;
            }

            $syncAssessmentStmt->bind_param('dsi', $updatedPaidAmount, $assessmentStatus, $studentId);
            if (!$syncAssessmentStmt->execute()) {
                $conn->rollback();
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to sync assessment payment: ' . $syncAssessmentStmt->error]);
                $syncAssessmentStmt->close();
                exit;
            }
            $syncAssessmentStmt->close();

            if ($paymentStatus === 'Partial' || $paymentStatus === 'Full') {
                $applicationStatusStmt = $conn->prepare("\n                    UPDATE applications\n                    SET status = 'Enrolled'\n                    WHERE reference_id = ?\n                      AND status <> 'Enrolled'\n                ");
                if ($applicationStatusStmt) {
                    $applicationStatusStmt->bind_param('i', $referenceId);
                    $applicationStatusStmt->execute();
                    $applicationStatusStmt->close();
                }

                $enrollmentStatusStmt = $conn->prepare("\n                    UPDATE enrollments e\n                    INNER JOIN students s ON s.student_id = e.student_id\n                    INNER JOIN applications a ON a.application_id = s.application_id\n                    SET e.status = 'Enrolled'\n                    WHERE a.reference_id = ?\n                      AND e.status <> 'Enrolled'\n                ");
                if ($enrollmentStatusStmt) {
                    $enrollmentStatusStmt->bind_param('i', $referenceId);
                    $enrollmentStatusStmt->execute();
                    $enrollmentStatusStmt->close();
                }
            }

            $conn->commit();

            echo json_encode([
                'success' => true,
                'receipt_number' => $receiptNo,
                'message' => 'Payment submitted successfully',
                'payment_data' => [
                    'receipt_number' => $receiptNo,
                    'student_id' => $studentId,
                    'student_number' => $referenceRow['student_number'] ?? null,
                    'reference_number' => $referenceNumber,
                    'amount_paid' => $amountPaid,
                    'grand_total' => $grandTotal,
                    'discount_amount' => $discountAmount,
                    'net_assessment' => $netPayable,
                    'total_subject_cost' => $totalTuition,
                    'total_fees' => $totalFees,
                    'total_paid' => $updatedPaidAmount,
                    'remaining_balance' => max(0, $netPayable - $updatedPaidAmount),
                    'payment_status' => $paymentStatus,
                    'payment_date' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to save payment: ' . $insertStmt->error]);
        }
        exit;
    }
}

// Get payments list
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_payments') {
    header('Content-Type: application/json');
    
    $conn = sms_get_db_connection();
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $paymentsStmt = $conn->prepare("
        SELECT 
            p.payment_id,
            p.receipt_number,
            COALESCE(api.first_name, 'Unknown') as first_name,
            COALESCE(api.last_name, 'Student') as last_name,
            p.amount,
            p.payment_status,
            p.payment_date
        FROM payments p
        LEFT JOIN reference_numbers rn ON p.reference_id = rn.reference_id
        LEFT JOIN applications a ON a.reference_id = rn.reference_id
        LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id
        ORDER BY p.payment_date DESC
        LIMIT 50
    ");
    
    if (!$paymentsStmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query error']);
        exit;
    }

    $paymentsStmt->execute();
    $paymentsResult = $paymentsStmt->get_result();

    $payments = [];
    while ($payment = $paymentsResult->fetch_assoc()) {
        $payments[] = $payment;
    }

    echo json_encode(['success' => true, 'payments' => $payments]);
    $paymentsStmt->close();
    exit;
}

$section = isset($_GET['section']) ? trim($_GET['section']) : 'payment-entry';
$navActive = $section;
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payment and Accounting - Cashier Management</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
		rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/cashier-management-new.css">
	<!-- PDF Libraries -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
	<!-- Header -->
	<header>
		<h1>
			<i class="fa-solid fa-cash-register"></i>
			Cashier Management
		</h1>
		<div class="header-actions">
			<div class="role-badge">
				<i class="fa-solid fa-user-tie"></i>
				<span><?php echo htmlspecialchars($loggedInUser); ?></span>
			</div>
			<a href="../../../../components/logout.php" class="logout-btn">
				<i class="fa-solid fa-sign-out-alt"></i>
				Logout
			</a>
		</div>
	</header>

	<div class="container">
		<!-- Sidebar Navigation -->
		<aside class="sidebar">
			<div class="sidebar-header">
				<h2>Menu</h2>
			</div>

			<nav class="sidebar-nav">
                <a href="analytics.php" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Analytics</span>
                </a>

                <a href="scholarship-and-discount.php" class="nav-item">
                    <i class="fa-solid fa-graduation-cap"></i>
                    <span>Scholarships & Discounts</span>
                </a>

                <a href="cashier-management.php?section=payment-entry" class="nav-item <?php echo $section === 'payment-entry' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-receipt"></i>
                    <span>Payment Entry</span>
                </a>

                <a href="cashier-management.php?section=payment-history" class="nav-item <?php echo $section === 'payment-history' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-history"></i>
                    <span>Payment History</span>
                </a>

                <a href="transaction-log.php" class="nav-item">
                    <i class="fa-solid fa-list-check"></i>
                    <span>Transaction Log</span>
                </a>
			</nav>
		</aside>

		<!-- Main Content -->
		<main class="content-wrapper">
			<!-- Payment Entry Section -->
			<section id="payment-entry-section" class="content-section <?php echo $section === 'payment-entry' ? 'active' : ''; ?>">
				<div class="section-header">
					<div>
						<h2><i class="fa-solid fa-receipt"></i> Payment Entry</h2>
						<p class="header-description">Search student and process payment</p>
					</div>
				</div>

				<!-- Search Card -->
				<div class="card">
					<div class="card-header">
						<h3>Student Search</h3>
					</div>
					<div class="card-body">
						<div class="search-input-group">
							<input 
								type="text" 
								id="referenceSearch" 
								placeholder="Enter reference number or student number"
								class="search-input"
							>
							<button type="button" class="btn-search-card" id="searchBtn">
								<i class="fa-solid fa-magnifying-glass"></i> Search
							</button>
						</div>
						<p class="search-hint">Enter a reference number or student number to view the current balance</p>
					</div>
				</div>

				<!-- Payment Card (Initially Hidden) -->
				<div id="paymentCard" class="card" style="display: none;">
					<div class="card-header">
						<h3>Payment Information</h3>
					</div>
					<div class="card-body">
						<!-- Student Info -->
						<div class="info-section">
							<h4>Student Details</h4>
							<div class="info-grid">
								<div class="info-item">
									<label>Reference Number</label>
									<span id="refNumber">-</span>
								</div>
								<div class="info-item">
									<label>Full Name</label>
									<span id="fullName">-</span>
								</div>
								<div class="info-item">
									<label>Admission Type</label>
									<span id="admissionType">-</span>
								</div>
								<div class="info-item">
									<label>Course / Year</label>
									<span id="courseYear">-</span>
								</div>
							</div>
						</div>

						<!-- Subjects Info -->
						<div class="info-section">
							<h4>Subject Load</h4>
							<div class="subjects-list" id="subjectsList"></div>
							<div class="summary-row">
								<span>Total Units: <strong id="totalUnitsText">0</strong></span>
								<span>Subtotal: <strong id="subjectSubtotal">₱0.00</strong></span>
							</div>
						</div>

						<!-- Fees Info -->
						<div class="info-section">
							<h4>Fees Breakdown</h4>
							<div class="fees-list" id="feesList"></div>
							<div class="summary-row">
								<span>Total Fees: <strong id="totalFeesText">₱0.00</strong></span>
							</div>
						</div>

						<!-- Total Amount -->
						<div class="info-section total-amount-section">
							<div class="total-amount-display">
								<label>Grand Total</label>
								<span id="grandTotalText">₱0.00</span>
							</div>
							<div class="total-amount-display">
								<label>Discounted</label>
								<span id="discountAmountText">₱0.00</span>
							</div>
							<div class="total-amount-display">
								<label>Total Paid</label>
								<span id="totalPaidText">₱0.00</span>
							</div>
							<div class="total-amount-display">
								<label>Remaining Balance</label>
								<span id="remainingBalanceText">₱0.00</span>
							</div>
						</div>

						<!-- Payment Details -->
						<div class="info-section">
							<h4>Payment Details</h4>
							<div class="info-grid">
								<div class="info-item">
									<label>Payment Method</label>
									<span class="payment-method-badge">Cash Only</span>
								</div>
								<div class="info-item">
									<label>Amount to Pay</label>
									<input 
										type="number" 
										id="amountToPay" 
										class="amount-input" 
										placeholder="Enter amount"
										min="0"
										step="0.01"
									>
								</div>
							</div>
						</div>

						<!-- Action Buttons -->
						<div class="card-actions">
							<button type="button" class="btn btn-primary" id="submitPaymentBtn">
								<i class="fa-solid fa-credit-card"></i> Submit Payment & Generate Receipt
							</button>
							<button type="button" class="btn btn-secondary" id="resetSearchBtn">
								<i class="fa-solid fa-arrow-left"></i> New Search
							</button>
						</div>

						<p class="status-message" id="statusMessage"></p>
					</div>
				</div>
			</section>

			<!-- Payment History Section -->
			<section id="payment-history-section" class="content-section <?php echo $section === 'payment-history' ? 'active' : ''; ?>">
				<div class="section-header">
					<div>
						<h2><i class="fa-solid fa-history"></i> Payment History</h2>
						<p class="header-description">View all recorded payments</p>
					</div>
				</div>
			<!-- Search Card -->
			<div class="card">
				<div class="card-header">
					<h3>Search Payments</h3>
				</div>
				<div class="card-body">
					<div class="search-input-group">
						<input 
							type="text" 
							id="paymentHistorySearch" 
							placeholder="Search by receipt number, student name, or payment status..."
							class="search-input"
						>
						<button type="button" class="btn-search-card" id="paymentSearchBtn">
							<i class="fa-solid fa-magnifying-glass"></i> Search
						</button>
					</div>
					<p class="search-hint">Results update as you type</p>
				</div>
			</div>
				<!-- Payment History Card -->
				<div class="card">
					<div class="card-header">
						<h3>Payment Records</h3>
					</div>
					<div class="card-body">
						<div class="table-responsive">
							<table class="history-table">
								<thead>
									<tr>
										<th>Receipt #</th>
										<th>Student Name</th>
										<th>Amount Paid</th>
										<th>Payment Status</th>
										<th>Payment Date</th>
									</tr>
								</thead>
								<tbody id="paymentHistoryBody">
									<tr>
										<td colspan="5" class="no-data">Loading payment history...</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</section>
		</main>
	</div>

	<script src="../../assets/js/cashier-management-new.js"></script>
</body>

</html>
