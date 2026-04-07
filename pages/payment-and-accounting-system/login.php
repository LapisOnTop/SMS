<?php
	// Cashier-only login page
	// Store role in session for post-authentication use
	session_start();
	$_SESSION['selected_role'] = 'cashier';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cashier Login - Payment and Accounting</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="assets/css/cashier-login.css">
</head>
<body>
	<main class="login-shell">
		<section class="login-card">
			<div class="login-hero">
				<div class="hero-icon">
					<i class="fa-solid fa-cash-register"></i>
				</div>
				<h1>Cashier Login</h1>
				<p>Payment and Accounting System</p>
			</div>

			<!-- Role Display -->
			<div class="role-badge">
				<span class="badge-label">Logging in as:</span>
				<span class="badge-value cashier">Cashier</span>
			</div>

			<form class="login-form" id="loginForm" novalidate>
				<!-- Hidden role field -->
				<input type="hidden" name="role" id="role" value="cashier">
				
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

	<script src="assets/js/cashier-login.js"></script>
</body>
</html>
