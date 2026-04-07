<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$db = ums_db();
ums_install_schema($db);

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    ums_json(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    ums_json(['ok' => false, 'message' => 'Invalid JSON'], 400);
}

$username = trim((string)($data['username'] ?? ''));
$password = (string)($data['password'] ?? '');
if ($username === '' || $password === '') {
    ums_json(['ok' => false, 'message' => 'Username and password are required'], 400);
}

$stmt = $db->prepare(
    'SELECT u.user_id, u.username, u.password_hash, u.is_active, r.role_name
     FROM users u
     JOIN roles r ON r.role_id = u.role_id
     WHERE u.username = ? AND r.role_name = "Admin"
     LIMIT 1'
);
if (!$stmt) {
    ums_json(['ok' => false, 'message' => 'Database error'], 500);
}
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || (int)($user['is_active'] ?? 0) !== 1) {
    ums_json(['ok' => false, 'message' => 'Invalid username or password'], 401);
}

$stored = (string)($user['password_hash'] ?? '');
$ok = false;
if ($stored !== '') {
    $info = password_get_info($stored);
    if (($info['algo'] ?? 0) !== 0) {
        $ok = password_verify($password, $stored);
    } else {
        $ok = hash_equals($stored, $password);
    }
}
if (!$ok) {
    ums_json(['ok' => false, 'message' => 'Invalid username or password'], 401);
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['username'] = (string)$user['username'];
$_SESSION['role'] = 'Admin';

ums_log($db, 'Logged in', null);

ums_json([
    'ok' => true,
    'redirect' => sms_url('pages/user-management-system/user-management.php')
]);

