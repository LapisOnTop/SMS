<?php
require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    payment_api_json(['success' => false, 'message' => 'Invalid request method'], 405);
}

$db = payment_api_require_db();

$stmt = $db->prepare(
    "SELECT
        p.payment_id,
        p.receipt_number,
        p.amount,
        p.payment_method,
        p.payment_status,
        p.payment_date,
        p.reference_id,
        COALESCE(
            TRIM(CONCAT(
                COALESCE(api.first_name, ''), ' ',
                COALESCE(api.middle_name, ''), ' ',
                COALESCE(api.last_name, '')
            )),
            'Unknown Student'
        ) AS student_name,
        COALESCE(s.student_number, CONCAT('REF-', rn.reference_number)) AS student_identifier
     FROM payments p
     LEFT JOIN reference_numbers rn ON p.reference_id = rn.reference_id
     LEFT JOIN applications a ON a.reference_id = rn.reference_id
     LEFT JOIN students s ON s.application_id = a.application_id
     LEFT JOIN applicant_personal_info api ON api.application_id = a.application_id
     ORDER BY p.payment_date DESC"
);

if (!$stmt) {
    payment_api_json(['success' => false, 'message' => 'Failed to prepare transaction query'], 500);
}

$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
$totalPayments = 0.0;
$fullCount = 0;
$partialCount = 0;

while ($row = $result->fetch_assoc()) {
    $studentName = trim(preg_replace('/\s+/', ' ', (string)$row['student_name']));
    if ($studentName === '') {
        $studentName = 'Unknown Student';
    }

    $amount = (float)$row['amount'];
    $status = (string)$row['payment_status'];

    $transactions[] = [
        'id' => 'TX-' . str_pad((string)$row['payment_id'], 6, '0', STR_PAD_LEFT),
        'date' => date('Y-m-d', strtotime((string)$row['payment_date'])),
        'studentId' => (string)$row['student_identifier'],
        'name' => $studentName,
        'type' => 'Payment',
        'method' => (string)$row['payment_method'],
        'amount' => $amount,
        'orNumber' => (string)$row['receipt_number'],
        'status' => $status
    ];

    $totalPayments += $amount;
    if (strcasecmp($status, 'Full') === 0) {
        $fullCount++;
    } elseif (strcasecmp($status, 'Partial') === 0) {
        $partialCount++;
    }
}

$stmt->close();

payment_api_json([
    'success' => true,
    'summary' => [
        'totalTransactions' => count($transactions),
        'totalPayments' => $totalPayments,
        'paymentCount' => count($transactions),
        'fullCount' => $fullCount,
        'partialCount' => $partialCount
    ],
    'transactions' => $transactions
], 200);
