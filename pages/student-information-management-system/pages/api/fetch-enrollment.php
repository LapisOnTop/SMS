<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['registrar_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

$enrollmentId = isset($_GET['enrollment_id']) ? (int) $_GET['enrollment_id'] : 0;
if ($enrollmentId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Enrollment ID.']);
    exit;
}

function sim_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table'
    );
    $stmt->execute(['table' => $table]);
    return (int) $stmt->fetchColumn() > 0;
}

function sim_column_exists(PDO $pdo, string $table, string $column): bool
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.columns
         WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column'
    );
    $stmt->execute(['table' => $table, 'column' => $column]);
    return (int) $stmt->fetchColumn() > 0;
}

try {
    $pdo = sim_db();
    if (!sim_table_exists($pdo, 'enrollments')) {
        echo json_encode(['success' => false, 'error' => 'Enrollment table is not available.']);
        exit;
    }

    $hasAdmissionId = sim_column_exists($pdo, 'enrollments', 'admission_id');
    $hasProgramId = sim_column_exists($pdo, 'enrollments', 'program_id');
    $hasSemester = sim_column_exists($pdo, 'enrollments', 'semester');
    $hasSchoolYear = sim_column_exists($pdo, 'enrollments', 'school_year');

    $select = ['e.enrollment_id', 'e.student_id'];
    if ($hasAdmissionId) {
        $select[] = 'e.admission_id';
    }
    if ($hasProgramId) {
        $select[] = 'e.program_id';
    }
    if ($hasSemester) {
        $select[] = 'e.semester';
    }
    if ($hasSchoolYear) {
        $select[] = 'e.school_year';
    }

    $stmt = $pdo->prepare(
        'SELECT ' . implode(', ', $select) . ' FROM enrollments e WHERE e.enrollment_id = :eid LIMIT 1'
    );
    $stmt->execute(['eid' => $enrollmentId]);
    $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enrollment) {
        echo json_encode([
            'success' => false,
            'error' => 'No student details found for this Enrollment ID. Make sure it was processed properly.',
        ]);
        exit;
    }

    $data = [
        'enrollment_id' => (int) $enrollment['enrollment_id'],
        'admission_id' => isset($enrollment['admission_id']) ? (int) $enrollment['admission_id'] : null,
        'program_id' => isset($enrollment['program_id']) ? (int) $enrollment['program_id'] : null,
        'school_year' => $enrollment['school_year'] ?? null,
        'semester' => $enrollment['semester'] ?? null,
        'first_name' => null,
        'last_name' => null,
        'middle_name' => null,
        'sex' => null,
        'birth_date' => null,
        'email' => null,
        'contact_number' => null,
        'region' => null,
        'city_municipality' => null,
        'barangay' => null,
    ];

    $studentApplicationId = null;

    if (!empty($enrollment['admission_id']) && sim_table_exists($pdo, 'admission_personal')) {
        $stmtPersonal = $pdo->prepare(
            'SELECT first_name, last_name, middle_name, sex, birth_date, email, contact_number
             FROM admission_personal WHERE admission_id = :aid LIMIT 1'
        );
        $stmtPersonal->execute(['aid' => (int) $enrollment['admission_id']]);
        $row = $stmtPersonal->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data = array_merge($data, $row);
        }
    } elseif (!empty($enrollment['student_id']) && sim_table_exists($pdo, 'students')) {
        $stmtStudent = $pdo->prepare(
            'SELECT application_id
             FROM students WHERE student_id = :sid LIMIT 1'
        );
        $stmtStudent->execute(['sid' => (int) $enrollment['student_id']]);
        $row = $stmtStudent->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $studentApplicationId = isset($row['application_id']) ? (int) $row['application_id'] : null;
            $data = array_merge($data, $row);
        }
    }

    if (!empty($enrollment['admission_id']) && sim_table_exists($pdo, 'admission_address')) {
        $stmtAddress = $pdo->prepare(
            'SELECT region, city_municipality, barangay FROM admission_address WHERE admission_id = :aid LIMIT 1'
        );
        $stmtAddress->execute(['aid' => (int) $enrollment['admission_id']]);
        $row = $stmtAddress->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $data = array_merge($data, $row);
        }
    }

    // Compatibility fallback for current sms_db schema (applicant_* tables).
    if ($studentApplicationId && sim_table_exists($pdo, 'applicant_personal_info')) {
        $stmtApplicant = $pdo->prepare(
            'SELECT first_name, last_name, middle_name, sex, birthdate AS birth_date
             FROM applicant_personal_info WHERE application_id = :app LIMIT 1'
        );
        $stmtApplicant->execute(['app' => $studentApplicationId]);
        $row = $stmtApplicant->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ((empty($data[$key]) || $data[$key] === null) && $value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    if ($studentApplicationId && sim_table_exists($pdo, 'applicant_contact')) {
        $stmtContact = $pdo->prepare(
            'SELECT email_address AS email, contact_number
             FROM applicant_contact WHERE application_id = :app LIMIT 1'
        );
        $stmtContact->execute(['app' => $studentApplicationId]);
        $row = $stmtContact->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ((empty($data[$key]) || $data[$key] === null) && $value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    if ($studentApplicationId && sim_table_exists($pdo, 'applicant_address')) {
        $stmtAddressLegacy = $pdo->prepare(
            'SELECT region, city AS city_municipality, barangay
             FROM applicant_address WHERE application_id = :app LIMIT 1'
        );
        $stmtAddressLegacy->execute(['app' => $studentApplicationId]);
        $row = $stmtAddressLegacy->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ((empty($data[$key]) || $data[$key] === null) && $value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error while fetching enrollment details.']);
}
