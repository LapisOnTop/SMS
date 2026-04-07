<?php
/* ================================================================
   SMS Class Scheduling & Section Management
   FILE: includes/modals.php
   Description: All modal dialogs used across the app
   ================================================================ */
?>

<!-- ── CREATE SECTION ─────────────────────────────────── -->
<div class="modal-overlay" id="modal-createSection">
    <div class="modal">
        <div class="modal-header">
            <h3>📁 Create New Section</h3>
            <button class="modal-close" onclick="closeModal('createSection')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Subject Code <span class="req">*</span></label>
                    <input class="form-control" id="s_subjectCode" placeholder="e.g. CS301"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Section Label <span class="req">*</span></label>
                    <input class="form-control" id="s_sectionLabel" placeholder="e.g. CS301-A"/>
                </div>
                <div class="form-group span2">
                    <label class="form-label">Subject Name <span class="req">*</span></label>
                    <input class="form-control" id="s_subjectName" placeholder="e.g. Data Structures and Algorithms"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Year Level <span class="req">*</span></label>
                    <select class="form-control" id="s_yearLevel">
                        <option value="">— Select —</option>
                        <option value="1">Year 1</option>
                        <option value="2">Year 2</option>
                        <option value="3">Year 3</option>
                        <option value="4">Year 4</option>
                        <option value="5">Year 5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select class="form-control" id="s_category">
                        <option value="">— Select —</option>
                        <option value="LECTURE">Lecture</option>
                        <option value="LABORATORY">Laboratory</option>
                        <option value="SEMINAR">Seminar</option>
                        <option value="GYM">Gym</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Capacity</label>
                    <input class="form-control" id="s_maxCap" type="number" value="40" min="1" max="200"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Academic Term <span class="req">*</span></label>
                    <select class="form-control" id="s_termId">
                        <option value="2526-2ND">2nd Sem 2025–2026</option>
                        <option value="2526-1ST">1st Sem 2025–2026</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('createSection')">Cancel</button>
            <button class="btn btn-primary" onclick="createSection()">Create Section</button>
        </div>
    </div>
</div>

<!-- ── ADD SCHEDULE ───────────────────────────────────── -->
<div class="modal-overlay" id="modal-addSchedule">
    <div class="modal">
        <div class="modal-header">
            <h3>🕐 Add Schedule Entry</h3>
            <button class="modal-close" onclick="closeModal('addSchedule')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group span2">
                    <label class="form-label">Section <span class="req">*</span></label>
                    <select class="form-control" id="sc_sectionId">
                        <option value="">— Select Section —</option>
                        <option value="SEC-2026-0001">CS301-A — Data Structures</option>
                        <option value="SEC-2026-0002">IT201-B — Web Development</option>
                        <option value="SEC-2026-0003">CS401-A — Algorithm Design</option>
                        <option value="SEC-2026-0005">CS201-A — OOP</option>
                        <option value="SEC-2026-0006">IT101-A — Intro to Computing</option>
                    </select>
                </div>
                <div class="form-group span2">
                    <label class="form-label">Instructor Name <span class="req">*</span></label>
                    <input class="form-control" id="sc_instrName" placeholder="e.g. Dr. Santos"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Day of Week <span class="req">*</span></label>
                    <select class="form-control" id="sc_day">
                        <option value="">— Select —</option>
                        <option value="MONDAY">Monday</option>
                        <option value="TUESDAY">Tuesday</option>
                        <option value="WEDNESDAY">Wednesday</option>
                        <option value="THURSDAY">Thursday</option>
                        <option value="FRIDAY">Friday</option>
                        <option value="SATURDAY">Saturday</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Units</label>
                    <input class="form-control" id="sc_units" type="number" step="0.5" value="3.0" min="0.5" max="9"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Time Start <span class="req">*</span></label>
                    <input class="form-control" id="sc_timeStart" type="time" value="08:00"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Time End <span class="req">*</span></label>
                    <input class="form-control" id="sc_timeEnd" type="time" value="09:30"/>
                </div>
            </div>
            <div style="margin-top:12px;padding:10px 12px;background:rgba(42,157,143,0.08);border:1px solid rgba(42,157,143,0.2);border-radius:8px;font-size:.75rem;color:var(--teal-light)">
                ℹ️ Conflict detection runs automatically before saving.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('addSchedule')">Cancel</button>
            <button class="btn btn-primary"   onclick="addSchedule()">Save Schedule</button>
        </div>
    </div>
</div>

<!-- ── ASSIGN ROOM ────────────────────────────────────── -->
<div class="modal-overlay" id="modal-assignRoom">
    <div class="modal">
        <div class="modal-header">
            <h3>🏛️ Assign Room</h3>
            <button class="modal-close" onclick="closeModal('assignRoom')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group span2">
                    <label class="form-label">Schedule Entry <span class="req">*</span></label>
                    <select class="form-control" id="rm_scheduleId">
                        <option value="SCH-2026-00001">SCH-2026-00001 — CS301-A, Monday 08:00–09:30</option>
                        <option value="SCH-2026-00002">SCH-2026-00002 — IT201-B, Monday 10:00–11:30</option>
                        <option value="SCH-2026-00003">SCH-2026-00003 — CS401-A, Tuesday 08:00–09:30</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Room ID <span class="req">*</span></label>
                    <input class="form-control" id="rm_roomId" placeholder="e.g. RM-204"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Room Name <span class="req">*</span></label>
                    <input class="form-control" id="rm_roomName" placeholder="e.g. Computer Lab 2"/>
                </div>
            </div>
            <div id="roomConflictAlert" style="display:none;margin-top:10px;padding:10px;background:rgba(224,82,82,0.1);border:1px solid rgba(224,82,82,0.3);border-radius:8px;font-size:.75rem;color:var(--rose-light)">
                ⚠️ Room conflict detected. This room is already occupied at the selected time.
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('assignRoom')">Cancel</button>
            <button class="btn btn-primary"   onclick="assignRoom()">Assign Room</button>
        </div>
    </div>
</div>

<!-- ── CREATE TERM ────────────────────────────────────── -->
<div class="modal-overlay" id="modal-createTerm">
    <div class="modal">
        <div class="modal-header">
            <h3>📆 New Academic Term</h3>
            <button class="modal-close" onclick="closeModal('createTerm')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Term ID <span class="req">*</span></label>
                    <input class="form-control" id="t_termId" placeholder="e.g. 2627-1ST"/>
                    <span class="form-hint">Use format: YYYYYY-SEM (e.g. 2627-1ST)</span>
                </div>
                <div class="form-group">
                    <label class="form-label">School Year <span class="req">*</span></label>
                    <input class="form-control" id="t_schoolYear" placeholder="e.g. 2026-2027"/>
                </div>
                <div class="form-group span2">
                    <label class="form-label">Term Label <span class="req">*</span></label>
                    <input class="form-control" id="t_label" placeholder="e.g. 1st Semester 2026-2027"/>
                </div>
                <div class="form-group">
                    <label class="form-label">Semester</label>
                    <select class="form-control" id="t_semester">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                        <option value="3">Summer</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Set as Active</label>
                    <select class="form-control" id="t_active">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date <span class="req">*</span></label>
                    <input class="form-control" id="t_startDate" type="date"/>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date <span class="req">*</span></label>
                    <input class="form-control" id="t_endDate" type="date"/>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('createTerm')">Cancel</button>
            <button class="btn btn-primary"   onclick="createTerm()">Create Term</button>
        </div>
    </div>
</div>
