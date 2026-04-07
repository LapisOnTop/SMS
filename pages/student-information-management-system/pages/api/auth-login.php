<?php

declare(strict_types=1);

error_log('AUTH-LOGIN: Request started - ' . json_encode($_SERVER['REQUEST_URI'] ?? ''));

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

error_log('AUTH-LOGIN: Session ID - ' . session_id() . ' | User: ' . ($_SESSION['user_id'] ?? 'none'));

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once dirname(__DIR__, 4) . '/config/database.php';

$simBootstrap = dirname(__DIR__) . '/includes/sim_bootstrap.php';

$db = sms_get_db_connection();
if (!$db) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid JSON']);
    exit;
}

$username = trim((string) ($data['username'] ?? ''));
$password = (string) ($data['password'] ?? '');
$roleKey = strtolower(trim((string) ($data['role'] ?? 'registrar')));

$roleMap = [
    'registrar' => 'Registrar',
    'student' => 'Student',
    'admin' => 'Admin',
];

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Username and password are required']);
    exit;
}

if (!isset($roleMap[$roleKey])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid role']);
    exit;
}

$roleDb = $roleMap[$roleKey];

$stmt = $db->prepare(
    'SELECT u.user_id, u.username, r.role_name as role, u.password_hash, u.is_active 
     FROM users u 
     JOIN roles r ON u.role_id = r.role_id 
     WHERE u.username = ? AND r.role_name = ? LIMIT 1'
);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('ss', $username, $roleDb);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || (int)($user['is_active'] ?? 0) !== 1) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password']);
    exit;
}

$stored = trim((string) ($user['password_hash'] ?? ''));
$ok = false;
if ($stored !== '') {
    $info = password_get_info($stored);
    if (($info['algo'] ?? 0) !== 0) {
        $ok = password_verify($password, $stored);
    } else {
        // Legacy / seed data: plain text in password_hash column
        $ok = hash_equals($stored, $password);
    }
}

if (!$ok) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Invalid username or password']);
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Role-specific session keys used across this module
unset($_SESSION['registrar_id'], $_SESSION['student_id']);

// Absolute path from web root (avoids relative-URL resolution bugs in browsers after fetch + redirect)
$moduleRoot = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$moduleRoot = str_replace('\\', '/', $moduleRoot);

if ($roleDb === 'Registrar') {
    $_SESSION['registrar_id'] = (int) $user['user_id'];
    $_SESSION['registrar'] = true;
    $redirect = $moduleRoot . '/pages/registrar/student-profile-registration.php';
} elseif ($roleDb === 'Student') {
    $sid = null;
    $q = $db->prepare('SELECT student_id FROM students WHERE user_id = ? LIMIT 1');
    if ($q) {
        $uid = (int) $user['user_id'];
        $q->bind_param('i', $uid);
        $q->execute();
        $r = $q->get_result()->fetch_assoc();
        $q->close();
        if ($r && isset($r['student_id'])) {
            $sid = (int) $r['student_id'];
        }
    }
    if ($sid === null) {
        $sid = (int) $user['user_id'];
    }
    $_SESSION['student_id'] = $sid;
    $redirect = $moduleRoot . '/pages/user/update-personal-info.php';

    $_SESSION['active_student_id'] = (string) $sid;
    $_SESSION['explicit_logout'] = false;
} else {
    $redirect = $moduleRoot . '/pages/user/update-personal-info.php';
}

echo json_encode([
    'ok' => true,
    'redirect' => $redirect,
    'role' => $user['role'],
]);
