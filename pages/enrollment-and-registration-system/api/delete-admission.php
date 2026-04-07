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
if (!isset($input['application_id'])) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Missing required field: application_id'
	]);
	exit;
}

$application_id = intval($input['application_id']);

// Verify application exists
$sql = 'SELECT application_id FROM applications WHERE application_id = ?';
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

$stmt->close();

// Delete from applications table
$sql = 'DELETE FROM applications WHERE application_id = ?';
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
if (!$stmt->execute()) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Failed to delete application: ' . $db->error
	]);
	exit;
}

$stmt->close();

echo json_encode([
	'ok' => true,
	'message' => 'Application deleted successfully',
	'data' => [
		'application_id' => $application_id
	]
]);
?>
