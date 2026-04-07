<?php /* SMS — pages/conflicts.php — Module 5 */ ?>
<div class="page" id="page-conflicts">
    <h1 class="page-title">Schedule <span>Conflicts</span></h1>
    <p class="subtitle">Detect and resolve Room, Faculty, and Section conflicts in <code>schedules.conflicts</code>.</p>

    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <button class="btn btn-primary" onclick="CFMOD.detect()">
            <i class="fa-solid fa-bolt"></i> Run Conflict Detection
        </button>
        <button class="btn btn-secondary btn-sm" onclick="CFMOD.load()">
            <i class="fa-solid fa-rotate"></i> Refresh
        </button>
    </div>

    <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:20px;">
        <div class="stat-card" style="--accent-color:#ef4444"><div class="label">Room Conflicts</div><div class="value" id="cf_room">—</div><div class="sub">Same room + time</div><div class="icon-bg"><i class="fa-solid fa-building"></i></div></div>
        <div class="stat-card" style="--accent-color:#f59e0b"><div class="label">Faculty Conflicts</div><div class="value" id="cf_fac">—</div><div class="sub">Double-booked</div><div class="icon-bg"><i class="fa-solid fa-chalkboard-teacher"></i></div></div>
        <div class="stat-card" style="--accent-color:#6366f1"><div class="label">Section Conflicts</div><div class="value" id="cf_sec">—</div><div class="sub">Duplicate labels</div><div class="icon-bg"><i class="fa-solid fa-folder-open"></i></div></div>
        <div class="stat-card" style="--accent-color:#dc2626"><div class="label">Unresolved</div><div class="value" id="cf_unres">—</div><div class="sub">Needs attention</div><div class="icon-bg"><i class="fa-solid fa-triangle-exclamation"></i></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Conflict List <span class="chip" id="cf_chip">0</span></h2>
        </div>
        <div id="cf_list" style="padding:16px;">
            <div style="text-align:center;padding:30px;color:var(--text-gray);font-size:.85rem;">
                <i class="fa-solid fa-spinner fa-spin"></i> Loading…
            </div>
        </div>
    </div>
</div>

<script>
const CFMOD = (() => {
    async function load() {
        const res = await Api.conflicts.list();
        if (!res?.success) {
            document.getElementById('cf_list').innerHTML='<div style="color:#ef4444;text-align:center;padding:20px;">DB not connected.</div>';
            return;
        }
        const {conflicts,stats}=res.data;
        document.getElementById('cf_room').textContent  =stats?.room||0;
        document.getElementById('cf_fac').textContent   =stats?.faculty||0;
        document.getElementById('cf_sec').textContent   =stats?.section||0;
        document.getElementById('cf_unres').textContent =stats?.unresolved||0;
        document.getElementById('cf_chip').textContent  =conflicts.length;

        /* Update sidebar badge */
        const badge=document.getElementById('conflictBadge');
        if(badge){
            const u=stats?.unresolved||0;
            badge.textContent=u;
            badge.style.display=u>0?'inline-block':'none';
        }

        const container=document.getElementById('cf_list');
        if (!conflicts.length) {
            container.innerHTML='<div style="text-align:center;padding:50px;color:var(--text-gray);"><i class="fa-solid fa-circle-check" style="font-size:3rem;color:#10b981;display:block;margin-bottom:12px;opacity:.6;"></i><div style="font-size:1rem;font-weight:600;color:var(--text-dark);">No Conflicts Detected</div><div style="font-size:.85rem;margin-top:6px;">Click "Run Conflict Detection" to scan for issues.</div></div>';
            return;
        }

        container.innerHTML=conflicts.map(c=>{
            const typeColor=c.type==='Room'?{bg:'#fef2f2',border:'#fecaca',color:'#991b1b',icon:'fa-building'}
                :c.type==='Faculty'?{bg:'#fffbeb',border:'#fde68a',color:'#92400e',icon:'fa-chalkboard-teacher'}
                :{bg:'#eef2ff',border:'#c7d2fe',color:'#5b21b6',icon:'fa-folder-open'};
            const resolved=c.is_resolved;
            return `<div style="display:flex;gap:14px;padding:14px 16px;border:1px solid ${resolved?'#d1fae5':'#e5e7eb'};border-radius:10px;margin-bottom:10px;background:${resolved?'#f0fdf4':'white'};transition:.2s;"
                onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.07)'" onmouseout="this.style.boxShadow=''">
                <div style="width:38px;height:38px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:${typeColor.bg};border:1px solid ${typeColor.border};">
                    <i class="fa-solid ${typeColor.icon}" style="color:${typeColor.color};"></i>
                </div>
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:4px;background:${typeColor.bg};color:${typeColor.color};border:1px solid ${typeColor.border};">${c.type}</span>
                        <strong style="font-size:.88rem;color:var(--text-dark);">${c.section_a}${c.section_b?' vs '+c.section_b:''}</strong>
                        ${resolved?'<span class="badge badge-green" style="font-size:.65rem;">Resolved</span>':''}
                    </div>
                    <div style="font-size:.8rem;color:var(--text-gray);line-height:1.5;">${c.description}</div>
                    <div style="font-size:.7rem;color:var(--text-gray);margin-top:4px;"><i class="fa-regular fa-clock"></i> ${new Date(c.detected_at).toLocaleString()}</div>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end;flex-shrink:0;">
                    ${!resolved?`<button class="btn btn-success btn-sm" onclick="CFMOD.resolve(${c.conflict_id})"><i class="fa-solid fa-check"></i> Resolve</button>`:''}
                    <button class="btn btn-danger btn-sm" onclick="CFMOD.del(${c.conflict_id})"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`;
        }).join('');
    }

    async function detect() {
        const btn=event.currentTarget;
        btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Detecting…';
        btn.disabled=true;
        const res=await Api.conflicts.detect();
        btn.innerHTML='<i class="fa-solid fa-bolt"></i> Run Conflict Detection';
        btn.disabled=false;
        if (res?.success) {
            _toast(res.data.count>0?'warning':'success',
                res.data.count+' Conflict(s) Found', res.message);
            await load();
        } else {
            alert(res?.message||'Detection failed.');
        }
    }

    async function resolve(id) {
        const res=await Api.conflicts.resolve(id);
        if (res?.success) { _toast('success','Resolved','Conflict marked as resolved.'); load(); }
    }

    async function del(id) {
        if (!confirm('Remove this conflict entry?')) return;
        const res=await Api.conflicts.delete(id);
        if (res?.success) load();
    }

    function _toast(type,title,msg) {
        if(typeof toast==='function'){toast(type,title,msg);return;}
        const c=document.getElementById('toastContainer');if(!c)return;
        const el=document.createElement('div');el.className=`toast ${type}`;
        el.innerHTML=`<span class="toast-icon">${type==='success'?'✅':'⚠️'}</span><div class="toast-text"><strong>${title}</strong><span>${msg}</span></div>`;
        c.appendChild(el);setTimeout(()=>{el.style.animation='toastOut .3s ease forwards';setTimeout(()=>el.remove(),300);},3500);
    }

    document.addEventListener('DOMContentLoaded', load);
    return {load, detect, resolve, del};
})();
</script>
