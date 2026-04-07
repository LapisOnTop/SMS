<?php /* SMS — pages/terms.php — Module 4 */ ?>
<div class="page" id="page-terms">
    <h1 class="page-title">Academic <span>Terms</span></h1>
    <p class="subtitle">Create and activate academic terms stored in <code>schedules.terms</code>.</p>

    <div style="display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start;">

        <!-- Create Term Panel -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h2><i class="fa-solid fa-calendar-plus" style="color:var(--active-blue)"></i> New Term</h2>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Semester <span style="color:#ef4444">*</span></label>
                    <select class="form-control" id="term_sem">
                        <option value="1">1st Semester</option>
                        <option value="2">2nd Semester</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Academic Year <span style="color:#ef4444">*</span></label>
                    <input class="form-control" id="term_year" placeholder="e.g. 2026-2027"
                           pattern="\d{4}-\d{4}" value="2025-2026"/>
                    <div style="font-size:.72rem;color:var(--text-gray);margin-top:4px;">Format: YYYY-YYYY</div>
                </div>
                <button class="btn btn-primary" style="width:100%;justify-content:center;" onclick="TERMMOD.create()">
                    <i class="fa-solid fa-plus"></i> Create Term
                </button>
            </div>
        </div>

        <!-- Terms Table -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h2>All Academic Terms <span class="chip" id="term_count">0</span></h2>
                <button class="btn btn-secondary btn-sm" onclick="TERMMOD.load()">
                    <i class="fa-solid fa-rotate"></i> Refresh
                </button>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>#</th><th>Term Label</th><th>Semester</th>
                        <th>Academic Year</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody id="term_tbody">
                        <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-gray);">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading…
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
const TERMMOD = (() => {
    async function load() {
        const res=await Api.terms.list();
        const tbody=document.getElementById('term_tbody');
        const cnt=document.getElementById('term_count');
        if (!res?.success) {
            tbody.innerHTML='<tr><td colspan="6" style="text-align:center;color:#ef4444;padding:20px;">DB not connected.</td></tr>';
            return;
        }
        cnt.textContent=res.data.length;
        if (!res.data.length) {
            tbody.innerHTML='<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-gray);">No terms yet. Create one.</td></tr>';
            return;
        }
        tbody.innerHTML=res.data.map((t,i)=>`
            <tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td style="color:var(--text-gray);font-size:.76rem;">${i+1}</td>
                <td style="font-weight:600;color:var(--text-dark);">${t.term_label}</td>
                <td><span style="font-size:.72rem;background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:4px;border:1px solid #bfdbfe;">${t.semester===1||t.semester==='1'?'1st':'2nd'} Semester</span></td>
                <td style="font-family:monospace;font-weight:600;">${t.academic_year}</td>
                <td>
                    ${(t.is_active==1||t.is_active===true)
                        ?'<span class="badge badge-green"><span class="badge-dot"></span>Active</span>'
                        :'<span class="badge badge-gray"><span class="badge-dot"></span>Inactive</span>'}
                </td>
                <td>
                    ${(t.is_active!=1&&t.is_active!==true)?`
                        <button class="btn btn-primary btn-sm" onclick="TERMMOD.activate(${t.term_id})">
                            <i class="fa-solid fa-check"></i> Set Active
                        </button>`
                    :'<span style="font-size:.75rem;color:var(--text-gray);">Current Term</span>'}
                </td>
            </tr>`).join('');
    }

    async function create() {
        const sem  = parseInt(document.getElementById('term_sem').value)||0;
        const year = document.getElementById('term_year').value.trim();
        if (!sem)  return alert('Please select a semester.');
        if (!/^\d{4}-\d{4}$/.test(year)) return alert('Academic year format: YYYY-YYYY (e.g. 2026-2027)');

        const res=await Api.terms.create({semester:sem,academic_year:year});
        if (res?.success) {
            _toast('success','Term Created','Academic term added successfully.');
            document.getElementById('term_year').value='';
            load();
        } else {
            alert(res?.message||'Failed to create term.');
        }
    }

    async function activate(id) {
        const res=await Api.terms.activate(id);
        if (res?.success) { _toast('success','Term Activated','Active term updated.'); load(); }
        else alert(res?.message||'Failed to activate term.');
    }

    function _toast(type,title,msg) {
        if(typeof toast==='function'){toast(type,title,msg);return;}
        const c=document.getElementById('toastContainer');if(!c)return;
        const el=document.createElement('div');el.className=`toast ${type}`;
        el.innerHTML=`<span class="toast-icon">✅</span><div class="toast-text"><strong>${title}</strong><span>${msg}</span></div>`;
        c.appendChild(el);setTimeout(()=>{el.style.animation='toastOut .3s ease forwards';setTimeout(()=>el.remove(),300);},3500);
    }

    document.addEventListener('DOMContentLoaded', load);
    return {load, create, activate};
})();
</script>
