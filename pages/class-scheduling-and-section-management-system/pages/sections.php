<?php /* SMS — pages/sections.php */ ?>
<div class="page" id="page-sections">

    <h1 class="page-title">Class <span>Sections</span></h1>
    <p class="subtitle">Create sections from the Curriculum DB and auto-assign students from Pamana.</p>

    <!-- Actions -->
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
        <button class="btn btn-primary" onclick="SECMOD.openCreate()">
            <i class="fa-solid fa-plus"></i> Create Section
        </button>
        <button class="btn btn-secondary btn-sm" onclick="SECMOD.load()">
            <i class="fa-solid fa-rotate"></i> Refresh
        </button>
        <div style="margin-left:auto;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
            <select class="filter-select" id="sec_f_prog" onchange="SECMOD.load()">
                <option value="">All Programs</option>
                <option value="BSIT">BSIT</option>
                <option value="BSCS">BSCS</option>
                <option value="BSCRIM">BSCRIM</option>
            </select>
            <select class="filter-select" id="sec_f_year" onchange="SECMOD.load()">
                <option value="">All Years</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
            <select class="filter-select" id="sec_f_sem" onchange="SECMOD.load()">
                <option value="">All Semesters</option>
                <option value="1">1st Sem</option>
                <option value="2">2nd Sem</option>
            </select>
            <input class="filter-input" id="sec_f_q" placeholder="Search…" oninput="SECMOD.load()" style="width:140px;"/>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card" style="--accent-color:#2563eb"><div class="label">Total Sections</div><div class="value" id="ss_total">—</div><div class="sub">All programs</div><div class="icon-bg"><i class="fa-solid fa-folder-open"></i></div></div>
        <div class="stat-card" style="--accent-color:#10b981"><div class="label">Open</div><div class="value" id="ss_open">—</div><div class="sub">Available slots</div><div class="icon-bg"><i class="fa-solid fa-circle-check"></i></div></div>
        <div class="stat-card" style="--accent-color:#ef4444"><div class="label">Full</div><div class="value" id="ss_full">—</div><div class="sub">Max capacity</div><div class="icon-bg"><i class="fa-solid fa-users-slash"></i></div></div>
        <div class="stat-card" style="--accent-color:#f59e0b"><div class="label">Total Enrolled</div><div class="value" id="ss_enrolled">—</div><div class="sub">Across all sections</div><div class="icon-bg"><i class="fa-solid fa-user-graduate"></i></div></div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-header">
            <h2>All Sections <span class="chip" id="sec_chip">0</span></h2>
            <span style="font-size:.75rem;color:var(--text-gray);"><i class="fa-solid fa-hand-pointer"></i> Click row for details</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>#</th><th>Section Label</th><th>Program</th><th>Year</th>
                    <th>Sem</th><th>Category</th><th>Room</th><th>Schedule</th>
                    <th>Enrolled/Cap</th><th>Status</th><th>Actions</th>
                </tr></thead>
                <tbody id="sec_tbody"><tr><td colspan="11" style="text-align:center;padding:40px;color:var(--text-gray);"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</td></tr></tbody>
            </table>
        </div>
        <div class="table-footer"><span id="sec_meta" style="font-size:.82rem;color:var(--text-gray);">—</span></div>
    </div>
</div>

<!-- ══════════ CREATE SECTION MODAL ══════════════════════════ -->
<div id="sec_create_ov" onclick="SECMOD.closeCreate()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:500;backdrop-filter:blur(3px);"></div>
<div id="sec_create_modal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:501;
            background:white;border-radius:16px;width:700px;max-width:97vw;max-height:93vh;
            overflow-y:auto;box-shadow:0 24px 48px rgba(0,0,0,0.18);">

    <!-- Header -->
    <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;
                justify-content:space-between;background:#f9fafb;border-radius:16px 16px 0 0;position:sticky;top:0;z-index:2;">
        <h3 style="font-size:1rem;font-weight:600;color:var(--text-dark);display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-folder-plus" style="color:var(--active-blue)"></i>
            Create New Section
        </h3>
        <button onclick="SECMOD.closeCreate()"
                style="background:#f3f4f6;width:30px;height:30px;border-radius:50%;border:none;
                       cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-gray);">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>

    <div style="padding:24px;">

        <!-- STEP A: Program & Period -->
        <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#1d4ed8;margin-bottom:10px;">
                STEP A — Program & Period
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Program <span style="color:#ef4444">*</span></label>
                    <select class="form-control" id="f_program" onchange="SECMOD.onProgramChange()">
                        <option value="">— Loading... —</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Year Level <span style="color:#ef4444">*</span></label>
                    <select class="form-control" id="f_year" onchange="SECMOD.fetchSubjects()">
                        <option value="">— Select —</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Semester <span style="color:#ef4444">*</span></label>
                    <select class="form-control" id="f_sem" onchange="SECMOD.fetchSubjects()">
                        <option value="">— Select —</option>
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP B: Subjects (auto-loaded from curriculum_db) -->
        <div style="background:#f0fdf4;border:1px solid #a7f3d0;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#065f46;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                <span>STEP B — Subjects <span style="font-size:.62rem;font-weight:400;margin-left:4px;">(from curriculum_db — auto-selected)</span></span>
                <span id="sub_badge" style="background:#ecfdf5;color:#065f46;font-size:.68rem;padding:2px 8px;border-radius:20px;border:1px solid #a7f3d0;font-weight:600;display:none;"></span>
            </div>
            <div id="sub_loading" style="text-align:center;padding:12px;color:var(--text-gray);font-size:.82rem;">
                <i class="fa-solid fa-arrow-up"></i> Select Program, Year, and Semester above to load subjects.
            </div>
            <div id="sub_table_wrap" style="display:none;">
                <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
                    <thead><tr style="background:#d1fae5;">
                        <th style="padding:7px 10px;text-align:left;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;">Code</th>
                        <th style="padding:7px 10px;text-align:left;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;">Subject</th>
                        <th style="padding:7px 10px;text-align:center;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;">Units</th>
                        <th style="padding:7px 10px;text-align:center;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;">Hours</th>
                        <th style="padding:7px 10px;text-align:center;font-size:.63rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#065f46;">Pre-req</th>
                    </tr></thead>
                    <tbody id="sub_tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- STEP C: Section Details -->
        <div style="background:#fdf4ff;border:1px solid #ddd6fe;border-radius:8px;padding:12px 16px;margin-bottom:20px;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#7c3aed;margin-bottom:10px;">
                STEP C — Section Details
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Section Label <span style="color:#ef4444">*</span></label>
                    <input class="form-control" id="f_label" placeholder="e.g. BSIT-3220"
                           oninput="this.value=this.value.toUpperCase()"/>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Category <span style="color:#ef4444">*</span></label>
                    <select class="form-control" id="f_category">
                        <option value="Lecture">Lecture</option>
                        <option value="Laboratory">Laboratory</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Max Capacity <span style="color:var(--text-gray);font-weight:400;">(≤40)</span></label>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <input class="form-control" id="f_cap" type="number" min="1" max="40" value="40"
                               style="width:80px;" oninput="SECMOD.updateCapBar(this)"/>
                        <div style="flex:1;background:#e5e7eb;border-radius:20px;height:7px;overflow:hidden;">
                            <div id="f_capbar" style="height:100%;background:#10b981;width:100%;border-radius:20px;transition:width .3s,background .3s;"></div>
                        </div>
                        <span id="f_cappct" style="font-size:.75rem;font-weight:600;min-width:34px;color:var(--text-gray);">100%</span>
                    </div>
                    <div id="f_caperr" style="font-size:.72rem;color:#ef4444;margin-top:3px;display:none;">⚠ Max 40.</div>
                </div>
            </div>
        </div>

        <!-- STEP D: Academic Term -->
        <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;">
            <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#92400e;margin-bottom:10px;">
                STEP D — Academic Term
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Term <span style="color:#ef4444">*</span></label>
                <select class="form-control" id="f_term" style="max-width:320px;">
                    <option value="">— Loading terms... —</option>
                </select>
                <div style="font-size:.72rem;color:#92400e;margin-top:4px;">
                    <i class="fa-solid fa-database"></i> Sourced from <code>schedules.terms</code>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:flex-end;
                background:#f9fafb;border-radius:0 0 16px 16px;position:sticky;bottom:0;">
        <button class="btn btn-ghost" onclick="SECMOD.closeCreate()">Cancel</button>
        <button class="btn btn-primary" id="f_submit" onclick="SECMOD.submit()">
            <i class="fa-solid fa-check"></i> Create Section
        </button>
    </div>
</div>

<!-- DETAIL MODAL -->
<div id="sec_det_ov" onclick="SECMOD.closeDetail()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:500;backdrop-filter:blur(3px);"></div>
<div id="sec_det_modal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:501;
            background:white;border-radius:16px;width:680px;max-width:97vw;max-height:93vh;
            overflow-y:auto;box-shadow:0 24px 48px rgba(0,0,0,0.18);">
    <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;
                justify-content:space-between;background:#f9fafb;border-radius:16px 16px 0 0;">
        <h3 id="sec_det_title" style="font-size:1rem;font-weight:600;color:var(--text-dark);display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-folder-open" style="color:var(--active-blue)"></i> Section Details
        </h3>
        <button onclick="SECMOD.closeDetail()"
                style="background:#f3f4f6;width:30px;height:30px;border-radius:50%;border:none;cursor:pointer;
                       display:flex;align-items:center;justify-content:center;color:var(--text-gray);">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div id="sec_det_body" style="padding:24px;"></div>
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;gap:10px;
                justify-content:space-between;background:#f9fafb;border-radius:0 0 16px 16px;">
        <div style="display:flex;gap:8px;">
            <button id="sec_det_add_btn" class="btn btn-primary btn-sm" onclick="SECMOD.openStudentEnroll()">
                <i class="fa-solid fa-user-plus"></i> Add Student
            </button>
            <button id="sec_det_sched_btn" class="btn btn-teal btn-sm" onclick="SECMOD.openSchedule()">
                <i class="fa-solid fa-calendar-check"></i> Assign Schedule
            </button>
            <button id="sec_det_del_btn" class="btn btn-danger btn-sm">
                <i class="fa-solid fa-trash"></i> Delete
            </button>
        </div>
        <button class="btn btn-ghost btn-sm" onclick="SECMOD.closeDetail()">Close</button>
    </div>
</div>

<!-- SCHEDULE MODAL -->
<div id="sec_sched_ov" onclick="SECMOD.closeSchedule()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:600;backdrop-filter:blur(3px);"></div>
<div id="sec_sched_modal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:601;
            background:white;border-radius:16px;width:440px;max-width:97vw;
            box-shadow:0 24px 48px rgba(0,0,0,0.18);">
    <div style="padding:16px 24px;border-bottom:1px solid #e5e7eb;background:#f9fafb;border-radius:16px 16px 0 0;">
        <h3 style="font-size:1rem;font-weight:600;color:var(--text-dark);">
            <i class="fa-solid fa-calendar-plus" style="color:var(--active-blue)"></i>
            Assign Room &amp; Schedule
        </h3>
    </div>
    <div style="padding:24px;">
        <div id="sched_suggest" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:.8rem;color:#1d4ed8;margin-bottom:16px;display:none;">
            <i class="fa-solid fa-lightbulb"></i> <strong>Suggested:</strong> <span id="sched_suggest_text"></span>
        </div>
        <div class="form-group">
            <label class="form-label">Room Number (1–15) <span style="color:#ef4444">*</span></label>
            <select class="form-control" id="sched_room">
                <?php for($i=1;$i<=15;$i++) echo "<option value='$i'>Room $i</option>"; ?>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:0;">
            <label class="form-label">Schedule Type <span style="color:#ef4444">*</span></label>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:4px;">
                <label id="sched_lbl_m" onclick="SECMOD.pickTime('Morning')"
                       style="cursor:pointer;padding:12px;border:2px solid #2563eb;border-radius:8px;background:#eff6ff;text-align:center;">
                    <div style="font-size:1.2rem;">🌅</div>
                    <div style="font-size:.82rem;font-weight:700;color:#2563eb;">Morning</div>
                    <div style="font-size:.65rem;color:#6b7280;">6:00 AM – 12:00 PM</div>
                </label>
                <label id="sched_lbl_a" onclick="SECMOD.pickTime('Afternoon')"
                       style="cursor:pointer;padding:12px;border:2px solid #e5e7eb;border-radius:8px;background:white;text-align:center;">
                    <div style="font-size:1.2rem;">🌇</div>
                    <div style="font-size:.82rem;font-weight:700;color:var(--text-gray);">Afternoon</div>
                    <div style="font-size:.65rem;color:#6b7280;">12:00 PM – 6:00 PM</div>
                </label>
            </div>
        </div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:flex-end;
                background:#f9fafb;border-radius:0 0 16px 16px;">
        <button class="btn btn-ghost" onclick="SECMOD.closeSchedule()">Cancel</button>
        <button class="btn btn-primary" onclick="SECMOD.saveSchedule()">
            <i class="fa-solid fa-check"></i> Save Schedule
        </button>
    </div>
</div>

<!-- ENROLL STUDENT MODAL -->
<div id="sec_enroll_ov" onclick="SECMOD.closeStudentEnroll()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:700;backdrop-filter:blur(3px);"></div>
<div id="sec_enroll_modal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:701;
            background:white;border-radius:16px;width:640px;max-width:97vw;max-height:88vh;
            overflow-y:auto;box-shadow:0 24px 48px rgba(0,0,0,0.18);">
    <div style="padding:18px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;
                justify-content:space-between;background:#f9fafb;border-radius:16px 16px 0 0;">
        <h3 style="font-size:1rem;font-weight:600;color:var(--text-dark);display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-user-plus" style="color:var(--active-blue)"></i>
            Add Student to Section
        </h3>
        <button onclick="SECMOD.closeStudentEnroll()"
                style="background:#f3f4f6;width:30px;height:30px;border-radius:50%;border:none;
                       cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-gray);">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div style="padding:24px;">
        <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:18px;flex-wrap:wrap;">
            <input id="sec_enroll_q" class="form-control" placeholder="Search student number, name, email, or contact"
                   style="flex:1;min-width:240px;" onkeydown="if(event.key==='Enter')SECMOD.searchStudents()" />
            <button class="btn btn-primary btn-sm" style="min-width:120px;" onclick="SECMOD.searchStudents()">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
        </div>
        <div id="sec_enroll_msg" style="font-size:.85rem;color:#374151;margin-bottom:14px;">
            Enter a search term to find available students for this section.
        </div>
        <div id="sec_enroll_results" style="max-height:320px;overflow-y:auto;"></div>
    </div>
    <div style="padding:16px 24px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:flex-end;
                background:#f9fafb;border-radius:0 0 16px 16px;">
        <button class="btn btn-ghost" onclick="SECMOD.closeStudentEnroll()">Cancel</button>
    </div>
</div>

<!-- PRINT MODAL -->
<div id="sec_print_modal"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:700;">
    <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
                background:white;border-radius:12px;width:700px;max-width:97vw;max-height:90vh;
                overflow-y:auto;box-shadow:0 24px 48px rgba(0,0,0,0.3);">
        <div style="padding:14px 20px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border-radius:12px 12px 0 0;">
            <h3 style="font-size:.95rem;font-weight:600;color:var(--text-dark);">
                <i class="fa-solid fa-print" style="color:var(--active-blue)"></i> Print Section Roster
            </h3>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
                <button class="btn btn-ghost btn-sm" onclick="document.getElementById('sec_print_modal').style.display='none'">Close</button>
            </div>
        </div>
        <div id="sec_print_body" style="padding:24px;"></div>
    </div>
</div>

<script>
const SECMOD = (() => {
    const YL = {1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};
    const SL = {1:'1st Sem',2:'2nd Sem'};
    const YC = {1:{bg:'#ecfdf5',c:'#065f46'},2:{bg:'#eff6ff',c:'#1d4ed8'},3:{bg:'#fdf4ff',c:'#7c3aed'},4:{bg:'#fffbeb',c:'#92400e'}};

    let _subjects   = [];    // loaded from curriculum_db
    let _currentSec = null;  // for detail/schedule modal
    let _timeChoice = 'Morning';

    /* ── Init ────────────────────────────────────────── */
    async function init() {
        await Promise.all([loadPrograms(), loadTerms(), load()]);
    }

    async function loadPrograms() {
        const res = await Api.curriculum.programs();
        const sel = document.getElementById('f_program');
        if (res?.success) {
            sel.innerHTML = '<option value="">— Select Program —</option>' +
                res.data.map(p=>`<option value="${p.program_code}">${p.program_code} — ${p.program_description}</option>`).join('');
        } else {
            sel.innerHTML = '<option value="BSIT">BSIT (Offline)</option><option value="BSCS">BSCS</option>';
        }
    }

    async function loadTerms() {
        const res = await Api.sections.terms();
        const sel = document.getElementById('f_term');
        if (res?.success && res.data.length > 0) {
            sel.innerHTML = res.data.map(t=>`<option value="${t.term_id}" ${t.is_active?'selected':''}>${t.term_label}</option>`).join('');
        } else {
            sel.innerHTML = '<option value="">No terms found — create one in Terms page</option>';
        }
    }

    /* ── Fetch subjects from curriculum_db via AJAX ── */
    async function fetchSubjects() {
        const prog = document.getElementById('f_program').value;
        const year = document.getElementById('f_year').value;
        const sem  = document.getElementById('f_sem').value;
        const wrap = document.getElementById('sub_table_wrap');
        const hint = document.getElementById('sub_loading');
        const badge= document.getElementById('sub_badge');

        if (!prog||!year||!sem) { wrap.style.display='none'; hint.style.display='block'; _subjects=[]; return; }

        hint.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Loading subjects from <code>curriculum_db</code>…';
        hint.style.display = 'block';
        wrap.style.display = 'none';

        const res = await Api.curriculum.subjects(prog, year, sem);

        /* Debug — check browser console */
        console.log('[fetchSubjects] response:', res);

        if (!res?.success) {
            hint.innerHTML = `<span style="color:#ef4444;">
                <i class="fa-solid fa-circle-xmark"></i>
                <strong>DB Error:</strong> ${res?.message || 'Cannot reach curriculum_db'}
                <br><small>Run <code>db/01_curriculum_db.sql</code> first, then check <code>api/config.php</code> credentials.</small>
            </span>`;
            _subjects = []; badge.style.display = 'none'; return;
        }

        if (!res.data || res.data.length === 0) {
            hint.innerHTML = `<span style="color:#f59e0b;">
                <i class="fa-solid fa-triangle-exclamation"></i>
                No subjects found for <strong>${prog} — Year ${year}, Sem ${sem}</strong>.
                <br><small>Check that <code>db/01_curriculum_db.sql</code> was imported correctly.</small>
            </span>`;
            _subjects = []; badge.style.display = 'none'; return;
        }

        _subjects = res.data;
        hint.style.display = 'none';
        wrap.style.display = 'block';
        badge.textContent  = _subjects.length + ' subjects';
        badge.style.display= 'inline-block';

        document.getElementById('sub_tbody').innerHTML = _subjects.map(s=>`
            <tr style="border-bottom:1px solid #d1fae5;">
                <td style="padding:7px 10px;font-family:monospace;font-weight:700;color:#2563eb;">${s.subject_code}</td>
                <td style="padding:7px 10px;color:var(--text-dark);">${s.subject_description}</td>
                <td style="padding:7px 10px;text-align:center;font-weight:600;">${s.units}</td>
                <td style="padding:7px 10px;text-align:center;color:var(--text-gray);">${s.hours}</td>
                <td style="padding:7px 10px;text-align:center;font-size:.72rem;color:var(--text-gray);">${s.prerequisite||'None'}</td>
            </tr>`).join('');
    }

    function onProgramChange() { fetchSubjects(); }

    function updateCapBar(input) {
        const v   = parseInt(input.value)||0;
        const pct = Math.min(100,Math.round((v/40)*100));
        const bar = document.getElementById('f_capbar');
        if (bar) { bar.style.width=pct+'%'; bar.style.background=v>40?'#ef4444':v>=32?'#f59e0b':'#10b981'; }
        const lbl = document.getElementById('f_cappct');
        if (lbl) lbl.textContent=pct+'%';
        const err = document.getElementById('f_caperr');
        if (err) err.style.display=(v<1||v>40)?'block':'none';
    }

    /* ── Create modal ────────────────────────────────── */
    function openCreate() {
        document.getElementById('f_label').value='';
        document.getElementById('f_cap').value='40';
        document.getElementById('f_capbar').style.cssText='height:100%;width:100%;background:#10b981;border-radius:20px;';
        document.getElementById('f_cappct').textContent='100%';
        document.getElementById('f_caperr').style.display='none';
        document.getElementById('sub_table_wrap').style.display='none';
        document.getElementById('sub_loading').innerHTML='<i class="fa-solid fa-arrow-up"></i> Select Program, Year, and Semester above.';
        document.getElementById('sub_loading').style.display='block';
        document.getElementById('sub_badge').style.display='none';
        _subjects=[];
        document.getElementById('sec_create_ov').style.display='block';
        document.getElementById('sec_create_modal').style.display='block';
        loadPrograms(); loadTerms();
    }
    function closeCreate() {
        document.getElementById('sec_create_ov').style.display='none';
        document.getElementById('sec_create_modal').style.display='none';
    }

    /* ── Submit ──────────────────────────────────────── */
    async function submit() {
        const program  = document.getElementById('f_program').value;
        const year     = parseInt(document.getElementById('f_year').value)||0;
        const sem      = parseInt(document.getElementById('f_sem').value)||0;
        const label    = document.getElementById('f_label').value.trim();
        const cat      = document.getElementById('f_category').value;
        const cap      = parseInt(document.getElementById('f_cap').value)||0;
        const termId   = parseInt(document.getElementById('f_term').value)||0;

        if (!program) return _alert('Please select a Program.');
        if (!year||!sem) return _alert('Please select Year Level and Semester.');
        if (!label) return _alert('Section Label is required.');
        if (cap<1||cap>40) return _alert('Capacity must be 1–40.');
        if (!termId) return _alert('Please select an Academic Term.');
        if (_subjects.length===0) return _alert('No subjects loaded. Select Program/Year/Semester first.');

        const btn=document.getElementById('f_submit');
        btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Creating…';
        btn.disabled=true;

        const res = await Api.sections.create({
            program, year_level:year, semester:sem,
            section_label:label, category:cat, max_capacity:cap,
            term_id:termId, subjects:_subjects,
        });

        btn.innerHTML='<i class="fa-solid fa-check"></i> Create Section';
        btn.disabled=false;

        if (res?.success) {
            closeCreate();
            _toast('success','Section Created',res.message);
            await load();
            /* Show success detail with print option */
            setTimeout(()=>showSuccess(res.data),400);
        } else {
            _alert(res?.message || 'Failed to create section. Check DB connection.');
        }
    }

    function showSuccess(data) {
        const msg = `
            <div style="text-align:center;padding:20px;">
                <div style="font-size:3rem;margin-bottom:12px;">🎉</div>
                <h2 style="color:var(--text-dark);margin-bottom:8px;">Section Created!</h2>
                <p style="color:var(--text-gray);margin-bottom:20px;">
                    <strong>${data.section_label}</strong> has been created with
                    <strong>${data.enrolled}</strong> students and
                    <strong>${data.subjects_added}</strong> subjects.
                </p>
                <div style="display:flex;gap:10px;justify-content:center;">
                    <button class="btn btn-primary" onclick="SECMOD.showStudentList(${data.section_id}, '${data.section_label}')">
                        <i class="fa-solid fa-users"></i> View Students
                    </button>
                    <button class="btn btn-secondary" onclick="document.getElementById('sec_det_ov').style.display='none';document.getElementById('sec_det_modal').style.display='none';">Close</button>
                </div>
            </div>`;
        document.getElementById('sec_det_title').innerHTML='<i class="fa-solid fa-party-horn" style="color:var(--active-blue)"></i> Success';
        document.getElementById('sec_det_body').innerHTML=msg;
        document.getElementById('sec_det_sched_btn').style.display='none';
        document.getElementById('sec_det_del_btn').style.display='none';
        document.getElementById('sec_det_ov').style.display='block';
        document.getElementById('sec_det_modal').style.display='block';
    }

    /* ── Load & render table ─────────────────────────── */
    async function load() {
        const tbody = document.getElementById('sec_tbody');
        const params = {};
        const prog = document.getElementById('sec_f_prog')?.value;
        const year = document.getElementById('sec_f_year')?.value;
        const sem  = document.getElementById('sec_f_sem')?.value;
        const q    = document.getElementById('sec_f_q')?.value;
        if (prog) params.program=prog;
        if (year) params.year_level=year;
        if (sem)  params.semester=sem;
        if (q)    params.search=q;

        const res = await Api.sections.list(params);
        if (!res?.success) {
            tbody.innerHTML=`<tr><td colspan="11" style="text-align:center;padding:40px;color:#ef4444;">
                <i class="fa-solid fa-circle-exclamation"></i> DB not connected — run SQL setup scripts first.</td></tr>`;
            return;
        }

        const {sections,stats} = res.data;
        document.getElementById('ss_total').textContent   = stats?.total||0;
        document.getElementById('ss_open').textContent    = stats?.open_c||0;
        document.getElementById('ss_full').textContent    = stats?.full_c||0;
        document.getElementById('ss_enrolled').textContent= stats?.enrolled||0;
        document.getElementById('sec_chip').textContent   = sections.length;
        document.getElementById('sec_meta').textContent   = `Showing ${sections.length} section${sections.length!==1?'s':''}`;

        if (!sections.length) {
            tbody.innerHTML=`<tr><td colspan="11" style="padding:50px;text-align:center;color:var(--text-gray);">
                <i class="fa-solid fa-folder-open" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:12px;"></i>
                No sections yet — create your first section.</td></tr>`;
            return;
        }

        tbody.innerHTML = sections.map((s,i)=>{
            const yc = YC[s.year_level]||{bg:'#f3f4f6',c:'#6b7280'};
            const pct= s.max_capacity>0?Math.round((s.enrolled_count/s.max_capacity)*100):0;
            const bc = s.enrolled_count>=s.max_capacity?'#ef4444':pct>=80?'#f59e0b':'#10b981';
            return `<tr style="cursor:pointer;transition:background .12s;"
                onclick="SECMOD.showDetail(${s.section_id})"
                onmouseover="this.style.background='#eff6ff';this.style.outline='1px solid #bfdbfe'"
                onmouseout="this.style.background='';this.style.outline=''">
                <td style="color:var(--text-gray);font-size:.76rem;">${i+1}</td>
                <td class="mono">${s.section_label}</td>
                <td><span style="background:#f3f4f6;font-size:.7rem;font-weight:600;padding:2px 7px;border-radius:4px;">${s.program}</span></td>
                <td><span style="padding:2px 9px;border-radius:20px;font-size:.7rem;font-weight:500;background:${yc.bg};color:${yc.c};">${YL[s.year_level]||s.year_level}</span></td>
                <td style="font-size:.78rem;color:var(--text-gray);">${SL[s.semester]||s.semester}</td>
                <td><span style="font-size:.72rem;background:${s.category==='Laboratory'?'#ede9fe':'#eff6ff'};color:${s.category==='Laboratory'?'#7c3aed':'#2563eb'};padding:2px 8px;border-radius:4px;border:1px solid ${s.category==='Laboratory'?'#ddd6fe':'#bfdbfe'};">${s.category}</span></td>
                <td style="font-weight:600;text-align:center;">${s.room_number?'Room '+s.room_number:'<span style="color:var(--text-gray);font-size:.75rem;">—</span>'}</td>
                <td style="text-align:center;">${s.time_schedule?`<span style="font-size:.72rem;font-weight:600;color:${s.time_schedule==='Morning'?'#0891b2':'#d97706'};">${s.time_schedule}</span>`:'<span style="color:var(--text-gray);font-size:.75rem;">—</span>'}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:7px;">
                        <div style="width:50px;background:#e5e7eb;border-radius:20px;height:5px;overflow:hidden;flex-shrink:0;">
                            <div style="height:100%;width:${pct}%;background:${bc};border-radius:20px;"></div>
                        </div>
                        <span style="font-size:.76rem;color:var(--text-gray);">${s.enrolled_count}/${s.max_capacity}</span>
                    </div>
                </td>
                <td><span class="badge ${s.status==='Full'?'badge-red':s.status==='Cancelled'?'badge-gray':'badge-green'}">
                    <span class="badge-dot"></span>${s.status}</span></td>
                <td onclick="event.stopPropagation()" style="text-align:center;">
                    <div class="row-actions" style="justify-content:center;">
                        <button class="act-btn view" onclick="SECMOD.showDetail(${s.section_id})" title="View"><i class="fa-solid fa-eye"></i></button>
                    <button class="act-btn edit" onclick="SECMOD.showStudentList(${s.section_id}, '${s.section_label}')" title="Students"><i class="fa-solid fa-users"></i></button>
                        <button class="act-btn del" onclick="SECMOD.del(${s.section_id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    /* ── Detail modal ────────────────────────────────── */
    async function showDetail(id) {
        const res = await Api.sections.detail(id);
        if (!res?.success) return _alert('Failed to load section details.');
        const s = res.data;
        _currentSec = s;

        document.getElementById('sec_det_title').innerHTML=`<i class="fa-solid fa-folder-open" style="color:var(--active-blue)"></i> ${s.section_label}`;
        document.getElementById('sec_det_add_btn').style.display='inline-flex';
        document.getElementById('sec_det_sched_btn').style.display='inline-flex';
        document.getElementById('sec_det_del_btn').style.display='inline-flex';
        document.getElementById('sec_det_del_btn').onclick=()=>del(s.section_id);

        const pct = s.max_capacity>0?Math.round((s.enrolled_count/s.max_capacity)*100):0;
        const bc  = s.enrolled_count>=s.max_capacity?'#ef4444':pct>=80?'#f59e0b':'#10b981';
        const yc  = YC[s.year_level]||{bg:'#f3f4f6',c:'#6b7280'};

        document.getElementById('sec_det_body').innerHTML = `
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:16px;">
                ${[
                    ['Section Label',`<span style="font-family:monospace;font-weight:700;color:var(--active-blue);">${s.section_label}</span>`],
                    ['Program',s.program],
                    ['Category',s.category],
                    ['Year Level',`<span style="padding:2px 9px;border-radius:20px;font-size:.76rem;font-weight:500;background:${yc.bg};color:${yc.c};">${YL[s.year_level]}</span>`],
                    ['Semester',SL[s.semester]||s.semester],
                    ['Term',s.term_label||'—'],
                    ['Room',s.room_number?'Room '+s.room_number:'Not assigned'],
                    ['Schedule',s.time_schedule||'Not assigned'],
                    ['Status',`<span class="badge ${s.status==='Full'?'badge-red':s.status==='Cancelled'?'badge-gray':'badge-green'}"><span class="badge-dot"></span>${s.status}</span>`],
                ].map(([l,v])=>`
                    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;">
                        <div style="font-size:.63rem;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-gray);margin-bottom:4px;">${l}</div>
                        <div style="font-size:.85rem;color:var(--text-dark);">${v}</div>
                    </div>`).join('')}
            </div>
            <div style="margin-bottom:14px;">
                <div style="font-size:.8rem;font-weight:600;color:var(--text-dark);margin-bottom:5px;">Enrollment — ${s.enrolled_count}/${s.max_capacity}</div>
                <div style="background:#e5e7eb;border-radius:20px;height:9px;overflow:hidden;">
                    <div style="height:100%;width:${pct}%;background:${bc};border-radius:20px;"></div>
                </div>
            </div>
            <div style="margin-bottom:14px;">
                <div style="font-size:.8rem;font-weight:600;color:var(--text-dark);margin-bottom:8px;">
                    Subjects <span style="background:#eff6ff;color:#2563eb;font-size:.68rem;padding:2px 8px;border-radius:20px;border:1px solid #bfdbfe;margin-left:6px;">${s.subjects?.length||0}</span>
                    <button class="btn btn-secondary btn-sm" style="float:right;" onclick="SECMOD.showStudentList(${s.section_id}, '${s.section_label}')">
                        <i class="fa-solid fa-users"></i> View Students (${s.student_count||0})
                    </button>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;max-height:180px;overflow-y:auto;">
                    ${(s.subjects||[]).map((sub,i)=>`
                        <div style="display:flex;align-items:center;gap:10px;padding:7px 11px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;">
                            <span style="width:18px;height:18px;border-radius:50%;background:#2563eb;color:white;display:flex;align-items:center;justify-content:center;font-size:.58rem;font-weight:700;flex-shrink:0;">${i+1}</span>
                            <span style="font-family:monospace;font-size:.7rem;font-weight:700;color:#2563eb;min-width:65px;">${sub.subject_code}</span>
                            <span style="font-size:.8rem;color:var(--text-dark);flex:1;">${sub.subject_desc}</span>
                            ${sub.professor?`<span style="font-size:.68rem;color:var(--green);font-weight:500;"><i class="fa-solid fa-chalkboard-teacher"></i> ${sub.professor}</span>`:'<span style="font-size:.68rem;color:var(--text-gray);">No faculty</span>'}
                        </div>`).join('')}
                </div>
            </div>`;

        document.getElementById('sec_det_ov').style.display='block';
        document.getElementById('sec_det_modal').style.display='block';
    }
    function closeDetail() {
        document.getElementById('sec_det_ov').style.display='none';
        document.getElementById('sec_det_modal').style.display='none';
        _currentSec=null;
    }

    /* ── Student List (print view) ───────────────────── */
    async function showStudentList(sectionId, label) {
        const res = await Api.sections.students(sectionId);
        const students = res?.data || [];
        const body = `
            <div style="text-align:center;margin-bottom:20px;">
                <h2 style="font-size:1.1rem;font-weight:700;">Section: ${label}</h2>
                <p style="font-size:.82rem;color:var(--text-gray);">Enrolled Students — ${students.length} total</p>
            </div>
            <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
                <thead><tr style="background:#f3f4f6;">
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">#</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Student No.</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Name</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Gender</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Contact</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Room</th>
                    <th style="padding:7px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Schedule</th>
                </tr></thead>
                <tbody>
                    ${students.length===0
                        ? '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-gray);">No students enrolled.</td></tr>'
                        : students.map((s,i)=>`
                            <tr style="border-bottom:1px solid #f3f4f6;">
                                <td style="padding:7px 10px;color:var(--text-gray);">${i+1}</td>
                                <td style="padding:7px 10px;font-family:monospace;font-size:.72rem;color:#2563eb;">${s.student_number}</td>
                                <td style="padding:7px 10px;font-weight:500;">${s.last_name}, ${s.first_name} ${s.middle_name||''}</td>
                                <td style="padding:7px 10px;color:var(--text-gray);">${s.gender||'—'}</td>
                                <td style="padding:7px 10px;font-size:.72rem;color:var(--text-gray);">${s.contact_number||'—'}</td>
                                <td style="padding:7px 10px;font-weight:600;">${s.room_number?'Room '+s.room_number:'—'}</td>
                                <td style="padding:7px 10px;color:${s.time_schedule==='Morning'?'#0891b2':'#d97706'};font-weight:500;">${s.time_schedule||'—'}</td>
                            </tr>`).join('')}
                </tbody>
            </table>`;
        document.getElementById('sec_print_body').innerHTML=body;
        document.getElementById('sec_print_modal').style.display='block';
    }

    /* ── Schedule modal ──────────────────────────────── */
    async function openSchedule() {
        if (!_currentSec) return;
        _timeChoice='Morning';
        _setTimeUI('Morning');

        /* Get suggestion */
        const res = await Api.schedule.suggest(_currentSec.section_id);
        const hint= document.getElementById('sched_suggest');
        const txt = document.getElementById('sched_suggest_text');
        if (res?.success && res.data.suggestion) {
            const sug = res.data.suggestion;
            hint.style.display='block';
            txt.textContent = `Room ${sug.room_number} — ${sug.time_schedule}`;
            document.getElementById('sched_room').value = sug.room_number;
            pickTime(sug.time_schedule);
        } else {
            hint.style.display='none';
        }

        /* Pre-fill if already assigned */
        if (_currentSec.room_number) document.getElementById('sched_room').value=_currentSec.room_number;
        if (_currentSec.time_schedule) pickTime(_currentSec.time_schedule);

        document.getElementById('sec_sched_ov').style.display='block';
        document.getElementById('sec_sched_modal').style.display='block';
    }
    function closeSchedule() {
        document.getElementById('sec_sched_ov').style.display='none';
        document.getElementById('sec_sched_modal').style.display='none';
    }
    async function saveSchedule() {
        if (!_currentSec) return;
        const room = parseInt(document.getElementById('sched_room').value)||0;
        if (!room||room<1||room>15) return _alert('Please select a valid room (1–15).');

        const res = await Api.schedule.assign({
            section_id: _currentSec.section_id,
            room_number: room,
            time_schedule: _timeChoice,
        });
        if (res?.success) {
            closeSchedule();
            _toast('success','Schedule Assigned',`Room ${room} — ${_timeChoice}`);
            await showDetail(_currentSec.section_id);
            load();
        } else {
            _alert(res?.message||'Failed to assign schedule.');
        }
    }
    function pickTime(v) {
        _timeChoice=v; _setTimeUI(v);
    }
    function _setTimeUI(v) {
        const m=document.getElementById('sched_lbl_m');
        const a=document.getElementById('sched_lbl_a');
        const set=(el,on)=>{
            el.style.borderColor=on?'#2563eb':'#e5e7eb';
            el.style.background=on?'#eff6ff':'white';
            el.querySelector('div:nth-child(2)').style.color=on?'#2563eb':'var(--text-gray)';
        };
        if(m&&a){set(m,v==='Morning');set(a,v==='Afternoon');}
    }

    function openStudentEnroll() {
        if (!_currentSec) return;
        document.getElementById('sec_enroll_q').value = '';
        document.getElementById('sec_enroll_msg').textContent =
            `Search available enrolled students for ${_currentSec.section_label}. Leave blank to list all eligible students.`;
        document.getElementById('sec_enroll_results').innerHTML = '';
        document.getElementById('sec_enroll_ov').style.display = 'block';
        document.getElementById('sec_enroll_modal').style.display = 'block';
    }

    function closeStudentEnroll() {
        document.getElementById('sec_enroll_ov').style.display = 'none';
        document.getElementById('sec_enroll_modal').style.display = 'none';
    }

    async function searchStudents() {
        if (!_currentSec) return;
        const query = document.getElementById('sec_enroll_q').value.trim();

        const msg = document.getElementById('sec_enroll_msg');
        const results = document.getElementById('sec_enroll_results');
        msg.textContent = 'Searching…';
        results.innerHTML = '';

        const res = await Api.sections.searchStudents(_currentSec.section_id, query);
        if (!res?.success) {
            msg.textContent = res?.message || 'Search failed. Please try again.';
            return;
        }

        const students = res.data || [];
        if (!students.length) {
            msg.textContent = 'No matching students found. Make sure the student is not already enrolled in this section.';
            return;
        }

        msg.textContent = `Found ${students.length} available student${students.length!==1?'s':''}.`;
        results.innerHTML = `
            <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
                <thead><tr style="background:#f3f4f6;">
                    <th style="padding:9px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Student No.</th>
                    <th style="padding:9px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Name</th>
                    <th style="padding:9px 10px;text-align:left;border-bottom:2px solid #e5e7eb;">Email / Contact</th>
                    <th style="padding:9px 10px;text-align:right;border-bottom:2px solid #e5e7eb;">Action</th>
                </tr></thead>
                <tbody>
                    ${students.map(s => `
                        <tr style="border-bottom:1px solid #e5e7eb;">
                            <td style="padding:10px 10px;font-family:monospace;color:#2563eb;">${s.student_number}</td>
                            <td style="padding:10px 10px;color:var(--text-dark);">${s.last_name}, ${s.first_name} ${s.middle_name||''}</td>
                            <td style="padding:10px 10px;color:#4b5563;">${s.email||'—'} / ${s.contact_number||'—'}</td>
                            <td style="padding:10px 10px;text-align:right;"><button class="btn btn-primary btn-sm" onclick="SECMOD.enrollStudent(${s.student_id})">Add</button></td>
                        </tr>`).join('')}
                </tbody>
            </table>`;
    }

    async function enrollStudent(studentId) {
        if (!_currentSec) return;
        const res = await Api.sections.enroll({
            section_id: _currentSec.section_id,
            student_id: studentId,
        });
        if (!res?.success) {
            return _alert(res?.message || 'Failed to add student.');
        }
        _toast('success','Student Added',res.message);
        closeStudentEnroll();
        await showDetail(_currentSec.section_id);
        load();
    }

    async function del(id) {
        if (!confirm('Delete this section? All student data will be removed.')) return;
        const res = await Api.sections.delete(id);
        if (res?.success) {
            closeDetail();
            _toast('warning','Deleted','Section removed.');
            load();
        } else {
            _alert(res?.message||'Delete failed.');
        }
    }

    function _alert(msg) { alert(msg); }
    function _toast(type,title,msg) {
        if(typeof toast==='function'){toast(type,title,msg);return;}
        const c=document.getElementById('toastContainer');if(!c)return;
        const el=document.createElement('div');el.className=`toast ${type}`;
        el.innerHTML=`<span class="toast-icon">${type==='success'?'✅':type==='warning'?'⚠️':'❌'}</span>
            <div class="toast-text"><strong>${title}</strong><span>${msg}</span></div>`;
        c.appendChild(el);setTimeout(()=>{el.style.animation='toastOut .3s ease forwards';setTimeout(()=>el.remove(),300);},3500);
    }

    document.addEventListener('DOMContentLoaded', init);
    return {openCreate,closeCreate,fetchSubjects,onProgramChange,updateCapBar,submit,
            load,showDetail,closeDetail,del,openSchedule,closeSchedule,saveSchedule,
            pickTime,showStudentList,openStudentEnroll,closeStudentEnroll,searchStudents,enrollStudent};
})();
</script>
