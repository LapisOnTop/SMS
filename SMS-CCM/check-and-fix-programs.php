<?php
require 'config/database.php';
$db = sms_get_db_connection();

echo "=== Current Programs ===\n";
$result = $db->query('SELECT * FROM programs');
$count = $result->num_rows;
echo "Total programs: $count\n";
while($row = $result->fetch_assoc()) {
    echo "- " . $row['program_id'] . ": " . $row['program_name'] . "\n";
}

// Since programs already exist, just fix the INSERT to use only existing columns
echo "\n=== Checking if programs need to be added ===\n";
if ($count == 0) {
    echo "Programs table is empty. Will insert sample programs...\n";
    
    $sql = "INSERT INTO programs (program_name) VALUES 
    ('Bachelor of Science in Computer Science'),
    ('Bachelor of Science in Information Technology'),
    ('Bachelor of Science in Engineering'),
    ('Bachelor of Arts in English'),
    ('Bachelor of Arts in Philosophy'),
    ('Bachelor of Science in Nursing')";
    
    if ($db->query($sql)) {
        echo "✓ Sample programs inserted successfully!\n";
    } else {
        echo "✗ Error inserting programs: " . $db->error . "\n";
    }
} else {
    echo "Programs already exist. Skipping insert.\n";
}
?>
