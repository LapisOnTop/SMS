<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up session before any includes
session_start();
$_SESSION['selected_role'] = 'registrar';
$_SESSION['username'] = 'Registrar';

// Change to the correct directory so relative paths work
chdir('c:\xampp\htdocs\SMS\pages\enrollment-and-registration-system\admin');

echo "Dashboard Theme Verification:\n";
echo "================================\n";

ob_start();
/* Suppress output buffering capture to allow the file to run normally */
include 'dashboard.php';
$content = ob_get_clean();

$checks = [
    'Poppins font' => strpos($content, 'Poppins') !== false,
    'Primary color #3f69ff' => strpos($content, '#3f69ff') !== false,
    'Dark background #1e2532' => strpos($content, '#1e2532') !== false,
    'Gradient background' => strpos($content, 'linear-gradient(135deg, #e0e0e0, #cfcfcf)') !== false,
    'Status badges' => strpos($content, 'status-pending') !== false && strpos($content, 'status-validated') !== false,
    'Total Admissions' => strpos($content, 'Total Admissions') !== false,
    'Sidebar menu' => strpos($content, 'Enrollment Reports') !== false,
    'File size' => strlen($content) > 10000
];

foreach ($checks as $label => $result) {
    $symbol = $result ? "[PASS]" : "[FAIL]";
    echo $symbol . " " . $label . "\n";
}

echo "================================\n";
echo "Content size: " . strlen($content) . " bytes\n";
?>
