<?php
/**
 * Validate Subject Loads (Preselection)
 * 
 * Approves the subject preselection for a student
 * Updates subject_preselection status to 'Approved'
 */

session_start();
header('Content-Type: application/json');

require_once '../../../config/database.php';

$db = sms_get_db_connection();
if (!$db) {
	http_response_code(500);
	echo json_encode(['success' => false, 'message' => 'Database connection failed']);
	exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$application_id = isset($input['application_id']) ? intval($input['application_id']) : 0;
$remarks = isset($input['remarks']) ? trim($input['remarks']) : '';

if (!$application_id) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Application ID is required']);
	exit;
}

try {
	// Use application_id directly to update subject preselection
	$sql = "UPDATE subject_preselection 
			SET status = 'Approved',
				validated_by = ?,
				validated_at = NOW()
			WHERE application_id = ? AND status = 'Pending'
			LIMIT 1";
	
	$validated_by = $_SESSION['user_id'] ?? 1; // Get current user ID from session, default to 1
	$stmt = $db->prepare($sql);
	$stmt->bind_param('ii', $validated_by, $application_id);
	$stmt->execute();

	if ($stmt->affected_rows > 0) {
		// Align workflow: approved subject loads should move admission to Validated.
		$statusStmt = $db->prepare("UPDATE applications SET status = 'Validated' WHERE application_id = ? AND status <> 'Validated' LIMIT 1");
		if ($statusStmt) {
			$statusStmt->bind_param('i', $application_id);
			$statusStmt->execute();
			$statusStmt->close();
		}

		echo json_encode([
			'success' => true,
			'message' => 'Subject loads approved and application marked as Validated.',
			'skipped_no_load' => false
		]);
	} else {
		$existingStmt = $db->prepare("SELECT COUNT(*) AS total FROM subject_preselection WHERE application_id = ?");
		$existingStmt->bind_param('i', $application_id);
		$existingStmt->execute();
		$existingResult = $existingStmt->get_result();
		$existingRow = $existingResult ? $existingResult->fetch_assoc() : null;
		$hasSubjectLoad = intval($existingRow['total'] ?? 0) > 0;
		$existingStmt->close();

		if ($hasSubjectLoad) {
			// If loads were already processed earlier, still push the admission to Validated.
			$statusStmt = $db->prepare("UPDATE applications SET status = 'Validated' WHERE application_id = ? AND status <> 'Validated' LIMIT 1");
			if ($statusStmt) {
				$statusStmt->bind_param('i', $application_id);
				$statusStmt->execute();
				$statusStmt->close();
			}
		}

		echo json_encode([
			'success' => true,
			'message' => $hasSubjectLoad
				? 'No pending subject loads found. Existing loads were already processed, and application is now marked as Validated.'
				: 'No subject load found. Validation bypassed so admin can continue.',
			'skipped_no_load' => true
		]);
	}

	$stmt->close();

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => 'An error occurred: ' . $e->getMessage()
	]);
}
