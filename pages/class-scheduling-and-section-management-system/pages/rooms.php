<?php /* SMS — pages/rooms.php — Module 2 */ ?>
<div class="page" id="page-rooms">
    <h1 class="page-title">Room <span>Assignment</span></h1>
    <p class="subtitle">Assign rooms (1–15) and schedule types to sections. Conflict detection prevents double-booking.</p>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- Assign Panel -->
        <div>
            <div class="card" style="margin-bottom:0;">
                <div class="card-header">
                    <h2><i class="fa-solid fa-door-open" style="color:var(--active-blue)"></i> Assign Room</h2>
                </div>
                <div style="padding:20px;display:flex;flex-direction:column;gap:14px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Section</label>
                        <select class="form-control" id="rm_sec" onchange="RMMOD.onSelect()">
                            <option value="">— Loading... —</option>
                        </select>
                    </div>

                    <div id="rm_sec_info" style="display:none;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;padding:11px;">
                        <div style="font-size:.65rem;font-weight:600;text-transform:uppercase;color:var(--text-gray);letter-spacing:.6px;margin-bottom:8px;">Current Assignment</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <div style="background:white;border:1px solid #e5e7eb;border-radius:6px;padding:8px;text-align:center;">
                                <div style="font-size:.62rem;color:var(--text-gray);margin-bottom:2px;">Room</div>
                                <div id="rm_cur_room" style="font-size:.88rem;font-weight:700;color:#2563eb;">—</div>
                            </div>
                            <div style="background:white;border:1px solid #e5e7eb;border-radius:6px;padding:8px;text-align:center;">
                                <div style="font-size:.62rem;color:var(--text-gray);margin-bottom:2px;">Schedule</div>
                                <div id="rm_cur_time" style="font-size:.88rem;font-weight:700;color:#2563eb;">—</div>
                            </div>
                        </div>
                    </div>

                    <div id="rm_suggest_box" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px;font-size:.78rem;color:#1d4ed8;">
                        <i class="fa-solid fa-lightbulb"></i> <strong>Suggestion:</strong> <span id="rm_suggest_text"></span>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Room Number (1–15)</label>
                        <select class="form-control" id="rm_room">
                            <?php for($i=1;$i<=15;$i++) echo "<option value='$i'>Room $i</option>"; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Schedule Type</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                            <label id="rm_lbl_m" onclick="RMMOD.pickTime('Morning')"
                                   style="cursor:pointer;padding:10px;border:2px solid #2563eb;border-radius:8px;background:#eff6ff;text-align:center;">
                                <div style="font-size:1.1rem;margin-bottom:3px;">🌅</div>
                                <div style="font-size:.78rem;font-weight:700;color:#2563eb;">Morning</div>
                                <div style="font-size:.62rem;color:#6b7280;">6:00 AM – 12:00 PM</div>
                            </label>
                            <label id="rm_lbl_a" onclick="RMMOD.pickTime('Afternoon')"
                                   style="cursor:pointer;padding:10px;border:2px solid #e5e7eb;border-radius:8px;background:white;text-align:center;">
                                <div style="font-size:1.1rem;margin-bottom:3px;">🌇</div>
                                <div style="font-size:.78rem;font-weight:700;color:var(--text-gray);">Afternoon</div>
                                <div style="font-size:.62rem;color:#6b7280;">12:00 PM – 6:00 PM</div>
                            </label>
                        </div>
                    </div>

                    <button class="btn btn-primary" style="width:100%;justify-content:center;" onclick="RMMOD.assign()">
                        <i class="fa-solid fa-check"></i> Assign Room &amp; Schedule
                    </button>
                </div>
            </div>
        </div>

        <!-- Availability Matrix -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h2><i class="fa-solid fa-table-cells" style="color:var(--active-blue)"></i> Room Availability (15 Rooms)</h2>
                <span style="font-size:.73rem;color:var(--text-gray);">
                    <span style="background:#ecfdf5;color:#065f46;padding:2px 8px;border-radius:4px;border:1px solid #a7f3d0;font-weight:500;">✓ Free</span>&nbsp;
                    <span style="background:#fef2f2;color:#991b1b;padding:2px 8px;border-radius:4px;border:1px solid #fecaca;font-weight:500;">✗ Occupied</span>
                </span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr>
                        <th>Room</th>
                        <th style="text-align:center;">Morning</th>
                        <th style="text-align:center;">Afternoon</th>
                    </tr></thead>
                    <tbody id="rm_matrix">
                        <tr><td colspan="3" style="text-align:center;padding:30px;color:var(--text-gray);">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading…
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <h2><i class="fa-solid fa-list-check" style="color:var(--active-blue)"></i>
                Section Assignments <span class="chip" id="rm_count">0</span>
            </h2>
            <button class="btn btn-secondary btn-sm" onclick="RMMOD.loadMatrix()">
                <i class="fa-solid fa-rotate"></i> Refresh
            </button>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr>
                    <th>#</th><th>Section</th><th>Program</th><th>Year</th>
                    <th>Room</th><th>Schedule</th><th>Status</th>
                </tr></thead>
                <tbody id="rm_assign_tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const RMMOD = (() => {
    let _timeChoice='Morning';

    function pickTime(v) {
        _timeChoice=v;
        const m=document.getElementById('rm_lbl_m');
        const a=document.getElementById('rm_lbl_a');
        const set=(el,on)=>{
            el.style.borderColor=on?'#2563eb':'#e5e7eb';
            el.style.background=on?'#eff6ff':'white';
            el.querySelector('div:nth-child(2)').style.color=on?'#2563eb':'var(--text-gray)';
        };
        if(m&&a){set(m,v==='Morning');set(a,v==='Afternoon');}
    }

    async function loadSections() {
        const res=await Api.sections.list();
        const sel=document.getElementById('rm_sec');
        if (res?.success) {
            sel.innerHTML='<option value="">— Choose Section —</option>'+
                res.data.sections.map(s=>`<option value="${s.section_id}">${s.section_label} (${s.program} Y${s.year_level})</option>`).join('');
        }
    }

    async function onSelect() {
        const id=parseInt(document.getElementById('rm_sec').value)||0;
        const info=document.getElementById('rm_sec_info');
        const sugBox=document.getElementById('rm_suggest_box');
        if (!id) { info.style.display='none'; sugBox.style.display='none'; return; }

        const res=await Api.schedule.suggest(id);
        if (res?.success) {
            const sec=res.data.section;
            info.style.display='block';
            document.getElementById('rm_cur_room').textContent=sec.room_number?'Room '+sec.room_number:'None';
            document.getElementById('rm_cur_time').textContent=sec.time_schedule||'None';
            if (sec.room_number) document.getElementById('rm_room').value=sec.room_number;
            if (sec.time_schedule) pickTime(sec.time_schedule);

            if (res.data.suggestion) {
                sugBox.style.display='block';
                document.getElementById('rm_suggest_text').textContent=
                    `Room ${res.data.suggestion.room_number} — ${res.data.suggestion.time_schedule}`;
            } else {
                sugBox.style.display='none';
            }
        } else { info.style.display='none'; }
    }

    async function assign() {
        const secId=parseInt(document.getElementById('rm_sec').value)||0;
        const room=parseInt(document.getElementById('rm_room').value)||0;
        if (!secId) return alert('Please select a section.');
        if (!room||room<1||room>15) return alert('Please select a valid room (1–15).');

        const res=await Api.schedule.assign({section_id:secId,room_number:room,time_schedule:_timeChoice});
        if (res?.success) {
            _toast('success','Assigned',`Room ${room} — ${_timeChoice}`);
            await loadMatrix();
            onSelect();
        } else {
            alert(res?.message||'Assignment failed.');
        }
    }

    async function loadMatrix() {
        /* Build matrix from sections list */
        const res=await Api.sections.list();
        const tbody=document.getElementById('rm_matrix');
        const atbody=document.getElementById('rm_assign_tbody');
        const cnt=document.getElementById('rm_count');

        if (!res?.success) {
            tbody.innerHTML='<tr><td colspan="3" style="text-align:center;color:#ef4444;padding:20px;">DB not connected.</td></tr>';
            return;
        }

        const secs=res.data.sections;

        /* Build occupancy map: room → {Morning: sectionLabel, Afternoon: sectionLabel} */
        const occ={};
        for(let r=1;r<=15;r++) occ[r]={Morning:null,Afternoon:null};
        secs.forEach(s=>{
            const rn=parseInt(s.room_number)||0;
            const ts=s.time_schedule;
            if(rn>=1&&rn<=15&&ts&&occ[rn]) {
                occ[rn][ts]=s.section_label;
            }
        });

        tbody.innerHTML=Array.from({length:15},(_,i)=>i+1).map(r=>{
            const m=occ[r].Morning;
            const a=occ[r].Afternoon;
            const cellM=m?`<span style="background:#fef2f2;color:#991b1b;font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:4px;border:1px solid #fecaca;">✗ ${m}</span>`
                          :`<span style="background:#ecfdf5;color:#065f46;font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:4px;border:1px solid #a7f3d0;">✓ Free</span>`;
            const cellA=a?`<span style="background:#fef2f2;color:#991b1b;font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:4px;border:1px solid #fecaca;">✗ ${a}</span>`
                          :`<span style="background:#ecfdf5;color:#065f46;font-size:.7rem;font-weight:600;padding:3px 9px;border-radius:4px;border:1px solid #a7f3d0;">✓ Free</span>`;
            return `<tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td style="font-weight:700;color:var(--text-dark);">Room ${r}</td>
                <td style="text-align:center;">${cellM}</td>
                <td style="text-align:center;">${cellA}</td>
            </tr>`;
        }).join('');

        /* Assignments table */
        const assigned=secs.filter(s=>s.room_number);
        cnt.textContent=assigned.length;
        const YL={1:'1st Year',2:'2nd Year',3:'3rd Year',4:'4th Year'};
        const YC={1:{bg:'#ecfdf5',c:'#065f46'},2:{bg:'#eff6ff',c:'#1d4ed8'},3:{bg:'#fdf4ff',c:'#7c3aed'},4:{bg:'#fffbeb',c:'#92400e'}};
        if (!assigned.length) {
            atbody.innerHTML='<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-gray);"><i class="fa-solid fa-door-open" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px;"></i>No room assignments yet.</td></tr>';
            return;
        }
        atbody.innerHTML=assigned.map((s,i)=>{
            const yc=YC[s.year_level]||{bg:'#f3f4f6',c:'#6b7280'};
            return `<tr onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                <td style="color:var(--text-gray);font-size:.76rem;">${i+1}</td>
                <td><strong style="color:var(--text-dark);">${s.section_label}</strong></td>
                <td><span style="font-size:.7rem;background:#f3f4f6;padding:2px 7px;border-radius:4px;font-weight:600;">${s.program}</span></td>
                <td><span style="font-size:.7rem;padding:2px 9px;border-radius:20px;font-weight:500;background:${yc.bg};color:${yc.c};">${YL[s.year_level]||s.year_level}</span></td>
                <td><strong style="font-family:monospace;">Room ${s.room_number}</strong></td>
                <td><span style="font-size:.78rem;font-weight:600;color:${s.time_schedule==='Morning'?'#0891b2':'#d97706'};">
                    <i class="fa-solid fa-clock"></i> ${s.time_schedule}</span></td>
                <td><span class="badge badge-green"><span class="badge-dot"></span>Assigned</span></td>
            </tr>`;
        }).join('');
    }

    function _toast(type,title,msg) {
        if(typeof toast==='function'){toast(type,title,msg);return;}
        const c=document.getElementById('toastContainer');if(!c)return;
        const el=document.createElement('div');el.className=`toast ${type}`;
        el.innerHTML=`<span class="toast-icon">${type==='success'?'✅':'⚠️'}</span><div class="toast-text"><strong>${title}</strong><span>${msg}</span></div>`;
        c.appendChild(el);setTimeout(()=>{el.style.animation='toastOut .3s ease forwards';setTimeout(()=>el.remove(),300);},3500);
    }

    document.addEventListener('DOMContentLoaded',()=>{loadSections();loadMatrix();});
    document.addEventListener('click',e=>{if(e.target.closest('.menu-item,.nav-link'))setTimeout(()=>{loadSections();loadMatrix();},80);});
    return {onSelect,assign,loadMatrix,pickTime};
})();
</script>
