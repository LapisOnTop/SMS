<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = read_json_body();
$action = $body['action'] ?? '';

if ($action === 'status') {
    json_response(['ok' => true, 'registrar' => is_registrar_session()]);
}

if ($action === 'logout') {
    $_SESSION['registrar'] = false;
    unset($_SESSION['registrar']);
    json_response(['ok' => true, 'registrar' => false]);
}

if ($action === 'login') {
    $code = isset($body['code']) ? (string) $body['code'] : '';
    $cfg = app_config();
    $allowed = $cfg['registrar_codes'] ?? ['registrar', 'admin'];
    $ok = false;
    foreach ($allowed as $plain) {
        if (hash_equals((string) $plain, $code)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        json_response(['ok' => false, 'error' => 'Invalid access code'], 401);
    }
    $_SESSION['registrar'] = true;
    json_response(['ok' => true, 'registrar' => true]);
}

json_response(['ok' => false, 'error' => 'Unknown action'], 400);
