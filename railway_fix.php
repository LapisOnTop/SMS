<?php
/**
 * Emergency Railway DB Fix
 * This script scans all possible Railway environment variables to find the DB.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Internal Connection Debugger</h1><pre>";

$possible_hosts = [
    getenv('MYSQLHOST'),
    'mysql.railway.internal',
    'mysql',
    '127.0.0.1'
];

$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$port = (int)(getenv('MYSQLPORT') ?: 3306);
$db   = getenv('MYSQLDATABASE') ?: 'railway';

foreach (array_unique(array_filter($possible_hosts)) as $host) {
    echo "Testing $user@$host:$port... ";
    
    try {
        $mysqli = @new mysqli($host, $user, $pass, $db, $port);
        
        if ($mysqli->connect_error) {
            echo "FAILED: " . $mysqli->connect_error . "\n";
        } else {
            echo "<b>SUCCESS!</b>\n";
            
            // If we find it, do the import immediately!
            $file = 'sms_db.sql';
            if (file_exists($file)) {
                echo "Found $file. Starting import...\n";
                $sql = file_get_contents($file);
                $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
                $queries = preg_split("/;[\r\n]+/", $sql);
                $count = 0;
                foreach ($queries as $q) {
                    if (trim($q) && $mysqli->query($q)) $count++;
                }
                $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");
                echo "Imported $count queries.\n";
                
                // Fix Registrar
                $hash = '$2y$10$lGf5BjZxPfKZkkJPZBaQAeb2q.OhMBMy1tA7qFWl8sl0OkSyyCWTm';
                $mysqli->query("UPDATE users SET password_hash = '$hash' WHERE username = 'Registrar1'");
                echo "Registrar1 Password Fixed.\n";
            } else {
                echo "Wait, $file is missing from Git!\n";
            }
            
            echo "\n<b>DATABASE IS NOW READY!</b>";
            $mysqli->close();
            exit;
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nCould not connect to any host. Check if your MySQL service is running on Railway Canvas.";
echo "</pre>";
