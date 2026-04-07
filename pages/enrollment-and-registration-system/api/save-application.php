<?php

error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, we'll return them as JSON
ini_set('log_errors', 1);

require_once __DIR__ . '/_helpers.php';

// Set custom error handler to catch errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return;
    }
    
    // Return error as JSON instead of letting PHP display it
    enrollment_api_json([
        'ok' => false,
        'message' => 'Error: ' . $errstr . ' in ' . basename($errfile) . ':' . $errline
    ], 500);
});

set_exception_handler(function($exception) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Exception: ' . $exception->getMessage()
    ], 500);
});

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

$requiredMap = [
    'admissionType' => 'Admission Type',
    'lastName' => 'Lastname',
    'firstName' => 'Firstname',
    'sex' => 'Sex',
    'civilStatus' => 'Civil Status',
    'birthdate' => 'Birthday',
    'email' => 'Email Address',
    'contact' => 'Contact Number',
    'fbName' => 'Facebook / Messenger Name',
    'address' => 'Address',
    'regionName' => 'Region',
    'cityName' => 'Municipality / City',
    'barangayName' => 'Barangay',
    'fatherLast' => "Father's Lastname",
    'fatherFirst' => "Father's Firstname",
    'motherLast' => "Mother's Lastname",
    'motherFirst' => "Mother's Firstname",
    'guardianRelation' => 'Guardian Relation',
    'guardianLast' => 'Parent/Guardian Lastname',
    'guardianFirst' => 'Parent/Guardian Firstname',
    'guardianContact' => 'Parent/Guardian Contact Number',
    'yearLevel' => 'Year Level',
    'primarySchool' => 'Primary',
    'primaryGrad' => 'Primary Year Graduated',
    'secondarySchool' => 'Secondary',
    'secondaryGrad' => 'Secondary Year Graduated',
    'lastSchool' => 'Last School Attended',
    'lastSchoolYear' => 'Last School Year Attended',
    'referral' => 'Referral Option'
];

foreach ($requiredMap as $key => $label) {
    if (($key === 'regionName' || $key === 'cityName' || $key === 'barangayName')) {
        continue;
    }

    $value = enrollment_api_string($payload[$key] ?? '');
    if ($value === '') {
        enrollment_api_json([
            'ok' => false,
            'message' => $label . ' is required.'
        ], 422);
    }
}

$regionCandidate = enrollment_api_string($payload['regionName'] ?? ($payload['regionCode'] ?? ''));
$cityCandidate = enrollment_api_string($payload['cityName'] ?? ($payload['cityCode'] ?? ''));
$barangayCandidate = enrollment_api_string($payload['barangayName'] ?? ($payload['barangayCode'] ?? ''));

if ($regionCandidate === '') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Region is required.'
    ], 422);
}

if ($cityCandidate === '') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Municipality / City is required.'
    ], 422);
}

if ($barangayCandidate === '') {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Barangay is required.'
    ], 422);
}

$db = enrollment_api_require_db();

// Extract and normalize data
$admissionType = enrollment_api_string($payload['admissionType']);
$admissionTypeDbValue = ($admissionType === 'Old Student') ? 'Returnee' : $admissionType;
$studentNumber = enrollment_api_string($payload['studentNumber'] ?? '');
$isWorkingStudent = (int) enrollment_api_bool($payload['workingStudent'] ?? false);
$is4ps = (int) enrollment_api_bool($payload['is4ps'] ?? false);

$lastName = enrollment_api_string($payload['lastName']);
$firstName = enrollment_api_string($payload['firstName']);
$middleName = enrollment_api_nullable_string($payload['middleName'] ?? null);
$suffix = enrollment_api_nullable_string($payload['suffix'] ?? null);
$sex = enrollment_api_string($payload['sex']);
$civilStatus = enrollment_api_string($payload['civilStatus']);
$religion = enrollment_api_nullable_string($payload['religion'] ?? null);
$birthdate = enrollment_api_string($payload['birthdate']);
$email = strtolower(enrollment_api_string($payload['email']));
$contact = enrollment_api_string($payload['contact']);
$fbName = enrollment_api_string($payload['fbName']);
$address = enrollment_api_string($payload['address']);

$regionName = enrollment_api_resolve_region_name($regionCandidate);
$cityName = enrollment_api_resolve_city_name($cityCandidate);
$barangayName = enrollment_api_resolve_barangay_name($barangayCandidate);

$fatherLast = enrollment_api_string($payload['fatherLast']);
$fatherFirst = enrollment_api_string($payload['fatherFirst']);
$fatherMiddle = enrollment_api_nullable_string($payload['fatherMiddle'] ?? null);
$fatherSuffix = enrollment_api_nullable_string($payload['fatherSuffix'] ?? null);

$motherLast = enrollment_api_string($payload['motherLast']);
$motherFirst = enrollment_api_string($payload['motherFirst']);
$motherMiddle = enrollment_api_nullable_string($payload['motherMiddle'] ?? null);
$motherSuffix = enrollment_api_nullable_string($payload['motherSuffix'] ?? null);

$guardianRelation = enrollment_api_string($payload['guardianRelation']);
$guardianLast = enrollment_api_string($payload['guardianLast']);
$guardianFirst = enrollment_api_string($payload['guardianFirst']);
$guardianMiddle = enrollment_api_nullable_string($payload['guardianMiddle'] ?? null);
$guardianSuffix = enrollment_api_nullable_string($payload['guardianSuffix'] ?? null);
$guardianContact = enrollment_api_string($payload['guardianContact']);
$guardianOccupation = enrollment_api_nullable_string($payload['guardianOccupation'] ?? null);

$branchId = !empty($payload['branchId']) ? (int) $payload['branchId'] : null;
$course = enrollment_api_nullable_string($payload['course'] ?? null);
// Accept only known year-level labels to prevent empty/invalid submissions.
$yearLevelStr = enrollment_api_string($payload['yearLevel']);
$yearLevelMap = [
    '1st Year' => 1,
    '2nd Year' => 2,
    '3rd Year' => 3,
    '4th Year' => 4
];

if (!array_key_exists($yearLevelStr, $yearLevelMap)) {
    enrollment_api_json([
        'ok' => false,
        'message' => 'Year Level is required.'
    ], 422);
}

$yearLevel = $yearLevelMap[$yearLevelStr];

$primarySchool = enrollment_api_string($payload['primarySchool']);
$primaryGrad = enrollment_api_string($payload['primaryGrad']);
$secondarySchool = enrollment_api_string($payload['secondarySchool']);
$secondaryGrad = enrollment_api_string($payload['secondaryGrad']);
$lastSchool = enrollment_api_string($payload['lastSchool']);
$lastSchoolYear = enrollment_api_string($payload['lastSchoolYear']);

$referral = enrollment_api_string($payload['referral']);

// Extract selected subject IDs for pre-selection
$selectedSubjectIds = $payload['selectedSubjectIds'] ?? [];
if (!is_array($selectedSubjectIds)) {
    $selectedSubjectIds = [];
}
$selectedSubjectIds = array_filter($selectedSubjectIds, function($id) {
    return is_numeric($id) && (int)$id > 0;
});
$selectedSubjectIds = array_map('intval', $selectedSubjectIds);

try {
    // Begin transaction
    $db->begin_transaction();
    $isVerifiedExistingStudent = false;
    $isOldStudentFlow = ($admissionType === 'Old Student' || $admissionType === 'Returnee');

    if ($admissionType === 'Old Student' && $studentNumber === '') {
        $db->rollback();
        enrollment_api_json([
            'ok' => false,
            'message' => 'Student Number is required for old student enrollment.'
        ], 422);
    }

    if ($isOldStudentFlow && $studentNumber !== '') {
        $studentLookupSql = 'SELECT student_id FROM students WHERE student_number = ? LIMIT 1';
        $studentLookupStmt = $db->prepare($studentLookupSql);
        if (!$studentLookupStmt) {
            throw new Exception('Failed to prepare student lookup query: ' . $db->error);
        }

        $studentLookupStmt->bind_param('s', $studentNumber);
        if (!$studentLookupStmt->execute()) {
            throw new Exception('Failed to execute student lookup query: ' . $studentLookupStmt->error);
        }

        $studentLookupResult = $studentLookupStmt->get_result();
        $existingStudent = $studentLookupResult ? $studentLookupResult->fetch_assoc() : null;
        $studentLookupStmt->close();

        if (!$existingStudent) {
            $db->rollback();
            enrollment_api_json([
                'ok' => false,
                'message' => 'Student Number not found. Leave it blank if you do not have an existing student record yet.'
            ], 404);
        }

        $isVerifiedExistingStudent = true;
    }

    // Ensure email address and contact number are unique per applicant.
    if (!$isVerifiedExistingStudent) {
        $duplicateCheckSql = 'SELECT api.application_id,
                                     ac.email_address,
                                     ac.contact_number
                              FROM applicant_personal_info api
                              INNER JOIN applicant_contact ac ON api.application_id = ac.application_id
                              WHERE LOWER(ac.email_address) = LOWER(?)
                                 OR ac.contact_number = ?
                              ORDER BY api.application_id DESC
                              LIMIT 1';
        $dupStmt = $db->prepare($duplicateCheckSql);
        if ($dupStmt) {
            $dupStmt->bind_param('ss', $email, $contact);
            $dupStmt->execute();
            $dupResult = $dupStmt->get_result();
            if ($dupResult->num_rows > 0) {
                $existing = $dupResult->fetch_assoc();

                $duplicateFields = [];
                if (isset($existing['email_address']) && strcasecmp((string) $existing['email_address'], $email) === 0) {
                    $duplicateFields[] = 'email address';
                }
                if (isset($existing['contact_number']) && (string) $existing['contact_number'] === $contact) {
                    $duplicateFields[] = 'contact number';
                }

                $duplicateLabel = !empty($duplicateFields)
                    ? implode(' and ', $duplicateFields)
                    : 'email address or contact number';

                $db->rollback();
                enrollment_api_json([
                    'ok' => false,
                    'message' => 'The submitted ' . $duplicateLabel . ' is already used. Please use unique details.',
                    'isDuplicate' => true
                ], 409);
            }
            $dupStmt->close();
        }
    }

    // Generate and insert reference number
    $referenceData = enrollment_api_generate_reference_number($db);
    $referenceId = $referenceData['id'];
    $applicationReference = $referenceData['number'];

    // Get selection_id (1 for now - Main Branch, BSIT, Year Level)
    // In future, this should come from the form
    $selectionId = null;
    $selectionSql = 'SELECT selection_id FROM selection WHERE course_id = 1 AND year_level = ? LIMIT 1';
    $selectionStmt = $db->prepare($selectionSql);
    if ($selectionStmt) {
        $selectionStmt->bind_param('i', $yearLevel);
        $selectionStmt->execute();
        $selectionResult = $selectionStmt->get_result();
        if ($row = $selectionResult->fetch_assoc()) {
            $selectionId = $row['selection_id'];
        }
        $selectionStmt->close();
    }

    // Insert into applications table
    $applicationStatus = 'Pending';
    $sql = 'INSERT INTO applications (reference_id, selection_id, admission_type, is_working, is_4ps, status)
            VALUES (?, ?, ?, ?, ?, ?)';
    
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare applications query: ' . $db->error);
    }

    $stmt->bind_param('iisiis', $referenceId, $selectionId, $admissionTypeDbValue, $isWorkingStudent, $is4ps, $applicationStatus);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert application: ' . $stmt->error);
    }
    
    $applicationId = $db->insert_id;
    $stmt->close();

    // Insert into applicant_personal_info
    $personalSql = 'INSERT INTO applicant_personal_info (application_id, first_name, middle_name, last_name, suffix, sex, civil_status, birthdate, religion)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $personalStmt = $db->prepare($personalSql);
    if (!$personalStmt) {
        throw new Exception('Failed to prepare personal info query: ' . $db->error);
    }
    $personalStmt->bind_param('issssssss', $applicationId, $firstName, $middleName, $lastName, $suffix, $sex, $civilStatus, $birthdate, $religion);
    if (!$personalStmt->execute()) {
        throw new Exception('Failed to insert personal info: ' . $personalStmt->error);
    }
    $personalStmt->close();

    // Insert into applicant_address
    $addressSql = 'INSERT INTO applicant_address (application_id, region, city, barangay, address)
                   VALUES (?, ?, ?, ?, ?)';
    $addressStmt = $db->prepare($addressSql);
    if (!$addressStmt) {
        throw new Exception('Failed to prepare address query: ' . $db->error);
    }
    $addressStmt->bind_param('issss', $applicationId, $regionName, $cityName, $barangayName, $address);
    if (!$addressStmt->execute()) {
        throw new Exception('Failed to insert address: ' . $addressStmt->error);
    }
    $addressStmt->close();

    // Insert into applicant_contact
    $contactSql = 'INSERT INTO applicant_contact (application_id, email_address, contact_number, fbname)
                   VALUES (?, ?, ?, ?)';
    $contactStmt = $db->prepare($contactSql);
    if (!$contactStmt) {
        throw new Exception('Failed to prepare contact query: ' . $db->error);
    }
    $contactStmt->bind_param('isss', $applicationId, $email, $contact, $fbName);
    if (!$contactStmt->execute()) {
        throw new Exception('Failed to insert contact: ' . $contactStmt->error);
    }
    $contactStmt->close();

    // Insert into applicant_family
    $fatherName = trim($fatherFirst . ' ' . ($fatherMiddle ? $fatherMiddle . ' ' : '') . $fatherLast . ($fatherSuffix ? ' ' . $fatherSuffix : ''));
    $motherName = trim($motherFirst . ' ' . ($motherMiddle ? $motherMiddle . ' ' : '') . $motherLast . ($motherSuffix ? ' ' . $motherSuffix : ''));
    $guardianName = trim($guardianFirst . ' ' . ($guardianMiddle ? $guardianMiddle . ' ' : '') . $guardianLast . ($guardianSuffix ? ' ' . $guardianSuffix : ''));
    
    $familySql = 'INSERT INTO applicant_family (application_id, father_name, mother_name, guardian_name, guardian_contact, guardian_relation, guardian_occupation)
                  VALUES (?, ?, ?, ?, ?, ?, ?)';
    $familyStmt = $db->prepare($familySql);
    if (!$familyStmt) {
        throw new Exception('Failed to prepare family query: ' . $db->error);
    }
    $familyStmt->bind_param('issssss', $applicationId, $fatherName, $motherName, $guardianName, $guardianContact, $guardianRelation, $guardianOccupation);
    if (!$familyStmt->execute()) {
        throw new Exception('Failed to insert family: ' . $familyStmt->error);
    }
    $familyStmt->close();

    // Insert into applicant_education
    $educationSql = 'INSERT INTO applicant_education (application_id, primary_school, primary_graduation_date, secondary_school, secondary_graduation_date, last_school_attended, last_school_year_attended)
                     VALUES (?, ?, ?, ?, ?, ?, ?)';
    $educationStmt = $db->prepare($educationSql);
    if (!$educationStmt) {
        throw new Exception('Failed to prepare education query: ' . $db->error);
    }
    $educationStmt->bind_param('issssss', $applicationId, $primarySchool, $primaryGrad, $secondarySchool, $secondaryGrad, $lastSchool, $lastSchoolYear);
    if (!$educationStmt->execute()) {
        throw new Exception('Failed to insert education: ' . $educationStmt->error);
    }
    $educationStmt->close();

    // Create subject pre-selection if subjects were selected
    if (!empty($selectedSubjectIds)) {
        // Get the current term (use the first term for now)
        $termSql = 'SELECT term_id FROM terms LIMIT 1';
        $termResult = $db->query($termSql);
        $termId = 1; // Default to first term
        if ($termResult && $row = $termResult->fetch_assoc()) {
            $termId = $row['term_id'];
        }

        // Create pre-selection record
        $preselectionStatus = 'Pending';
        $preselectionSql = 'INSERT INTO subject_preselection (application_id, term_id, status) 
                            VALUES (?, ?, ?)';
        $preselectionStmt = $db->prepare($preselectionSql);
        if (!$preselectionStmt) {
            throw new Exception('Failed to prepare preselection query: ' . $db->error);
        }
        $preselectionStmt->bind_param('iis', $applicationId, $termId, $preselectionStatus);
        if (!$preselectionStmt->execute()) {
            throw new Exception('Failed to insert preselection: ' . $preselectionStmt->error);
        }
        $preselectionId = $db->insert_id;
        $preselectionStmt->close();

        // Insert subject pre-selection details
        foreach ($selectedSubjectIds as $subjectId) {
            $subjectId = (int)$subjectId;
            $detailSql = 'INSERT INTO subject_preselection_details (preselection_id, subject_id) 
                          VALUES (?, ?)';
            $detailStmt = $db->prepare($detailSql);
            if (!$detailStmt) {
                throw new Exception('Failed to prepare detail query: ' . $db->error);
            }
            $detailStmt->bind_param('ii', $preselectionId, $subjectId);
            if (!$detailStmt->execute()) {
                throw new Exception('Failed to insert subject detail: ' . $detailStmt->error);
            }
            $detailStmt->close();
        }
    }

    // Commit transaction
    $db->commit();

    enrollment_api_json([
        'ok' => true,
        'message' => 'Application submitted successfully.',
        'application_id' => $applicationId,
        'applicationReference' => $applicationReference
    ]);

} catch (Exception $e) {
    $db->rollback();
    enrollment_api_json([
        'ok' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
