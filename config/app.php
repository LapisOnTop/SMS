<?php

declare(strict_types=1);

/**
 * Base-path helpers.
 *
 * This project is sometimes deployed at:
 * - http://localhost/SMS/... (XAMPP)
 * - https://example.com/...  (domain root)
 *
 * These helpers make links + redirects work in both cases.
 */

function sms_base_path(): string
{
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    // If the app lives under "/SMS", keep that prefix; otherwise assume domain-root install.
    return (stripos($script, '/SMS/') !== false || str_ends_with(strtolower($script), '/sms/index.php'))
        ? '/SMS'
        : '';
}

function sms_url(string $path): string
{
    return sms_base_path() . '/' . ltrim($path, '/');
}

