<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sms_db';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Admissions table structure:\n";
$result = $conn->query("DESCRIBE admissions;");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n\nAdmission_personal table structure:\n";
$result = $conn->query("DESCRIBE admission_personal;");
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}

echo "\n\nSample admissions data (first 3 records):\n";
$result = $conn->query("SELECT * FROM admissions LIMIT 3;");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    foreach ($row as $key => $value) {
        echo $key . ": " . $value . "\n";
    }
}

$conn->close();
?>
