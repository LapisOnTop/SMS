<?php

require_once __DIR__ . '/_helpers.php';

// Get subjects grouped by year and semester with their prerequisites
$db = enrollment_api_require_db();

$sql = 'SELECT 
            s.subject_id,
            s.subject_code, 
            s.subject_name, 
            s.units, 
            s.year_level, 
            s.semester,
            GROUP_CONCAT(CONCAT(ps.subject_code, ":", ps.subject_name) SEPARATOR "|") as prerequisites
        FROM subjects s
        LEFT JOIN subject_prerequisite sp ON s.subject_id = sp.subject_id
        LEFT JOIN subjects ps ON sp.prerequisite_id = ps.subject_id
        GROUP BY s.subject_id
        ORDER BY s.year_level ASC, s.semester ASC, s.subject_code ASC';

$result = $db->query($sql);
if (!$result) {
    enrollment_api_json([
        'success' => false,
        'message' => 'Failed to fetch subjects'
    ], 500);
}

// Group subjects by year and semester
$curriculum = [];
while ($row = $result->fetch_assoc()) {
    $yearKey = 'Year ' . $row['year_level'];
    $semesterKey = $row['semester'] == 1 ? '1st Semester' : '2nd Semester';
    
    if (!isset($curriculum[$yearKey])) {
        $curriculum[$yearKey] = [];
    }
    if (!isset($curriculum[$yearKey][$semesterKey])) {
        $curriculum[$yearKey][$semesterKey] = [];
    }
    
    // Parse prerequisites - they come as "CODE:Name|CODE:Name" format
    $prerequisites = [];
    if (!empty($row['prerequisites'])) {
        $prereqPairs = explode('|', $row['prerequisites']);
        foreach ($prereqPairs as $pair) {
            if (!empty($pair)) {
                list($code, $name) = explode(':', $pair, 2);
                $prerequisites[] = [
                    'code' => trim($code),
                    'name' => trim($name)
                ];
            }
        }
    }
    
    $curriculum[$yearKey][$semesterKey][] = [
        'code' => $row['subject_code'],
        'description' => $row['subject_name'],
        'units' => (int) $row['units'],
        'hours' => 54,
        'prerequisites' => $prerequisites
    ];
}

enrollment_api_json([
    'success' => true,
    'curriculum' => $curriculum
], 200);
