/* ================================================================
   SMS Class Scheduling & Section Management
   FILE: assets/js/actions.js
   Description: All CRUD actions and form submit handlers.
                Each function tries the PHP backend API first,
                then falls back to in-memory state for demo mode.
   ================================================================ */

/* ═══════════════════════════════════════════════════════════
   SECTIONS
   ═══════════════════════════════════════════════════════════ */

async function createSection() {
    const label   = document.getElementById('s_sectionLabel').value.trim();
    const code    = document.getElementById('s_subjectCode').value.trim();
    const name    = document.getElementById('s_subjectName').value.trim();
    const year    = document.getElementById('s_yearLevel').value;
    const cat     = document.getElementById('s_category').value;
    const cap     = parseInt(document.getElementById('s_maxCap').value) || 40;
    const termId  = document.getElementById('s_termId').value;

    /* Validation */
    if (!label || !code || !name || !year || !cat || !termId) {
        toast('error', 'Validation Error', 'Please fill all required fields.'); return;
    }
    if (SMS.sections.find(s => s.label === label.toUpperCase())) {
        toast('error', 'Duplicate Section', `Section "${label.toUpperCase()}" already exists for this term.`); return;
    }

    const payload = {
        section_label: label.toUpperCase(),
        subject_code:  code.toUpperCase(),
        subject_name:  name,
        year_level:    parseInt(year),
        category:      cat,
        max_capacity:  cap,
        term_id:       termId,
    };

    /* Try backend */
    const res = await API.post('/sections', payload);
    if (res && res.status === 'success') {
        toast('success', 'Section Created', `${res.data.section_label} saved to database.`);
        /* Reload sections from backend */
        await loadSections();
    } else {
        /* Fallback: in-memory */
        SMS.sections.unshift({
            id:      'SEC-' + Date.now().toString().slice(-8),
            label:   payload.section_label,
            subject: payload.subject_code,
            name:    payload.subject_name,
            year:    payload.year_level,
            cat:     payload.category,
            cap:     payload.max_capacity,
            count:   0,
            status:  'OPEN',
            term:    payload.term_id,
        });
        toast('success', 'Section Created', `${payload.section_label} added (demo mode).`);
    }

    closeModal('createSection');
    clearForm(['s_sectionLabel','s_subjectCode','s_subjectName','s_yearLevel','s_category']);
    renderSections();
}

async function loadSections() {
    const termId = 'current'; /* replace with active term from state */
    const res = await API.get(`/sections?term_id=${termId}`);
    if (res && res.data) {
        SMS.sections = res.data.map(mapSection);
        renderSections();
    }
}

function viewSection(id) {
    const s = SMS.sections.find(x => x.id === id);
    if (!s) return;
    toast('info', s.label, `${s.name} | Year ${s.year} | ${s.cat} | ${s.count}/${s.cap} enrolled`);
}

function editSection(id) {
    toast('info', 'Edit Section', `Opening editor for section ${id}…`);
    /* TODO: populate edit modal and open it */
}

async function deleteSection(id) {
    if (!confirm('Delete this section? This cannot be undone.')) return;

    const res = await API.delete(`/sections/${id}`);
    if (res && res.status === 'success') {
        toast('success', 'Deleted', 'Section removed from database.');
    } else {
        toast('success', 'Deleted', 'Section removed (demo mode).');
    }

    SMS.sections = SMS.sections.filter(s => s.id !== id);
    renderSections();
}

function filterSections() {
    /* TODO: wire to filter bar values and call renderSections(filtered) */
    renderSections();
}

/* ═══════════════════════════════════════════════════════════
   SCHEDULES
   ═══════════════════════════════════════════════════════════ */

async function addSchedule() {
    const sectionId  = document.getElementById('sc_sectionId').value;
    const instrName  = document.getElementById('sc_instrName').value.trim();
    const day        = document.getElementById('sc_day').value;
    const timeStart  = document.getElementById('sc_timeStart').value;
    const timeEnd    = document.getElementById('sc_timeEnd').value;
    const units      = parseFloat(document.getElementById('sc_units').value) || 3.0;

    if (!sectionId || !instrName || !day || !timeStart || !timeEnd) {
        toast('error', 'Validation Error', 'Please fill all required fields.'); return;
    }
    if (timeEnd <= timeStart) {
        toast('error', 'Invalid Time', 'End time must be after start time.'); return;
    }

    /* Client-side conflict check */
    const conflict = SMS.schedules.find(s =>
        s.instr === instrName && s.day === day &&
        s.ts < timeEnd && s.te > timeStart
    );
    if (conflict) {
        toast('error', 'Conflict Detected',
            `${instrName} already has a class on ${day} at ${conflict.ts}–${conflict.te}.`);
        return;
    }

    const sectionLabel = document.getElementById('sc_sectionId')
                                 .selectedOptions[0]?.text.split('—')[0].trim() || sectionId;

    const payload = {
        section_id:      sectionId,
        instructor_id:   'FAC-' + Date.now().toString().slice(-4),
        instructor_name: instrName,
        day_of_week:     day,
        time_start:      timeStart,
        time_end:        timeEnd,
        units,
        term_id:         '2526-2ND',
    };

    const res = await API.post('/schedules', payload);
    if (res && res.status === 'success') {
        toast('success', 'Schedule Saved', 'Entry saved to database.');
    } else {
        SMS.schedules.push({
            id:      'SCH-' + Date.now().toString().slice(-8),
            section: sectionLabel,
            subject: '',
            instr:   instrName,
            day:     day,
            ts:      timeStart,
            te:      timeEnd,
            units,
            room:    '',
            cat:     'LECTURE',
        });
        toast('success', 'Schedule Added', 'Entry added (demo mode).');
    }

    closeModal('addSchedule');
    clearForm(['sc_sectionId','sc_instrName','sc_day']);
    renderSchedules();
    renderTimetable();
}

function editSchedule(id) {
    toast('info', 'Edit Schedule', `Opening editor for ${id}…`);
}

async function deleteSchedule(id) {
    if (!confirm('Delete this schedule entry?')) return;

    const res = await API.delete(`/schedules/${id}`);
    SMS.schedules = SMS.schedules.filter(s => s.id !== id);
    renderSchedules();
    renderTimetable();
    toast('success', 'Deleted', 'Schedule entry removed.');
}

/* ═══════════════════════════════════════════════════════════
   ROOMS
   ═══════════════════════════════════════════════════════════ */

async function assignRoom() {
    const roomId   = document.getElementById('rm_roomId').value.trim();
    const roomName = document.getElementById('rm_roomName').value.trim();

    if (!roomId || !roomName) {
        toast('error', 'Validation Error', 'Room ID and Room Name are required.'); return;
    }

    const res = await API.post('/rooms/assign', { room_id: roomId, room_name: roomName });
    if (res && res.status === 'success') {
        toast('success', 'Room Assigned', `${roomId} assigned to database.`);
    } else {
        toast('success', 'Room Assigned', `${roomId} assigned (demo mode).`);
    }

    closeModal('assignRoom');
    clearForm(['rm_roomId','rm_roomName']);
    renderRooms();
}

function unassignRoom(scheduleId) {
    if (!confirm('Remove this room assignment?')) return;
    const sched = SMS.schedules.find(s => s.id === scheduleId);
    if (sched) sched.room = '';
    renderRooms();
    toast('success', 'Room Removed', 'Room assignment has been cleared.');
}

function updateRoomGrid() {
    renderRooms();
}

/* ═══════════════════════════════════════════════════════════
   CONFLICTS
   ═══════════════════════════════════════════════════════════ */

async function detectConflicts() {
    toast('info', 'Running Detection…', 'Scanning all schedules for conflicts…');

    const res = await API.post('/conflicts/detect', { term_id: '2526-2ND' });
    if (res && res.data) {
        toast(
            res.data.conflicts_found ? 'warning' : 'success',
            res.data.conflicts_found ? `${res.data.conflicts_found} Conflict(s) Found` : 'No Conflicts',
            res.data.conflicts_found ? 'Review details in the Conflicts page.' : 'All schedules are clean.'
        );
    } else {
        /* Demo: recount from state */
        setTimeout(() => {
            const found = SMS.conflicts.filter(c => c.status === 'UNRESOLVED').length;
            toast(
                found ? 'warning' : 'success',
                found ? `${found} Conflict(s) Found` : 'No Conflicts',
                found ? 'Review details in the Conflicts page.' : 'All schedules are clean.'
            );
        }, 1400);
    }

    renderConflicts();
}

async function resolveConflict(id, status) {
    const res = await API.patch(`/conflicts/${id}/resolve`, { resolution_status: status });
    SMS.conflicts = SMS.conflicts.map(c => c.id === id ? { ...c, status } : c);
    renderConflicts();
    toast('success',
        status === 'RESOLVED' ? 'Conflict Resolved' : 'Conflict Ignored',
        `Conflict ${id} marked as ${status.toLowerCase()}.`
    );
}

/* ═══════════════════════════════════════════════════════════
   TIMETABLE
   ═══════════════════════════════════════════════════════════ */

function generateTimetable() {
    toast('info', 'Generating Timetable…', 'Optimizing time slot distribution…');
    setTimeout(() => {
        renderTimetable();
        toast('success', 'Timetable Generated', 'Schedule grid has been updated.');
    }, 1200);
}

/* ═══════════════════════════════════════════════════════════
   TERMS
   ═══════════════════════════════════════════════════════════ */

async function createTerm() {
    closeModal('createTerm');
    toast('success', 'Term Created', 'New academic term has been added.');
    /* TODO: collect form values and POST to /terms */
}

async function activateTerm(id) {
    const res = await API.patch(`/terms/${id}/activate`, {});
    SMS.terms = SMS.terms.map(t => ({ ...t, active: t.id === id ? 1 : 0 }));
    renderTerms();
    toast('success', 'Term Activated', `Term ${id} is now the active term.`);
}

/* ═══════════════════════════════════════════════════════════
   HELPERS
   ═══════════════════════════════════════════════════════════ */

function clearForm(ids) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = el.tagName === 'SELECT' ? '' : '';
    });
}

/** Map backend section object to frontend format */
function mapSection(s) {
    return {
        id:      s.section_id,
        label:   s.section_label,
        subject: s.subject_code,
        name:    s.subject_name,
        year:    s.year_level,
        cat:     s.category,
        cap:     s.max_capacity,
        count:   s.current_count,
        status:  s.status,
        term:    s.term_id,
    };
}
