<?php
require_once __DIR__ . '/_helpers.php';
payment_api_set_error_handler();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	payment_api_json(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
	$input = json_decode(file_get_contents('php://input'), true);

	if (!$input) {
		payment_api_json(['success' => false, 'message' => 'Invalid request body'], 400);
	}

	$username = payment_api_string($input['username'] ?? '');
	$password = payment_api_string($input['password'] ?? '');

	if ($username === '' || $password === '') {
		payment_api_json(['success' => false, 'message' => 'Username and password are required'], 400);
	}

	$db = payment_api_require_db();
	$user = payment_api_validate_login($db, $username, $password, 'Cashier');

	if (!$user) {
		payment_api_json(['success' => false, 'message' => 'Invalid username or password'], 401);
	}

	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}

	$_SESSION['user_id'] = $user['user_id'];
	$_SESSION['username'] = $user['username'];
	$_SESSION['role_id'] = $user['role_id'];
	$_SESSION['user_role'] = 'cashier';
	$_SESSION['is_active'] = $user['is_active'];

	payment_api_json([
		'success' => true,
		'message' => 'Login successful',
		'user' => [
			'user_id' => $user['user_id'],
			'username' => $user['username'],
			'role_id' => $user['role_id']
		]
	], 200);

} catch (Exception $e) {
	payment_api_json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
