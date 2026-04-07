<?php
// Quick test to see if the database connection works

require_once '../config/database.php';

echo "=== Database Connection Test ===\n\n";

$db = sms_get_db_connection();

if ($db) {
	echo "✅ Database connection successful!\n";
	echo "Connected to database\n\n";
	
	// Test query
	$users = $db->query("SELECT COUNT(*) as count FROM users");
	if ($users) {
		$data = $users->fetch_assoc();
		echo "✅ User count: " . $data['count'] . "\n\n";
		
		// Show test users
		$testUsers = $db->query("SELECT user_id, username, role_id FROM users WHERE username IN ('Cashier1', 'Admin1')");
		if ($testUsers->num_rows > 0) {
			echo "✅ Test users found:\n";
			while ($row = $testUsers->fetch_assoc()) {
				echo "   - " . $row['username'] . " (role_id: " . $row['role_id'] . ")\n";
			}
		} else {
			echo "⚠️  Test users (Cashier1, Admin1) not found in database\n";
			echo "   Please run setup-users.php first!\n";
		}
	} else {
		echo "❌ Query failed: " . $db->error . "\n";
	}
} else {
	echo "❌ Database connection FAILED!\n";
	echo "Check your database credentials and make sure the SMS database exists.\n";
}
?>
