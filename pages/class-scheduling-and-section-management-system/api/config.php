<?php
/* ============================================================
   FILE: api/config.php
   Single database connection manager — sms_db
   ============================================================ */

/* Suppress HTML error output — must be before any output */
ini_set('display_errors', 0);
error_reporting(E_ALL);  // still log, never display

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', 3306);

/* Single unified database */
define('DB_NAME', 'sms_db');

/* CORS & JSON headers */
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

/* ── PDO singleton ───────────────────────────────────────── */
function smsDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $dsn = "mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

/* ── Response helpers ───────────────────────────────────── */
function ok($data, string $msg = ''): void {
    echo json_encode(['success'=>true,'data'=>$data,'message'=>$msg]); exit;
}
function fail(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success'=>false,'data'=>null,'message'=>$msg]); exit;
}
function body(): array {
    static $b = null;
    if ($b === null) $b = json_decode(file_get_contents('php://input'), true) ?? [];
    return $b;
}
