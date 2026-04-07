<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

$repo = new StudentRepository(db());
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = isset($_GET['action']) ? (string) $_GET['action'] : '';

if ($method === 'GET') {
    // Frontend pages expect action=list => { success, data }.
    if ($action === 'list') {
        if (!is_registrar_session()) {
            json_response(['success' => false, 'message' => 'Registrar access required'], 403);
        }
        json_response(['success' => true, 'data' => $repo->getAll()]);
    }

    $id = isset($_GET['id']) ? (string) $_GET['id'] : '';
    if ($id !== '') {
        $s = $repo->getById($id);
        if (!$s) {
            json_response(['ok' => false, 'error' => 'Not found'], 404);
        }
        $registrar = is_registrar_session();
        $active = resolve_active_student_id($repo);
        if (!$registrar && (string) $active !== (string) $s['id']) {
            json_response(['ok' => false, 'error' => 'Forbidden'], 403);
        }
        json_response(['ok' => true, 'student' => $s]);
    }
    if (!is_registrar_session()) {
        json_response(['ok' => false, 'error' => 'Registrar access required'], 403);
    }
    json_response(['ok' => true, 'students' => $repo->getAll()]);
}

if ($method === 'POST') {
    $body = read_json_body();
    if (($body['fullName'] ?? '') === '') {
        $first = trim((string) ($body['first_name'] ?? $body['firstName'] ?? ''));
        $middle = trim((string) ($body['middle_name'] ?? $body['middleName'] ?? ''));
        $last = trim((string) ($body['last_name'] ?? $body['lastName'] ?? ''));
        $derived = trim(implode(' ', array_values(array_filter([$first, $middle, $last], fn ($v) => $v !== ''))));
        if ($derived === '') {
            json_response(['ok' => false, 'error' => 'fullName required'], 400);
        }
        $body['fullName'] = $derived;
    }
    try {
        $saved = $repo->insert($body);
    } catch (Throwable $e) {
        error_log('SIM students POST failed: ' . $e->getMessage());
        json_response(['ok' => false, 'error' => 'Could not save student: ' . $e->getMessage()], 500);
    }
    $_SESSION['active_student_id'] = $saved['id'];
    $_SESSION['explicit_logout'] = false;
    json_response(['ok' => true, 'student' => $saved, 'boot' => compute_app_boot($repo)]);
}

if ($method === 'PATCH') {
    $body = read_json_body();
    $id = isset($body['id']) ? (string) $body['id'] : '';
    if ($id === '') {
        json_response(['ok' => false, 'error' => 'id required'], 400);
    }
    $existing = $repo->getById($id);
    if (!$existing) {
        json_response(['ok' => false, 'error' => 'Not found'], 404);
    }
    $registrar = is_registrar_session();
    $active = resolve_active_student_id($repo);
    if (!$registrar && (string) $active !== $id) {
        json_response(['ok' => false, 'error' => 'Forbidden'], 403);
    }

    $fields = $body['fields'] ?? null;
    if (!is_array($fields)) {
        json_response(['ok' => false, 'error' => 'fields object required'], 400);
    }

    if (!$registrar) {
        $blocked = ['status' => true];
        foreach ($blocked as $k => $_) {
            if (array_key_exists($k, $fields)) {
                unset($fields[$k]);
            }
        }
    }

    $updated = $repo->update($id, $fields);
    json_response(['ok' => true, 'student' => $updated, 'boot' => compute_app_boot($repo)]);
}

json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
