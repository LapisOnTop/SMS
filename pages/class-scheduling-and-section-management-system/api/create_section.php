<?php
/* ============================================================
   FILE: api/create_section.php
   POST — Create a section in sms_db.
   Accepts the original frontend payload:
     { program, year_level, semester, section_label, category,
       max_capacity, term_id, subjects: [{subject_id, subject_code,
       subject_description, units, hours}, …] }
   ============================================================ */

/* Suppress HTML error output — must be first line */
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('POST only.');

$b = body();

/* ── 1. Read & map frontend fields ───────────────────────── */
$sectionName = strtoupper(trim(
    $b['section_label'] ?? $b['section_name'] ?? ''
));
$program     = strtoupper(trim($b['program']    ?? ''));
$yearLevel   = (int)($b['year_level']           ?? 0);
$semester    = (int)($b['semester']             ?? 0);
$category    = in_array($b['category'] ?? '', ['Lecture','Laboratory'])
                 ? $b['category'] : 'Lecture';
$capacity    = min((int)($b['max_capacity'] ?? $b['capacity'] ?? 40), 40);
$termId      = (int)($b['term_id']              ?? 0);
$subjects    = $b['subjects']                   ?? [];   // array of subject objects

/* ── 2. Basic validation ──────────────────────────────────── */
if (!$sectionName) fail('section_label is required.');
if (!$program)     fail('program is required.');
if (!$yearLevel)   fail('year_level is required.');
if (!$semester)    fail('semester is required.');
if ($capacity < 1) fail('max_capacity must be at least 1.');
if (empty($subjects)) fail('No subjects provided.');

try {
    $db = smsDB();

    /* ── 3. Term must exist ──────────────────────────────── */
    if ($termId) {
        $tc = $db->prepare('SELECT term_id FROM terms WHERE term_id=?');
        $tc->execute([$termId]);
        if (!$tc->fetch()) fail('Invalid term_id.');
    }

    /* ── 4. Duplicate section name check ─────────────────── */
    $chk = $db->prepare('SELECT section_id FROM sections WHERE section_name=?');
    $chk->execute([$sectionName]);
    if ($chk->fetch()) fail("Section '{$sectionName}' already exists.");

    /* ── 5. Resolve subjects — prefer subject_id, fallback to subject_code ── */
    $resolvedSubjects = [];
    foreach ($subjects as $sub) {
        $subId = (int)($sub['subject_id'] ?? 0);

        if ($subId) {
            $row = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE subject_id=?');
            $row->execute([$subId]);
            $found = $row->fetch();
        } else {
            $code = trim($sub['subject_code'] ?? '');
            if (!$code) continue;
            $row = $db->prepare('SELECT subject_id, subject_name FROM subjects WHERE subject_code=?');
            $row->execute([$code]);
            $found = $row->fetch();
        }

        if ($found) {
            $resolvedSubjects[] = $found;
        }
        // Silently skip subjects not found in sms_db
    }

    if (empty($resolvedSubjects)) {
        fail('None of the provided subjects were found in sms_db. ' .
             'Ensure subjects are imported and subject_id or subject_code values match.');
    }

    /* Use the first resolved subject as the section's primary subject_id */
    $primarySubjectId = $resolvedSubjects[0]['subject_id'];

    /* ── 6. BEGIN TRANSACTION ─────────────────────────────── */
    $db->beginTransaction();

    /* ── 7. INSERT into sms_db.sections ─────────────────── */
    $ins = $db->prepare(
        'INSERT INTO sections
           (section_name, subject_id, teacher_id, room, day, start_time, end_time, capacity)
         VALUES (?,?,NULL,NULL,NULL,NULL,NULL,?)'
    );
    $ins->execute([$sectionName, $primarySubjectId, $capacity]);
    $sectionId = (int)$db->lastInsertId();

    /* ── 8. INSERT each subject into section_subjects ────── */
    $ssIns = $db->prepare(
        'INSERT INTO section_subjects
           (section_id, subject_id, teacher_id, day, start_time, end_time, room)
         VALUES (?,?,NULL,NULL,NULL,NULL,NULL)'
    );
    foreach ($resolvedSubjects as $rs) {
        $ssIns->execute([$sectionId, $rs['subject_id']]);
    }

    $db->commit();

    ok([
        'section_id'     => $sectionId,
        'section_label'  => $sectionName,   // match old key so frontend toast works
        'section_name'   => $sectionName,
        'program'        => $program,
        'year_level'     => $yearLevel,
        'semester'       => $semester,
        'capacity'       => $capacity,
        'enrolled'       => 0,              // no auto-populate in sms_db flow
        'subjects_added' => count($resolvedSubjects),
    ], "Section '{$sectionName}' created with " . count($resolvedSubjects) . " subject(s).");

} catch (Throwable $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    fail('Creation failed: ' . $e->getMessage(), 500);
}
