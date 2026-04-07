<?php
/**
 * Internal Railway Database Importer - V2 (Better Debugging)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Report all mysqli errors as exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: 3306);
$db   = getenv('MYSQLDATABASE') ?: 'railway';

echo "<h1>Railway Database Setup (V2)</h1>";
echo "<pre>";
echo "Attempting connection to: $user@$host:$port/$db\n";

try {
    $mysqli = new mysqli();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
    $mysqli->real_connect($host, $user, $pass, $db, $port);
    
    echo "<b>Connection Successful!</b>\n\n";

    $file = 'sms_db.sql';
    if (!file_exists($file)) {
        die("Error: $file missing. Make sure you pushed it to GitHub.");
    }

    echo "Reading $file...\n";
    $sql = file_get_contents($file);

    echo "Importing data...\n";
    $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

    // Better splitting for large SQL
    $queries = preg_split("/;[\r\n]+/", $sql);
    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) continue;
        if ($mysqli->query($query)) {
            $count++;
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

    echo "\n<b>SETUP COMPLETE!</b> Please delete this file (railway_import.php) for security.";
    $mysqli->close();

} catch (Exception $e) {
    echo "\n<b style='color:red'>DATABASE ERROR:</b>\n" . $e->getMessage();
    echo "\n\n<b>Possible solutions:</b>\n";
    echo "1. Double check your MySQL service is running on Railway.\n";
    echo "2. Make sure the MySQL service is linked to this SMS service.\n";
    echo "3. Try re-importing using Option 1 (External Client) if this continues to fail.";
}
echo "</pre>";

