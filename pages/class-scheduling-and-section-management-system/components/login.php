<?php /* SMS — components/login.php */ ?>
<div class="login-page active" id="loginPage">
    <div class="login-bg"></div>

    <div class="login-box">
        <div class="login-logo">
            <div class="shield">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <h2>Class Scheduling &amp;<br>Section Management</h2>
            <p>School Management System — Scheduling Module</p>
        </div>

        <div class="login-form">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input class="form-control" id="loginUser" type="text"
                       placeholder="Enter username" autocomplete="username"/>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input class="form-control" id="loginPass" type="password"
                       placeholder="Enter password" autocomplete="current-password"/>
            </div>
            <button class="login-submit" onclick="doLogin()">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </div>

        <div class="login-demo">
            <p>Demo credentials — click to fill:</p>
            <div class="demo-creds">
                <div class="demo-cred" onclick="fillCred('admin','admin123')">
                    <i class="fa-solid fa-crown" style="color:#fbbf24"></i>
                    admin / admin123
                    <span style="margin-left:auto;font-size:0.72rem;color:#9ca3af">[Super Admin]</span>
                </div>
                <div class="demo-cred" onclick="fillCred('registrar','reg123')">
                    <i class="fa-solid fa-clipboard-list" style="color:#0891b2"></i>
                    registrar / reg123
                    <span style="margin-left:auto;font-size:0.72rem;color:#9ca3af">[Registrar]</span>
                </div>
                <div class="demo-cred" onclick="fillCred('dean','dean123')">
                    <i class="fa-solid fa-graduation-cap" style="color:#6366f1"></i>
                    dean / dean123
                    <span style="margin-left:auto;font-size:0.72rem;color:#9ca3af">[Dean]</span>
                </div>
            </div>
        </div>
    </div>
</div>
