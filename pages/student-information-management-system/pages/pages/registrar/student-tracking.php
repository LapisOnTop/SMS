<?php
session_start();

if (!isset($_SESSION['registrar_id'])) {
	header('Location: ../../login.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student Status Tracking</title>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/student-profile.css">
	<link rel="stylesheet" href="../../assets/css/student-tracking.css">
</head>
<body>

    <nav class="sidebar">
        <div class="brand">
            Student Information<br>Management
        </div>

        <a href="student-profile-registration.php" class="menu-item">
            <i class="fa-solid fa-id-card"></i> Student Profile<br>Registration
        </a>
        <a href="student-tracking.php" class="menu-item active">
            <i class="fa-solid fa-chart-line"></i> Student Tracking
        </a>
        <a href="student-list.php" class="menu-item">
            <i class="fa-solid fa-list"></i> Student List
        </a>

        <div class="sidebar-footer" style="margin-top: auto; padding: 20px;">
            <a href="../../api/logout.php" class="support-btn" style="text-decoration:none; color:var(--text-dark);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>

	<main class="main-content">
		<div class="breadcrumbs">
			Registration / Student Status Tracking
		</div>

		<h1 class="page-title">Student Status Tracking</h1>
		<p class="subtitle">Analytics dashboard, then search the list to set each student's status.</p>

		<section class="stats-grid">
			<article class="stat-card purple">
				<p class="stat-label"><i class="fa-solid fa-users"></i> Total Enrolled</p>
				<h3 id="enrolledCount">0</h3>
				<span>All students in the system</span>
			</article>
			<article class="stat-card green">
				<p class="stat-label"><i class="fa-solid fa-circle-check"></i> Active</p>
				<h3 id="activeCount">0</h3>
				<span>Currently enrolled &amp; active</span>
			</article>
			<article class="stat-card gray">
				<p class="stat-label"><i class="fa-solid fa-circle-info"></i> Inactive</p>
				<h3 id="inactiveCount">0</h3>
				<span>Dropped, graduated, on leave, etc.</span>
			</article>
		</section>

		<section class="analytics-grid">
			<article class="panel chart-panel">
				<h3>Enrollment mix</h3>
				<p>Pie chart: active vs inactive students</p>
				<div class="pie-wrap">
					<div class="pie-chart" id="pieChart"></div>
				</div>
				<div class="legend">
					<span><i class="dot active"></i>Active (enrolled)</span>
					<span><i class="dot inactive"></i>Inactive</span>
				</div>
			</article>

			<article class="panel table-mini">
				<h3>Count by status</h3>
				<p>How records are labeled in the database</p>
				<table>
					<thead>
						<tr><th>Status</th><th>Students</th></tr>
					</thead>
					<tbody id="statusCountBody"></tbody>
				</table>
			</article>
		</section>

		<section class="students-panel">
			<h4>Students - Search &amp; Set Status</h4>
			<p>Search by name or student ID, then pick status from the list.</p>
			<div class="search-wrap">
				<i class="fa-solid fa-magnifying-glass"></i>
				<input type="text" id="studentSearch" placeholder="Search by name or student ID...">
			</div>

			<div class="table-wrap">
				<table>
					<thead>
						<tr>
							<th>Student</th>
							<th>Student ID</th>
							<th>Program</th>
							<th>Year</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody id="studentsBody"></tbody>
				</table>
			</div>
		</section>
	</main>

	<script src="../../assets/js/student-tracking.js"></script>
</body>
</html>
