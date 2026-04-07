<?php /* SMS — pages/faculty.php — Module 3 */ ?>
<div class="page" id="page-faculty">
    <h1 class="page-title">Faculty <span>Loading</span></h1>
    <p class="subtitle">Assign professors to subjects per section. Data from <code>pamana.faculty</code> → saved to <code>schedules.faculty_loads</code>.</p>

    <!-- Stats -->
    <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
        <div class="stat-card" style="--accent-color:#10b981"><div class="label">Within Limit</div><div class="value" id="fl_within">—</div><div class="sub"><span class="up">Safe range</span></div><div class="icon-bg"><i class="fa-solid fa-circle-check"></i></div></div>
        <div class="stat-card" style="--accent-color:#f59e0b"><div class="label">Near Limit</div><div class="value" id="fl_near">—</div><div class="sub"><span style="color:#f59e0b">Approaching max</span></div><div class="icon-bg"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
        <div class="stat-card" style="--accent-color:#ef4444"><div class="label">Overloaded</div><div class="value" id="fl_over">—</div><div class="sub"><span class="down">Needs immediate fix</span></div><div class="icon-bg"><i class="fa-solid fa-circle-exclamation"></i></div></div>
    </div>

    <!-- Assign Panel -->
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h2><i class="fa-solid fa-chalkboard-teacher" style="color:var(--active-blue)"></i> Assign Faculty to Subject</h2>
        </div>
        <div style="padding:18px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Section</label>
                <select class="form-control" id="fl_sec" onchange="FACMOD.loadSectionSubjects()">
                    <option value="">— Select Section —</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Subject</label>
                <select class="form-control" id="fl_sub">
                    <option value="">— Select section first —</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Faculty (from pamana.faculty)</label>
                <select class="form-control" id="fl_fac">
                    <option value="">— Loading faculty... —</option>
                </select>
            </div>
            <button class="btn btn-primary" onclick="FACMOD.assign()">
                <i class="fa-solid fa-plus"></i> Assign
            </button>
        </div>
        <div style="padding:0 18px 14px;">
            <div id="fl_fac_info" style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:10px 14px;font-size:.78rem;"></div>
        </div>
    </div>

    <!-- Faculty Load Table -->
    <div class="card">
        <div class="card-header">
            <h2>Faculty Load Report <span class="chip" id="fl_term_badge">Active Term</span></h2>
            <div style="display:flex;gap:8px;">
                <button class="btn btn-secondary btn-sm" onclick="FACMOD.load()"><i class="fa-solid fa-rotate"></i> Refresh</button>
                <button class="btn btn-secondary btn-sm" onclick="FACMOD.exportCSV()"><i class="fa-solid fa-download"></i> Export</button>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Faculty ID</th><th>Name</th><th>Type</th>
                    <th>Assigned</th><th>Max</th><th>Load</th>
                    <th>Status</th><th>Sections</th>
                </tr></thead>
                <tbody id="fl_tbody"><tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-gray);"><i class="fa-solid fa-spinner fa-spin"></i> Loading…</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Per-Section Faculty View -->
    <div class="card" style="margin-top:16px;">
        <div class="card-header">
            <h2><i class="fa-solid fa-list-check" style="color:var(--active-blue)"></i> Section Subject Assignments</h2>
            <select class="filter-select" id="fl_view_sec" onchange="FACMOD.renderSectionView()">
                <option value="">— Select Section to View —</option>
            </select>
        </div>
        <div id="fl_sec_view" style="padding:16px;color:var(--text-gray);font-size:.85rem;">
            Select a section above to see its subject-faculty assignments.
        </div>
    </div>
</div>

<script>
const FACMOD = (() => {
    let _facList = [];
    let _activeTerm = null;

    async function init() {
        await Promise.all([load(), loadFacultyList(), loadSections()]);
        /* Get active term */
        const tRes = await Api.sections.terms();
        if (tRes?.success) {
            _activeTerm = tRes.data.find(t=>t.is_active) || tRes.data[0];
            if (_activeTerm) document.getElementById('fl_term_badge').textContent=_activeTerm.term_label;
        }
    }

    async function load() {
        const res = await Api.faculty.summary();
        if (!res?.success) {
            document.getElementById('fl_tbody').innerHTML=`<tr><td colspan="8" style="text-align:center;padding:30px;color:#ef4444;">DB not connected.</td></tr>`;
            return;
        }
        const {faculty,stats} = res.data;
        document.getElementById('fl_within').textContent=stats?.within||0;
        document.getElementById('fl_near').textContent  =stats?.near||0;
        document.getElementById('fl_over').textContent  =stats?.over||0;
        renderTable(faculty);
    }

    function renderTable(faculty) {
        const tbody=document.getElementById('fl_tbody');
        if (!faculty?.length) {
            tbody.innerHTML='<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-gray);">No faculty data.</td></tr>';
            return;
        }
        tbody.innerHTML=faculty.map(f=>{
            const pct=f.load_pct||0;
            const bc=f.load_status==='OVERLOADED'?'#ef4444':f.load_status==='NEAR_LIMIT'?'#f59e0b':'#10b981';
            const badge=f.load_status==='OVERLOADED'
                ?'<span class="badge badge-red"><span class="badge-dot"></span>Overloaded</span>'
                :f.load_status==='NEAR_LIMIT'
                ?'<span class="badge badge-amber"><span class="badge-dot"></span>Near Limit</span>'
                :'<span class="badge badge-green"><span class="badge-dot"></span>Within Limit</span>';
            return `<tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td class="mono">${f.faculty_code}</td>
                <td style="font-weight:600;color:var(--text-dark);">${f.full_name}</td>
                <td><span style="font-size:.72rem;background:${f.type==='Full-Time'?'#eff6ff':'#f0f9ff'};color:${f.type==='Full-Time'?'#2563eb':'#0e7490'};padding:2px 8px;border-radius:20px;border:1px solid ${f.type==='Full-Time'?'#bfdbfe':'#bae6fd'};">${f.type}</span></td>
                <td style="font-weight:700;">${f.assigned_units||0}</td>
                <td style="color:var(--text-gray);">${f.max_units}</td>
                <td><div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:90px;background:#e5e7eb;border-radius:20px;height:7px;overflow:hidden;flex-shrink:0;">
                        <div style="height:100%;width:${Math.min(100,pct)}%;background:${bc};border-radius:20px;"></div>
                    </div>
                    <span style="font-size:.76rem;color:var(--text-gray);">${pct}%</span>
                </div></td>
                <td>${badge}</td>
                <td style="font-size:.73rem;color:var(--text-gray);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="${f.sections||'—'}">${f.sections||'—'}</td>
            </tr>`;
        }).join('');
    }

    async function loadFacultyList() {
        const res = await Api.faculty.list();
        const sel = document.getElementById('fl_fac');
        if (res?.success) {
            _facList = res.data;
            sel.innerHTML='<option value="">— Select Faculty —</option>' +
                _facList.map(f=>`<option value="${f.faculty_id}">${f.faculty_code} — ${f.full_name} (${f.assigned_units||0}/${f.max_units} units)</option>`).join('');
        }
        /* Show info on change */
        sel.onchange = () => {
            const id=parseInt(sel.value);
            const fac=_facList.find(f=>f.faculty_id===id);
            const info=document.getElementById('fl_fac_info');
            if (fac) {
                info.style.display='block';
                info.innerHTML=`<strong>${fac.full_name}</strong> — ${fac.designation} (${fac.type}) · 
                    ${fac.assigned_units||0}/${fac.max_units} units · 
                    <span style="color:${fac.load_status==='OVERLOADED'?'#ef4444':fac.load_status==='NEAR_LIMIT'?'#f59e0b':'#10b981'};font-weight:600;">${fac.load_status?.replace('_',' ')}</span>
                    ${fac.specialization?`<br><small style="color:var(--text-gray);">Specialization: ${fac.specialization}</small>`:''}`;
            } else { info.style.display='none'; }
        };
    }

    async function loadSections() {
        const res = await Api.sections.list();
        const sel  = document.getElementById('fl_sec');
        const sel2 = document.getElementById('fl_view_sec');
        if (res?.success) {
            const opts='<option value="">— Select Section —</option>'+
                res.data.sections.map(s=>`<option value="${s.section_id}">${s.section_label} (${s.program} Y${s.year_level})</option>`).join('');
            sel.innerHTML=opts;
            sel2.innerHTML=opts;
        }
    }

    async function loadSectionSubjects() {
        const id=parseInt(document.getElementById('fl_sec').value)||0;
        const sel=document.getElementById('fl_sub');
        if (!id) { sel.innerHTML='<option value="">— Select section first —</option>'; return; }
        const res=await Api.faculty.sectionSubjects(id);
        if (res?.success) {
            sel.innerHTML='<option value="">— Select Subject —</option>'+
                res.data.map(s=>`<option value="${s.id}">${s.subject_code} — ${s.subject_desc} (${s.units}u)${s.professor?' ['+s.professor+']':''}</option>`).join('');
        }
    }

    async function assign() {
        const secId   = parseInt(document.getElementById('fl_sec').value)||0;
        const subId   = parseInt(document.getElementById('fl_sub').value)||0;
        const facId   = parseInt(document.getElementById('fl_fac').value)||0;
        const termId  = _activeTerm?.term_id || 1;

        if (!secId) return alert('Please select a Section.');
        if (!subId) return alert('Please select a Subject.');
        if (!facId) return alert('Please select a Faculty member.');

        const res=await Api.faculty.assign({subject_id:subId,faculty_id:facId,term_id:termId});
        if (res?.success) {
            _toast('success','Faculty Assigned',res.message);
            await Promise.all([load(),loadFacultyList(),loadSectionSubjects()]);
            renderSectionView();
        } else {
            alert(res?.message||'Assignment failed.');
        }
    }

    async function renderSectionView() {
        const id=parseInt(document.getElementById('fl_view_sec').value)||0;
        const container=document.getElementById('fl_sec_view');
        if (!id) { container.innerHTML='<div style="color:var(--text-gray);font-size:.85rem;">Select a section above.</div>'; return; }

        const res=await Api.faculty.sectionSubjects(id);
        if (!res?.success||!res.data.length) { container.innerHTML='<div style="color:var(--text-gray);">No subjects found.</div>'; return; }

        container.innerHTML=`<table style="width:100%;border-collapse:collapse;font-size:.82rem;">
            <thead><tr style="background:#f3f4f6;">
                <th style="padding:8px 14px;text-align:left;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-gray);">Subject Code</th>
                <th style="padding:8px 14px;text-align:left;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-gray);">Description</th>
                <th style="padding:8px 14px;text-align:center;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-gray);">Units</th>
                <th style="padding:8px 14px;text-align:left;font-size:.65rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-gray);">Assigned Professor</th>
            </tr></thead>
            <tbody>${res.data.map(s=>`
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                    <td style="padding:10px 14px;font-family:monospace;font-weight:700;color:#2563eb;">${s.subject_code}</td>
                    <td style="padding:10px 14px;color:var(--text-dark);">${s.subject_desc}</td>
                    <td style="padding:10px 14px;text-align:center;">${s.units}</td>
                    <td style="padding:10px 14px;">
                        ${s.professor
                            ?`<span style="color:var(--green);font-weight:600;"><i class="fa-solid fa-circle-check"></i> ${s.professor}</span>`
                            :'<span style="color:var(--text-gray);font-size:.75rem;"><i class="fa-solid fa-circle-xmark"></i> Not assigned</span>'}
                    </td>
                </tr>`).join('')}
            </tbody></table>`;
    }

    function exportCSV() {
        const rows = document.querySelectorAll('#fl_tbody tr');
        if (!rows.length) return;
        let csv='Faculty Code,Name,Type,Assigned Units,Max Units,Load%,Status,Sections\n';
        rows.forEach(r=>{
            const cells=r.querySelectorAll('td');
            if(cells.length>=8) csv+=`"${cells[0].textContent}","${cells[1].textContent}","${cells[2].textContent}","${cells[3].textContent}","${cells[4].textContent}","${cells[5].textContent}","${cells[6].textContent}","${cells[7].textContent}"\n`;
        });
        const a=document.createElement('a');a.href='data:text/csv;charset=utf-8,'+encodeURIComponent(csv);a.download='faculty_loads.csv';a.click();
    }

    function _toast(type,title,msg) {
        if(typeof toast==='function'){toast(type,title,msg);return;}
        const c=document.getElementById('toastContainer');if(!c)return;
        const el=document.createElement('div');el.className=`toast ${type}`;
        el.innerHTML=`<span class="toast-icon">${type==='success'?'✅':'⚠️'}</span><div class="toast-text"><strong>${title}</strong><span>${msg}</span></div>`;
        c.appendChild(el);setTimeout(()=>{el.style.animation='toastOut .3s ease forwards';setTimeout(()=>el.remove(),300);},3500);
    }

    document.addEventListener('DOMContentLoaded', init);
    document.addEventListener('click',e=>{if(e.target.closest('.menu-item,.nav-link'))setTimeout(()=>{loadSections();loadFacultyList();},80);});
    return {load,assign,loadSectionSubjects,renderSectionView,exportCSV};
})();
</script>
