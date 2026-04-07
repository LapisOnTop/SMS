<?php /* SMS — includes/sidebar.php */ ?>
<nav class="sidebar" id="sidebar">

    <div class="brand">
        Class Scheduling &amp;<br>Section Management
    </div>

    <div class="menu-category">Overview</div>
    <a class="menu-item" onclick="showPage('dashboard', this)">
        <i class="fa-solid fa-gauge-high"></i> Dashboard
    </a>

    <div class="menu-category">Scheduling</div>
    <a class="menu-item" onclick="showPage('sections', this)">
        <i class="fa-solid fa-folder-open"></i> Sections
    </a>
    <a class="menu-item" onclick="showPage('timetable', this)">
        <i class="fa-solid fa-calendar-days"></i> Timetable
    </a>

    <div class="menu-category">Management</div>
    <a class="menu-item" onclick="showPage('rooms', this)">
        <i class="fa-solid fa-building"></i> Room Assignment
    </a>
    <a class="menu-item" onclick="showPage('faculty', this)">
        <i class="fa-solid fa-chalkboard-teacher"></i> Faculty Loads
    </a>
    <a class="menu-item" onclick="showPage('conflicts', this)">
        <i class="fa-solid fa-triangle-exclamation"></i> Conflicts
        <span class="badge-count" id="conflictBadge"
              style="margin-left:auto;background:#ef4444;color:#fff;font-size:0.65rem;
                     font-weight:700;padding:2px 7px;border-radius:20px;display:none;">0</span>
    </a>

    <div class="menu-category">System</div>
    <a class="menu-item" onclick="showPage('terms', this)">
        <i class="fa-solid fa-calendar-check"></i> Academic Terms
    </a>

</nav>
