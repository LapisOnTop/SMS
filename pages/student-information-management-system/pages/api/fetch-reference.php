<?php

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['registrar_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

$ref = isset($_GET['reference_number']) ? trim((string) $_GET['reference_number']) : '';
if ($ref === '') {
    echo json_encode(['success' => false, 'error' => 'Invalid Reference Number.']);
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

    if (!sim_table_exists($pdo, 'reference_numbers') || !sim_table_exists($pdo, 'applications')) {
        echo json_encode(['success' => false, 'error' => 'Reference lookup is not available (missing tables).']);
        exit;
    }

    $stmtRef = $pdo->prepare(
        'SELECT reference_id FROM reference_numbers WHERE reference_number = :ref LIMIT 1'
    );
    $stmtRef->execute(['ref' => $ref]);
    $refRow = $stmtRef->fetch(PDO::FETCH_ASSOC);
    if (!$refRow) {
        echo json_encode(['success' => false, 'error' => 'Reference number not found.']);
        exit;
    }

    $referenceId = (int) $refRow['reference_id'];

    $hasProgramId = sim_column_exists($pdo, 'applications', 'program_id');
    $stmtApp = $pdo->prepare(
        $hasProgramId
            ? 'SELECT application_id, program_id, selection_id FROM applications WHERE reference_id = :rid LIMIT 1'
            : 'SELECT application_id, selection_id FROM applications WHERE reference_id = :rid LIMIT 1'
    );
    $stmtApp->execute(['rid' => $referenceId]);
    $appRow = $stmtApp->fetch(PDO::FETCH_ASSOC);
    if (!$appRow) {
        echo json_encode(['success' => false, 'error' => 'No application found for this reference number.']);
        exit;
    }

    $appId = (int) $appRow['application_id'];

    // Reuse the existing application fetch logic by replicating key parts here.
    $data = [
        'reference_number' => $ref,
        'reference_id' => $referenceId,
        'application_id' => $appId,
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
        'program_id' => isset($appRow['program_id']) ? (int) $appRow['program_id'] : null,
        'program_name' => null,
    ];

    // If applications has no program_id, try selection.course_id (current sms_db.sql schema).
    if (!$data['program_id'] && !empty($appRow['selection_id']) && sim_table_exists($pdo, 'selection')) {
        $stmtSel = $pdo->prepare('SELECT course_id FROM selection WHERE selection_id = :sid LIMIT 1');
        $stmtSel->execute(['sid' => (int) $appRow['selection_id']]);
        $sel = $stmtSel->fetch(PDO::FETCH_ASSOC);
        if ($sel && !empty($sel['course_id'])) {
            $data['program_id'] = (int) $sel['course_id'];
        }
    }

    if ($data['program_id'] && sim_table_exists($pdo, 'courses')) {
        $stmtCourse = $pdo->prepare('SELECT course_name FROM courses WHERE course_id = :cid LIMIT 1');
        $stmtCourse->execute(['cid' => (int) $data['program_id']]);
        $c = $stmtCourse->fetch(PDO::FETCH_ASSOC);
        if ($c && !empty($c['course_name'])) {
            $data['program_name'] = (string) $c['course_name'];
        }
    }

    if (sim_table_exists($pdo, 'applicant_personal_info')) {
        $stmtApplicant = $pdo->prepare(
            'SELECT first_name, last_name, middle_name, sex, birthdate AS birth_date
             FROM applicant_personal_info WHERE application_id = :app LIMIT 1'
        );
        $stmtApplicant->execute(['app' => $appId]);
        $row = $stmtApplicant->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ($value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    if (sim_table_exists($pdo, 'applicant_contact')) {
        $stmtContact = $pdo->prepare(
            'SELECT email_address AS email, contact_number
             FROM applicant_contact WHERE application_id = :app LIMIT 1'
        );
        $stmtContact->execute(['app' => $appId]);
        $row = $stmtContact->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ($value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    if (sim_table_exists($pdo, 'applicant_address')) {
        $stmtAddress = $pdo->prepare(
            'SELECT region, city AS city_municipality, barangay
             FROM applicant_address WHERE application_id = :app LIMIT 1'
        );
        $stmtAddress->execute(['app' => $appId]);
        $row = $stmtAddress->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ($value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error while fetching reference details.']);
}

