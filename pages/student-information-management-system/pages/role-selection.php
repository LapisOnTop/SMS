<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Student Information Management - Role Selection</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/role-selection.css">
</head>
<body>
	<button class="back-button" id="backButton" title="Go back">
		<i class="fa-solid fa-arrow-left"></i>
	</button>

	<main class="selection-shell">
		<div class="selection-card">
			<div class="selection-hero">
				<div class="hero-icon">
					<i class="fa-solid fa-user-graduate"></i>
				</div>
				<h1>Student Information Management System</h1>
				<p>Select your role to continue</p>
			</div>

			<div class="role-options">
				<a href="login.php?role=registrar" class="role-card registrar">
					<div class="role-icon">
						<i class="fa-solid fa-user-tie"></i>
					</div>
					<div class="role-title">Registrar</div>
					<div class="role-description">Login to manage student information</div>
				</a>

				<a href="login.php?role=student" class="role-card user">
					<div class="role-icon">
						<i class="fa-solid fa-user"></i>
					</div>
					<div class="role-title">Student</div>
					<div class="role-description">View profile, records, and enrollment</div>
				</a>
			</div>

			<div class="selection-footnote">
				<i class="fa-solid fa-shield-halved"></i>
				<span>Secure role selection</span>
			</div>
		</div>
	</main>

	<script src="assets/js/role-selection.js"></script>
</body>
</html>
