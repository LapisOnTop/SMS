<?php

require_once __DIR__ . '/../../../config/database.php';

function enrollment_api_json($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function enrollment_api_require_db()
{
    $db = sms_get_db_connection();
    if (!$db) {
        enrollment_api_json([
            'ok' => false,
            'message' => 'Database connection failed. Check DB credentials and import pamana.sql first.'
        ], 500);
    }

    return $db;
}

function enrollment_api_string($value)
{
    return trim((string) ($value ?? ''));
}

function enrollment_api_nullable_string($value)
{
    $normalized = trim((string) ($value ?? ''));
    return $normalized === '' ? null : $normalized;
}

function enrollment_api_bool($value)
{
    return !empty($value) ? 1 : 0;
}

function enrollment_api_application_reference()
{
    return 'SMS-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

function enrollment_api_generate_reference_number($db)
{
    // Format: SMS-ABC1D234
    // ABC = 3 random letters
    // 1 = 1 random digit
    // D = 1 random letter
    // 234 = 3 random digits
    
    while (true) {
        $letters1 = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90)); // 3 letters
        $digit1 = rand(0, 9); // 1 digit
        $letter2 = chr(rand(65, 90)); // 1 letter
        $digits2 = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT); // 3 digits
        
        $referenceNumber = "SMS-{$letters1}{$digit1}{$letter2}{$digits2}";
        
        // Insert into reference_numbers table
        $sql = 'INSERT INTO reference_numbers (reference_number) VALUES (?)';
        $stmt = $db->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare reference number query: ' . $db->error);
        }
        
        $stmt->bind_param('s', $referenceNumber);
        
        if ($stmt->execute()) {
            $referenceId = $db->insert_id;
            $stmt->close();
            return [
                'number' => $referenceNumber,
                'id' => $referenceId
            ];
        }
        
        $stmt->close();
        // If unique constraint fails, try again with different random values
    }
}

function enrollment_api_http_get_json($url)
{
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 4,
            'ignore_errors' => true
        ]
    ]);

    $raw = @file_get_contents($url, false, $context);
    if ($raw === false) {
        return null;
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : null;
}

function enrollment_api_resolve_region_name($value)
{
    $candidate = trim((string) $value);
    if ($candidate === '' || preg_match('/[A-Za-z]/', $candidate)) {
        return $candidate;
    }

    $data = enrollment_api_http_get_json('https://psgc.gitlab.io/api/regions/' . rawurlencode($candidate) . '.json');
    if (!is_array($data)) {
        return $candidate;
    }

    return trim((string) ($data['regionName'] ?? $data['name'] ?? $candidate));
}

function enrollment_api_resolve_city_name($value)
{
    $candidate = trim((string) $value);
    if ($candidate === '' || preg_match('/[A-Za-z]/', $candidate)) {
        return $candidate;
    }

    $data = enrollment_api_http_get_json('https://psgc.gitlab.io/api/cities-municipalities/' . rawurlencode($candidate) . '.json');
    if (!is_array($data)) {
        return $candidate;
    }

    return trim((string) ($data['name'] ?? $candidate));
}

function enrollment_api_resolve_barangay_name($value)
{
    $candidate = trim((string) $value);
    if ($candidate === '' || preg_match('/[A-Za-z]/', $candidate)) {
        return $candidate;
    }

    $data = enrollment_api_http_get_json('https://psgc.gitlab.io/api/barangays/' . rawurlencode($candidate) . '.json');
    if (!is_array($data)) {
        return $candidate;
    }

    return trim((string) ($data['name'] ?? $candidate));
}

// Email notification functions
function enrollment_api_send_email($to, $subject, $body)
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: admissions@pamana-university.edu" . "\r\n";

    return mail($to, $subject, $body, $headers);
}

function enrollment_api_send_status_notification($email, $firstName, $status, $applicationReference)
{
    $statusMessages = [
        'Submitted' => 'Your application has been submitted successfully. The registrar will review it within 3-5 business days.',
        'For Review' => 'Your application is currently being reviewed by the registrar. You will be notified once a decision is made.',
        'Approved' => 'Congratulations! Your application has been approved. Please proceed to payment to complete your enrollment.',
        'Paid' => 'Your payment has been verified. Your enrollment is now complete! Check your student portal for class schedules.',
        'Enrolled' => 'You are now officially enrolled. Log in to your student portal to view your class schedule and course materials.',
        'Rejected' => 'Unfortunately, your application has been rejected. Please contact the admissions office for more information.'
    ];

    $statusMessage = $statusMessages[$status] ?? 'Your application status has been updated.';
    $subject = "Application Status Update - " . $applicationReference;

    $body = "
    <html>
    <body>
        <h2>Welcome to Pamana University</h2>
        <p>Dear {$firstName},</p>
        <p>Your application <strong>{$applicationReference}</strong> has been updated.</p>
        <p><strong>Current Status:</strong> {$status}</p>
        <p>{$statusMessage}</p>
        <p>If you have any questions, please contact our admissions office.</p>
        <p>Best regards,<br/>Pamana University Admissions Team</p>
    </body>
    </html>";

    return enrollment_api_send_email($email, $subject, $body);
}

// Audit trail logging function
function enrollment_api_log_status_change($db, $applicationId, $oldStatus, $newStatus, $changedBy, $notes)
{
    $sql = '
        INSERT INTO application_status_history 
        (application_id, old_status, new_status, changed_by, notes, changed_at)
        VALUES (?, ?, ?, ?, ?, NOW())
    ';

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('issss', $applicationId, $oldStatus, $newStatus, $changedBy, $notes);
    return $stmt->execute();
}

// Login validation function
function enrollment_api_validate_login($db, $emailOrUsername, $password, $roleName = null)
{
    if ($roleName !== null && $roleName !== '') {
        $sql = '
            SELECT u.user_id, u.username, u.password_hash, u.role_id, u.is_active
            FROM users u
            INNER JOIN roles r ON r.role_id = u.role_id
            WHERE (u.username = ? OR u.username = ?)
              AND LOWER(r.role_name) = LOWER(?)
            LIMIT 1
        ';
    } else {
        $sql = 'SELECT user_id, username, password_hash, role_id, is_active FROM users WHERE (username = ? OR username = ?) LIMIT 1';
    }
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    if ($roleName !== null && $roleName !== '') {
        $stmt->bind_param('sss', $emailOrUsername, $emailOrUsername, $roleName);
    } else {
        $stmt->bind_param('ss', $emailOrUsername, $emailOrUsername);
    }
    
    if (!$stmt->execute()) {
        return null;
    }
    
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        return null;
    }
    
    // Check if user is active
    if (!$user['is_active']) {
        return null;
    }
    
    $storedPassword = (string) ($user['password_hash'] ?? '');
    $passwordOk = false;

    // Support proper hashes and legacy plaintext entries, then migrate legacy values.
    if ($storedPassword !== '' && password_get_info($storedPassword)['algo'] !== null) {
        $passwordOk = password_verify($password, $storedPassword);
        if ($passwordOk && password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            if ($newHash !== false) {
                $rehashStmt = $db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ? LIMIT 1');
                if ($rehashStmt) {
                    $rehashStmt->bind_param('si', $newHash, $user['user_id']);
                    $rehashStmt->execute();
                    $rehashStmt->close();
                }
            }
        }
    } else {
        $passwordOk = hash_equals($storedPassword, $password);
        if ($passwordOk) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            if ($newHash !== false) {
                $migrateStmt = $db->prepare('UPDATE users SET password_hash = ? WHERE user_id = ? LIMIT 1');
                if ($migrateStmt) {
                    $migrateStmt->bind_param('si', $newHash, $user['user_id']);
                    $migrateStmt->execute();
                    $migrateStmt->close();
                }
            }
        }
    }

    if (!$passwordOk) {
        return null;
    }
    
    return array(
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'role_id' => $user['role_id'],
        'is_active' => $user['is_active']
    );
}
