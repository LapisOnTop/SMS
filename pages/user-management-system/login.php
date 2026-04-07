<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// If already logged in as Admin, go straight to app.
if (!empty($_SESSION['user_id']) && (($_SESSION['role'] ?? '') === 'Admin')) {
    header('Location: ' . sms_url('pages/user-management-system/user-management.php'));
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — Login</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .login-wrap{min-height:100vh; display:grid; place-items:center; padding:26px;}
        .login-card{width:min(520px, 100%); padding:18px; }
        .login-card .card-body{padding:18px;}
        .login-head{display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px;}
        .login-head h2{font-size:18px;}
        .hint{font-size:12px; color:var(--muted);}
        .err{display:none; margin-top:10px; background:rgba(239,68,68,.10); border:1px solid rgba(239,68,68,.25); padding:10px 12px; border-radius:12px; color:#991b1b; font-size:12px;}
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="card login-card">
        <div class="card-head">
            <div>
                <h4><i class="fa-solid fa-user-shield"></i> User Management</h4>
                <div class="hint">Sign in as <b>Admin</b> to manage accounts.</div>
            </div>
        </div>
        <div class="card-body">
            <form id="loginForm">
                <div class="grid-2">
                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" name="username" autocomplete="username" required>
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn primary" type="submit"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
                </div>
                <div class="err" id="errBox"></div>
            </form>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('loginForm');
const errBox = document.getElementById('errBox');
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  errBox.style.display = 'none';
  const payload = {
    username: document.getElementById('username').value.trim(),
    password: document.getElementById('password').value
  };
  try {
    const res = await fetch('api/login.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });
    const j = await res.json();
    if (!res.ok || j.ok === false) throw new Error(j.message || ('HTTP ' + res.status));
    window.location.href = j.redirect;
  } catch (err) {
    errBox.textContent = err.message || String(err);
    errBox.style.display = 'block';
  }
});
</script>
</body>
</html>

