<?php
/* ============================================================
   FILE: api/assign_schedule.php
   Room assignment for sections. Rooms 1–15 (stored as "1"–"15").
   Schedule is Morning (07:00–12:00) or Afternoon (12:00–17:00).

   GET  ?section_id=X  — current assignment + suggestion
   POST               — assign room_number + time_schedule

   JS reads: section.room_number (INT), section.time_schedule,
             suggestion.room_number (INT), suggestion.time_schedule
   ============================================================ */
ini_set('display_errors', 0);
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = smsDB();

/* ── Normalize stored room varchar → INT 1-15 ─────────────── */
function toRoomInt(?string $v): ?int {
    if ($v === null || $v === '') return null;
    $n = (int)$v;
    if ($n <= 0) return null;
    if ($n <= 15) return $n;
    $mod = $n % 100;
    return ($mod >= 1 && $mod <= 15) ? $mod : null;
}

/* ── GET: suggestion + current assignment ────────────────── */
if ($method === 'GET') {
    $sectionId = (int)($_GET['section_id'] ?? 0);
    if (!$sectionId) fail('section_id required.');

    $sec = $db->prepare(
        "SELECT sec.section_id,
                sec.section_name AS section_label,
                sec.room,
                CASE
                    WHEN sec.day IN ('Morning','Afternoon') THEN sec.day
                    WHEN sec.start_time < '12:00:00'        THEN 'Morning'
                    WHEN sec.start_time >= '12:00:00'       THEN 'Afternoon'
                    ELSE NULL
                END              AS time_schedule,
                sec.start_time,
                sec.end_time
         FROM sections sec
         WHERE sec.section_id = ?"
    );
    $sec->execute([$sectionId]);
    $section = $sec->fetch();
    if (!$section) fail('Section not found.');

    /* Expose as room_number (INT) for JS */
    $section['room_number'] = toRoomInt($section['room']);
    unset($section['room']);

    /* All occupied slots (excluding this section) */
    $occupied = $db->query(
        "SELECT room, day AS time_schedule
         FROM sections
         WHERE room IS NOT NULL AND day IS NOT NULL
           AND section_id != {$sectionId}"
    )->fetchAll();

    $taken = [];
    foreach ($occupied as $o) {
        $rn = toRoomInt($o['room']);
        if ($rn) $taken[$rn][$o['time_schedule']] = true;
    }

    /* Suggest first free slot, rooms 1–15 Morning→Afternoon */
    $suggestion = null;
    for ($r = 1; $r <= 15; $r++) {
        foreach (['Morning', 'Afternoon'] as $slot) {
            if (!isset($taken[$r][$slot])) {
                $suggestion = [
                    'room_number'   => $r,
                    'time_schedule' => $slot,
                    'start_time'    => $slot === 'Morning' ? '07:00:00' : '12:00:00',
                    'end_time'      => $slot === 'Morning' ? '12:00:00' : '17:00:00',
                ];
                break 2;
            }
        }
    }

    ok(['section' => $section, 'suggestion' => $suggestion, 'occupied' => $occupied]);
}

/* ── POST: assign ────────────────────────────────────────── */
if ($method === 'POST') {
    $b          = body();
    $sectionId  = (int)($b['section_id']   ?? 0);
    $roomNumber = (int)($b['room_number']   ?? 0);
    $timeSlot   = trim($b['time_schedule'] ?? '');

    if (!$sectionId || !$roomNumber || !$timeSlot)
        fail('section_id, room_number, time_schedule required.');
    if ($roomNumber < 1 || $roomNumber > 15)
        fail('room_number must be 1–15.');
    if (!in_array($timeSlot, ['Morning', 'Afternoon']))
        fail('time_schedule must be Morning or Afternoon.');

    $startTime = $timeSlot === 'Morning' ? '07:00:00' : '12:00:00';
    $endTime   = $timeSlot === 'Morning' ? '12:00:00' : '17:00:00';
    $roomStr   = (string)$roomNumber;   // store as "1"–"15"

    /* Verify section exists */
    $secStmt = $db->prepare('SELECT section_name FROM sections WHERE section_id=?');
    $secStmt->execute([$sectionId]);
    $secRow = $secStmt->fetch();
    if (!$secRow) fail('Section not found.');

    /* Conflict check: same room AND same time slot, different section.
       Also check any section storing room as 100x variant. */
    $conflict = $db->prepare(
        "SELECT section_name FROM sections
         WHERE (room = ? OR room = ?)
           AND day = ?
           AND section_id != ?"
    );
    $conflict->execute([$roomStr, '100'.$roomStr, $timeSlot, $sectionId]);
    $existing = $conflict->fetch();
    if ($existing) {
        fail("Room {$roomNumber} at {$timeSlot} is already occupied by '{$existing['section_name']}'.");
    }

    /* Save — always write as "1"–"15" from now on */
    $db->prepare(
        'UPDATE sections SET room=?, day=?, start_time=?, end_time=? WHERE section_id=?'
    )->execute([$roomStr, $timeSlot, $startTime, $endTime, $sectionId]);

    $db->prepare(
        'UPDATE section_subjects SET room=?, day=?, start_time=?, end_time=? WHERE section_id=?'
    )->execute([$roomStr, $timeSlot, $startTime, $endTime, $sectionId]);

    ok([
        'room_number'   => $roomNumber,
        'time_schedule' => $timeSlot,
        'start_time'    => $startTime,
        'end_time'      => $endTime,
    ], "Room {$roomNumber} ({$timeSlot}) assigned to '{$secRow['section_name']}'.");
}

fail('Method not allowed.');
