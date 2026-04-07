<?php

declare(strict_types=1);

// includes/ -> user-management-system/ -> pages/ -> SMS root
require_once dirname(__DIR__, 3) . '/config/app.php';
require_once dirname(__DIR__, 3) . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function ums_db(): mysqli
{
    $db = sms_get_db_connection();
    if (!$db) {
        http_response_code(500);
        echo 'Database connection failed';
        exit;
    }
    return $db;
}

function ums_json(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ums_require_admin(): void
{
    if (empty($_SESSION['user_id']) || (($_SESSION['role'] ?? '') !== 'Admin')) {
        header('Location: ' . sms_url('pages/user-management-system/login.php'));
        exit;
    }
}

function ums_install_schema(mysqli $db): void
{
    // roles: ensure extra roles used by UI exist (safe no-op if already present)
    $wanted = ['Nurse', 'Hr Officer'];
    $existing = [];
    $res = $db->query('SELECT role_name FROM roles');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $existing[strtolower((string) $row['role_name'])] = true;
        }
    }
    foreach ($wanted as $name) {
        if (!isset($existing[strtolower($name)])) {
            $stmt = $db->prepare('INSERT INTO roles (role_name) VALUES (?)');
            if ($stmt) {
                $stmt->bind_param('s', $name);
                @$stmt->execute();
                $stmt->close();
            }
        }
    }

    // user_profiles
    $db->query(
        "CREATE TABLE IF NOT EXISTS user_profiles (
            user_id INT(11) NOT NULL PRIMARY KEY,
            full_name VARCHAR(150) DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            position VARCHAR(120) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );

    // user_activity_logs (audit trail)
    $db->query(
        "CREATE TABLE IF NOT EXISTS user_activity_logs (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            actor_user_id INT(11) DEFAULT NULL,
            actor_username VARCHAR(100) DEFAULT NULL,
            actor_role VARCHAR(50) DEFAULT NULL,
            action VARCHAR(200) NOT NULL,
            module VARCHAR(80) NOT NULL DEFAULT 'User Management',
            target VARCHAR(200) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
    );
}

function ums_log(mysqli $db, string $action, ?string $target = null): void
{
    $actorId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    $actorUser = isset($_SESSION['username']) ? (string) $_SESSION['username'] : null;
    $actorRole = isset($_SESSION['role']) ? (string) $_SESSION['role'] : null;

    $stmt = $db->prepare(
        'INSERT INTO user_activity_logs (actor_user_id, actor_username, actor_role, action, target) VALUES (?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return;
    }
    $stmt->bind_param(
        'issss',
        $actorId,
        $actorUser,
        $actorRole,
        $action,
        $target
    );
    @$stmt->execute();
    $stmt->close();
}

