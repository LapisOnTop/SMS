<?php
require 'config/database.php';
$db = sms_get_db_connection();
$result = $db->query('DESCRIBE programs');
echo "Program Table Columns:\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
