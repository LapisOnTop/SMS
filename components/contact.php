<?php
// ==========================================
// BCP Landing Page - PHP Backend
// Contact Form & Enrollment Handler
// ==========================================

// Set JSON header for AJAX responses
header('Content-Type: application/json');

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);
ini_set('error_log', 'php-errors.log');

// CORS headers (if needed for local testing)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';

// Response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

$landingDb = sms_get_db_connection();
if (!$landingDb) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

ensureLandingFormTables($landingDb);

try {
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get form type
    $formType = isset($_POST['form_type']) ? sanitize($_POST['form_type']) : '';

    // Route to appropriate handler
    switch ($formType) {
        case 'enrollment':
            handleEnrollmentForm();
            break;
        case 'contact':
            handleContactForm();
            break;
        default:
            throw new Exception('Invalid form type');
    }

} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    echo json_encode($response);
    exit;
}

// ==========================================
// Handle Enrollment Form Submission
// ==========================================
function handleEnrollmentForm() {
    global $response, $landingDb;

    try {
        // Get and validate form data
        $fullName = sanitize($_POST['fullName'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $program = sanitize($_POST['program'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        // Validation
        $errors = [];

        if (empty($fullName)) {
            $errors[] = 'Full name is required';
        } elseif (strlen($fullName) < 3) {
            $errors[] = 'Full name must be at least 3 characters';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($phone)) {
            $errors[] = 'Phone number is required';
        } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {
            $errors[] = 'Invalid phone number format';
        }

        if (empty($program)) {
            $errors[] = 'Please select a program';
        }

        // If there are validation errors
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = implode(', ', $errors);
            echo json_encode($response);
            exit;
        }

        // Prepare data for storage/email
        $enrollmentData = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'program' => $program,
            'message' => $message,
            'submitted_at' => date('Y-m-d H:i:s'),
            'ip_address' => getClientIP()
        ];

        saveEnrollmentToDatabase($landingDb, $enrollmentData);

        // Send email notification
        sendEnrollmentEmail($enrollmentData);

        // Success response
        $response['success'] = true;
        $response['message'] = 'Your enrollment application has been submitted successfully!';
        echo json_encode($response);

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'An error occurred while processing your enrollment. Please try again.';
        error_log('Enrollment Error: ' . $e->getMessage());
        echo json_encode($response);
    }
}

// ==========================================
// Handle Contact Form Submission
// ==========================================
function handleContactForm() {
    global $response, $landingDb;

    try {
        // Get and validate form data
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Name is required';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Name must be at least 3 characters';
        }

        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($subject)) {
            $errors[] = 'Subject is required';
        }

        if (empty($message)) {
            $errors[] = 'Message is required';
        } elseif (strlen($message) < 10) {
            $errors[] = 'Message must be at least 10 characters';
        }

        // If there are validation errors
        if (!empty($errors)) {
            $response['errors'] = $errors;
            $response['message'] = implode(', ', $errors);
            echo json_encode($response);
            exit;
        }

        // Prepare data for storage/email
        $contactData = [
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'submitted_at' => date('Y-m-d H:i:s'),
            'ip_address' => getClientIP()
        ];

        saveContactToDatabase($landingDb, $contactData);

        // Send email notification
        sendContactEmail($contactData);

        // Success response
        $response['success'] = true;
        $response['message'] = 'Thank you for your message! We will get back to you soon.';
        echo json_encode($response);

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'An error occurred while sending your message. Please try again.';
        error_log('Contact Error: ' . $e->getMessage());
        echo json_encode($response);
    }
}

// ==========================================
// Save Enrollment Data to File
// ==========================================
function saveEnrollmentToFile($data) {
    $filename = 'enrollments.txt';
    
    // Create formatted entry
    $entry = "\n" . str_repeat('=', 50) . "\n";
    $entry .= "ENROLLMENT APPLICATION\n";
    $entry .= str_repeat('=', 50) . "\n";
    $entry .= "Date: " . $data['submitted_at'] . "\n";
    $entry .= "Name: " . $data['full_name'] . "\n";
    $entry .= "Email: " . $data['email'] . "\n";
    $entry .= "Phone: " . $data['phone'] . "\n";
    $entry .= "Program: " . $data['program'] . "\n";
    $entry .= "Message: " . $data['message'] . "\n";
    $entry .= "IP Address: " . $data['ip_address'] . "\n";
    $entry .= str_repeat('=', 50) . "\n";
    
    // Append to file
    file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX);
}

// ==========================================
// Save Contact Data to File
// ==========================================
function saveContactToFile($data) {
    $filename = 'contacts.txt';
    
    // Create formatted entry
    $entry = "\n" . str_repeat('=', 50) . "\n";
    $entry .= "CONTACT MESSAGE\n";
    $entry .= str_repeat('=', 50) . "\n";
    $entry .= "Date: " . $data['submitted_at'] . "\n";
    $entry .= "Name: " . $data['name'] . "\n";
    $entry .= "Email: " . $data['email'] . "\n";
    $entry .= "Subject: " . $data['subject'] . "\n";
    $entry .= "Message: " . $data['message'] . "\n";
    $entry .= "IP Address: " . $data['ip_address'] . "\n";
    $entry .= str_repeat('=', 50) . "\n";
    
    // Append to file
    file_put_contents($filename, $entry, FILE_APPEND | LOCK_EX);
}

// ==========================================
// Send Enrollment Email
// ==========================================
function sendEnrollmentEmail($data) {
    // Email configuration
    $to = 'admissions@bcp.edu.ph'; // Replace with actual email
    $subject = 'New Enrollment Application - ' . $data['program'];
    
    // Email body
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Enrollment Application</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Full Name:</span><br>
                    {$data['full_name']}
                </div>
                <div class='field'>
                    <span class='label'>Email:</span><br>
                    {$data['email']}
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span><br>
                    {$data['phone']}
                </div>
                <div class='field'>
                    <span class='label'>Program:</span><br>
                    {$data['program']}
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    {$data['message']}
                </div>
                <div class='field'>
                    <span class='label'>Submitted:</span><br>
                    {$data['submitted_at']}
                </div>
            </div>
            <div class='footer'>
                This email was sent from BCP Enrollment Form<br>
                IP Address: {$data['ip_address']}
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: BCP Website <noreply@bcp.edu.ph>\r\n";
    $headers .= "Reply-To: {$data['email']}\r\n";
    
    // Send email (uncomment in production)
    // mail($to, $subject, $body, $headers);
    
    // For testing, log instead of sending
    error_log("Enrollment email would be sent to: $to");
}

// ==========================================
// Send Contact Email
// ==========================================
function sendContactEmail($data) {
    // Email configuration
    $to = 'info@bcp.edu.ph'; // Replace with actual email
    $subject = 'New Contact Message - ' . $data['subject'];
    
    // Email body
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2563eb; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; margin-top: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #2563eb; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Message</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span><br>
                    {$data['name']}
                </div>
                <div class='field'>
                    <span class='label'>Email:</span><br>
                    {$data['email']}
                </div>
                <div class='field'>
                    <span class='label'>Subject:</span><br>
                    {$data['subject']}
                </div>
                <div class='field'>
                    <span class='label'>Message:</span><br>
                    {$data['message']}
                </div>
                <div class='field'>
                    <span class='label'>Submitted:</span><br>
                    {$data['submitted_at']}
                </div>
            </div>
            <div class='footer'>
                This email was sent from BCP Contact Form<br>
                IP Address: {$data['ip_address']}
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: BCP Website <noreply@bcp.edu.ph>\r\n";
    $headers .= "Reply-To: {$data['email']}\r\n";
    
    // Send email (uncomment in production)
    // mail($to, $subject, $body, $headers);
    
    // For testing, log instead of sending
    error_log("Contact email would be sent to: $to");
}

// ==========================================
// Helper Functions
// ==========================================

// Sanitize input data
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Get client IP address
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Validate CSRF token (implement if needed)
function validateCSRFToken($token) {
    // Implement CSRF validation logic here
    return true;
}

// Rate limiting function (basic implementation)
function checkRateLimit($identifier, $limit = 5, $period = 3600) {
    $filename = 'rate_limit.json';
    $data = [];
    
    if (file_exists($filename)) {
        $data = json_decode(file_get_contents($filename), true);
    }
    
    $now = time();
    $key = md5($identifier);
    
    if (!isset($data[$key])) {
        $data[$key] = [];
    }
    
    // Clean old entries
    $data[$key] = array_filter($data[$key], function($timestamp) use ($now, $period) {
        return ($now - $timestamp) < $period;
    });
    
    // Check limit
    if (count($data[$key]) >= $limit) {
        return false;
    }
    
    // Add new entry
    $data[$key][] = $now;
    
    // Save data
    file_put_contents($filename, json_encode($data), LOCK_EX);
    
    return true;
}

function ensureLandingFormTables(mysqli $db) {
    $createEnrollmentSql = "
        CREATE TABLE IF NOT EXISTS website_enrollment_submissions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(150) NOT NULL,
            email VARCHAR(150) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            program VARCHAR(120) NOT NULL,
            message TEXT NULL,
            status ENUM('Pending','Validated','Enrolled') NOT NULL DEFAULT 'Pending',
            submitted_at DATETIME NOT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $createContactSql = "
        CREATE TABLE IF NOT EXISTS website_contact_submissions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            sender_name VARCHAR(150) NOT NULL,
            sender_email VARCHAR(150) NOT NULL,
            subject VARCHAR(180) NOT NULL,
            message TEXT NOT NULL,
            status ENUM('New','Read','Resolved') NOT NULL DEFAULT 'New',
            submitted_at DATETIME NOT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";

    $db->query($createEnrollmentSql);
    $db->query($createContactSql);
}

function saveEnrollmentToDatabase(mysqli $db, array $data) {
    $stmt = $db->prepare(
        "INSERT INTO website_enrollment_submissions
        (full_name, email, phone, program, message, status, submitted_at, ip_address)
        VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Failed to prepare enrollment insert query');
    }

    $stmt->bind_param(
        'sssssss',
        $data['full_name'],
        $data['email'],
        $data['phone'],
        $data['program'],
        $data['message'],
        $data['submitted_at'],
        $data['ip_address']
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Failed to save enrollment submission');
    }

    $stmt->close();
}

function saveContactToDatabase(mysqli $db, array $data) {
    $stmt = $db->prepare(
        "INSERT INTO website_contact_submissions
        (sender_name, sender_email, subject, message, status, submitted_at, ip_address)
        VALUES (?, ?, ?, ?, 'New', ?, ?)"
    );

    if (!$stmt) {
        throw new Exception('Failed to prepare contact insert query');
    }

    $stmt->bind_param(
        'ssssss',
        $data['name'],
        $data['email'],
        $data['subject'],
        $data['message'],
        $data['submitted_at'],
        $data['ip_address']
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception('Failed to save contact submission');
    }

    $stmt->close();
}

?>