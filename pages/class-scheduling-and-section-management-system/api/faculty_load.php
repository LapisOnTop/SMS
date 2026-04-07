<?php
/* ============================================================
   FILE: api/faculty_load.php
   Uses sms_db.faculty table (faculty_id, faculty_code, user_id,
   first_name, last_name, designation, type, max_units, …)
   Teacher assignments tracked in section_subjects.teacher_id
   which links to users.user_id = faculty.user_id

   GET  ?action=list_faculty          — all active faculty
   GET  ?action=section_subjects&id=X — subjects for a section
   GET  ?action=loads&term_id=X       — sections per faculty
   GET  ?action=summary               — faculty load summary
   POST                               — assign faculty to section_subject
   DELETE ?load_id=X                  — remove teacher from section_subject
   ============================================================ */
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db     = smsDB();

/* ── Helper: get assigned units for a faculty by user_id ─── */
function getAssignedUnits(PDO $db, int $userId): float {
    $s = $db->prepare(
        'SELECT COALESCE(SUM(sub.units),0) AS total
         FROM section_subjects ss
         JOIN subjects sub ON sub.subject_id = ss.subject_id
         WHERE ss.teacher_id = ?'
    );
    $s->execute([$userId]);
    return (float)($s->fetch()['total'] ?? 0);
}

/* ── GET ─────────────────────────────────────────────────── */
if ($method === 'GET') {

    /* ── List all active faculty ────────────────────────── */
    if ($action === 'list_faculty') {
        $rows = $db->query(
            "SELECT f.faculty_id,
                    f.faculty_code,
                    f.user_id,
                    CONCAT(f.last_name, ', ', f.first_name) AS full_name,
                    f.designation,
                    f.type,
                    f.max_units,
                    f.specialization,
                    f.email
             FROM faculty f
             WHERE f.status = 'Active'
             ORDER BY f.last_name, f.first_name"
        )->fetchAll();

        foreach ($rows as &$f) {
            $asgn = getAssignedUnits($db, (int)$f['user_id']);
            $maxU = (int)$f['max_units'];
            $pct  = $maxU > 0 ? ($asgn / $maxU) * 100 : 0;
            $f['assigned_units'] = $asgn;
            $f['available']      = $maxU - $asgn;
            $f['load_pct']       = round($pct, 1);
            $f['load_status']    = $pct >= 100 ? 'OVERLOADED' : ($pct >= 80 ? 'NEAR_LIMIT' : 'WITHIN_LIMIT');
        }
        ok($rows);
    }

    /* ── Section subjects (with assigned faculty name) ───── */
    if ($action === 'section_subjects') {
        $secId = (int)($_GET['id'] ?? 0);
        if (!$secId) fail('section id required.');

        $stmt = $db->prepare(
            "SELECT ss.id,
                    sub.subject_code,
                    sub.subject_name  AS subject_desc,
                    sub.units,
                    ss.teacher_id     AS faculty_id,
                    CASE WHEN f.faculty_id IS NOT NULL
                         THEN CONCAT(f.last_name, ', ', f.first_name)
                         ELSE u.username
                    END               AS professor,
                    ss.day, ss.start_time, ss.end_time, ss.room
             FROM section_subjects ss
             JOIN subjects sub ON sub.subject_id = ss.subject_id
             LEFT JOIN users u    ON u.user_id    = ss.teacher_id
             LEFT JOIN faculty f  ON f.user_id    = ss.teacher_id
             WHERE ss.section_id = ?
             ORDER BY sub.subject_code"
        );
        $stmt->execute([$secId]);
        ok($stmt->fetchAll());
    }

    /* ── Loads grouped by faculty ────────────────────────── */
    if ($action === 'loads') {
        $stmt = $db->query(
            "SELECT ss.id AS load_id,
                    f.faculty_id,
                    f.faculty_code,
                    CONCAT(f.last_name, ', ', f.first_name) AS faculty_name,
                    sub.subject_code,
                    sub.subject_name AS subject_desc,
                    sub.units,
                    sec.section_name AS sec_label,
                    ss.day, ss.start_time, ss.end_time, ss.room
             FROM section_subjects ss
             JOIN subjects  sub ON sub.subject_id = ss.subject_id
             JOIN sections  sec ON sec.section_id  = ss.section_id
             JOIN faculty   f   ON f.user_id       = ss.teacher_id
             WHERE ss.teacher_id IS NOT NULL
             ORDER BY f.last_name, f.first_name, sec.section_name"
        );
        $grouped = [];
        foreach ($stmt->fetchAll() as $r) {
            $fid = $r['faculty_id'];
            $grouped[$fid]['faculty_code'] = $r['faculty_code'];
            $grouped[$fid]['faculty_name'] = $r['faculty_name'];
            $grouped[$fid]['loads'][]      = $r;
        }
        ok(array_values($grouped));
    }

    /* ── Summary per faculty ─────────────────────────────── */
    if ($action === 'summary') {
        $rows = $db->query(
            "SELECT f.faculty_id,
                    f.faculty_code,
                    CONCAT(f.last_name, ', ', f.first_name) AS full_name,
                    f.type,
                    f.max_units,
                    f.user_id,
                    COUNT(ss.id) AS section_count,
                    COALESCE(SUM(sub.units), 0) AS assigned_units,
                    GROUP_CONCAT(DISTINCT sec.section_name
                                 ORDER BY sec.section_name SEPARATOR ', ') AS sections
             FROM faculty f
             LEFT JOIN section_subjects ss ON ss.teacher_id = f.user_id
             LEFT JOIN subjects  sub ON sub.subject_id = ss.subject_id
             LEFT JOIN sections  sec ON sec.section_id  = ss.section_id
             WHERE f.status = 'Active'
             GROUP BY f.faculty_id
             ORDER BY f.last_name, f.first_name"
        )->fetchAll();

        foreach ($rows as &$f) {
            $maxU = (int)$f['max_units'];
            $asgn = (float)$f['assigned_units'];
            $pct  = $maxU > 0 ? ($asgn / $maxU) * 100 : 0;
            $f['load_pct']    = round($pct, 1);
            $f['load_status'] = $pct >= 100 ? 'OVERLOADED' : ($pct >= 80 ? 'NEAR_LIMIT' : 'WITHIN_LIMIT');
            $f['sections']    = $f['sections'] ?? '—';
        }

        $within = count(array_filter($rows, fn($r) => $r['load_status'] === 'WITHIN_LIMIT'));
        $near   = count(array_filter($rows, fn($r) => $r['load_status'] === 'NEAR_LIMIT'));
        $over   = count(array_filter($rows, fn($r) => $r['load_status'] === 'OVERLOADED'));
        ok(['faculty' => $rows, 'stats' => compact('within','near','over')]);
    }
}

/* ── POST: assign faculty to a section_subject ───────────── */
if ($method === 'POST') {
    $b            = body();
    $sectionSubId = (int)($b['subject_id']  ?? 0);   // section_subjects.id
    $facultyId    = (int)($b['faculty_id']  ?? 0);   // faculty.faculty_id
    $termId       = (int)($b['term_id']     ?? 0);

    if (!$sectionSubId || !$facultyId)
        fail('subject_id (section_subjects.id) and faculty_id required.');

    /* Get section_subject info */
    $ssStmt = $db->prepare(
        'SELECT ss.id, sub.units, sub.subject_code, sec.section_name,
                ss.day, ss.start_time, ss.end_time
         FROM section_subjects ss
         JOIN subjects  sub ON sub.subject_id = ss.subject_id
         JOIN sections  sec ON sec.section_id  = ss.section_id
         WHERE ss.id = ?'
    );
    $ssStmt->execute([$sectionSubId]);
    $ss = $ssStmt->fetch();
    if (!$ss) fail('Section subject not found.');

    /* Get faculty — resolve user_id from faculty_id */
    $facStmt = $db->prepare(
        "SELECT f.faculty_id, f.user_id, f.max_units,
                CONCAT(f.last_name, ', ', f.first_name) AS full_name,
                f.type
         FROM faculty f
         WHERE f.faculty_id = ? AND f.status = 'Active'"
    );
    $facStmt->execute([$facultyId]);
    $fac = $facStmt->fetch();
    if (!$fac) fail('Faculty not found or inactive.');

    $userId  = (int)$fac['user_id'];
    $maxUnits = (int)$fac['max_units'];

    /* Check unit load */
    $current = getAssignedUnits($db, $userId);
    if (($current + $ss['units']) > $maxUnits)
        fail("Adding {$ss['units']} units would exceed {$fac['full_name']}'s maximum of {$maxUnits} units (currently {$current}).");

    /* Time conflict check */
    if ($ss['start_time'] && $ss['end_time'] && $ss['day']) {
        $timeConf = $db->prepare(
            'SELECT sec.section_name
             FROM section_subjects ss2
             JOIN sections sec ON sec.section_id = ss2.section_id
             WHERE ss2.teacher_id = ?
               AND ss2.day        = ?
               AND ss2.start_time < ?
               AND ss2.end_time   > ?
               AND ss2.id != ?'
        );
        $timeConf->execute([
            $userId, $ss['day'],
            $ss['end_time'], $ss['start_time'],
            $sectionSubId,
        ]);
        $cf = $timeConf->fetch();
        if ($cf) fail("{$fac['full_name']} already has a class at this time in section '{$cf['section_name']}'.");
    }

    /* Assign teacher_id (user_id) to section_subject */
    $db->prepare('UPDATE section_subjects SET teacher_id=? WHERE id=?')
       ->execute([$userId, $sectionSubId]);

    /* Also update parent sections.teacher_id if it matches this section */
    $db->prepare(
        'UPDATE sections SET teacher_id=?
         WHERE section_id=(SELECT section_id FROM section_subjects WHERE id=?)'
    )->execute([$userId, $sectionSubId]);

    ok(['load_id' => $sectionSubId], "{$fac['full_name']} assigned to {$ss['subject_code']}.");
}

/* ── DELETE: remove teacher from section_subject ─────────── */
if ($method === 'DELETE') {
    $loadId = (int)($_GET['load_id'] ?? 0);
    if (!$loadId) fail('load_id required.');

    $row = $db->prepare('SELECT id FROM section_subjects WHERE id=?');
    $row->execute([$loadId]);
    if (!$row->fetch()) fail('Section subject not found.');

    $db->prepare('UPDATE section_subjects SET teacher_id=NULL WHERE id=?')->execute([$loadId]);
    ok(null, 'Teacher assignment removed.');
}

fail('Method not allowed.');
