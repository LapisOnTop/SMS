<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';
require_once dirname(__DIR__, 4) . '/config/app.php';

// Clear all session data (registrar/user/grade unlock, active student, etc.)
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

// Back to SIM role selection
header('Location: ' . sms_url('pages/student-information-management-system/pages/role-selection.php'));
exit;

