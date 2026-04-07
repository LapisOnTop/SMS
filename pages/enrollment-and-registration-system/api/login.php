<?php

require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enrollment_api_json([
        'success' => false,
        'message' => 'Invalid request method'
    ], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    enrollment_api_json([
        'success' => false,
        'message' => 'Invalid request body'
    ], 400);
}

$emailOrUsername = enrollment_api_string($input['email'] ?? $input['username'] ?? '');
$password = enrollment_api_string($input['password'] ?? '');
$role = 'Registrar'; // Only registrar role for this login

if ($emailOrUsername === '' || $password === '') {
    enrollment_api_json([
        'success' => false,
        'message' => 'Email and password are required'
    ], 400);
}

// Get database connection
$db = enrollment_api_require_db();

// Validate only Registrar credentials from users/roles tables.
$user = enrollment_api_validate_login($db, $emailOrUsername, $password, $role);

if (!$user) {
    enrollment_api_json([
        'success' => false,
        'message' => 'Invalid email/username or password'
    ], 401);
}

// Start session and store user info
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role_id'] = $user['role_id'];
$_SESSION['is_active'] = $user['is_active'];
$_SESSION['selected_role'] = 'registrar'; // Store selected role for authorization checks

enrollment_api_json([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role_id' => $user['role_id']
    ]
], 200);
