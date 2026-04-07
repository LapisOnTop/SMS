<?php

declare(strict_types=1);

/**
 * SIM stack: PDO → sms_db (or STUDENT_MGMT_DB) + JSON helpers.
 * Uses same MySQL host/user/pass as sms_get_db_connection(); DB name from STUDENT_MGMT_DB (default sms_db).
 */

require_once dirname(__DIR__, 4) . '/config/database.php';

require_once __DIR__ . '/academic_defaults.php';
require_once __DIR__ . '/StudentRepository.php';
require_once __DIR__ . '/app_boot.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/** SMS-SIM registrar login + legacy SIM registrar flag */
function is_registrar_session(): bool
{
    return !empty($_SESSION['registrar_id']) || !empty($_SESSION['registrar']);
}

function is_grade_unlock_session(): bool
{
    return !empty($_SESSION['grade_unlock']);
}

function sim_app_config(): array
{
    return [
        'grade_edit_password' => getenv('SIM_GRADE_EDIT_PASSWORD') ?: 'Bestling',
        'registrar_codes' => array_values(array_filter(array_map(
            'trim',
            explode(',', getenv('SIM_REGISTRAR_CODES') ?: 'registrar,admin')
        ))),
    ];
}

/** Alias for grade_unlock / registrar SIM APIs */
function app_config(): array
{
    return sim_app_config();
}

function sim_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $name = getenv('STUDENT_MGMT_DB') ?: 'sms_db';
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

/** @return PDO|null Fails quietly if DB missing (e.g. optional bridge in auth-login) */
function sim_db_try(): ?PDO
{
    try {
        return sim_db();
    } catch (Throwable $e) {
        return null;
    }
}

/** Alias expected by migrated SIM API files */
function db(): PDO
{
    return sim_db();
}

function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function require_registrar(): void
{
    if (!is_registrar_session()) {
        json_response(['ok' => false, 'error' => 'Registrar access required'], 403);
    }
}
