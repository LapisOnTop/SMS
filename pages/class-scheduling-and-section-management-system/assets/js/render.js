/* ================================================================
   SMS Class Scheduling & Section Management
   FILE: assets/js/render.js
   Description: All DOM render functions for every page/table
   ================================================================ */

/* ── SECTIONS TABLE ──────────────────────────────────────── */
function renderSections(data) {
    const items = data || SMS.sections;
    const tbody = document.getElementById('sectionTableBody');
    if (!tbody) return;

    if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="8">
            <div class="empty"><div class="ei">📁</div>
            <h3>No sections found</h3><p>Create your first section.</p></div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(s => {
        const pct = s.cap > 0 ? Math.round(s.count / s.cap * 100) : 0;
        const barCls = pct >= 95 ? 'near' : 'safe';
        return `
        <tr>
            <td class="mono">${s.id}</td>
            <td><strong style="color:var(--text)">${s.label}</strong></td>
            <td>${s.name}</td>
            <td class="muted">Year ${s.year}</td>
            <td><span class="tag">${capitalize(s.cat)}</span></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <div class="load-bar-wrap" style="width:60px">
                        <div class="load-bar ${barCls}" style="width:${pct}%"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--text-muted)">${s.count}/${s.cap}</span>
                </div>
            </td>
            <td>${statusBadge(s.status)}</td>
            <td>
                <div class="row-actions">
                    <div class="act-btn view" title="View"   onclick="viewSection('${s.id}')">👁</div>
                    <div class="act-btn edit" title="Edit"   onclick="editSection('${s.id}')">✏️</div>
                    <div class="act-btn del"  title="Delete" onclick="deleteSection('${s.id}')">🗑</div>
                </div>
            </td>
        </tr>`;
    }).join('');

    const countEl = document.getElementById('sectionCount');
    if (countEl) countEl.textContent = items.length;
}

/* ── SCHEDULES TABLE ─────────────────────────────────────── */
function renderSchedules(data) {
    const items = data || SMS.schedules;
    const tbody = document.getElementById('schedTableBody');
    if (!tbody) return;

    if (!items.length) {
        tbody.innerHTML = `<tr><td colspan="8">
            <div class="empty"><div class="ei">🕐</div>
            <h3>No schedules yet</h3><p>Add schedule entries for your sections.</p></div>
        </td></tr>`;
        return;
    }

    tbody.innerHTML = items.map(s => `
        <tr>
            <td class="mono">${s.id}</td>
            <td><span class="section-chip">${s.section}</span></td>
            <td style="color:var(--text-muted);font-size:.8rem">${s.instr}</td>
            <td><span class="badge badge-blue">${s.day.substring(0, 3)}</span></td>
            <td style="font-family:var(--font-mono);font-size:.75rem;color:var(--gold-light)">${s.ts} – ${s.te}</td>
            <td class="muted">${s.units}</td>
            <td style="color:var(--teal-light);font-size:.78rem">${s.room || '—'}</td>
            <td>
                <div class="row-actions">
                    <div class="act-btn edit" title="Edit"   onclick="editSchedule('${s.id}')">✏️</div>
                    <div class="act-btn del"  title="Delete" onclick="deleteSchedule('${s.id}')">🗑</div>
                </div>
            </td>
        </tr>`
    ).join('');

    const countEl = document.getElementById('schedCount');
    if (countEl) countEl.textContent = items.length;
}

/* ── FACULTY TABLE ───────────────────────────────────────── */
function renderFaculty(data) {
    const items = data || SMS.faculty;
    const tbody = document.getElementById('facultyTableBody');
    if (!tbody) return;

    tbody.innerHTML = items.map(f => {
        const pct    = Math.min(100, Math.round(f.units / f.max * 100));
        const barCls = f.status === 'OVERLOADED' ? 'over' : f.status === 'NEAR_LIMIT' ? 'near' : 'safe';
        const stMap  = {
            OVERLOADED:   ['badge-red',   'Overloaded'],
            NEAR_LIMIT:   ['badge-amber', 'Near Limit'],
            WITHIN_LIMIT: ['badge-green', 'Within Limit'],
        };
        const [stCls, stLabel] = stMap[f.status] || ['badge-gray', f.status];
        return `
        <tr>
            <td class="mono">${f.id}</td>
            <td><strong style="color:var(--text)">${f.name}</strong></td>
            <td style="font-weight:500">${f.units}</td>
            <td class="muted">${f.max}</td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;min-width:120px">
                    <div class="load-bar-wrap">
                        <div class="load-bar ${barCls}" style="width:${pct}%"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--text-muted)">${pct}%</span>
                </div>
            </td>
            <td><span class="badge ${stCls}"><span class="badge-dot"></span>${stLabel}</span></td>
            <td style="font-size:.72rem;color:var(--text-muted)">${f.sections.join(', ')}</td>
        </tr>`;
    }).join('');
}

/* ── DASHBOARD LOAD SUMMARY ──────────────────────────────── */
function renderDashLoad() {
    const el = document.getElementById('dashLoadList');
    if (!el) return;
    el.innerHTML = SMS.faculty.slice(0, 5).map(f => {
        const pct    = Math.min(100, Math.round(f.units / f.max * 100));
        const barCls = f.status === 'OVERLOADED' ? 'over' : f.status === 'NEAR_LIMIT' ? 'near' : 'safe';
        return `
        <div style="display:flex;align-items:center;gap:12px;padding:7px 0;border-bottom:1px solid var(--border)">
            <div style="font-size:.78rem;color:var(--text);min-width:170px">${f.name}</div>
            <div class="load-bar-wrap" style="flex:1">
                <div class="load-bar ${barCls}" style="width:${pct}%"></div>
            </div>
            <span style="font-size:.72rem;color:var(--text-muted);min-width:55px;text-align:right">
                ${f.units}/${f.max} u
            </span>
        </div>`;
    }).join('');
}

/* ── CONFLICTS LIST ──────────────────────────────────────── */
function renderConflicts(data) {
    const items  = data || SMS.conflicts;
    const el     = document.getElementById('conflictList');
    const badge  = document.getElementById('conflictBadge');
    if (!el) return;

    const unresolved = items.filter(c => c.status === 'UNRESOLVED');
    if (badge) {
        badge.textContent    = unresolved.length;
        badge.style.display  = unresolved.length ? '' : 'none';
    }

    if (!items.length) {
        el.innerHTML = `<div class="empty">
            <div class="ei">✅</div>
            <h3>No conflicts detected</h3>
            <p>Click "Run Detection" to scan the full schedule.</p>
        </div>`;
        return;
    }

    el.innerHTML = items.map(c => `
        <div class="conflict-card">
            <div class="conflict-type-icon ${c.cls}">${c.icon}</div>
            <div class="conflict-info">
                <h4>${c.title}</h4>
                <p>${c.desc}</p>
                <div class="conflict-meta">${c.id} · ${c.detail} · Detected ${c.detected}</div>
            </div>
            <div class="conflict-actions">
                <span class="badge ${c.status === 'UNRESOLVED' ? 'badge-red' : 'badge-green'}">
                    <span class="badge-dot"></span>${c.status}
                </span>
                <div style="display:flex;gap:6px">
                    <button class="btn btn-sm btn-teal"      onclick="resolveConflict('${c.id}','RESOLVED')">Resolve</button>
                    <button class="btn btn-sm btn-secondary" onclick="resolveConflict('${c.id}','IGNORED')">Ignore</button>
                </div>
            </div>
        </div>`
    ).join('');
}

/* ── TIMETABLE GRID ──────────────────────────────────────── */
function renderTimetable() {
    const grid = document.getElementById('timetableGrid');
    if (!grid) return;

    const DAYS  = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
    const FULL  = ['MONDAY','TUESDAY','WEDNESDAY','THURSDAY','FRIDAY','SATURDAY'];
    const TIMES = [
        '07:00','07:30','08:00','08:30','09:00','09:30',
        '10:00','10:30','11:00','11:30','12:00','12:30',
        '13:00','13:30','14:00','14:30','15:00','15:30',
        '16:00','16:30','17:00',
    ];

    let html = `<div class="th-time">Time</div>`;
    DAYS.forEach(d => html += `<div class="th-day">${d}</div>`);

    TIMES.forEach(t => {
        html += `<div class="time-label">${t}</div>`;
        FULL.forEach(fullDay => {
            const sched = SMS.schedules.find(s => s.day === fullDay && s.ts === t);
            if (sched) {
                const cls = sched.cat === 'LABORATORY' ? 'lab' : sched.cat === 'SEMINAR' ? 'sem' : 'lec';
                html += `
                <div class="tt-cell">
                    <div class="tt-slot ${cls}"
                         onclick="toast('info','${sched.section}','${sched.instr} • ${sched.ts}–${sched.te} • Room: ${sched.room || 'TBA'}')">
                        <strong>${sched.section}</strong>
                        <span>${sched.instr.split(' ')[0]}</span>
                        <span>${sched.room || 'TBA'}</span>
                    </div>
                </div>`;
            } else {
                html += `<div class="tt-cell"></div>`;
            }
        });
    });

    grid.innerHTML = html;
}

/* ── ACADEMIC TERMS TABLE ────────────────────────────────── */
function renderTerms(data) {
    const items = data || SMS.terms;
    const tbody = document.getElementById('termTableBody');
    if (!tbody) return;

    const semLabel = { 1:'1st Semester', 2:'2nd Semester', 3:'Summer' };
    tbody.innerHTML = items.map(t => `
        <tr>
            <td class="mono">${t.id}</td>
            <td><strong style="color:var(--text)">${t.label}</strong></td>
            <td class="muted">${t.sy}</td>
            <td class="muted">${semLabel[t.sem] || t.sem}</td>
            <td class="muted">${t.start}</td>
            <td class="muted">${t.end}</td>
            <td>
                ${t.active
                    ? '<span class="badge badge-green"><span class="badge-dot"></span>Active</span>'
                    : '<span class="badge badge-gray">Inactive</span>'}
            </td>
            <td>
                <div class="row-actions">
                    ${!t.active
                        ? `<button class="btn btn-sm btn-teal" onclick="activateTerm('${t.id}')">Activate</button>`
                        : '<span style="font-size:.72rem;color:var(--text-dim)">Current</span>'}
                </div>
            </td>
        </tr>`
    ).join('');
}

/* ── ROOM AVAILABILITY ───────────────────────────────────── */
function renderRooms() {
    const ROOMS = ['RM-101','RM-201','RM-204','RM-301','RM-302','LAB-1','LAB-2'];
    const SLOTS = [
        ['07:00','08:30'], ['08:30','10:00'], ['10:00','11:30'],
        ['11:30','13:00'], ['13:00','14:30'], ['14:30','16:00'],
    ];

    /* Build busy map from schedules */
    const busyMap = {};
    SMS.schedules.forEach(s => {
        ROOMS.forEach(r => {
            if (s.room === r) {
                if (!busyMap[r]) busyMap[r] = [];
                busyMap[r].push([s.ts, s.te]);
            }
        });
    });

    /* Availability table */
    const tbody = document.getElementById('roomAvailBody');
    if (tbody) {
        tbody.innerHTML = ROOMS.map(r => `
            <tr>
                <td style="font-family:var(--font-mono);font-size:.75rem;color:var(--gold-light)">${r}</td>
                ${SLOTS.map(([slotStart, slotEnd]) => {
                    const busy = (busyMap[r] || []).some(([bs, be]) => bs < slotEnd && be > slotStart);
                    return `<td style="text-align:center">
                        ${busy
                            ? '<span style="font-size:.72rem;padding:3px 8px;background:rgba(224,82,82,0.15);color:var(--rose-light);border-radius:4px">Occupied</span>'
                            : '<span style="font-size:.72rem;padding:3px 8px;background:rgba(34,197,94,0.1);color:var(--green);border-radius:4px">Free</span>'}
                    </td>`;
                }).join('')}
            </tr>`
        ).join('');
    }

    /* Assignments list */
    const tbody2 = document.getElementById('roomAssignBody');
    if (tbody2) {
        const assigned = SMS.schedules.filter(s => s.room);
        tbody2.innerHTML = assigned.map(s => `
            <tr>
                <td style="font-family:var(--font-mono);color:var(--gold-light)">${s.room}</td>
                <td><span class="section-chip">${s.section}</span></td>
                <td><span class="badge badge-blue">${s.day.substring(0,3)}</span></td>
                <td style="font-family:var(--font-mono);font-size:.75rem">${s.ts} – ${s.te}</td>
                <td>
                    <button class="btn btn-sm btn-danger"
                            onclick="unassignRoom('${s.id}')">Remove</button>
                </td>
            </tr>`
        ).join('');
    }
}
