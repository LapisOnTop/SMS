<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$body = read_json_body();
$pass = isset($body['password']) ? (string) $body['password'] : '';
$expected = (string) (app_config()['grade_edit_password'] ?? 'Bestling');

if (!hash_equals($expected, $pass)) {
    json_response(['ok' => false, 'error' => 'Invalid password'], 401);
}

$_SESSION['grade_unlock'] = true;
json_response(['ok' => true, 'gradeUnlock' => true]);
