<?php /* SMS — pages/dashboard.php */ ?>
<div class="page active" id="page-dashboard">

    <h1 class="page-title">Welcome back, <span id="dashName">Admin</span></h1>
    <p class="subtitle">Scheduling overview — data from <code>schedules</code> database.</p>

    <!-- Stats -->

    <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px;">
        <button class="btn btn-primary" onclick="showPage('sections',null);SECMOD.openCreate()">
            <i class="fa-solid fa-plus"></i> New Section
        </button>

        <button class="btn btn-secondary btn-sm" onclick="showPage('terms',null)">
            <i class="fa-solid fa-calendar-check"></i> Manage Terms
        </button>
        <button class="btn btn-secondary btn-sm" onclick="location.reload()">
            <i class="fa-solid fa-rotate"></i> Refresh
        </button>
    </div>
    <div class="stats-grid">
        <div class="stat-card fade-up fade-up-1" style="--accent-color:#2563eb">
            <div class="label">Total Sections</div>
            <div class="value" id="dash_sections">—</div>
            <div class="sub" id="dash_sections_sub">Loading…</div>
            <div class="icon-bg"><i class="fa-solid fa-folder-open"></i></div>
        </div>
        <div class="stat-card fade-up fade-up-2" style="--accent-color:#10b981">
            <div class="label">Total Enrolled</div>
            <div class="value" id="dash_enrolled">—</div>
            <div class="sub">Across all sections</div>
            <div class="icon-bg"><i class="fa-solid fa-user-graduate"></i></div>
        </div>
        <div class="stat-card fade-up fade-up-3" style="--accent-color:#ef4444">
            <div class="label">Conflicts</div>
            <div class="value" id="dash_conflicts">—</div>
            <div class="sub" id="dash_conflicts_sub">Unresolved issues</div>
            <div class="icon-bg"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-card fade-up fade-up-4" style="--accent-color:#6366f1">
            <div class="label">Assigned Rooms</div>
            <div class="value" id="dash_rooms">—</div>
            <div class="sub">Of 15 available</div>
            <div class="icon-bg"><i class="fa-solid fa-building"></i></div>
        </div>
    </div>

    <div class="dash-grid">
        <!-- Recent Sections -->
        <div class="card fade-up">
            <div class="card-header">
                <h2>Recent Sections <span class="chip">Latest 5</span></h2>
                <button class="btn btn-secondary btn-sm" onclick="showPage('sections',null)">View All</button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Program</th>
                            <th>Year</th>
                            <th>Room</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="dash_recent_tbody">
                        <tr>
                            <td colspan="5" style="text-align:center;padding:20px;color:var(--text-gray);"><i class="fa-solid fa-spinner fa-spin"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Faculty Load Summary -->
        <div class="card fade-up">
            <div class="card-header">
                <h2>Faculty Load Summary</h2>
                <button class="btn btn-secondary btn-sm" onclick="showPage('faculty',null)">Full Report</button>
            </div>
            <div style="padding:14px;" id="dash_faculty_list">
                <div style="text-align:center;padding:20px;color:var(--text-gray);"><i class="fa-solid fa-spinner fa-spin"></i></div>
            </div>
        </div>
    </div>

    <!-- DB Status 
    <div class="card fade-up">
        <div class="card-header">
            <h2><i class="fa-solid fa-database" style="color:var(--active-blue)"></i> Database Status</h2>
        </div>
        <div style="padding:16px;display:grid;grid-template-columns:repeat(4,1fr);gap:12px;" id="dash_db_status">
            <?php
            $dbs = [
                ['curriculum_db', 'Curriculum DB', 'READ-ONLY', 'fa-book'],
                ['pamana', 'Pamana DB', 'Students & Faculty', 'fa-users'],
                ['schedules', 'Schedules DB', 'Main System', 'fa-calendar-days'],
                ['sections_db', 'Sections DB', 'Student Lists', 'fa-folder-open'],
            ];
            foreach ($dbs as [$name, $label, $desc, $icon]):
            ?>
                <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:14px;text-align:center;">
                    <div style="font-size:1.5rem;margin-bottom:8px;"><i class="fa-solid <?= $icon ?>"></i></div>
                    <div style="font-size:.78rem;font-weight:700;color:var(--text-dark);margin-bottom:2px;"><?= $label ?></div>
                    <div style="font-size:.68rem;color:var(--text-gray);margin-bottom:8px;"><?= $desc ?></div>
                    <div id="db_status_<?= $name ?>" style="font-size:.7rem;padding:3px 8px;border-radius:20px;display:inline-block;background:#fffbeb;border:1px solid #fde68a;color:#92400e;">
                        <i class="fa-solid fa-circle-notch fa-spin"></i> Checking…
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    -->

</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        /* Sections stats */
        Api.sections.list().then(res => {
            if (res?.success) {
                const {
                    sections,
                    stats
                } = res.data;
                document.getElementById('dash_sections').textContent = stats?.total || 0;
                document.getElementById('dash_sections_sub').textContent = `${stats?.open_c||0} open, ${stats?.full_c||0} full`;
                document.getElementById('dash_enrolled').textContent = stats?.enrolled || 0;

                /* Assigned rooms count */
                const rmCount = sections.filter(s => s.room_number).length;
                document.getElementById('dash_rooms').textContent = rmCount;

                /* Recent sections */
                const recent = sections.slice(0, 5);
                const YL = {
                    1: '1st',
                    2: '2nd',
                    3: '3rd',
                    4: '4th'
                };
                document.getElementById('dash_recent_tbody').innerHTML = recent.map(s => `
                <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td class="mono">${s.section_label}</td>
                    <td><span style="font-size:.7rem;background:#f3f4f6;padding:2px 6px;border-radius:4px;font-weight:600;">${s.program}</span></td>
                    <td style="font-size:.78rem;color:var(--text-gray);">${YL[s.year_level]||s.year_level} Yr</td>
                    <td style="font-size:.78rem;">${s.room_number?'Room '+s.room_number:'<span style="color:var(--text-gray);">—</span>'}</td>
                    <td><span class="badge ${s.status==='Full'?'badge-red':'badge-green'}"><span class="badge-dot"></span>${s.status}</span></td>
                </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--text-gray);">No sections yet.</td></tr>';
            }
        });

        /* Conflicts */
        Api.conflicts.list().then(res => {
            if (res?.success) {
                const u = res.data.stats?.unresolved || 0;
                document.getElementById('dash_conflicts').textContent = u;
                document.getElementById('dash_conflicts_sub').textContent = u > 0 ? `${u} need attention` : 'All resolved';
                if (u > 0) {
                    const b = document.getElementById('conflictBadge');
                    if (b) {
                        b.textContent = u;
                        b.style.display = 'inline-block';
                    }
                }
            }
        });

        /* Faculty summary */
        Api.faculty.summary().then(res => {
            if (res?.success) {
                const {
                    faculty
                } = res.data;
                const top = faculty.slice(0, 5);
                document.getElementById('dash_faculty_list').innerHTML = top.map(f => {
                    const pct = f.load_pct || 0;
                    const bc = f.load_status === 'OVERLOADED' ? '#ef4444' : f.load_status === 'NEAR_LIMIT' ? '#f59e0b' : '#10b981';
                    return `<div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                    <div style="font-size:.76rem;font-weight:600;color:var(--text-dark);min-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${f.full_name}</div>
                    <div style="flex:1;background:#e5e7eb;border-radius:20px;height:6px;overflow:hidden;">
                        <div style="height:100%;width:${Math.min(100,pct)}%;background:${bc};border-radius:20px;transition:width .4s;"></div>
                    </div>
                    <div style="font-size:.72rem;color:var(--text-gray);min-width:60px;text-align:right;">${f.assigned_units||0}/${f.max_units}u</div>
                </div>`;
                }).join('') || '<div style="text-align:center;color:var(--text-gray);font-size:.82rem;">No faculty data.</div>';
            }
        });

        /* DB status checks */
        const dbs = ['curriculum_db', 'pamana', 'schedules', 'sections_db'];
        for (const db of dbs) {
            const el = document.getElementById('db_status_' + db);
            try {
                /* Quick check via fetch_subjects for curriculum_db, sections_api for others */
                const ep = db === 'curriculum_db' ? 'api/fetch_subjects.php?action=programs' : 'api/sections_api.php?action=terms';
                const res = await fetch(db === 'curriculum_db' ? ep : 'api/sections_api.php?action=terms');
                const data = await res.json();
                if (el) {
                    el.style.background = '#ecfdf5';
                    el.style.border = '1px solid #a7f3d0';
                    el.style.color = '#065f46';
                    el.innerHTML = '<i class="fa-solid fa-circle-check"></i> Connected';
                }
            } catch (e) {
                if (el) {
                    el.style.background = '#fef2f2';
                    el.style.border = '1px solid #fecaca';
                    el.style.color = '#991b1b';
                    el.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Offline';
                }
            }
        }
    });
</script>