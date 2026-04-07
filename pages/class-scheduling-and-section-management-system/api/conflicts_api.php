<?php
/* ============================================================
   FILE: api/conflicts_api.php
   Detects and lists scheduling conflicts in sms_db.
   Conflicts are detected live (sms_db has no conflicts table).

   GET  ?action=detect  — run detection and return results
   GET  ?action=list    — run detection (same as detect)
   ============================================================ */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = smsDB();

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    $found  = [];

    /* ── 1. Room conflicts: same room, same day, overlapping times ── */
    $room = $db->query(
        'SELECT a.section_id AS id_a, a.section_name AS lbl_a,
                b.section_id AS id_b, b.section_name AS lbl_b,
                a.room, a.day
         FROM sections a
         JOIN sections b
           ON a.room       = b.room
          AND a.day        = b.day
          AND a.section_id < b.section_id
          AND a.start_time < b.end_time
          AND a.end_time   > b.start_time
         WHERE a.room IS NOT NULL AND a.day IS NOT NULL'
    )->fetchAll();

    foreach ($room as $c) {
        $found[] = [
            'type'        => 'Room',
            'section_a'   => $c['lbl_a'],
            'section_b'   => $c['lbl_b'],
            'description' => "Room {$c['room']} double-booked on {$c['day']}: {$c['lbl_a']} and {$c['lbl_b']}",
            'is_resolved' => 0,
        ];
    }

    /* ── 2. Faculty conflicts: same teacher, same day, overlapping ── */
    $fac = $db->query(
        'SELECT a.section_name AS sec_a, b.section_name AS sec_b,
                u.username AS faculty_name,
                a.day
         FROM sections a
         JOIN sections b
           ON a.teacher_id  = b.teacher_id
          AND a.day         = b.day
          AND a.section_id  < b.section_id
          AND a.start_time  < b.end_time
          AND a.end_time    > b.start_time
         JOIN users u ON u.user_id = a.teacher_id
         WHERE a.teacher_id IS NOT NULL'
    )->fetchAll();

    foreach ($fac as $c) {
        $found[] = [
            'type'        => 'Faculty',
            'section_a'   => $c['sec_a'],
            'section_b'   => $c['sec_b'],
            'description' => "{$c['faculty_name']} double-booked on {$c['day']}: {$c['sec_a']} and {$c['sec_b']}",
            'is_resolved' => 0,
        ];
    }

    /* ── 3. Section_subjects: same teacher, same day, time overlap ── */
    $ss = $db->query(
        'SELECT sec.section_name AS sec_a, sec2.section_name AS sec_b,
                u.username AS faculty_name,
                ss.day
         FROM section_subjects ss
         JOIN section_subjects ss2
           ON ss.teacher_id  = ss2.teacher_id
          AND ss.day         = ss2.day
          AND ss.id          < ss2.id
          AND ss.start_time  < ss2.end_time
          AND ss.end_time    > ss2.start_time
         JOIN sections sec  ON sec.section_id  = ss.section_id
         JOIN sections sec2 ON sec2.section_id = ss2.section_id
         JOIN users u       ON u.user_id       = ss.teacher_id
         WHERE ss.teacher_id IS NOT NULL
           AND ss.start_time IS NOT NULL'
    )->fetchAll();

    foreach ($ss as $c) {
        $found[] = [
            'type'        => 'Faculty',
            'section_a'   => $c['sec_a'],
            'section_b'   => $c['sec_b'],
            'description' => "{$c['faculty_name']} subject-level conflict on {$c['day']}: {$c['sec_a']} and {$c['sec_b']}",
            'is_resolved' => 0,
        ];
    }

    /* Deduplicate */
    $unique = array_unique(array_map('serialize', $found));
    $found  = array_map('unserialize', $unique);
    $found  = array_values($found);

    $stats = [
        'room'       => count(array_filter($found, fn($r) => $r['type'] === 'Room')),
        'faculty'    => count(array_filter($found, fn($r) => $r['type'] === 'Faculty')),
        'section'    => 0,
        'unresolved' => count($found),
    ];

    ok(['conflicts' => $found, 'stats' => $stats, 'detected' => $found, 'count' => count($found)],
       count($found) . ' conflict(s) found.');
}

/* ── PUT / DELETE are no-ops since there's no conflicts table ── */
if ($method === 'PUT' || $method === 'DELETE') {
    ok(null, 'Conflicts are computed live in sms_db; no stored record to update.');
}

fail('Method not allowed.');
