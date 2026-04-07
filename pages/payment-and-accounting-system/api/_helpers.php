<?php
/**
 * Payment API Helper Functions
 * Shared utilities for payment system APIs
 */

// Prevent any HTML output on errors
ini_set('display_errors', '0');
error_reporting(E_ALL);

/**
 * Send JSON response and exit
 */
function payment_api_json($data, $status = 200) {
	http_response_code($status);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($data);
	exit;
}

/**
 * Safely get and trim string value
 */
function payment_api_string($value) {
	return trim((string) ($value ?? ''));
}

/**
 * Get database connection with error handling
 */
function payment_api_require_db() {
	require_once __DIR__ . '/../config/database.php';
	$db = sms_get_db_connection();
	if (!$db) {
		payment_api_json([
			'success' => false,
			'message' => 'Database connection failed'
		], 500);
	}
	return $db;
}

/**
 * Set error handler for API responses
 */
function payment_api_set_error_handler() {
	set_error_handler(function($errno, $errstr, $errfile, $errline) {
		payment_api_json([
			'success' => false,
			'message' => $errstr,
			'debug' => ['file' => $errfile, 'line' => $errline]
		], 500);
	}, E_ALL);
}

/**
 * Validate login credentials
 * 
 * @param mysqli $db Database connection
 * @param string $username Username to validate
 * @param string $password Password to validate
 * @param string|null $requiredRole Optional role name to verify
 * @return array|null User data if valid, null otherwise
 */
function payment_api_validate_login($db, $username, $password, $requiredRole = null) {
	// Get user from database
	$sql = 'SELECT user_id, username, password_hash, role_id, is_active FROM users WHERE username = ? LIMIT 1';
	
	$stmt = $db->prepare($sql);
	if (!$stmt) {
		return null;
	}
	
	$stmt->bind_param('s', $username);
	
	if (!$stmt->execute()) {
		return null;
	}
	
	$result = $stmt->get_result();
	$user = $result->fetch_assoc();
	
	if (!$user) {
		return null;
	}
	
	// Check if user is active
	if (!$user['is_active']) {
		return null;
	}
	
	// Verify password - try both bcrypt and plain text
	$passwordValid = false;
	if (!empty($user['password_hash'])) {
		// Try bcrypt (password_verify)
		if (password_verify($password, $user['password_hash'])) {
			$passwordValid = true;
		} else if ($password === $user['password_hash']) {
			// Fallback to plain text comparison
			$passwordValid = true;
		}
	}
	
	if (!$passwordValid) {
		return null;
	}
	
	// If role verification required, check role
	if ($requiredRole !== null) {
		// Get role name
		$roleStmt = $db->prepare('SELECT role_name FROM roles WHERE role_id = ? LIMIT 1');
		if (!$roleStmt) {
			return null;
		}
		
		$roleStmt->bind_param('i', $user['role_id']);
		$roleStmt->execute();
		$roleResult = $roleStmt->get_result();
		$roleData = $roleResult->fetch_assoc();
		
		if (!$roleData || $roleData['role_name'] !== $requiredRole) {
			return null;
		}
	}
	
	return array(
		'user_id' => $user['user_id'],
		'username' => $user['username'],
		'role_id' => $user['role_id'],
		'is_active' => $user['is_active']
	);
}
