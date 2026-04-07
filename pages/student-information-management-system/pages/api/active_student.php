<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

$repo = new StudentRepository(db());
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response([
        'ok' => true,
        'activeStudentId' => resolve_active_student_id($repo),
        'explicitLogout' => !empty($_SESSION['explicit_logout']),
    ]);
}

if ($method === 'POST') {
    $body = read_json_body();
    $id = isset($body['studentId']) ? (string) $body['studentId'] : '';
    if ($id === '') {
        json_response(['ok' => false, 'error' => 'studentId required'], 400);
    }
    if (!$repo->getById($id)) {
        json_response(['ok' => false, 'error' => 'Student not found'], 404);
    }
    $_SESSION['active_student_id'] = $id;
    $_SESSION['explicit_logout'] = false;
    json_response(['ok' => true, 'boot' => compute_app_boot($repo)]);
}

if ($method === 'DELETE') {
    $_SESSION['explicit_logout'] = true;
    unset($_SESSION['active_student_id']);
    json_response(['ok' => true, 'boot' => compute_app_boot($repo)]);
}

json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
