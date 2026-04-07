<?php
/**
 * Enrollment System Database Setup Script
 * This script creates all necessary tables for the enrollment and registration system
 */

require_once __DIR__ . '/config/database.php';

$db = sms_get_db_connection();

if (!$db) {
    die("Fatal: Cannot connect to database. Please check your database credentials in config/database.php\n");
}

echo "Connected to database successfully.\n";

// Read the SQL schema file
$sqlFile = __DIR__ . '/enrollment-schema.sql';
if (!file_exists($sqlFile)) {
    die("Error: enrollment-schema.sql not found at: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Remove comments and split properly
$lines = explode("\n", $sql);
$statements = [];
$currentStatement = '';

foreach ($lines as $line) {
    // Skip comments and empty lines
    $line = trim($line);
    if (empty($line) || str_starts_with($line, '--')) {
        continue;
    }
    
    $currentStatement .= ' ' . $line;
    
    // Check if statement ends with semicolon
    if (str_ends_with($line, ';')) {
        $statements[] = trim($currentStatement);
        $currentStatement = '';
    }
}

// Add any remaining statement
if (!empty($currentStatement)) {
    $statements[] = trim($currentStatement);
}

$successCount = 0;
$errorCount = 0;

foreach ($statements as $statement) {
    $statement = trim($statement);
    if (empty($statement)) continue;
    
    // Remove trailing semicolon if present (we'll add it back)
    $statement = rtrim($statement, ';');
    if (empty($statement)) continue;
    
    if ($db->query($statement . ';')) {
        $successCount++;
        $shortStatement = substr($statement, 0, 60);
        echo "✓ Executed: " . $shortStatement . "...\n";
    } else {
        // For INSERT statements, if they fail, log but continue
        if (strpos(strtoupper($statement), 'INSERT IGNORE') === 0) {
            $successCount++;
            echo "◇ Skipped insert (already exists): " . substr($statement, 0, 60) . "...\n";
        } else {
            $errorCount++;
            echo "✗ Error: " . $db->error . "\n";
            echo "  Statement: " . substr($statement, 0, 100) . "...\n";
        }
    }
}

echo "\n=== Setup Summary ===\n";
echo "Successful statements: $successCount\n";
echo "Failed statements: $errorCount\n";

if ($errorCount === 0) {
    echo "\n✓ Database setup completed successfully!\n";
    echo "The enrollment system tables are ready to use.\n";
} else {
    echo "\n⚠ Some statements failed. Please review the errors above.\n";
}

$db->close();
?>
