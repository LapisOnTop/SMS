<?php

function sms_get_db_connection()
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        // Reuse live connection; auto-recover if stale.
        if (@$conn->ping()) {
            return $conn;
        }
        $conn = null;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dbCandidates = [];
    $envDb = getenv('DB_NAME');
    if ($envDb) {
        $dbCandidates[] = $envDb;
    }
    $dbCandidates[] = 'sms_db';

    foreach ($dbCandidates as $dbName) {
        $try = @new mysqli($host, $user, $pass, $dbName);
        if (!$try->connect_errno) {
            $try->set_charset('utf8mb4');
            $conn = $try;
            return $conn;
        }
    }

    return null;
}
