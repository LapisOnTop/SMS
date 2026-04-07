(function () {
    'use strict';

    const API = 'program-setup.php';

    // ───────────────── API HELPER ─────────────────
    async function api(action, body) {
        const opts = { headers: { 'Content-Type': 'application/json' } };
        if (body) {
            opts.method = 'POST';
            opts.body = JSON.stringify(body);
        }
        try {
            const res = await fetch(API + '?action=' + action, opts);
            return await res.json();
        } catch {
            return { success: false, message: 'Network error. Please try again.' };
        }
    }

    // ───────────────── TOAST MESSAGE ─────────────────
    function showMessage(type, message) {
        const toast = document.createElement('div');
        toast.className = `message-toast message-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            background: ${getMessageColor(type)};
            color: white;
            border-radius: 8px;
            z-index: 2000;
            animation: slideInRight 0.3s ease;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function getMessageColor(type) {
        return {
            success: '#10b981',
            error: '#ef4444',
            info: '#3f69ff'
        }[type] || '#3f69ff';
    }

    // ───────────────── MODAL ─────────────────
    function openModal(title = 'Register Program') {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('programModal').classList.add('active');
        document.getElementById('modalOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('programModal').classList.remove('active');
        document.getElementById('modalOverlay').classList.remove('active');
        document.getElementById('programForm').reset();
        document.getElementById('programId').value = '';
        document.getElementById('submitBtn').textContent = 'Register Program';
        document.body.style.overflow = 'auto';
    }

    // ───────────────── TABLE RENDER ─────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildRow(p, i) {
        return `
        <tr data-id="${p.id}">
            <td>${i + 1}</td>
            <td>${escHtml(p.name)}</td>
            <td>${p.totalSubjects ?? 0}</td>
            <td>
                <button class="btn-edit" data-id="${p.id}">Edit</button>
                <button class="btn-view" data-id="${p.id}">View</button>
                <button class="btn-delete" data-id="${p.id}">Delete</button>
            </td>
        </tr>`;
    }

    async function refreshTable() {
        const res = await api('list');
        const tbody = document.getElementById('programsTableBody');

        if (!res.success || !res.data?.length) {
            tbody.innerHTML = `<tr><td colspan="4">No programs found</td></tr>`;
            return;
        }

        tbody.innerHTML = res.data.map(buildRow).join('');
    }

    // ───────────────── FORM SUBMIT ─────────────────
    document.getElementById('programForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const id = document.getElementById('programId').value;
        const name = document.getElementById('programName').value.trim();

        if (!name) {
            showMessage('error', 'Program name is required');
            return;
        }

        const body = { program_name: name };
        if (id) body.id = parseInt(id);

        const res = await api(id ? 'update' : 'create', body);

        if (res.success) {
            showMessage('success', res.message);
            closeModal();
            refreshTable();
        } else {
            showMessage('error', res.message);
        }
    });

    // ───────────────── TABLE ACTIONS ─────────────────
    document.getElementById('programsTableBody').addEventListener('click', async function (e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        const id = parseInt(btn.dataset.id);

        // EDIT
        if (btn.classList.contains('btn-edit')) {
            const res = await api('get&id=' + id);
            if (!res.success) return showMessage('error', res.message);

            document.getElementById('programId').value = res.data.id;
            document.getElementById('programName').value = res.data.name;
            document.getElementById('submitBtn').textContent = 'Update Program';

            openModal('Edit Program');
        }

        // VIEW
        if (btn.classList.contains('btn-view')) {
            window.location.href = 'course-scheduling.php?program_id=' + id;
        }

        // DELETE
        if (btn.classList.contains('btn-delete')) {
            if (!confirm('Delete this program?')) return;

            const res = await api('delete', { id });

            if (res.success) {
                showMessage('success', res.message);
                refreshTable();
            } else {
                showMessage('error', res.message);
            }
        }
    });

    // ───────────────── BUTTONS ─────────────────
    document.getElementById('registerProgramBtn').addEventListener('click', () => openModal());
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
    document.getElementById('modalOverlay').addEventListener('click', closeModal);

    // ───────────────── ANIMATION ─────────────────
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(300px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            to { transform: translateX(300px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // ───────────────── INIT ─────────────────
    document.addEventListener('DOMContentLoaded', refreshTable);

})();