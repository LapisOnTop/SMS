<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['registrar_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit;
}

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

$appId = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;
if ($appId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid Application ID.']);
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

try {
    $pdo = sim_db();
    
    $data = [
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
        'program_id' => null,
    ];

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
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No student details found for this Application ID.',
            ]);
            exit;
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
        $stmtAddressLegacy = $pdo->prepare(
            'SELECT region, city AS city_municipality, barangay
             FROM applicant_address WHERE application_id = :app LIMIT 1'
        );
        $stmtAddressLegacy->execute(['app' => $appId]);
        $row = $stmtAddressLegacy->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            foreach ($row as $key => $value) {
                if ($value !== null && $value !== '') {
                    $data[$key] = $value;
                }
            }
        }
    }
    
    // Attempt to fetch program_id from selection via applications
    if (sim_table_exists($pdo, 'applications') && sim_table_exists($pdo, 'selection')) {
        $stmtApp = $pdo->prepare('SELECT selection_id FROM applications WHERE application_id = :app LIMIT 1');
        $stmtApp->execute(['app' => $appId]);
        $appRow = $stmtApp->fetch(PDO::FETCH_ASSOC);
        if ($appRow && $appRow['selection_id']) {
            $stmtSel = $pdo->prepare('SELECT course_id FROM selection WHERE selection_id = :sel LIMIT 1');
            $stmtSel->execute(['sel' => $appRow['selection_id']]);
            $selRow = $stmtSel->fetch(PDO::FETCH_ASSOC);
            if ($selRow && $selRow['course_id']) {
                $data['program_id'] = $selRow['course_id'];
            }
        }
    }

    echo json_encode(['success' => true, 'data' => $data]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error while fetching application details.']);
}
