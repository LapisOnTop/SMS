<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Catch any fatal errors and output as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	header('Content-Type: application/json', true);
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Error: ' . $errstr,
		'error_details' => [
			'file' => basename($errfile),
			'line' => $errline,
			'type' => 'PHP Error'
		]
	]);
	exit;
});

set_exception_handler(function($e) {
	header('Content-Type: application/json', true);
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Exception: ' . $e->getMessage(),
		'error_details' => [
			'file' => basename($e->getFile()),
			'line' => $e->getLine(),
			'type' => 'Exception'
		]
	]);
	exit;
});

session_start();
header('Content-Type: application/json');

// Check if user is authenticated as registrar
if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role'] !== 'registrar') {
	http_response_code(403);
	echo json_encode([
		'ok' => false,
		'message' => 'Unauthorized access'
	]);
	exit;
}

// Use absolute path for includes
$config_path = __DIR__ . '/../../../config/database.php';
if (!file_exists($config_path)) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Configuration file not found',
		'debug' => [
			'path' => $config_path,
			'exists' => false
		]
	]);
	exit;
}

require_once $config_path;

$db = sms_get_db_connection();
if (!$db) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Database connection failed'
	]);
	exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode([
		'ok' => false,
		'message' => 'Method not allowed'
	]);
	exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['application_id']) || !isset($input['status'])) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Missing required fields: application_id, status'
	]);
	exit;
}

$application_id = intval($input['application_id']);
$status = trim($input['status']);
$validation_notes = isset($input['validation_notes']) ? trim($input['validation_notes']) : '';

// Validate status enum
$valid_statuses = ['Pending', 'Submitted', 'For Review', 'Approved', 'Validated', 'Paid', 'Enrolled', 'Rejected'];
if (!in_array($status, $valid_statuses)) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Invalid status. Allowed values: ' . implode(', ', $valid_statuses)
	]);
	exit;
}

// Get current application record
$sql = 'SELECT application_id, status FROM applications WHERE application_id = ?';
$stmt = $db->prepare($sql);
if (!$stmt) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Database error: ' . $db->error
	]);
	exit;
}

$stmt->bind_param('i', $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
	echo json_encode([
		'ok' => false,
		'message' => 'Application record not found'
	]);
	exit;
}

$application = $result->fetch_assoc();
$old_status = $application['status'];

// Prevent duplicate status updates
if ($old_status === $status) {
	echo json_encode([
		'ok' => false,
		'message' => 'Application already has status: ' . $status
	]);
	exit;
}

// Update application status
$sql = 'UPDATE applications SET status = ? WHERE application_id = ?';
$stmt = $db->prepare($sql);
if (!$stmt) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Database error: ' . $db->error
	]);
	exit;
}

$stmt->bind_param('si', $status, $application_id);
if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Failed to update application status: ' . $db->error
	]);
	exit;
}

$stmt->close();

// Log the status change (optional - if you have a history table)
// You can add logging here if needed

// Return success response
echo json_encode([
	'ok' => true,
	'message' => 'Application status updated successfully to: ' . $status,
	'data' => [
		'application_id' => $application_id,
		'old_status' => $old_status,
		'new_status' => $status
	]
]);

exit;
?>
