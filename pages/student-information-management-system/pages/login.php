<?php
session_start();

$roleKey = isset($_GET['role']) ? strtolower(preg_replace('/[^a-z_]/', '', (string) $_GET['role'])) : 'registrar';
if ($roleKey !== 'student' && $roleKey !== 'registrar') {
	$roleKey = 'registrar';
}

$isStudent = $roleKey === 'student';
$_SESSION['selected_role'] = $roleKey;

$pageTitle = $isStudent ? 'Student Login' : 'Registrar Login';
$badgeLabel = $isStudent ? 'Student' : 'Registrar';
$badgeClass = $isStudent ? 'student' : 'admin';
$heroIcon = $isStudent ? 'fa-user-graduate' : 'fa-user-tie';
$formRole = $isStudent ? 'student' : 'registrar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Online Admission — <?php echo htmlspecialchars($pageTitle); ?></title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="assets/css/login.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
	<main class="login-shell">
		<section class="login-card">
			<div class="login-hero">
				<div class="hero-icon">
					<i class="fa-solid <?php echo $heroIcon; ?>"></i>
				</div>
				<h1><?php echo htmlspecialchars($pageTitle); ?></h1>
				<p>Online Admission System</p>
			</div>

			<div class="role-badge">
				<span class="badge-label">Logging in as:</span>
				<span class="badge-value <?php echo htmlspecialchars($badgeClass); ?>"><?php echo htmlspecialchars($badgeLabel); ?></span>
				<a href="role-selection.php" class="change-role-btn" title="Select a different role">
					<i class="fa-solid fa-arrow-right-arrow-left"></i>
				</a>
			</div>

			<form class="login-form" id="loginForm" novalidate>
				<input type="hidden" name="role" id="role" value="<?php echo htmlspecialchars($formRole); ?>">

				<div class="field">
					<label for="username">Username</label>
					<input id="username" name="username" type="text" placeholder="Enter your username" autocomplete="username" required>
					<p class="field-error" id="usernameError"></p>
				</div>

				<div class="field">
					<label for="password">Password</label>
					<div class="password-row">
						<input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required>
						<button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
							<i class="fa-solid fa-eye"></i>
						</button>
					</div>
					<p class="field-error" id="passwordError"></p>
				</div>

				<button type="submit" class="btn-signin" id="signInBtn">
					<i class="fa-solid fa-right-to-bracket"></i>
					Sign In
				</button>

				<div class="login-footnote">
					<i class="fa-solid fa-shield-halved"></i>
					<span>Secure login</span>
				</div>
			</form>
		</section>
	</main>

	<script src="assets/js/login.js"></script>
</body>
</html>
