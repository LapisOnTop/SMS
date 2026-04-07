<?php
session_start();

// Check if user is authenticated as registrar
if (!isset($_SESSION['selected_role']) || $_SESSION['selected_role'] !== 'registrar') {
	header('Location: login.php');
	exit;
}

require_once '../../../../config/database.php';

$db = sms_get_db_connection();
if (!$db) {
	header('Location: login.php');
	exit;
}

// Determine which section to display
$section = isset($_GET['section']) ? trim($_GET['section']) : 'reports';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Registrar Dashboard - Online Admission</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/dashboard.css">
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
	<!-- Header -->
	<header>
		<h1>
			<i class="fa-solid fa-graduation-cap"></i>
			Registrar Dashboard
		</h1>
		<div class="header-actions">
			<div class="role-badge">
				<i class="fa-solid fa-user-tie"></i>
				<span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Registrar'); ?></span>
			</div>
			<a href="login.php?logout=1" class="logout-btn">
				<i class="fa-solid fa-sign-out-alt"></i>
				Logout
			</a>
		</div>
	</header>

	<div class="container">
		<!-- Sidebar Navigation -->
		<aside class="sidebar">
			<div class="sidebar-header">
				<h2>Menu</h2>
			</div>

			<nav class="sidebar-nav">
				<a href="dashboard.php?section=reports" class="nav-item <?php echo $section === 'reports' ? 'active' : ''; ?>">
					<i class="fa-solid fa-chart-bar"></i>
					<span>Summary Reports</span>
				</a>

				<a href="dashboard.php?section=validation" class="nav-item <?php echo $section === 'validation' ? 'active' : ''; ?>">
					<i class="fa-solid fa-clipboard-check"></i>
					<span>Validate Admissions</span>
				</a>

				<a href="dashboard.php?section=enrollment-status" class="nav-item <?php echo $section === 'enrollment-status' ? 'active' : ''; ?>">
					<i class="fa-solid fa-envelope"></i>
					<span>Enrollment Status</span>
				</a>
			</nav>
		</aside>

		<!-- Main Content - Load Section Files -->
		<main>
			<?php
			// Load the appropriate section file based on the section parameter
			switch ($section) {
				case 'validation':
					include 'validation-and-approval.php';
					break;
				case 'enrollment-status':
					include 'enrollment-status.php';
					break;
				case 'reports':
				default:
					include 'summary-report.php';
					break;
			}
			?>
		</main>
	</div>

	<script src="../../assets/js/dashboard.js"></script>
</body>
</html>
