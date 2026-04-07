<?php

require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Invalid JSON payload.'
    ], 400);
}

$email = strtolower(enrollment_api_string($payload['email'] ?? ''));
$contact = enrollment_api_string($payload['contact'] ?? '');
$admissionType = enrollment_api_string($payload['admissionType'] ?? '');
$studentNumber = enrollment_api_string($payload['studentNumber'] ?? '');

if ($email === '' || $contact === '') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Email and contact number are required.'
    ], 422);
}

$db = enrollment_api_require_db();

$isOldStudentFlow = ($admissionType === 'Old Student' || $admissionType === 'Returnee');

if ($admissionType === 'Old Student' && $studentNumber === '') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Student Number is required for old student enrollment.'
    ], 422);
}

if ($isOldStudentFlow && $studentNumber !== '') {
    $studentSql = 'SELECT student_id FROM students WHERE student_number = ? LIMIT 1';
    $studentStmt = $db->prepare($studentSql);
    if (!$studentStmt) {
        enrollment_api_json([
            'ok' => false,
            'message' => 'Failed to prepare student lookup query: ' . $db->error
        ], 500);
    }

    $studentStmt->bind_param('s', $studentNumber);
    if (!$studentStmt->execute()) {
        enrollment_api_json([
            'ok' => false,
            'message' => 'Failed to execute student lookup query: ' . $studentStmt->error
        ], 500);
    }

    $studentResult = $studentStmt->get_result();
    $student = $studentResult ? $studentResult->fetch_assoc() : null;
    $studentStmt->close();

    if (!$student) {
        enrollment_api_json([
            'ok' => false,
            'message' => 'Student Number not found. Leave it blank if you do not have an existing student record yet.'
        ], 404);
    }

    enrollment_api_json([
        'ok' => true,
        'isDuplicate' => false,
        'isOldStudent' => true,
        'studentId' => intval($student['student_id'])
    ]);
}

$sql = 'SELECT ac.email_address,
               ac.contact_number
        FROM applicant_personal_info api
        INNER JOIN applicant_contact ac ON api.application_id = ac.application_id
        WHERE LOWER(ac.email_address) = LOWER(?)
           OR ac.contact_number = ?
        ORDER BY api.application_id DESC
        LIMIT 1';

$stmt = $db->prepare($sql);
if (!$stmt) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Failed to prepare query: ' . $db->error
    ], 500);
}

$stmt->bind_param('ss', $email, $contact);
if (!$stmt->execute()) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Failed to execute query: ' . $stmt->error
    ], 500);
}

$result = $stmt->get_result();
$existing = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$existing) {
    enrollment_api_json([
        'ok' => true,
        'isDuplicate' => false
    ]);
}

$duplicateEmail = isset($existing['email_address']) && strcasecmp((string) $existing['email_address'], $email) === 0;
$duplicateContact = isset($existing['contact_number']) && (string) $existing['contact_number'] === $contact;

$duplicateFields = [];
if ($duplicateEmail) {
    $duplicateFields[] = 'email address';
}
if ($duplicateContact) {
    $duplicateFields[] = 'contact number';
}

$label = !empty($duplicateFields)
    ? implode(' and ', $duplicateFields)
    : 'email address or contact number';

enrollment_api_json([
    'ok' => true,
    'isDuplicate' => true,
    'duplicateEmail' => $duplicateEmail,
    'duplicateContact' => $duplicateContact,
    'message' => 'The submitted ' . $label . ' is already used. Please use unique details.'
]);
