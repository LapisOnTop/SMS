<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$db = ums_db();
ums_install_schema($db);
ums_require_admin();

$tab = isset($_GET['tab']) ? preg_replace('/[^a-z\-]/', '', (string) $_GET['tab']) : 'dashboard';
if ($tab === '') $tab = 'dashboard';

function nav_class(string $current, string $target): string
{
    return 'nav-link' . ($current === $target ? ' active' : '');
}

$username = (string)($_SESSION['username'] ?? 'Admin');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="mark">SMS</div>
            <div>
                <h1>User Management</h1>
                <p>Users, roles, and access control</p>
            </div>
        </div>

        <nav class="nav">
            <div class="group">User Management</div>
            <a class="<?php echo nav_class($tab, 'dashboard'); ?>" href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/user-management.php?tab=dashboard'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-chart-line"></i> Dashboard
            </a>
            <a class="<?php echo nav_class($tab, 'create'); ?>" href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/user-management.php?tab=create'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-user-plus"></i> Create Account
            </a>
            <a class="<?php echo nav_class($tab, 'accounts'); ?>" href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/user-management.php?tab=accounts'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-users"></i> Accounts
            </a>
            <a class="<?php echo nav_class($tab, 'report'); ?>" href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/user-management.php?tab=report'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-file-lines"></i> Generate Report
            </a>
            <a class="<?php echo nav_class($tab, 'audit'); ?>" href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/user-management.php?tab=audit'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-shield-halved"></i> User Activity Logs
            </a>

            <a class="nav-link danger" href="<?php echo htmlspecialchars(sms_url('components/logout.php'), ENT_QUOTES); ?>">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </nav>
    </aside>

    <main class="content">
        <div class="topbar">
            <div class="title">
                <h2>User Management</h2>
                <p>Overview of users, roles, and activity.</p>
            </div>
            <div class="user">
                <span class="pill"><i class="fa-solid fa-user-gear"></i> <?php echo htmlspecialchars($username); ?></span>
                <a class="btn-link" href="<?php echo htmlspecialchars(sms_url('components/logout.php'), ENT_QUOTES); ?>">Logout</a>
            </div>
        </div>

        <section class="hero">
            <div>
                <h3><?php echo $tab === 'create' ? 'Create Account' : ($tab === 'accounts' ? 'Accounts' : ($tab === 'report' ? 'Generate Report' : ($tab === 'audit' ? 'User Activity Logs & Audit Trail' : 'Dashboard'))); ?></h3>
                <p><?php echo $tab === 'create' ? 'Create login credentials stored in the users table. These can be used to log into SIM.' : ($tab === 'accounts' ? 'Manage staff and student accounts.' : ($tab === 'report' ? 'Summary of account distribution and activity.' : ($tab === 'audit' ? 'Real activity log entries recorded by the system.' : 'Quick view of user system state.'))); ?></p>
            </div>
            <div class="hero-chip">
                <i class="fa-solid fa-circle-info"></i>
                <span style="font-size:12px;color:var(--muted);">Admin workspace</span>
            </div>
        </section>

        <?php if ($tab === 'dashboard') { ?>
            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-chart-pie"></i> Snapshot</h4>
                    <button class="btn ghost" id="refreshDash"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>
                <div class="card-body">
                    <div class="stat-row" id="statRow"></div>
                </div>
            </section>
        <?php } ?>

        <?php if ($tab === 'create') { ?>
            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-user-plus"></i> Create Registrar Staff/Nurse/Faculty/HR Officer Account</h4>
                </div>
                <div class="card-body">
                    <form id="createStaffForm">
                        <div class="grid-2">
                            <div class="field">
                                <label>Username *</label>
                                <input name="username" placeholder="Type employee ID, name, or position" required>
                            </div>
                            <div class="field">
                                <label>Password *</label>
                                <input name="password" type="password" placeholder="Set initial password" required>
                            </div>
                            <div class="field">
                                <label>Full Name</label>
                                <input name="full_name" placeholder="Full name">
                            </div>
                            <div class="field">
                                <label>Email</label>
                                <input name="email" type="email" placeholder="Email">
                            </div>
                            <div class="field">
                                <label>Role *</label>
                                <select name="role" id="roleSelect" required></select>
                            </div>
                            <div class="field">
                                <label>Position</label>
                                <input name="position" placeholder="Position">
                            </div>
                        </div>
                        <div class="actions">
                            <button class="btn primary" type="submit"><i class="fa-solid fa-user-check"></i> Create Account</button>
                        </div>
                        <div id="createMsg" style="margin-top:10px;font-size:12px;color:var(--muted);"></div>
                    </form>
                </div>
            </section>

            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-user-graduate"></i> Create Student Account (From Student List)</h4>
                </div>
                <div class="card-body">
                    <form id="createStudentForm">
                        <div class="grid-2">
                            <div class="field">
                                <label>Student *</label>
                                <select id="studentSelect" name="student_id" required></select>
                                <div class="hint" style="margin-top:6px;">Only shows students with no login yet (students.user_id is NULL).</div>
                            </div>
                            <div class="field">
                                <label>Initial Password *</label>
                                <input name="password" id="studentPassword" type="password" placeholder="Set initial password" required>
                                <div class="hint" style="margin-top:6px;">Tip: you can use the student number as the first password.</div>
                            </div>
                        </div>
                        <div class="actions">
                            <button class="btn primary" type="submit"><i class="fa-solid fa-user-check"></i> Create Student Account</button>
                        </div>
                        <div id="createStudentMsg" style="margin-top:10px;font-size:12px;color:var(--muted);"></div>
                    </form>
                </div>
            </section>
        <?php } ?>

        <?php if ($tab === 'accounts') { ?>
            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-users"></i> Registrar Staff, Nurse, Faculty &amp; HR Officer Accounts</h4>
                    <button class="btn ghost" id="refreshUsers"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>
                <div class="card-body">
                    <div style="overflow:auto;">
                        <table>
                            <thead>
                            <tr>
                                <th>Username</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody id="usersTbody"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php if ($tab === 'report') { ?>
            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-file-lines"></i> Generate Report</h4>
                    <button class="btn primary" id="btnGen"><i class="fa-solid fa-bolt"></i> Generate Report</button>
                </div>
                <div class="card-body">
                    <div class="stat-row" id="reportStats"></div>
                    <div class="grid-2" style="margin-top:14px;">
                        <div class="card" style="box-shadow:none;">
                            <div class="card-head"><h4>Accounts By Role</h4></div>
                            <div class="card-body">
                                <table>
                                    <thead><tr><th>Role</th><th>Total</th></tr></thead>
                                    <tbody id="byRole"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card" style="box-shadow:none;">
                            <div class="card-head"><h4>Recent Accounts</h4></div>
                            <div class="card-body">
                                <table>
                                    <thead><tr><th>Name</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
                                    <tbody id="recent"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>

        <?php if ($tab === 'audit') { ?>
            <section class="card">
                <div class="card-head">
                    <h4><i class="fa-solid fa-shield-halved"></i> Recent User Management Activity</h4>
                    <button class="btn ghost" id="refreshAudit"><i class="fa-solid fa-rotate"></i> Refresh</button>
                </div>
                <div class="card-body">
                    <div style="overflow:auto;">
                        <table>
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Actor</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Module</th>
                                <th>Target</th>
                            </tr>
                            </thead>
                            <tbody id="auditTbody"></tbody>
                        </table>
                    </div>
                </div>
            </section>
        <?php } ?>
    </main>
</div>

<script>
const tab = <?php echo json_encode($tab); ?>;

async function api(path, options) {
  const res = await fetch(path, Object.assign({
    credentials: 'same-origin',
    headers: {'Content-Type':'application/json'}
  }, options || {}));
  const j = await res.json();
  if (!res.ok || j.ok === false) throw new Error(j.message || ('HTTP ' + res.status));
  return j;
}

function statBox(k, v) {
  const d = document.createElement('div');
  d.className = 'stat';
  d.innerHTML = `<div class="k">${k}</div><div class="v">${v}</div>`;
  return d;
}

async function loadRoles() {
  const j = await api('api/users.php?action=roles');
  const sel = document.getElementById('roleSelect');
  if (!sel) return;
  sel.innerHTML = '';
  const preferred = ['Registrar','Faculty','Nurse','Hr Officer','Student','Admin','Cashier'];
  const roles = j.roles.slice().sort((a,b) => {
    const ia = preferred.indexOf(a.role_name);
    const ib = preferred.indexOf(b.role_name);
    return (ia === -1 ? 999 : ia) - (ib === -1 ? 999 : ib);
  });
  for (const r of roles) {
    const opt = document.createElement('option');
    opt.value = r.role_name;
    opt.textContent = r.role_name;
    sel.appendChild(opt);
  }
  sel.value = 'Registrar';
}

async function loadUsers() {
  const j = await api('api/users.php?action=list');
  const tb = document.getElementById('usersTbody');
  if (!tb) return;
  tb.innerHTML = '';
  for (const u of j.users) {
    const tr = document.createElement('tr');
    const active = Number(u.is_active) === 1;
    tr.innerHTML = `
      <td>${u.username || ''}</td>
      <td>${u.full_name || ''}</td>
      <td>${u.email || ''}</td>
      <td>${u.role || ''}</td>
      <td><span class="badge ${active ? 'ok' : 'off'}">${active ? 'Active' : 'Inactive'}</span></td>
      <td>${u.created_at || ''}</td>
      <td>
        <button class="btn ${active ? 'ghost' : 'primary'}" data-id="${u.user_id}" data-active="${active ? 1 : 0}">
          ${active ? 'Inactive' : 'Active'}
        </button>
      </td>
    `;
    tr.querySelector('button').addEventListener('click', async (e) => {
      const id = Number(e.currentTarget.getAttribute('data-id'));
      const was = Number(e.currentTarget.getAttribute('data-active')) === 1;
      await api('api/users.php?action=set_status', {
        method:'POST',
        body: JSON.stringify({user_id:id, is_active: was ? 0 : 1})
      });
      await loadUsers();
    });
    tb.appendChild(tr);
  }
}

async function loadReport() {
  const j = await api('api/users.php?action=report');
  const s = document.getElementById('reportStats');
  if (s) {
    s.innerHTML = '';
    s.appendChild(statBox('Total Accounts', j.stats.total));
    s.appendChild(statBox('Active Accounts', j.stats.active));
    s.appendChild(statBox('Student Accounts', j.stats.student));
    s.appendChild(statBox('Staff Accounts', j.stats.staff));
  }
  const byRole = document.getElementById('byRole');
  if (byRole) {
    byRole.innerHTML = '';
    for (const r of j.roles) {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.role || '-'}</td><td>${r.total}</td>`;
      byRole.appendChild(tr);
    }
  }
  const recent = document.getElementById('recent');
  if (recent) {
    recent.innerHTML = '';
    for (const r of j.recent) {
      const active = Number(r.is_active) === 1;
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${r.name}</td><td>${r.role || ''}</td><td><span class="badge ${active?'ok':'off'}">${active?'Active':'Inactive'}</span></td><td>${r.created_at}</td>`;
      recent.appendChild(tr);
    }
  }
}

async function loadAudit() {
  const j = await api('api/audit.php');
  const tb = document.getElementById('auditTbody');
  if (!tb) return;
  tb.innerHTML = '';
  for (const r of j.logs) {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${r.created_at || ''}</td>
      <td>${r.actor || ''}</td>
      <td>${r.role || ''}</td>
      <td>${r.action || ''}</td>
      <td>${r.module || ''}</td>
      <td>${r.target || ''}</td>
    `;
    tb.appendChild(tr);
  }
}

async function loadDashboard() {
  const j = await api('api/users.php?action=report');
  const row = document.getElementById('statRow');
  if (!row) return;
  row.innerHTML = '';
  row.appendChild(statBox('Total Accounts', j.stats.total));
  row.appendChild(statBox('Active Accounts', j.stats.active));
  row.appendChild(statBox('Student Accounts', j.stats.student));
  row.appendChild(statBox('Staff Accounts', j.stats.staff));
}

async function loadStudentCandidates() {
  const j = await api('api/users.php?action=student_candidates');
  const sel = document.getElementById('studentSelect');
  if (!sel) return;
  sel.innerHTML = '';
  const placeholder = document.createElement('option');
  placeholder.value = '';
  placeholder.textContent = 'Select a student...';
  sel.appendChild(placeholder);

  for (const s of j.students) {
    const name = [s.last_name, s.first_name, s.middle_name].filter(Boolean).join(', ').trim();
    const label = `${s.student_number || ''}${name ? ' — ' + name : ''}${s.status ? ' (' + s.status + ')' : ''}`;
    const opt = document.createElement('option');
    opt.value = s.student_id;
    opt.textContent = label;
    opt.setAttribute('data-student-number', s.student_number || '');
    sel.appendChild(opt);
  }
}

document.addEventListener('DOMContentLoaded', async () => {
  try {
    if (tab === 'create') {
      await loadRoles();
      await loadStudentCandidates();
      const form = document.getElementById('createStaffForm');
      const msg = document.getElementById('createMsg');
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msg.textContent = 'Creating...';
        const fd = new FormData(form);
        const payload = Object.fromEntries(fd.entries());
        await api('api/users.php?action=create', {method:'POST', body: JSON.stringify(payload)});
        msg.textContent = 'Account created. You can now use this username/password to login to SIM (choose the matching role).';
        form.reset();
        await loadRoles();
      });

      const stForm = document.getElementById('createStudentForm');
      const stMsg = document.getElementById('createStudentMsg');
      const stSel = document.getElementById('studentSelect');
      const stPw = document.getElementById('studentPassword');
      stSel.addEventListener('change', () => {
        const opt = stSel.options[stSel.selectedIndex];
        const sn = opt ? (opt.getAttribute('data-student-number') || '') : '';
        if (sn && !stPw.value) {
          stPw.value = sn;
        }
      });
      stForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        stMsg.textContent = 'Creating...';
        const fd = new FormData(stForm);
        const payload = Object.fromEntries(fd.entries());
        await api('api/users.php?action=create_student', {method:'POST', body: JSON.stringify(payload)});
        stMsg.textContent = 'Student account created. The student can now login to SIM as Student.';
        stForm.reset();
        await loadStudentCandidates();
      });
    }
    if (tab === 'accounts') {
      await loadUsers();
      document.getElementById('refreshUsers').addEventListener('click', loadUsers);
    }
    if (tab === 'report') {
      document.getElementById('btnGen').addEventListener('click', loadReport);
      await loadReport();
    }
    if (tab === 'audit') {
      await loadAudit();
      document.getElementById('refreshAudit').addEventListener('click', loadAudit);
    }
    if (tab === 'dashboard') {
      await loadDashboard();
      document.getElementById('refreshDash').addEventListener('click', loadDashboard);
    }
  } catch (e) {
    console.error(e);
  }
});
</script>
</body>
</html>

