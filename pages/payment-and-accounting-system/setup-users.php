<?php
/**
 * Payment System - User Setup Script
 * Run this ONCE to create test credentials with bcrypt-hashed passwords
 * Delete this file after running!
 */

require_once 'config/database.php';

$db = sms_get_db_connection();
if (!$db) {
	die("Database connection failed!\n");
}

// Define test credentials
$testUsers = [
	[
		'username' => 'Cashier1',
		'password' => 'test1234',
		'role_id' => 3,  // Cashier role
		'is_active' => 1
	],
	[
		'username' => 'Admin1',
		'password' => 'test1234',
		'role_id' => 2,   // Admin role
		'is_active' => 1
	]
];

echo "Setting up test users...\n\n";

foreach ($testUsers as $user) {
	// Hash the password with bcrypt
	$hashedPassword = password_hash($user['password'], PASSWORD_BCRYPT, ['cost' => 10]);
	
	// Check if user already exists
	$checkStmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
	$checkStmt->bind_param('s', $user['username']);
	$checkStmt->execute();
	$result = $checkStmt->get_result();
	
	if ($result->num_rows > 0) {
		echo "❌ User '{$user['username']}' already exists. Skipping...\n";
		$checkStmt->close();
		continue;
	}
	$checkStmt->close();
	
	// Insert new user
	$insertStmt = $db->prepare("
		INSERT INTO users (username, password_hash, role_id, is_active, created_at)
		VALUES (?, ?, ?, ?, NOW())
	");
	
	$insertStmt->bind_param('ssii', $user['username'], $hashedPassword, $user['role_id'], $user['is_active']);
	
	if ($insertStmt->execute()) {
		$userId = $insertStmt->insert_id;
		echo "✅ User created successfully!\n";
		echo "   Username: {$user['username']}\n";
		echo "   Password: {$user['password']}\n";
		echo "   Role ID: {$user['role_id']}\n";
		echo "   User ID: {$userId}\n\n";
	} else {
		echo "❌ Error creating user '{$user['username']}': " . $insertStmt->error . "\n\n";
	}
	
	$insertStmt->close();
}

echo "================================\n";
echo "✅ Setup complete!\n\n";
echo "TEST CREDENTIALS:\n";
echo "================================\n";
echo "CASHIER LOGIN:\n";
echo "  Username: Cashier1\n";
echo "  Password: test1234\n\n";
echo "ADMIN LOGIN:\n";
echo "  Username: Admin1\n";
echo "  Password: test1234\n\n";
echo "⚠️  REMEMBER: Delete this setup script after running!\n";
echo "================================\n";
?>
