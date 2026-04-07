<?php
/**
 * Internal Railway Database Importer
 * This script runs ON Railway to bypass public network blocks.
 */

// Use Railway-provided env variables
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: 3306);
$db   = getenv('MYSQLDATABASE') ?: 'railway';

echo "<h1>Railway Database Setup</h1>";
echo "<pre>";
echo "Target: $user@$host:$port/$db\n";

$mysqli = new mysqli($host, $user, $pass, $db, $port);

if ($mysqli->connect_error) {
    die("Connection Failed: " . $mysqli->connect_error . "\n(Make sure your MySQL service is linked to this app on Railway)");
}

echo "Connection Successful!\n";

$file = 'sms_db.sql';
if (!file_exists($file)) {
    die("Error: $file missing. Make sure you pushed it to GitHub.");
}

echo "Importing $file...\n";
$sql = file_get_contents($file);

// Disable foreign keys
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

$queries = explode(";\n", $sql);
$count = 0;
foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;
    if ($mysqli->query($query)) {
        $count++;
    } else {
        echo "Error on query: " . $mysqli->error . "\n";
    }
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Successfully imported $count queries.\n";

// Fix Registrar1 password
$hash = '$2y$10$lGf5BjZxPfKZkkJPZBaQAeb2q.OhMBMy1tA7qFWl8sl0OkSyyCWTm';
$updateSql = "UPDATE users SET password_hash = '$hash' WHERE username = 'Registrar1'";
if ($mysqli->query($updateSql)) {
    echo "Registrar1 password set to 'Registrar1' successfully.\n";
}

echo "\n<b>SETUP COMPLETE!</b> Please delete this file (railway_import.php) from your repo now for security.";
echo "</pre>";
$mysqli->close();
