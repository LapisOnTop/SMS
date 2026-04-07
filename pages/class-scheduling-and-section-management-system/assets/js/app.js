/* ============================================================
   FILE: assets/js/app.js
   Navigation, toast — NO LOGIN REQUIRED
   ============================================================ */

/* ── Navigation ──────────────────────────────────────────── */
function showPage(name, el) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.menu-item, .nav-link').forEach(n => n.classList.remove('active'));

    const page = document.getElementById('page-' + name);
    if (page) page.classList.add('active');
    if (el)   el.classList.add('active');

    const titleEl = document.getElementById('topbarTitle');
    const subEl   = document.getElementById('topbarSub');
    const titles  = {
        dashboard:'Dashboard', sections:'Sections', timetable:'Timetable',
        rooms:'Room Assignment', faculty:'Faculty Loads',
        conflicts:'Conflicts', terms:'Academic Terms',
    };
    if (titleEl) titleEl.textContent = titles[name] || name;
    if (subEl)   subEl.textContent   = 'Overview';

    if (name === 'conflicts') updateConflictBadge();
}

function updateConflictBadge() {
    Api.conflicts.list().then(res => {
        if (res?.success) {
            const u = res.data.stats?.unresolved || 0;
            const b = document.getElementById('conflictBadge');
            if (b) { b.textContent = u; b.style.display = u > 0 ? 'inline-block' : 'none'; }
        }
    });
}

/* ── Toast ───────────────────────────────────────────────── */
function toast(type, title, msg) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `
        <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
        <div class="toast-text">
            <strong>${title}</strong>
            <span>${msg}</span>
        </div>`;
    container.appendChild(el);
    setTimeout(() => {
        el.style.animation = 'toastOut .3s ease forwards';
        setTimeout(() => el.remove(), 300);
    }, 3500);
}

/* ── Modal helpers ───────────────────────────────────────── */
function openModal(id)  { const m = document.getElementById('modal-' + id); if (m) m.classList.add('open'); }
function closeModal(id) { const m = document.getElementById('modal-' + id); if (m) m.classList.remove('open'); }

/* ── Profile dropdown in topbar ──────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const avatarBtn = document.getElementById('userAvatarBtn');
    const dropdown  = document.getElementById('profileDropdown');
    if (avatarBtn && dropdown) {
        avatarBtn.addEventListener('click', e => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });
        document.addEventListener('click', () => dropdown.classList.remove('show'));
    }

    /* Activate Dashboard on load */
    const firstMenu = document.querySelector('.menu-item');
    if (firstMenu) firstMenu.classList.add('active');

    /* Load conflict badge */
    updateConflictBadge();
});
