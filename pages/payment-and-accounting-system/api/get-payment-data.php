<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once '../../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$reference_number = isset($_GET['reference']) ? trim($_GET['reference']) : '';

if (empty($reference_number)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Reference number is required']);
    exit;
}

$conn = sms_get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get reference ID from reference_numbers table
$refStmt = $conn->prepare("SELECT reference_id FROM reference_numbers WHERE reference_number = ?");
$refStmt->bind_param('s', $reference_number);
$refStmt->execute();
$refResult = $refStmt->get_result();
$refRow = $refResult->fetch_assoc();
$refStmt->close();

if (!$refRow) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Reference number not found']);
    exit;
}

$reference_id = $refRow['reference_id'];

// Get application details
$appStmt = $conn->prepare("
    SELECT 
        a.application_id,
        a.selection_id,
        a.admission_type,
        a.status,
        a.reference_id,
        api.first_name,
        api.middle_name,
        api.last_name,
        s.course_id,
        s.year_level,
        c.course_name
    FROM applications a
    LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id
    LEFT JOIN selection s ON a.selection_id = s.selection_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    WHERE a.reference_id = ?
");
$appStmt->bind_param('i', $reference_id);
$appStmt->execute();
$appResult = $appStmt->get_result();
$appRow = $appResult->fetch_assoc();
$appStmt->close();

if (!$appRow) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Application not found']);
    exit;
}

$application_id = $appRow['application_id'];
$year_level = $appRow['year_level'] ?? 1;

// Get subject preselection and subjects
$preselStmt = $conn->prepare("
    SELECT 
        sp.preselection_id,
        spd.subject_id,
        subj.subject_code,
        subj.subject_name,
        subj.units,
        subj.price
    FROM subject_preselection sp
    LEFT JOIN subject_preselection_details spd ON sp.preselection_id = spd.preselection_id
    LEFT JOIN subjects subj ON spd.subject_id = subj.subject_id
    WHERE sp.application_id = ?
    ORDER BY subj.subject_code
");
$preselStmt->bind_param('i', $application_id);
$preselStmt->execute();
$preselResult = $preselStmt->get_result();

$subjects = [];
$totalUnits = 0;
$totalSubjectCost = 0;

while ($row = $preselResult->fetch_assoc()) {
    if ($row['subject_id']) {
        $subjects[] = [
            'subject_id' => $row['subject_id'],
            'code' => $row['subject_code'],
            'name' => $row['subject_name'],
            'units' => (int)$row['units'],
            'price' => (float)$row['price']
        ];
        $totalUnits += (int)$row['units'];
        $totalSubjectCost += (float)$row['price'];
    }
}
$preselStmt->close();

// Get fees from fees table
$feesStmt = $conn->prepare("SELECT fee_id, fee_name, amount FROM fees WHERE is_active = 1");
$feesStmt->execute();
$feesResult = $feesStmt->get_result();

$fees = [];
$totalFees = 0;

while ($row = $feesResult->fetch_assoc()) {
    $fees[] = [
        'fee_id' => $row['fee_id'],
        'name' => $row['fee_name'],
        'amount' => (float)$row['amount']
    ];
    $totalFees += (float)$row['amount'];
}
$feesStmt->close();

// Calculate grand total
$grandTotal = $totalSubjectCost + $totalFees;

// Prepare response
$responseData = [
    'ok' => true,
    'application' => [
        'application_id' => $application_id,
        'reference_number' => $reference_number,
        'student_name' => trim($appRow['first_name'] . ' ' . $appRow['middle_name'] . ' ' . $appRow['last_name']),
        'course' => $appRow['course_name'] . ' - Year ' . $year_level,
        'admission_type' => $appRow['admission_type'],
        'year_level' => $year_level,
        'status' => $appRow['status']
    ],
    'subjects' => $subjects,
    'subject_summary' => [
        'total_units' => $totalUnits,
        'total_cost' => $totalSubjectCost,
        'subject_count' => count($subjects)
    ],
    'fees' => $fees,
    'fee_summary' => [
        'total_fees' => $totalFees
    ],
    'grand_total' => $grandTotal
];

echo json_encode($responseData);
?>
