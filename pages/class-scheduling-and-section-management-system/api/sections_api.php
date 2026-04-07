<?php
/* ============================================================
   FILE: api/sections_api.php
   Returns all fields the frontend JS expects:
     section_id, section_label, program, year_level, semester,
     category, room_number (INT 1-15), time_schedule, max_capacity,
     enrolled_count, subject_count, status, professor

   GET  ?action=list               — all sections
   GET  ?action=detail&id=X        — section + subjects + students
   GET  ?action=students&id=X      — students enrolled in section
   GET  ?action=terms              — all terms
   DELETE ?id=X                    — remove section
   ============================================================ */
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = smsDB();

/* ── Normalize room value to INT 1-15 ────────────────────────
   sms_db stores room as varchar e.g. "1001","1002" or "1"-"15".
   The JS always expects an integer 1–15.
   Strategy: if value > 15 treat it as a room-code (1001→1, 1015→15)
   by taking mod 100 when value > 15, otherwise cast directly.
   ─────────────────────────────────────────────────────────── */
function normalizeRoom(?string $room): ?int {
    if ($room === null || $room === '') return null;
    $n = (int)$room;
    if ($n === 0) return null;
    if ($n <= 15) return $n;
    // e.g. 1001 → 1, 1015 → 15
    $mod = $n % 100;
    if ($mod >= 1 && $mod <= 15) return $mod;
    // fallback: last digit(s)
    return min(max(1, $n % 15 ?: 15), 15);
}

if ($method === 'GET') {

    /* ── Terms ──────────────────────────────────────────── */
    if ($action === 'terms') {
        $rows = $db->query(
            "SELECT term_id,
                    school_year AS academic_year,
                    semester,
                    CONCAT(
                        CASE semester WHEN 1 THEN '1st' WHEN 2 THEN '2nd'
                        ELSE CONCAT(semester,'th') END,
                        ' Semester ', school_year
                    ) AS term_label,
                    0 AS is_active
             FROM terms
             ORDER BY school_year DESC, semester DESC"
        )->fetchAll();
        ok($rows);
    }

    /* ── Sections list ──────────────────────────────────── */
    if ($action === 'list' || $action === '') {
        $year   = (int)($_GET['year_level'] ?? 0);
        $sem    = (int)($_GET['semester']   ?? 0);
        $prog   = trim($_GET['program']     ?? '');
        $q      = trim($_GET['search']      ?? '');

        $sql =
            "SELECT sec.section_id,
                    sec.section_name                                AS section_label,
                    sub.subject_code                               AS program,
                    sub.year_level,
                    sub.semester,
                    'Lecture'                                      AS category,
                    sec.room                                       AS room_raw,
                    CASE
                        WHEN sec.day IN ('Morning','Afternoon') THEN sec.day
                        WHEN sec.start_time < '12:00:00'        THEN 'Morning'
                        WHEN sec.start_time >= '12:00:00'       THEN 'Afternoon'
                        ELSE NULL
                    END                                            AS time_schedule,
                    sec.start_time,
                    sec.end_time,
                    sec.capacity                                   AS max_capacity,
                    CASE WHEN f.faculty_id IS NOT NULL
                         THEN CONCAT(f.last_name, ', ', f.first_name)
                         ELSE u.username
                    END                                            AS professor,
                    (SELECT COUNT(*) FROM section_subjects ss
                     WHERE ss.section_id = sec.section_id)        AS subject_count,
                    (SELECT COUNT(DISTINCT e.student_id)
                     FROM enrollment_details ed
                     JOIN enrollments e ON e.enrollment_id = ed.enrollment_id
                     WHERE ed.section_subject_id IN (
                           SELECT id FROM section_subjects
                           WHERE section_id = sec.section_id
                     ))                                            AS enrolled_count,
                    'Open' AS status
             FROM sections sec
             JOIN subjects sub ON sub.subject_id = sec.subject_id
             LEFT JOIN users u   ON u.user_id    = sec.teacher_id
             LEFT JOIN faculty f ON f.user_id    = sec.teacher_id
             WHERE 1=1";

        $params = [];
        if ($year) { $sql .= ' AND sub.year_level=?'; $params[] = $year; }
        if ($sem)  { $sql .= ' AND sub.semester=?';   $params[] = $sem; }
        if ($q)    { $sql .= ' AND sec.section_name LIKE ?'; $params[] = '%'.$q.'%'; }
        $sql .= ' ORDER BY sub.year_level, sub.semester, sec.section_name';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $raw = $stmt->fetchAll();

        /* Normalize room_number for every section */
        $sections = array_map(function($s) {
            $s['room_number'] = normalizeRoom($s['room_raw'] ?? null);
            unset($s['room_raw']);
            return $s;
        }, $raw);

        $totalRow = $db->query('SELECT COUNT(*) AS total FROM sections')->fetch();
        $stats = [
            'total'    => (int)$totalRow['total'],
            'open_c'   => (int)$totalRow['total'],
            'full_c'   => 0,
            'enrolled' => array_sum(array_column($sections, 'enrolled_count')),
        ];

        ok(['sections' => $sections, 'stats' => $stats]);
    }

    /* ── Section detail ─────────────────────────────────── */
    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) fail('id required.');

        $sec = $db->prepare(
            "SELECT sec.section_id,
                    sec.section_name                               AS section_label,
                    sub.subject_code                              AS program,
                    sub.year_level,
                    sub.semester,
                    'Lecture'                                     AS category,
                    sec.room                                      AS room_raw,
                    CASE
                        WHEN sec.day IN ('Morning','Afternoon') THEN sec.day
                        WHEN sec.start_time < '12:00:00'        THEN 'Morning'
                        WHEN sec.start_time >= '12:00:00'       THEN 'Afternoon'
                        ELSE NULL
                    END                                           AS time_schedule,
                    sec.start_time,
                    sec.end_time,
                    sec.capacity                                  AS max_capacity,
                    CASE WHEN f.faculty_id IS NOT NULL
                         THEN CONCAT(f.last_name, ', ', f.first_name)
                         ELSE u.username
                    END                                           AS professor,
                    'Open' AS status
             FROM sections sec
             JOIN subjects sub ON sub.subject_id = sec.subject_id
             LEFT JOIN users u   ON u.user_id    = sec.teacher_id
             LEFT JOIN faculty f ON f.user_id    = sec.teacher_id
             WHERE sec.section_id = ?"
        );
        $sec->execute([$id]);
        $section = $sec->fetch();
        if (!$section) fail('Section not found.', 404);

        $section['room_number'] = normalizeRoom($section['room_raw'] ?? null);
        unset($section['room_raw']);

        /* Linked subjects */
        $subs = $db->prepare(
            "SELECT ss.id,
                    sub.subject_code,
                    sub.subject_name  AS subject_desc,
                    sub.units,
                    (sub.units * 18)  AS hours,
                    CASE WHEN f.faculty_id IS NOT NULL
                         THEN CONCAT(f.last_name, ', ', f.first_name)
                         ELSE u.username
                    END               AS professor,
                    ss.teacher_id     AS faculty_id,
                    ss.day, ss.start_time, ss.end_time, ss.room
             FROM section_subjects ss
             JOIN subjects sub ON sub.subject_id = ss.subject_id
             LEFT JOIN users u   ON u.user_id    = ss.teacher_id
             LEFT JOIN faculty f ON f.user_id    = ss.teacher_id
             WHERE ss.section_id = ?
             ORDER BY sub.subject_code"
        );
        $subs->execute([$id]);
        $section['subjects'] = $subs->fetchAll();

        /* Student count */
        $cnt = $db->prepare(
            'SELECT COUNT(DISTINCT e.student_id) AS cnt
             FROM enrollment_details ed
             JOIN enrollments e ON e.enrollment_id = ed.enrollment_id
             WHERE ed.section_subject_id IN (
                   SELECT id FROM section_subjects WHERE section_id = ?
             )'
        );
        $cnt->execute([$id]);
        $section['student_count'] = (int)($cnt->fetch()['cnt'] ?? 0);

        ok($section);
    }

    /* ── Enrolled students ───────────────────────────────── */
    if ($action === 'students') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) fail('section_id required.');

        $rows = $db->prepare(
            'SELECT DISTINCT
                    s.student_id,
                    s.student_number,
                    api.first_name,
                    api.middle_name,
                    api.last_name,
                    api.sex             AS gender,
                    ac.email_address    AS email,
                    ac.contact_number
             FROM enrollment_details ed
             JOIN enrollments e         ON e.enrollment_id   = ed.enrollment_id
             JOIN students s            ON s.student_id      = e.student_id
             LEFT JOIN applications app ON app.application_id = s.application_id
             LEFT JOIN applicant_personal_info api ON api.application_id = app.application_id
             LEFT JOIN applicant_contact ac         ON ac.application_id = app.application_id
             WHERE ed.section_subject_id IN (
                   SELECT id FROM section_subjects WHERE section_id = ?
             )
             ORDER BY api.last_name, api.first_name'
        );
        $rows->execute([$id]);
        $students = $rows->fetchAll();
        ok($students, count($students) . ' students');
    }
    /* ── Search available students for section ───────────────────────────────── */
    if ($action === 'search_students') {
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $query = trim($_GET['query'] ?? '');
        if (!$sectionId) fail('section_id required.');

        $sec = $db->prepare(
            "SELECT sec.section_name
             FROM sections sec
             JOIN subjects sub ON sub.subject_id = sec.subject_id
             WHERE sec.section_id = ?"
        );
        $sec->execute([$sectionId]);
        $section = $sec->fetch();
        if (!$section) fail('Section not found.', 404);

        $sql = 
            'SELECT DISTINCT
                    s.student_id,
                    s.student_number,
                    api.first_name,
                    api.middle_name,
                    api.last_name,
                    api.sex AS gender,
                    ac.email_address AS email,
                    ac.contact_number
             FROM enrollments e
             JOIN students s ON s.student_id = e.student_id
             LEFT JOIN applicant_personal_info api ON api.application_id = s.application_id
             LEFT JOIN applicant_contact ac ON ac.application_id = s.application_id
             WHERE 1=1';

        $params = [];
        if ($query !== '') {
            $sql .= ' AND (
                   s.student_number LIKE ? OR
                   api.first_name LIKE ? OR
                   api.last_name LIKE ? OR
                   ac.email_address LIKE ? OR
                   ac.contact_number LIKE ?
               )';
            $q = '%' . $query . '%';
            $params = array_merge($params, [$q, $q, $q, $q, $q]);
        }

        $sql .= ' AND NOT EXISTS (
                   SELECT 1
                   FROM enrollment_details ed
                   WHERE ed.enrollment_id = e.enrollment_id
                     AND ed.section_subject_id IN (
                         SELECT id FROM section_subjects WHERE section_id = ?
                     )
               )
             ORDER BY api.last_name, api.first_name
             LIMIT 50';

        $params[] = $sectionId;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll();
        ok($students, count($students) . ' available students');
    }
}

if ($method === 'POST' && ($_GET['action'] ?? '') === 'enroll') {
    $body = body();
    $sectionId = (int)($body['section_id'] ?? 0);
    $studentId = (int)($body['student_id'] ?? 0);
    if (!$sectionId || !$studentId) fail('section_id and student_id are required.');

    $sec = $db->prepare(
        "SELECT sec.section_name
         FROM sections sec
         JOIN subjects sub ON sub.subject_id = sec.subject_id
         WHERE sec.section_id = ?"
    );
    $sec->execute([$sectionId]);
    $section = $sec->fetch();
    if (!$section) fail('Section not found.', 404);

    $stu = $db->prepare(
        "SELECT s.student_number
         FROM students s
         WHERE s.student_id = ?"
    );
    $stu->execute([$studentId]);
    $student = $stu->fetch();
    if (!$student) fail('Student not found.', 404);

    $subj = $db->prepare('SELECT id FROM section_subjects WHERE section_id = ?');
    $subj->execute([$sectionId]);
    $sectionSubjectIds = array_column($subj->fetchAll(), 'id');
    if (empty($sectionSubjectIds)) fail('Section has no subjects to enroll the student in.');

    $en = $db->prepare('SELECT enrollment_id FROM enrollments WHERE student_id = ? LIMIT 1');
    $en->execute([$studentId]);
    $enrollmentId = (int)$en->fetchColumn();
    if (!$enrollmentId) {
        $ins = $db->prepare(
            "INSERT INTO enrollments (student_id, term_id, status, created_at)
             VALUES (?, 1, 'Enrolled', NOW())"
        );
        $ins->execute([$studentId]);
        $enrollmentId = (int)$db->lastInsertId();
    }

    $placeholders = implode(',', array_fill(0, count($sectionSubjectIds), '?'));
    $existing = $db->prepare(
        "SELECT ed.section_subject_id
         FROM enrollment_details ed
         WHERE ed.enrollment_id = ?
           AND ed.section_subject_id IN ($placeholders)"
    );
    $existing->execute(array_merge([$enrollmentId], $sectionSubjectIds));
    $existingIds = array_column($existing->fetchAll(), 'section_subject_id');

    $toInsert = array_values(array_diff($sectionSubjectIds, $existingIds));
    if (empty($toInsert)) {
        fail('This student is already assigned to the selected section.');
    }

    $db->beginTransaction();
    $insDetail = $db->prepare('INSERT INTO enrollment_details (enrollment_id, section_subject_id) VALUES (?, ?)');
    foreach ($toInsert as $sectionSubjectId) {
        $insDetail->execute([$enrollmentId, $sectionSubjectId]);
    }
    $db->commit();

    ok(['enrollment_id' => $enrollmentId, 'added_subject_rows' => count($toInsert)],
       "Student {$student['student_number']} assigned to section {$section['section_name']}.");
}

/* ── DELETE ─────────────────────────────────────────────── */
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) fail('id required.');

    $sec = $db->prepare('SELECT section_name FROM sections WHERE section_id=?');
    $sec->execute([$id]);
    $row = $sec->fetch();
    if (!$row) fail('Section not found.', 404);

    $db->beginTransaction();
    $db->prepare('DELETE FROM section_subjects WHERE section_id=?')->execute([$id]);
    $db->prepare('DELETE FROM sections WHERE section_id=?')->execute([$id]);
    $db->commit();

    ok(null, "Section '{$row['section_name']}' deleted.");
}

fail('Method not allowed.');
