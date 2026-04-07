<?php

require_once __DIR__ . '/_helpers.php';

// This API validates selected subject codes and returns their IDs
// Used during subject pre-selection to build subject ID list for later storage

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Method not allowed.'
    ], 405);
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Invalid JSON payload.'
    ], 400);
}

// Get selected subject codes
$subjectCodes = $payload['subjectCodes'] ?? [];
if (!is_array($subjectCodes) || empty($subjectCodes)) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'No subjects provided.'
    ], 422);
}

// Sanitize subject codes
$subjectCodes = array_map(function($code) {
    return enrollment_api_string($code);
}, $subjectCodes);

$db = enrollment_api_require_db();

// Get subject IDs for the provided codes
$placeholders = implode(',', array_fill(0, count($subjectCodes), '?'));
$sql = "SELECT subject_id, subject_code, subject_name, units, year_level, semester 
        FROM subjects 
        WHERE subject_code IN ($placeholders)
        ORDER BY year_level ASC, semester ASC";

$stmt = $db->prepare($sql);
if (!$stmt) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Failed to prepare query: ' . $db->error
    ], 500);
}

// Bind parameters dynamically
$stmt->bind_param(str_repeat('s', count($subjectCodes)), ...$subjectCodes);

if (!$stmt->execute()) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Failed to execute query: ' . $stmt->error
    ], 500);
}

$result = $stmt->get_result();
$subjects = [];
$subjectIds = [];
$checkedCodes = [];

while ($row = $result->fetch_assoc()) {
    $subjects[] = [
        'subject_id' => (int) $row['subject_id'],
        'subject_code' => $row['subject_code'],
        'subject_name' => $row['subject_name'],
        'units' => (int) $row['units'],
        'year_level' => (int) $row['year_level'],
        'semester' => (int) $row['semester']
    ];
    $subjectIds[] = (int) $row['subject_id'];
    $checkedCodes[] = $row['subject_code'];
}

$stmt->close();

// Check if any codes were invalid
$invalidCodes = array_diff($subjectCodes, $checkedCodes);
if (!empty($invalidCodes)) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Invalid subject code(s): ' . implode(', ', $invalidCodes)
    ], 422);
}

enrollment_api_json([
    'ok' => true,
    'subjects' => $subjects,
    'subjectIds' => $subjectIds,
    'count' => count($subjects)
], 200);
