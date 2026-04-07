<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$db = ums_db();
ums_install_schema($db);
ums_require_admin();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = isset($_GET['action']) ? (string)$_GET['action'] : '';

function role_id_by_name(mysqli $db, string $roleName): ?int
{
    $stmt = $db->prepare('SELECT role_id FROM roles WHERE role_name = ? LIMIT 1');
    if (!$stmt) return null;
    $stmt->bind_param('s', $roleName);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['role_id'] : null;
}

function read_json(): array
{
    $raw = file_get_contents('php://input');
    $j = json_decode($raw ?: '[]', true);
    return is_array($j) ? $j : [];
}

if ($method === 'GET' && $action === 'roles') {
    $rows = [];
    $res = $db->query('SELECT role_id, role_name FROM roles ORDER BY role_name');
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    ums_json(['ok' => true, 'roles' => $rows]);
}

if ($method === 'GET' && $action === 'student_candidates') {
    // Students without login (students.user_id IS NULL)
    $sql =
        "SELECT s.student_id,
                s.student_number,
                s.status,
                api.first_name,
                api.middle_name,
                api.last_name,
                ac.email_address AS email
         FROM students s
         LEFT JOIN applications app ON app.application_id = s.application_id
         LEFT JOIN applicant_personal_info api ON api.application_id = app.application_id
         LEFT JOIN applicant_contact ac ON ac.application_id = app.application_id
         WHERE s.user_id IS NULL
         ORDER BY s.created_at DESC, s.student_id DESC
         LIMIT 500";
    $rows = [];
    $res = $db->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    ums_json(['ok' => true, 'students' => $rows]);
}

if ($method === 'GET' && $action === 'list') {
    $sql =
        'SELECT u.user_id, u.username, u.is_active, u.created_at,
                r.role_name AS role,
                p.full_name, p.email, p.position
         FROM users u
         LEFT JOIN roles r ON r.role_id = u.role_id
         LEFT JOIN user_profiles p ON p.user_id = u.user_id
         ORDER BY u.created_at DESC, u.user_id DESC
         LIMIT 500';
    $rows = [];
    $res = $db->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
    ums_log($db, 'Viewed page: User Management', 'Accounts');
    ums_json(['ok' => true, 'users' => $rows]);
}

if ($method === 'POST' && $action === 'create') {
    $b = read_json();
    $username = trim((string)($b['username'] ?? ''));
    $password = (string)($b['password'] ?? '');
    $roleName = trim((string)($b['role'] ?? ''));
    $fullName = trim((string)($b['full_name'] ?? ''));
    $email = trim((string)($b['email'] ?? ''));
    $position = trim((string)($b['position'] ?? ''));

    if ($username === '' || $password === '' || $roleName === '') {
        ums_json(['ok' => false, 'message' => 'username, password, and role are required'], 400);
    }

    $roleId = role_id_by_name($db, $roleName);
    if (!$roleId) {
        ums_json(['ok' => false, 'message' => 'Unknown role'], 400);
    }

    $chk = $db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    if (!$chk) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $chk->bind_param('s', $username);
    $chk->execute();
    $exists = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($exists) {
        ums_json(['ok' => false, 'message' => 'Username already exists'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $ins = $db->prepare('INSERT INTO users (username, password_hash, role_id, is_active, created_at) VALUES (?, ?, ?, 1, NOW())');
    if (!$ins) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $ins->bind_param('ssi', $username, $hash, $roleId);
    if (!$ins->execute()) {
        $ins->close();
        ums_json(['ok' => false, 'message' => 'Failed to create user'], 500);
    }
    $userId = (int)$ins->insert_id;
    $ins->close();

    $p = $db->prepare('INSERT INTO user_profiles (user_id, full_name, email, position) VALUES (?, ?, ?, ?)');
    if ($p) {
        $p->bind_param('isss', $userId, $fullName, $email, $position);
        @$p->execute();
        $p->close();
    }

    ums_log($db, 'Created account', $username . ' (' . $roleName . ')');

    ums_json(['ok' => true, 'user_id' => $userId]);
}

if ($method === 'POST' && $action === 'create_student') {
    $b = read_json();
    $studentId = (int)($b['student_id'] ?? 0);
    $password = (string)($b['password'] ?? '');
    if (!$studentId || $password === '') {
        ums_json(['ok' => false, 'message' => 'student_id and password are required'], 400);
    }

    // Ensure student exists and has no login yet.
    $st = $db->prepare('SELECT student_number, user_id FROM students WHERE student_id = ? LIMIT 1');
    if (!$st) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $st->bind_param('i', $studentId);
    $st->execute();
    $student = $st->get_result()->fetch_assoc();
    $st->close();

    if (!$student) ums_json(['ok' => false, 'message' => 'Student not found'], 404);
    if (!empty($student['user_id'])) ums_json(['ok' => false, 'message' => 'Student already has an account'], 409);

    $username = trim((string)($student['student_number'] ?? ''));
    if ($username === '') ums_json(['ok' => false, 'message' => 'Student number missing'], 400);

    $roleId = role_id_by_name($db, 'Student');
    if (!$roleId) ums_json(['ok' => false, 'message' => 'Student role missing in roles table'], 500);

    // Ensure username is unique
    $chk = $db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
    if (!$chk) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $chk->bind_param('s', $username);
    $chk->execute();
    $exists = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($exists) {
        ums_json(['ok' => false, 'message' => 'Username already exists: ' . $username], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $ins = $db->prepare('INSERT INTO users (username, password_hash, role_id, is_active, created_at) VALUES (?, ?, ?, 1, NOW())');
    if (!$ins) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $ins->bind_param('ssi', $username, $hash, $roleId);
    if (!$ins->execute()) {
        $ins->close();
        ums_json(['ok' => false, 'message' => 'Failed to create user'], 500);
    }
    $userId = (int)$ins->insert_id;
    $ins->close();

    // Link student -> user
    $up = $db->prepare('UPDATE students SET user_id = ? WHERE student_id = ? AND user_id IS NULL');
    if (!$up) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $up->bind_param('ii', $userId, $studentId);
    $up->execute();
    $affected = $up->affected_rows;
    $up->close();
    if ($affected < 1) {
        // Roll back user creation if link failed (best-effort)
        $db->query('DELETE FROM users WHERE user_id = ' . (int)$userId);
        ums_json(['ok' => false, 'message' => 'Could not link student to new account'], 500);
    }

    ums_log($db, 'Created student account', $username . ' (Student)');
    ums_json(['ok' => true, 'user_id' => $userId, 'username' => $username]);
}

if ($method === 'POST' && $action === 'set_status') {
    $b = read_json();
    $userId = (int)($b['user_id'] ?? 0);
    $active = isset($b['is_active']) ? (int)(!!$b['is_active']) : 1;
    if (!$userId) ums_json(['ok' => false, 'message' => 'user_id required'], 400);

    $stmt = $db->prepare('UPDATE users SET is_active = ? WHERE user_id = ?');
    if (!$stmt) ums_json(['ok' => false, 'message' => 'Database error'], 500);
    $stmt->bind_param('ii', $active, $userId);
    $stmt->execute();
    $stmt->close();

    ums_log($db, 'Updated account status', 'user_id=' . $userId . ' is_active=' . $active);
    ums_json(['ok' => true]);
}

if ($method === 'GET' && $action === 'report') {
    $stats = [
        'total' => 0,
        'active' => 0,
        'student' => 0,
        'staff' => 0,
    ];

    $r = $db->query('SELECT COUNT(*) AS c FROM users');
    if ($r) $stats['total'] = (int)($r->fetch_assoc()['c'] ?? 0);
    $r = $db->query('SELECT COUNT(*) AS c FROM users WHERE is_active=1');
    if ($r) $stats['active'] = (int)($r->fetch_assoc()['c'] ?? 0);

    $studentRoleId = role_id_by_name($db, 'Student');
    if ($studentRoleId) {
        $q = $db->prepare('SELECT COUNT(*) AS c FROM users WHERE role_id = ?');
        $q->bind_param('i', $studentRoleId);
        $q->execute();
        $stats['student'] = (int)($q->get_result()->fetch_assoc()['c'] ?? 0);
        $q->close();
    }
    $stats['staff'] = max(0, $stats['total'] - $stats['student']);

    $roles = [];
    $res = $db->query(
        'SELECT r.role_name AS role, COUNT(*) AS total
         FROM users u
         LEFT JOIN roles r ON r.role_id = u.role_id
         GROUP BY r.role_name
         ORDER BY total DESC'
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) $roles[] = $row;
    }

    $recent = [];
    $res = $db->query(
        'SELECT u.user_id, COALESCE(p.full_name, u.username) AS name,
                r.role_name AS role, u.is_active, u.created_at
         FROM users u
         LEFT JOIN user_profiles p ON p.user_id = u.user_id
         LEFT JOIN roles r ON r.role_id = u.role_id
         ORDER BY u.created_at DESC
         LIMIT 10'
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) $recent[] = $row;
    }

    ums_log($db, 'Viewed page: User Management Report', null);

    ums_json(['ok' => true, 'stats' => $stats, 'roles' => $roles, 'recent' => $recent]);
}

ums_json(['ok' => false, 'message' => 'Not found'], 404);

