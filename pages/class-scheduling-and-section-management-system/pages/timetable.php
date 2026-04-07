<?php /* SMS — pages/timetable.php */ ?>
<div class="page" id="page-timetable">
    <h1 class="page-title">Class <span>Timetable</span></h1>
    <p class="subtitle">Visual overview of all section room and schedule assignments.</p>

    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;align-items:center;">
        <button class="btn btn-secondary btn-sm" onclick="TTMOD.load()">
            <i class="fa-solid fa-rotate"></i> Refresh
        </button>
        <select class="filter-select" id="tt_prog" onchange="TTMOD.load()">
            <option value="">All Programs</option>
            <option value="BSIT">BSIT</option>
            <option value="BSCS">BSCS</option>
            <option value="BSCRIM">BSCRIM</option>
        </select>
        <select class="filter-select" id="tt_year" onchange="TTMOD.load()">
            <option value="">All Years</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
        </select>
    </div>

    <!-- Timetable Grid -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fa-solid fa-calendar-days" style="color:var(--active-blue)"></i>
                Schedule Grid <span class="chip" id="tt_count">0</span>
            </h2>
            <div style="display:flex;gap:8px;align-items:center;">
                <span style="font-size:.72rem;color:var(--text-gray);">
                    <span style="background:#dbeafe;color:#1e40af;padding:2px 7px;border-radius:4px;border:1px solid #bfdbfe;font-weight:500;">Morning</span>
                    <span style="background:#d1fae5;color:#065f46;padding:2px 7px;border-radius:4px;border:1px solid #a7f3d0;font-weight:500;margin-left:5px;">Afternoon</span>
                </span>
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>Room</th>
                    <th>Morning (6AM–12PM)</th>
                    <th>Afternoon (12PM–6PM)</th>
                </tr></thead>
                <tbody id="tt_tbody">
                    <tr><td colspan="3" style="text-align:center;padding:40px;color:var(--text-gray);">
                        <i class="fa-solid fa-spinner fa-spin"></i> Loading…
                    </td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sections List -->
    <div class="card" style="margin-top:16px;">
        <div class="card-header">
            <h2><i class="fa-solid fa-list" style="color:var(--active-blue)"></i> All Sections Overview</h2>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>#</th><th>Section</th><th>Program</th><th>Year / Sem</th>
                    <th>Category</th><th>Subjects</th><th>Room</th><th>Schedule</th>
                    <th>Enrolled</th>
                </tr></thead>
                <tbody id="tt_list_tbody">
                    <tr><td colspan="9" style="text-align:center;padding:30px;color:var(--text-gray);">Loading…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const TTMOD = (() => {
    const YL={1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};
    const SL={1:'1st Sem',2:'2nd Sem'};
    const YC={1:{bg:'#ecfdf5',c:'#065f46'},2:{bg:'#eff6ff',c:'#1d4ed8'},3:{bg:'#fdf4ff',c:'#7c3aed'},4:{bg:'#fffbeb',c:'#92400e'}};

    async function load() {
        const params={};
        const prog=document.getElementById('tt_prog')?.value;
        const year=document.getElementById('tt_year')?.value;
        if(prog) params.program=prog;
        if(year) params.year_level=year;

        const res=await Api.sections.list(params);
        if (!res?.success) {
            document.getElementById('tt_tbody').innerHTML='<tr><td colspan="3" style="text-align:center;color:#ef4444;padding:20px;">DB not connected.</td></tr>';
            return;
        }

        const secs=res.data.sections;
        document.getElementById('tt_count').textContent=secs.length;

        /* Room grid */
        const occ={};
        for(let r=1;r<=15;r++) occ[r]={Morning:[],Afternoon:[]};
        secs.forEach(s=>{
            const rn=parseInt(s.room_number)||0;
            const ts=s.time_schedule;
            if(rn>=1&&rn<=15&&ts&&occ[rn]&&occ[rn][ts]!==undefined) occ[rn][ts].push(s);
        });

        document.getElementById('tt_tbody').innerHTML=Array.from({length:15},(_,i)=>i+1).map(r=>{
            const morningCells=occ[r].Morning.map(s=>`
                <div style="display:inline-block;background:#dbeafe;color:#1e40af;font-size:.72rem;font-weight:600;
                            padding:4px 9px;border-radius:6px;border:1px solid #bfdbfe;margin:2px;">
                    ${s.section_label}
                    <span style="font-size:.62rem;opacity:.7;">(${s.program} Y${s.year_level})</span>
                </div>`).join('') || `<span style="font-size:.7rem;color:var(--text-gray);">—</span>`;
            const afternoonCells=occ[r].Afternoon.map(s=>`
                <div style="display:inline-block;background:#d1fae5;color:#065f46;font-size:.72rem;font-weight:600;
                            padding:4px 9px;border-radius:6px;border:1px solid #a7f3d0;margin:2px;">
                    ${s.section_label}
                    <span style="font-size:.62rem;opacity:.7;">(${s.program} Y${s.year_level})</span>
                </div>`).join('') || `<span style="font-size:.7rem;color:var(--text-gray);">—</span>`;

            return `<tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td style="font-weight:700;white-space:nowrap;">Room ${r}</td>
                <td>${morningCells}</td>
                <td>${afternoonCells}</td>
            </tr>`;
        }).join('');

        /* List */
        document.getElementById('tt_list_tbody').innerHTML=secs.map((s,i)=>{
            const yc=YC[s.year_level]||{bg:'#f3f4f6',c:'#6b7280'};
            const pct=s.max_capacity>0?Math.round((s.enrolled_count/s.max_capacity)*100):0;
            const bc=s.enrolled_count>=s.max_capacity?'#ef4444':pct>=80?'#f59e0b':'#10b981';
            return `<tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td style="color:var(--text-gray);font-size:.76rem;">${i+1}</td>
                <td class="mono">${s.section_label}</td>
                <td><span style="font-size:.7rem;background:#f3f4f6;padding:2px 7px;border-radius:4px;font-weight:600;">${s.program}</span></td>
                <td>
                    <span style="font-size:.72rem;padding:2px 9px;border-radius:20px;font-weight:500;background:${yc.bg};color:${yc.c};">${YL[s.year_level]}</span>
                    <span style="font-size:.72rem;color:var(--text-gray);margin-left:4px;">${SL[s.semester]||s.semester+'th Sem'}</span>
                </td>
                <td><span style="font-size:.72rem;background:${(s.category||'Lecture')==='Laboratory'?'#ede9fe':'#eff6ff'};color:${(s.category||'Lecture')==='Laboratory'?'#7c3aed':'#2563eb'};padding:2px 8px;border-radius:4px;border:1px solid ${(s.category||'Lecture')==='Laboratory'?'#ddd6fe':'#bfdbfe'};">${s.category||'Lecture'}</span></td>
                <td><span style="background:#eff6ff;color:#2563eb;font-size:.68rem;font-weight:700;padding:2px 8px;border-radius:4px;border:1px solid #bfdbfe;">${s.subject_count||0} subj</span></td>
                <td style="font-weight:600;">${s.room_number?'Room '+s.room_number:'<span style="color:var(--text-gray);font-size:.75rem;">—</span>'}</td>
                <td>${s.time_schedule?`<span style="font-size:.76rem;font-weight:600;color:${s.time_schedule==='Morning'?'#0891b2':'#d97706'};">${s.time_schedule}</span>`:'<span style="color:var(--text-gray);font-size:.75rem;">—</span>'}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:7px;">
                        <div style="width:44px;background:#e5e7eb;border-radius:20px;height:5px;overflow:hidden;flex-shrink:0;">
                            <div style="height:100%;width:${pct}%;background:${bc};border-radius:20px;"></div>
                        </div>
                        <span style="font-size:.75rem;color:var(--text-gray);">${s.enrolled_count}/${s.max_capacity}</span>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    document.addEventListener('DOMContentLoaded', load);
    return {load};
})();
</script>
