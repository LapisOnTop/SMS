<?php
$host = 'junction.proxy.rlwy.net';
$user = 'root';
$pass = 'vBSedJaGwGB1seq1xtbQWtzUoNutREke';
$port = 20543;
$db   = 'railway';
$file = 'sms_db.sql';

echo "Reading $file...\n";
if (!file_exists($file)) {
    die("Error: $file not found.\n");
}
$sql = file_get_contents($file);

echo "Connecting to Railway MySQL...\n";
$mysqli = new mysqli($host, $user, $pass, $db, $port);

if ($mysqli->connect_error) {
    die("Connect Error (" . $mysqli->connect_errno . ") " . $mysqli->connect_error . "\n");
}

echo "Connection successful! Starting import...\n";

// Disable foreign key checks for the import
$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

// Split the SQL into individual queries
// This is a simple split—it might have issues with semicolons inside strings, 
// but for a standard dump it usually works.
$queries = explode(";\n", $sql);
$count = 0;
$total = count($queries);

foreach ($queries as $query) {
    $query = trim($query);
    if (empty($query)) continue;

    if (!$mysqli->query($query)) {
        echo "\nError executing query: " . $mysqli->error . "\n";
        echo "Query: " . substr($query, 0, 100) . "...\n";
    } else {
        $count++;
        if ($count % 50 == 0) echo "Processed $count / $total queries...\n";
    }
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

echo "\nImport finished! $count queries executed successfully.\n";

// Finally, update the Registrar1 password hash
$hash = '$2y$10$lGf5BjZxPfKZkkJPZBaQAeb2q.OhMBMy1tA7qFWl8sl0OkSyyCWTm';
$updateSql = "UPDATE users SET password_hash = '$hash' WHERE username = 'Registrar1'";
if ($mysqli->query($updateSql)) {
    echo "Registrar1 password hash updated successfully!\n";
} else {
    echo "Error updating password: " . $mysqli->error . "\n";
}

$mysqli->close();
echo "Done.\n";
