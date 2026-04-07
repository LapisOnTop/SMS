<?php

declare(strict_types=1);

/**
 * Registrar: enrollment statistics + status updates on student_management.students.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

if (!is_registrar_session()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$statusMap = [
    'active' => 'Active',
    'inactive' => 'Inactive',
    // DB enum uses On_Leave (underscore), not "On Leave"
    'on_leave' => 'On_Leave',
    'graduated' => 'Graduated',
    'dropped' => 'Dropped',
    'irregular' => 'Irregular',
];

if ($method === 'PUT' && $action === 'update') {
    $studentId = isset($_GET['student_id']) ? (string) $_GET['student_id'] : '';
    if ($studentId === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'student_id required']);
        exit;
    }
    $raw = read_json_body();
    $key = strtolower(preg_replace('/\s+/', '_', (string) ($raw['new_status'] ?? '')));
    if (!isset($statusMap[$key])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    try {
        $repo = new StudentRepository(sim_db());
        $updated = $repo->update($studentId, ['status' => $statusMap[$key]]);
        if (!$updated) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }
    } catch (Throwable $e) {
        http_response_code(503);
        echo json_encode(['success' => false, 'message' => 'Repository unavailable']);
        exit;
    }
    echo json_encode(['success' => true, 'data' => $updated]);
    exit;
}

if ($action !== 'statistics' || $method !== 'GET') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

try {
    $repo = new StudentRepository(sim_db());
    $sum = $repo->getEnrollmentStatusSummary();
} catch (Throwable $e) {
    http_response_code(503);
    echo json_encode(['success' => false, 'message' => 'Repository unavailable']);
    exit;
}

$by = [];
foreach ($sum['byStatus'] as $label => $count) {
    $by[strtolower(preg_replace('/\s+/', '_', $label))] = $count;
}

echo json_encode([
    'success' => true,
    'data' => [
        'total_students' => $sum['total'],
        'active_students' => $sum['active'],
        'inactive_students' => $sum['inactive'],
        'by_status' => $by,
    ],
]);
