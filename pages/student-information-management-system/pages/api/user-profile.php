<?php

declare(strict_types=1);

/**
 * Session student profile rows in pamana.sims_students (mysqli).
 * Used by update-personal-info.php (inline script) — not the SIM StudentRepository.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 3) . '/config/database.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$db = sms_get_db_connection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$sessionId = (int) $_SESSION['student_id'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
if ($action !== 'update') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$qid = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
if ($qid !== $sessionId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
    exit;
}

$first = trim((string) ($data['first_name'] ?? ''));
$middle = trim((string) ($data['middle_name'] ?? ''));
$last = trim((string) ($data['last_name'] ?? ''));
$email = trim((string) ($data['email'] ?? ''));
$contact = trim((string) ($data['contact_number'] ?? ''));
$birthdate = trim((string) ($data['birthdate'] ?? ''));
$address = trim((string) ($data['address'] ?? ''));
$city = trim((string) ($data['city'] ?? ''));
$province = trim((string) ($data['province'] ?? ''));
$zip = trim((string) ($data['zip_code'] ?? ''));
$program = trim((string) ($data['program'] ?? ''));
$yearLevel = trim((string) ($data['year_level'] ?? ''));
$admissionDate = trim((string) ($data['admission_date'] ?? ''));
$photoUrl = isset($data['photo_url']) ? (string) $data['photo_url'] : null;
$genderEmpty = '';

if ($first === '' || $last === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'First and last name are required']);
    exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if ($photoUrl !== null && strlen($photoUrl) > 15_000_000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Photo data is too large']);
    exit;
}

$bdSql = $birthdate === '' ? null : $birthdate;
$adSql = $admissionDate === '' ? null : $admissionDate;

$check = $db->prepare('SELECT id FROM sims_students WHERE id = ? LIMIT 1');
if (!$check) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$check->bind_param('i', $sessionId);
$check->execute();
$exists = $check->get_result()->fetch_assoc() !== null;
$check->close();

if ($exists) {
    $sql = 'UPDATE sims_students SET
        first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_number = ?,
        birthdate = ?, address = ?, city = ?, province = ?, zip_code = ?,
        program = ?, year_level = ?, admission_date = ?, photo_url = ?
        WHERE id = ?';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    $stmt->bind_param(
        'ssssssssssssssi',
        $first,
        $middle,
        $last,
        $email,
        $contact,
        $bdSql,
        $address,
        $city,
        $province,
        $zip,
        $program,
        $yearLevel,
        $adSql,
        $photoUrl,
        $sessionId
    );
} else {
    $sql = 'INSERT INTO sims_students (
        id, first_name, middle_name, last_name, email, contact_number, birthdate, gender,
        address, city, province, zip_code, program, year_level, admission_date, photo_url
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    $stmt->bind_param(
        'isssssssssssssss',
        $sessionId,
        $first,
        $middle,
        $last,
        $email,
        $contact,
        $bdSql,
        // Gender is read-only via UI; keep empty value on insert.
        $genderEmpty,
        $address,
        $city,
        $province,
        $zip,
        $program,
        $yearLevel,
        $adSql,
        $photoUrl
    );
}

if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save: ' . $db->error]);
    exit;
}
$stmt->close();

echo json_encode(['success' => true, 'message' => 'Saved']);
