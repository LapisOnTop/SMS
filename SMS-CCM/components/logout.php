<?php
/**
 * Logout Handler
 * Destroys session and redirects to role selection
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Clear all session data
$_SESSION = [];

if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time() - 42000,
		$params['path'],
		$params['domain'],
		$params['secure'],
		$params['httponly']
	);
}

session_destroy();

// Redirect to the main landing (SMS-CCM has no role-selection.php)
header('Location: /SMS/index.php');
exit;
?>
