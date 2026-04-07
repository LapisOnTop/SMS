<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Check if user is authenticated as registrar
if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role'] !== 'registrar') {
	http_response_code(403);
	echo json_encode([
		'ok' => false,
		'message' => 'Unauthorized access'
	]);
	exit;
}

require_once __DIR__ . '/_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode([
		'ok' => false,
		'message' => 'Invalid request method'
	]);
	exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email_address']) || !isset($input['first_name'])) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Missing required fields'
	]);
	exit;
}

$emailAddress = enrollment_api_string($input['email_address'] ?? '');
$firstName = enrollment_api_string($input['first_name'] ?? '');
$applicationId = intval($input['application_id'] ?? 0);

if (empty($emailAddress) || empty($firstName)) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Invalid email or name'
	]);
	exit;
}

// Validate email format
if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
	http_response_code(400);
	echo json_encode([
		'ok' => false,
		'message' => 'Invalid email format'
	]);
	exit;
}

// Get database connection
$db = enrollment_api_require_db();

// Get application details
$sql = 'SELECT a.status, api.first_name, api.last_name FROM applications a 
		LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id 
		WHERE a.application_id = ?';
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $applicationId);
$stmt->execute();
$result = $stmt->get_result();
$appData = $result->fetch_assoc();
$stmt->close();

if (!$appData) {
	http_response_code(404);
	echo json_encode([
		'ok' => false,
		'message' => 'Application not found'
	]);
	exit;
}

// Send enrollment confirmation email
$fullName = ($appData['first_name'] ?? $firstName) . ' ' . ($appData['last_name'] ?? '');
$subject = 'Enrollment Confirmation - Online Admission System';

$body = "
<!DOCTYPE html>
<html>
<head>
	<meta charset='UTF-8'>
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
		.container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
		.header { background: linear-gradient(135deg, #1e2532, #1a1f2a); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
		.header h1 { margin: 0; font-size: 28px; }
		.content { background: white; padding: 30px; border-radius: 0 0 8px 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
		.greeting { font-size: 16px; margin-bottom: 20px; }
		.status-box { background: #e8f5e9; border-left: 4px solid #4caf50; padding: 15px; margin: 20px 0; border-radius: 4px; }
		.status-box strong { color: #2e7d32; }
		.info-section { margin: 20px 0; }
		.info-section h3 { color: #1e2532; margin-top: 0; }
		.info-item { margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 4px; }
		.footer { text-align: center; padding: 20px; color: #666; font-size: 12px; border-top: 1px solid #ddd; }
		.button { display: inline-block; background: #1e2532; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; margin: 20px 0; }
	</style>
</head>
<body>
	<div class='container'>
		<div class='header'>
			<h1><i>🎓 Enrollment Confirmation</i></h1>
		</div>
		<div class='content'>
			<div class='greeting'>
				<p>Dear <strong>" . htmlspecialchars($firstName) . "</strong>,</p>
			</div>

			<p>We are pleased to inform you that your application has been processed and approved!</p>

			<div class='status-box'>
				<strong>✓ Your application status: APPROVED</strong>
				<p style='margin: 10px 0 0 0;'>Your enrollment is confirmed and you are ready to begin your studies with us.</p>
			</div>

			<div class='info-section'>
				<h3>What's Next?</h3>
				<div class='info-item'>
					<strong>1. Complete Payment:</strong> Ensure all required payment has been submitted.
				</div>
				<div class='info-item'>
					<strong>2. Class Schedule:</strong> Your class schedule will be available in your student portal.
				</div>
				<div class='info-item'>
					<strong>3. Orientation:</strong> Attend the mandatory student orientation program.
				</div>
				<div class='info-item'>
					<strong>4. Course Materials:</strong> Access course materials through the student portal.
				</div>
			</div>

			<div class='info-section'>
				<h3>Important Information</h3>
				<p>Please log in to your student portal to:</p>
				<ul>
					<li>View your complete class schedule</li>
					<li>Check payment status</li>
					<li>Access course materials</li>
					<li>View announcements and updates</li>
				</ul>
			</div>

			<p>If you have any questions or concerns, please don't hesitate to contact our Registrar's office.</p>

			<p><strong>Contact Information:</strong><br>
			Email: registrar@university.edu<br>
			Phone: (123) 456-7890<br>
			Hours: Monday - Friday, 9:00 AM - 5:00 PM</p>

			<p>Congratulations again on your enrollment!</p>

			<p>Best regards,<br>
			<strong>Office of the Registrar</strong><br>
			Online Admission System</p>
		</div>

		<div class='footer'>
			<p>This is an automated email. Please do not reply to this message.</p>
			<p>&copy; 2026 University. All rights reserved.</p>
		</div>
	</div>
</body>
</html>
";

// Email headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: registrar@university.edu\r\n";
$headers .= "Reply-To: registrar@university.edu\r\n";

// Send email
$mailSent = mail($emailAddress, $subject, $body, $headers);

if ($mailSent) {
	// Log the email send action if needed
	enrollment_api_json([
		'ok' => true,
		'success' => true,
		'message' => 'Enrollment email sent successfully'
	], 200);
} else {
	http_response_code(500);
	enrollment_api_json([
		'ok' => false,
		'message' => 'Failed to send email. Please try again.'
	], 500);
}
?>
