<?php
/**
 * Get Subject Preselection for a Student
 * 
 * Fetches the subject preselection and details for a specific student/application
 * Uses: applications, subject_preselection, subject_preselection_details, subjects tables
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

// Get application_id from POST request
$input = json_decode(file_get_contents('php://input'), true);
$application_id = isset($input['application_id']) ? intval($input['application_id']) : 0;

if (!$application_id) {
	http_response_code(400);
	echo json_encode(['success' => false, 'message' => 'Application ID is required']);
	exit;
}

try {
	// Use application_id directly to fetch subject preselection
	$sql = "SELECT 
				sp.preselection_id,
				sp.status,
				sp.validated_at,
				s.subject_id,
				s.subject_code,
				s.subject_name,
				s.units,
				s.price,
				s.year_level,
				s.semester
			FROM subject_preselection sp
			LEFT JOIN subject_preselection_details spd ON sp.preselection_id = spd.preselection_id
			LEFT JOIN subjects s ON spd.subject_id = s.subject_id
			WHERE sp.application_id = ?
			ORDER BY s.year_level, s.semester, s.subject_code";
	
	$stmt = $db->prepare($sql);
	$stmt->bind_param('i', $application_id);
	$stmt->execute();
	$result = $stmt->get_result();
	
	$subjects = [];
	$preselection_status = null;
	$validated_at = null;
	
	while ($row = $result->fetch_assoc()) {
		$preselection_status = $row['status'];
		$validated_at = $row['validated_at'];
		
		if ($row['subject_id']) { // Only include if subject_id exists
			$subjects[] = [
				'subject_id' => intval($row['subject_id']),
				'subject_code' => $row['subject_code'],
				'subject_name' => $row['subject_name'],
				'units' => intval($row['units']),
				'price' => floatval($row['price']),
				'year_level' => intval($row['year_level']),
				'semester' => intval($row['semester'])
			];
		}
	}

	echo json_encode([
		'success' => true,
		'subjects' => $subjects,
		'count' => count($subjects),
		'status' => $preselection_status,
		'validated_at' => $validated_at
	]);

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode([
		'success' => false,
		'message' => 'An error occurred: ' . $e->getMessage()
	]);
}
