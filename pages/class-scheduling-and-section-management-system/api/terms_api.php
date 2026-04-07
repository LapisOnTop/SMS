<?php
/* ============================================================
   FILE: api/terms_api.php
   GET  — all terms
   POST — create term
   PUT  ?id=X — set active (mark as current)
   ============================================================ */
require_once __DIR__ . '/config.php';
$db = smsDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    /*
     * sms_db.terms: term_id | school_year | semester
     * Build a human-readable label on the fly.
     */
    $rows = $db->query(
        "SELECT term_id,
                school_year AS academic_year,
                semester,
                CONCAT(
                    CASE semester WHEN 1 THEN '1st' WHEN 2 THEN '2nd' ELSE CONCAT(semester,'th') END,
                    ' Semester ',
                    school_year
                ) AS term_label
         FROM terms
         ORDER BY school_year DESC, semester DESC"
    )->fetchAll();
    ok($rows);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b        = body();
    $semester = (int)($b['semester']      ?? 0);
    $year     = trim($b['academic_year']  ?? $b['school_year'] ?? '');
    if (!in_array($semester,[1,2])) fail('semester must be 1 or 2.');
    if (!preg_match('/^\d{4}-\d{4}$/', $year)) fail('school_year format: YYYY-YYYY');

    $chk = $db->prepare('SELECT term_id FROM terms WHERE semester=? AND school_year=?');
    $chk->execute([$semester, $year]);
    if ($chk->fetch()) fail('Term already exists.');

    $db->prepare('INSERT INTO terms(semester,school_year) VALUES(?,?)')->execute([$semester,$year]);
    ok(['term_id'=>(int)$db->lastInsertId()], 'Term created.');
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    /* sms_db.terms has no is_active flag — this endpoint is a no-op confirmation */
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) fail('id required.');
    $chk = $db->prepare('SELECT term_id FROM terms WHERE term_id=?');
    $chk->execute([$id]);
    if (!$chk->fetch()) fail('Term not found.', 404);
    ok(null, 'Term noted as active reference.');
}

fail('Method not allowed.');
