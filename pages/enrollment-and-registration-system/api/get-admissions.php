<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

// Check if user is authenticated as registrar
if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role'] !== 'registrar') {
	http_response_code(403);
	echo json_encode(['ok' => false, 'message' => 'Unauthorized access']);
	exit;
}

require_once '../../../config/database.php';

$db = sms_get_db_connection();
if (!$db) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Database connection failed']);
	exit;
}

// Keep application status in sync with paid references.
$syncStatusSql = "
	UPDATE applications a
	INNER JOIN payments p ON p.reference_id = a.reference_id
	SET a.status = 'Enrolled'
	WHERE a.status = 'Validated'
	  AND p.payment_status IN ('Partial', 'Full')
";
$db->query($syncStatusSql);

// Accept both GET and POST
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : (isset($_POST['status']) ? trim($_POST['status']) : 'All Admissions');
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : (isset($_POST['search']) ? trim($_POST['search']) : '');

// Build SQL query
$sql = '
	SELECT 
		a.application_id,
		COALESCE(api.first_name, "N/A") as first_name,
		COALESCE(api.middle_name, "") as middle_name,
		COALESCE(api.last_name, "N/A") as last_name,
		COALESCE(ac.email_address, "N/A") as email_address,
		COALESCE(ac.contact_number, "N/A") as contact_number,
		a.admission_type,
		a.status as admission_status,
		a.created_at,
		rn.reference_number,
		COALESCE(c.course_name, "N/A") as program_name,
		COALESCE(s.year_level, "N/A") as year_level
	FROM applications a
	LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id
	LEFT JOIN applicant_contact ac ON a.application_id = ac.application_id
	LEFT JOIN reference_numbers rn ON a.reference_id = rn.reference_id
	LEFT JOIN selection s ON a.selection_id = s.selection_id
	LEFT JOIN courses c ON s.course_id = c.course_id
	WHERE 1=1
';

// Apply status filter
if ($statusFilter !== 'All Admissions') {
	$statusFilter = $db->real_escape_string($statusFilter);
	$sql .= ' AND a.status = "' . $statusFilter . '"';
}

// Apply search filter
if (!empty($searchFilter)) {
	$searchFilter = $db->real_escape_string($searchFilter);
	$sql .= ' AND (api.first_name LIKE "%' . $searchFilter . '%" 
		OR api.last_name LIKE "%' . $searchFilter . '%" 
		OR ac.email_address LIKE "%' . $searchFilter . '%" 
		OR ac.contact_number LIKE "%' . $searchFilter . '%"
		OR rn.reference_number LIKE "%' . $searchFilter . '%")';
}

$sql .= ' ORDER BY a.created_at DESC LIMIT 100';

$admissions = [];
$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		if (($row['admission_type'] ?? '') === 'Returnee') {
			$row['admission_type'] = 'Old Student';
		}
		$admissions[] = $row;
	}
}

echo json_encode([
	'ok' => true,
	'admissions' => $admissions,
	'count' => count($admissions)
]);
?>
