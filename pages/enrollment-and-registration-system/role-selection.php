<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Online Admission - Role Selection</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/role-selection.css">
</head>
<body>
	<main class="selection-shell">
		<div class="selection-card">
			<div class="selection-hero">
				<div class="hero-icon">
					<i class="fa-solid fa-graduation-cap"></i>
				</div>
				<h1>Online Admission System</h1>
				<p>Select your role to continue</p>
			</div>

			<div class="role-options">
				<a href="pages/admin/login.php" class="role-card admin">
					<div class="role-icon">
						<i class="fa-solid fa-user-tie"></i>
					</div>
					<div class="role-title">Registrar</div>
					<div class="role-description">Manage and review applications</div>
				</a>

				<a href="pages/user/landing.php?role=user" class="role-card user">
					<div class="role-icon">
						<i class="fa-solid fa-user-graduate"></i>
					</div>
					<div class="role-title">Student/Applicant</div>
					<div class="role-description">Submit your application</div>
				</a>
			</div>

			<div class="selection-footnote">
				<i class="fa-solid fa-shield-halved"></i>
				<span>Secure role selection</span>
			</div>
		</div>
	</main>
</body>
</html>
