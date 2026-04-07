<?php
session_start();
header('Content-Type: application/json');

// Check if user is authenticated as registrar
if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role'] !== 'registrar') {
	http_response_code(403);
	echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
	exit;
}

require_once '../../../config/database.php';

$db = sms_get_db_connection();
if (!$db) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
	exit;
}

// Get filter parameters from POST request
$input = json_decode(file_get_contents('php://input'), true);
$filter_status = isset($input['status']) ? $input['status'] : 'Pending';
$search_term = isset($input['search']) ? trim($input['search']) : '';

// Fetch applications for review
$applications = [];
$sql = '
	SELECT 
		a.application_id,
		a.ref_code as application_reference,
		a.first_name,
		a.last_name,
		a.email_address,
		a.contact_number,
		a.admission_type,
		a.status as application_status,
		a.created_at as submitted_at
	FROM applications a
	WHERE 1=1
';

if ($filter_status !== 'All') {
	$sql .= " AND a.status = '" . $db->real_escape_string($filter_status) . "'";
}

if (!empty($search_term)) {
	$search = $db->real_escape_string($search_term);
	$sql .= " AND (a.first_name LIKE '%$search%' OR a.last_name LIKE '%$search%' OR a.ref_code LIKE '%$search%' OR a.email_address LIKE '%$search%')";
}

$sql .= ' ORDER BY a.created_at DESC LIMIT 100';

$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$applications[] = $row;
	}
}

// Return JSON response
echo json_encode([
	'ok' => true,
	'applications' => $applications,
	'count' => count($applications)
]);
?>
