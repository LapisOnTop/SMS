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

// Validate required fields
if (!isset($input['application_id']) || !is_numeric($input['application_id'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'application_id is required']);
    exit;
}

if (!isset($input['grand_total']) || $input['grand_total'] < 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'grand_total is required']);
    exit;
}

$conn = sms_get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
    exit;
}

$application_id = (int)$input['application_id'];
$reference_number = $input['reference_number'] ?? '';
$student_name = $input['student_name'] ?? '';
$total_units = (int)($input['total_units'] ?? 0);
$subject_cost = (float)($input['subject_cost'] ?? 0);
$fees_cost = (float)($input['fees_cost'] ?? 0);
$grand_total = (float)$input['grand_total'];

// Create assessment record or update existing one
$stmt = $conn->prepare("
    INSERT INTO assessments 
    (application_id, reference_number, student_name, total_units, subject_cost, fees_cost, grand_total, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE 
    subject_cost = VALUES(subject_cost),
    fees_cost = VALUES(fees_cost),
    grand_total = VALUES(grand_total),
    updated_at = NOW()
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param(
    'issiidid',
    $application_id,
    $reference_number,
    $student_name,
    $total_units,
    $subject_cost,
    $fees_cost,
    $grand_total
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Failed to save assessment: ' . $stmt->error]);
    $stmt->close();
    exit;
}

// Get the assessment ID
$assessment_id = $stmt->insert_id;
$stmt->close();

// Check if we need to insert subjects (if assessments_subjects table exists)
if (isset($input['subjects']) && is_array($input['subjects'])) {
    // Check if table exists
    $checkTable = $conn->query("SHOW TABLES LIKE 'assessments_subjects'");
    if ($checkTable && $checkTable->num_rows > 0) {
        // Delete existing entries
        $delStmt = $conn->prepare("DELETE FROM assessments_subjects WHERE assessment_id = ?");
        $delStmt->bind_param('i', $assessment_id);
        $delStmt->execute();
        $delStmt->close();

        // Insert new entries
        foreach ($input['subjects'] as $subject) {
            $subjectStmt = $conn->prepare("
                INSERT INTO assessments_subjects (assessment_id, subject_id, units, price)
                VALUES (?, ?, ?, ?)
            ");
            $subjectStmt->bind_param(
                'iid',
                $assessment_id,
                $subject['subject_id'],
                $subject['units'],
                $subject['price']
            );
            $subjectStmt->execute();
            $subjectStmt->close();
        }
    }
}

// Check if we need to insert fees (if assessments_fees table exists)
if (isset($input['fees']) && is_array($input['fees'])) {
    $checkTable = $conn->query("SHOW TABLES LIKE 'assessments_fees'");
    if ($checkTable && $checkTable->num_rows > 0) {
        // Delete existing entries
        $delStmt = $conn->prepare("DELETE FROM assessments_fees WHERE assessment_id = ?");
        $delStmt->bind_param('i', $assessment_id);
        $delStmt->execute();
        $delStmt->close();

        // Insert new entries
        foreach ($input['fees'] as $fee) {
            $feeStmt = $conn->prepare("
                INSERT INTO assessments_fees (assessment_id, fee_id, amount)
                VALUES (?, ?, ?)
            ");
            $feeStmt->bind_param(
                'iid',
                $assessment_id,
                $fee['fee_id'],
                $fee['amount']
            );
            $feeStmt->execute();
            $feeStmt->close();
        }
    }
}

echo json_encode([
    'ok' => true,
    'message' => 'Assessment saved successfully',
    'assessment_id' => $assessment_id
]);
?>
