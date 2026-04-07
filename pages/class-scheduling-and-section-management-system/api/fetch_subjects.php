<?php
/* ============================================================
   FILE: api/fetch_subjects.php
   Fetch subjects and course info from sms_db
   ?action=programs
   ?action=subjects&year_level=1&semester=1
   ?action=subjects&year_level=1&semester=1&course_id=1
   ============================================================ */
require_once __DIR__ . '/config.php';

$action = $_GET['action'] ?? '';

try {
    $db = smsDB();

    switch ($action) {

        /* ── All distinct courses (replaces programs) ───── */
        case 'programs':
            $rows = $db->query(
                'SELECT course_id AS program_id,
                        course_code AS program_code,
                        course_name AS program_description
                 FROM courses ORDER BY course_code'
            )->fetchAll();
            ok($rows, 'Courses loaded');
            break;

        /* ── Subjects filtered by year_level / semester ─── */
        case 'subjects':
            $year     = (int)($_GET['year_level'] ?? 0);
            $sem      = (int)($_GET['semester']   ?? 0);
            $courseId = (int)($_GET['course_id']  ?? 0);

            if (!$year) fail('Missing: year_level');
            if (!$sem)  fail('Missing: semester');

            /*
             * sms_db.subjects columns:
             *   subject_id | subject_code | subject_name | units | price | year_level | semester
             *
             * Map to the names the frontend already expects:
             *   subject_id | subject_code | subject_description | units | hours | prerequisite
             */
            $sql = 'SELECT s.subject_id,
                           s.subject_code,
                           s.subject_name  AS subject_description,
                           s.units,
                           (s.units * 18)  AS hours,
                           COALESCE(
                               GROUP_CONCAT(pre.subject_code ORDER BY pre.subject_code SEPARATOR ", "),
                               "None"
                           ) AS prerequisite
                    FROM subjects s
                    LEFT JOIN subject_prerequisite sp ON sp.subject_id = s.subject_id
                    LEFT JOIN subjects pre            ON pre.subject_id = sp.prerequisite_id
                    WHERE s.year_level = ? AND s.semester = ?
                    GROUP BY s.subject_id
                    ORDER BY s.subject_code';

            $params = [$year, $sem];
            $stmt   = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            ok($rows, count($rows) . ' subjects loaded');
            break;

        default:
            fail('Unknown action: ' . $action);
    }

} catch (PDOException $e) {
    fail('Query Error (sms_db): ' . $e->getMessage(), 500);
}
