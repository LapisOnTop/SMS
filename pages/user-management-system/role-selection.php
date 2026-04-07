<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// This system is admin-only for now.
header('Location: ' . sms_url('pages/user-management-system/login.php'));
exit;

