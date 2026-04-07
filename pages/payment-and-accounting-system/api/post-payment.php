<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['application_id']) || !is_numeric($input['application_id']) || !isset($input['amountPaid']) || $input['amountPaid'] <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid application_id or amountPaid']);
    exit;
}

$application_id = (int)$input['application_id'];
$amount_paid = (float)$input['amountPaid'];
$or_number = 'OR-' . date('Ymd-') . strtoupper(substr(uniqid(), -6)); // Generate OR number
$payment_method = $input['paymentMethod'] ?? 'Cash';
$payment_date = $input['paymentDate'] ?? date('Y-m-d');
$payment_reference = $input['referenceNumber'] ?? $or_number;
$notes = $input['remarks'] ?? '';

$conn = sms_get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
    exit;
}

// Insert payment record
$stmt = $conn->prepare("
    INSERT INTO application_payments (application_id, payment_amount, payment_date, payment_method, payment_reference, payment_status, notes) 
    VALUES (?, ?, ?, ?, ?, 'Completed', ?)
");
$stmt->bind_param('idssss', $application_id, $amount_paid, $payment_date, $payment_method, $payment_reference, $notes);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to record payment']);
    exit;
}
$stmt->close();

// Resolve reference_id from provided reference number, then move statuses to Enrolled.
$reference_id = null;
$reference_number = trim((string)$payment_reference);

if ($reference_number !== '') {
    $reference_stmt = $conn->prepare("SELECT reference_id FROM reference_numbers WHERE reference_number = ? LIMIT 1");
    if ($reference_stmt) {
        $reference_stmt->bind_param('s', $reference_number);
        $reference_stmt->execute();
        $reference_result = $reference_stmt->get_result();
        if ($reference_row = $reference_result->fetch_assoc()) {
            $reference_id = (int)$reference_row['reference_id'];
        }
        $reference_stmt->close();
    }
}

if ($reference_id === null) {
    $fallback_ref_stmt = $conn->prepare("SELECT reference_id FROM applications WHERE application_id = ? LIMIT 1");
    if ($fallback_ref_stmt) {
        $fallback_ref_stmt->bind_param('i', $application_id);
        $fallback_ref_stmt->execute();
        $fallback_ref_result = $fallback_ref_stmt->get_result();
        if ($fallback_ref_row = $fallback_ref_result->fetch_assoc()) {
            $reference_id = isset($fallback_ref_row['reference_id']) ? (int)$fallback_ref_row['reference_id'] : null;
        }
        $fallback_ref_stmt->close();
    }
}

if ($reference_id !== null && $reference_id > 0) {
    $app_status_stmt = $conn->prepare("\n        UPDATE applications\n        SET status = 'Enrolled'\n        WHERE reference_id = ?\n          AND status IN ('Validated', 'Enrolled')\n    ");
    if ($app_status_stmt) {
        $app_status_stmt->bind_param('i', $reference_id);
        $app_status_stmt->execute();
        $app_status_stmt->close();
    }

    $enrollment_status_stmt = $conn->prepare("\n        UPDATE enrollments e\n        INNER JOIN students s ON s.student_id = e.student_id\n        INNER JOIN applications a ON a.application_id = s.application_id\n        SET e.status = 'Enrolled'\n        WHERE a.reference_id = ?\n          AND e.status IN ('Validated', 'Paid', 'Enrolled')\n    ");
    if ($enrollment_status_stmt) {
        $enrollment_status_stmt->bind_param('i', $reference_id);
        $enrollment_status_stmt->execute();
        $enrollment_status_stmt->close();
    }
}

// Fetch updated totals
$total_stmt = $conn->prepare("
    SELECT 
        8025.00 as assessment,  -- Hardcoded mock assessment
        COALESCE(SUM(ap.payment_amount), 0) as paid,
        0.00 as discount  -- Mock discount
    FROM applications a 
    LEFT JOIN application_payments ap ON a.application_id = ap.application_id 
    WHERE a.application_id = ?
    GROUP BY a.application_id
");
$total_stmt->bind_param('i', $application_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$totals = ['assessment' => 8025.00, 'paid' => 0, 'discount' => 0.00];
if ($total_row = $total_result->fetch_assoc()) {
    $totals = [
        'assessment' => (float)($total_row['assessment'] ?? 8025.00),
        'paid' => (float)$total_row['paid'],
        'discount' => 0.00
    ];
}
$total_stmt->close();

echo json_encode([
    'ok' => true,
    'orNumber' => $or_number,
    'totals' => $totals,
    'message' => 'Payment posted successfully. Status updated if applicable.'
]);
?>

