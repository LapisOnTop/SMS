<?php
/**
 * Logout Handler
 * Destroys session and redirects to role selection
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Clear session data
session_destroy();

// Redirect to role selection
header('Location: ../role-selection.php');
exit;
?>
