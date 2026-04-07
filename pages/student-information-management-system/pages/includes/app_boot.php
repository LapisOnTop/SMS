<?php

declare(strict_types=1);

function resolve_active_student_id(StudentRepository $repo): ?string
{
    $all = $repo->getAll();
    $count = count($all);

    if (!empty($_SESSION['explicit_logout'])) {
        $id = $_SESSION['active_student_id'] ?? null;
        if ($id) {
            return $repo->getById((string) $id) ? (string) $id : null;
        }
        return null;
    }

    if (!empty($_SESSION['active_student_id'])) {
        $sid = (string) $_SESSION['active_student_id'];
        return $repo->getById($sid) ? $sid : null;
    }

    if ($count === 0) {
        return null;
    }

    return (string) $all[$count - 1]['id'];
}

function compute_app_boot(StudentRepository $repo): array
{
    $full = $repo->getAll();
    $studentCount = count($full);
    $registrar = is_registrar_session();
    $activeId = resolve_active_student_id($repo);

    if ($registrar) {
        $visible = $full;
    } else {
        $visible = [];
        if ($activeId) {
            $one = $repo->getById($activeId);
            if ($one) {
                $visible = [$one];
            }
        }
    }

    return [
        'studentCount' => $studentCount,
        'students' => $visible,
        'activeStudentId' => $activeId,
        'explicitLogout' => !empty($_SESSION['explicit_logout']),
        'registrar' => $registrar,
        'gradeUnlock' => is_grade_unlock_session(),
    ];
}
