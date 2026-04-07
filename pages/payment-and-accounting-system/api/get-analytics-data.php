<?php
require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    payment_api_json(['success' => false, 'message' => 'Invalid request method'], 405);
}

$db = payment_api_require_db();

function get_sum_for_date(mysqli $db, string $dateExpr): float
{
    $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) AS total FROM payments WHERE DATE(payment_date) = {$dateExpr}");
    if (!$stmt) {
        return 0.0;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    return (float)($row['total'] ?? 0);
}

$todayCollection = get_sum_for_date($db, 'CURDATE()');
$yesterdayCollection = get_sum_for_date($db, 'CURDATE() - INTERVAL 1 DAY');
$todayChange = $todayCollection - $yesterdayCollection;

$monthlyStmt = $db->prepare(
    "SELECT COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE MONTH(payment_date) = MONTH(CURDATE())
       AND YEAR(payment_date) = YEAR(CURDATE())"
);
$monthlyStmt->execute();
$monthlyResult = $monthlyStmt->get_result();
$monthlyRow = $monthlyResult->fetch_assoc();
$monthlyCollection = (float)($monthlyRow['total'] ?? 0);
$monthlyStmt->close();

$txCountStmt = $db->prepare(
    "SELECT COUNT(*) AS tx_count
     FROM payments
     WHERE MONTH(payment_date) = MONTH(CURDATE())
       AND YEAR(payment_date) = YEAR(CURDATE())"
);
$txCountStmt->execute();
$txCountResult = $txCountStmt->get_result();
$txCountRow = $txCountResult->fetch_assoc();
$transactionCount = (int)($txCountRow['tx_count'] ?? 0);
$txCountStmt->close();

$pendingStmt = $db->prepare(
        "SELECT COUNT(DISTINCT rn.reference_id) AS pending_count
         FROM reference_numbers rn
         INNER JOIN applications a ON a.reference_id = rn.reference_id
         LEFT JOIN payments p ON p.reference_id = rn.reference_id
         WHERE a.status = 'Validated'
             AND p.payment_id IS NULL"
);
$pendingStmt->execute();
$pendingResult = $pendingStmt->get_result();
$pendingRow = $pendingResult->fetch_assoc();
$pendingCount = (int)($pendingRow['pending_count'] ?? 0);
$pendingStmt->close();

$statusStmt = $db->prepare(
    "SELECT
        COALESCE(SUM(CASE WHEN payment_status = 'Full' THEN 1 ELSE 0 END), 0) AS full_count,
        COALESCE(SUM(CASE WHEN payment_status = 'Partial' THEN 1 ELSE 0 END), 0) AS partial_count
     FROM payments"
);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();
$statusRow = $statusResult->fetch_assoc();
$fullCount = (int)($statusRow['full_count'] ?? 0);
$partialCount = (int)($statusRow['partial_count'] ?? 0);
$statusStmt->close();

$recentStmt = $db->prepare(
    "SELECT
        p.payment_id,
        p.receipt_number,
        p.amount,
        p.payment_status,
        p.payment_date,
        COALESCE(
            TRIM(CONCAT(
                COALESCE(api.first_name, ''), ' ',
                COALESCE(api.middle_name, ''), ' ',
                COALESCE(api.last_name, '')
            )),
            'Unknown Student'
        ) AS student_name
     FROM payments p
     LEFT JOIN reference_numbers rn ON p.reference_id = rn.reference_id
     LEFT JOIN applications a ON a.reference_id = rn.reference_id
     LEFT JOIN applicant_personal_info api ON api.application_id = a.application_id
     ORDER BY p.payment_date DESC
     LIMIT 20"
);
$recentStmt->execute();
$recentResult = $recentStmt->get_result();

$transactions = [];
while ($row = $recentResult->fetch_assoc()) {
    $studentName = trim(preg_replace('/\s+/', ' ', (string)$row['student_name']));
    if ($studentName === '') {
        $studentName = 'Unknown Student';
    }

    $transactions[] = [
        'payment_id' => (int)$row['payment_id'],
        'receipt_number' => (string)$row['receipt_number'],
        'student_name' => $studentName,
        'amount' => (float)$row['amount'],
        'status' => (string)$row['payment_status'],
        'payment_date' => (string)$row['payment_date']
    ];
}
$recentStmt->close();

$dailyStmt = $db->prepare(
    "SELECT DATE(payment_date) AS day_key, COALESCE(SUM(amount), 0) AS total
     FROM payments
     WHERE payment_date >= (CURDATE() - INTERVAL 6 DAY)
     GROUP BY DATE(payment_date)
     ORDER BY DATE(payment_date) ASC"
);
$dailyStmt->execute();
$dailyResult = $dailyStmt->get_result();

$dailyMap = [];
while ($row = $dailyResult->fetch_assoc()) {
    $dailyMap[$row['day_key']] = (float)$row['total'];
}
$dailyStmt->close();

$weekLabels = [];
$weekData = [];
for ($i = 6; $i >= 0; $i--) {
    $ts = strtotime("-{$i} day");
    $dayKey = date('Y-m-d', $ts);
    $weekLabels[] = date('D', $ts);
    $weekData[] = (float)($dailyMap[$dayKey] ?? 0);
}

payment_api_json([
    'success' => true,
    'metrics' => [
        'today' => $todayCollection,
        'todayChange' => $todayChange,
        'monthly' => $monthlyCollection,
        'transactions' => $transactionCount,
        'pending' => $pendingCount,
        'statusBreakdown' => [
            'full' => $fullCount,
            'partial' => $partialCount
        ]
    ],
    'charts' => [
        'week' => [
            'labels' => $weekLabels,
            'data' => $weekData
        ]
    ],
    'transactions' => $transactions
], 200);
