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
	<title>Students List</title>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/student-profile.css">
	<link rel="stylesheet" href="../../assets/css/student-list.css">
</head>
<body>

    <nav class="sidebar">
        <div class="brand">
            Student Information<br>Management
        </div>

        <a href="student-profile-registration.php" class="menu-item">
            <i class="fa-solid fa-id-card"></i> Student Profile<br>Registration
        </a>
        <a href="student-tracking.php" class="menu-item">
            <i class="fa-solid fa-chart-line"></i> Student Tracking
        </a>
        <a href="student-list.php" class="menu-item active">
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
			Registration / Students List
		</div>

		<h1 class="page-title">Students List</h1>
		<p class="subtitle">View students from the SIM repository.</p>

		<section class="search-box" style="margin-bottom:12px;">
			<i class="fa-solid fa-magnifying-glass"></i>
			<input type="search" id="studentSearch" placeholder="Search by name or student ID..." autocomplete="off">
		</section>
		<section class="table-panel">
			<div class="table-wrap">
				<table>
					<thead>
						<tr>
							<th>Student</th>
							<th>Student ID</th>
							<th>Program</th>
							<th>Year</th>
							<th>Status</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="studentsBody"></tbody>
				</table>
			</div>
		</section>
	</main>

	<script src="../../assets/js/student-list.js"></script>
</body>
</html>
