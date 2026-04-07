<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$reference = trim((string)($_GET['reference'] ?? ''));
if ($reference === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Reference number is required']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

$db = sms_get_db_connection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Keep statuses in sync when any payment is already posted for a validated application.
$syncStmt = $db->prepare(
    "UPDATE applications a
     INNER JOIN payments p ON p.reference_id = a.reference_id
     SET a.status = 'Enrolled'
     WHERE a.status = 'Validated'
       AND p.payment_status IN ('Partial', 'Full')"
);
if ($syncStmt) {
    $syncStmt->execute();
    $syncStmt->close();
}

$stmt = $db->prepare(
    "SELECT
        a.application_id,
        a.status,
        a.created_at,
        rn.reference_number,
        COALESCE(
            TRIM(CONCAT(
                COALESCE(api.first_name, ''), ' ',
                COALESCE(api.middle_name, ''), ' ',
                COALESCE(api.last_name, '')
            )),
            'Applicant'
        ) AS applicant_name
     FROM applications a
     INNER JOIN reference_numbers rn ON rn.reference_id = a.reference_id
     LEFT JOIN applicant_personal_info api ON api.application_id = a.application_id
     WHERE rn.reference_number = ?
     ORDER BY a.created_at DESC
     LIMIT 1"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare status query']);
    exit;
}

$stmt->bind_param('s', $reference);
$stmt->execute();
$result = $stmt->get_result();

if (!($row = $result->fetch_assoc())) {
    $stmt->close();
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Reference number not found']);
    exit;
}

$applicantName = trim(preg_replace('/\s+/', ' ', (string)$row['applicant_name']));
if ($applicantName === '') {
    $applicantName = 'Applicant';
}

$status = (string)$row['status'];
$allowedStatuses = ['Pending', 'Validated', 'Enrolled'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'Pending';
}

echo json_encode([
    'success' => true,
    'data' => [
        'reference_number' => (string)$row['reference_number'],
        'application_id' => (int)$row['application_id'],
        'applicant_name' => $applicantName,
        'status' => $status,
        'created_at' => (string)$row['created_at']
    ]
]);

$stmt->close();
