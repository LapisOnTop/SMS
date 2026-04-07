<?php
	session_start();
	// Set role for CMS admin
	$_SESSION['cms_role'] = 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Curriculum Management System - Admin Login</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<style>
		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
			font-family: 'Poppins', sans-serif;
		}

		body {
			min-height: 100vh;
			background: linear-gradient(135deg, #e0e0e0, #cfcfcf);
			display: flex;
			align-items: center;
			justify-content: center;
			color: #333;
		}

		.login-shell {
			width: 100%;
			padding: 20px;
		}

		.login-card {
			background: white;
			border-radius: 18px;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
			overflow: hidden;
			max-width: 480px;
			margin: 0 auto;
			border: 1px solid rgba(0, 0, 0, 0.08);
		}

		.login-hero {
			padding: 40px 30px;
			text-align: center;
			background: linear-gradient(180deg, #f5f5f5, #efefef);
			border-bottom: 1px solid rgba(0, 0, 0, 0.08);
		}

		.hero-icon {
			width: 70px;
			height: 70px;
			margin: 0 auto 20px;
			border-radius: 18px;
			display: flex;
			align-items: center;
			justify-content: center;
			background: linear-gradient(135deg, #1e2532, #1a1f2a);
			color: white;
			font-size: 2rem;
			box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
		}

		.login-hero h1 {
			font-size: 1.8rem;
			margin-bottom: 10px;
			color: #1e2532;
			font-weight: 700;
		}

		.login-hero p {
			color: #666;
			font-size: 0.95rem;
			margin: 0;
		}

		.role-badge {
			padding: 16px 30px;
			background: #f4f7ff;
			border-bottom: 1px solid #e2e8f5;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 12px;
		}

		.badge-label {
			font-size: 0.85rem;
			color: #666;
			font-weight: 500;
		}

		.badge-value {
			display: inline-block;
			padding: 6px 12px;
			border-radius: 6px;
			font-weight: 700;
			font-size: 0.9rem;
			background: rgba(63, 105, 255, 0.1);
			color: #3f69ff;
		}

		.login-form {
			padding: 40px 30px;
		}

		.field {
			margin-bottom: 24px;
		}

		.field label {
			display: block;
			margin-bottom: 8px;
			font-weight: 600;
			font-size: 0.95rem;
			color: #333;
		}

		.field input {
			width: 100%;
			padding: 12px 14px;
			border: 1px solid #dce3f2;
			border-radius: 8px;
			font-size: 0.95rem;
			font-family: 'Poppins', sans-serif;
			transition: all 0.2s ease;
		}

		.field input:focus {
			outline: none;
			border-color: #3f69ff;
			box-shadow: 0 0 0 3px rgba(63, 105, 255, 0.1);
		}

		.password-row {
			position: relative;
			display: flex;
			align-items: center;
		}

		.password-row input {
			width: 100%;
		}

		.toggle-password {
			position: absolute;
			right: 14px;
			background: none;
			border: none;
			color: #999;
			cursor: pointer;
			padding: 0;
			font-size: 0.95rem;
			transition: color 0.2s ease;
		}

		.toggle-password:hover {
			color: #3f69ff;
		}

		.field-error {
			margin-top: 6px;
			font-size: 0.8rem;
			color: #ef4444;
			display: none;
		}

		.field-error.show {
			display: block;
		}

		.field.error input {
			border-color: #ef4444;
			background-color: rgba(239, 68, 68, 0.05);
		}

		.btn-signin {
			width: 100%;
			padding: 14px;
			background: #3f69ff;
			color: white;
			border: none;
			border-radius: 8px;
			font-size: 1rem;
			font-weight: 700;
			cursor: pointer;
			margin-top: 10px;
			transition: all 0.2s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
		}

		.btn-signin:hover {
			background: #2d5adb;
			transform: translateY(-2px);
			box-shadow: 0 8px 24px rgba(63, 105, 255, 0.3);
		}

		.btn-signin:disabled {
			opacity: 0.6;
			cursor: not-allowed;
		}

		.login-footnote {
			padding: 20px 30px;
			text-align: center;
			background: rgba(0, 0, 0, 0.02);
			border-top: 1px solid rgba(0, 0, 0, 0.06);
			font-size: 0.85rem;
			color: #666;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		@media (max-width: 480px) {
			.login-card {
				border-radius: 16px;
			}

			.login-hero {
				padding: 32px 24px;
			}

			.login-form {
				padding: 30px 24px;
			}

			.login-footnote {
				padding: 16px 24px;
			}

			.login-hero h1 {
				font-size: 1.6rem;
			}
		}
	</style>
</head>
<body>
	<main class="login-shell">
		<section class="login-card">
			<div class="login-hero">
				<div class="hero-icon">
					<i class="fa-solid fa-book"></i>
				</div>
				<h1>Curriculum Management</h1>
				<p>Administrator Login</p>
			</div>

			<!-- Role Display -->
			<div class="role-badge">
				<span class="badge-label">Logging in as:</span>
				<span class="badge-value admin">Administrator</span>
			</div>

			<form class="login-form" id="loginForm" novalidate>
				<div class="field">
					<label for="email">Email Address</label>
					<input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required>
					<p class="field-error" id="emailError"></p>
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

				<button type="submit" class="btn-signin">
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

	<script>
		(function() {
			'use strict';

			const loginForm = document.getElementById('loginForm');
			const emailInput = document.getElementById('email');
			const passwordInput = document.getElementById('password');
			const togglePasswordBtn = document.getElementById('togglePassword');
			const emailError = document.getElementById('emailError');
			const passwordError = document.getElementById('passwordError');

			// Toggle password visibility
			togglePasswordBtn.addEventListener('click', function(e) {
				e.preventDefault();
				const type = passwordInput.type === 'password' ? 'text' : 'password';
				passwordInput.type = type;
				
				// Toggle icon
				this.innerHTML = type === 'password' 
					? '<i class="fa-solid fa-eye"></i>' 
					: '<i class="fa-solid fa-eye-slash"></i>';
			});

			// Form validation and submission
			loginForm.addEventListener('submit', function(e) {
				e.preventDefault();

				// Reset errors
				emailError.classList.remove('show');
				passwordError.classList.remove('show');
				emailInput.parentElement.parentElement.classList.remove('error');
				passwordInput.parentElement.parentElement.classList.remove('error');

				let isValid = true;

				// Validate email
				if (!emailInput.value.trim()) {
					emailError.textContent = 'Email address is required';
					emailError.classList.add('show');
					emailInput.parentElement.parentElement.classList.add('error');
					isValid = false;
				} else if (!isValidEmail(emailInput.value)) {
					emailError.textContent = 'Please enter a valid email address';
					emailError.classList.add('show');
					emailInput.parentElement.parentElement.classList.add('error');
					isValid = false;
				}

				// Validate password
				if (!passwordInput.value.trim()) {
					passwordError.textContent = 'Password is required';
					passwordError.classList.add('show');
					passwordInput.parentElement.parentElement.classList.add('error');
					isValid = false;
				}

				if (isValid) {
					// TODO: Send login request to API endpoint
					// POST /api/login.php
					console.log('Login attempt:', {
						email: emailInput.value,
						password: passwordInput.value
					});

					// Simulate successful login
					showMessage('success', 'Login successful! Redirecting...');
					setTimeout(() => {
						window.location.href = 'pages/program-setup.php';
					}, 1500);
				}
			});

			// Real-time email validation
			emailInput.addEventListener('blur', function() {
				if (this.value && !isValidEmail(this.value)) {
					emailError.textContent = 'Please enter a valid email address';
					emailError.classList.add('show');
					this.parentElement.parentElement.classList.add('error');
				}
			});

			emailInput.addEventListener('focus', function() {
				emailError.classList.remove('show');
				this.parentElement.parentElement.classList.remove('error');
			});

			// Email validation helper
			function isValidEmail(email) {
				const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				return emailRegex.test(email);
			}

			// Show message toast
			function showMessage(type, message) {
				const toast = document.createElement('div');
				toast.style.cssText = `
					position: fixed;
					top: 20px;
					right: 20px;
					padding: 16px 20px;
					background: ${type === 'success' ? '#10b981' : '#ef4444'};
					color: white;
					border-radius: 8px;
					box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
					font-weight: 500;
					font-size: 14px;
					z-index: 2000;
					animation: slideInRight 0.3s ease;
					font-family: 'Poppins', sans-serif;
				`;
				toast.textContent = message;
				document.body.appendChild(toast);

				// Add animation styles
				if (!document.getElementById('toastStyles')) {
					const style = document.createElement('style');
					style.id = 'toastStyles';
					style.textContent = `
						@keyframes slideInRight {
							from { transform: translateX(400px); opacity: 0; }
							to { transform: translateX(0); opacity: 1; }
						}
						@keyframes slideOutRight {
							from { transform: translateX(0); opacity: 1; }
							to { transform: translateX(400px); opacity: 0; }
						}
					`;
					document.head.appendChild(style);
				}

				setTimeout(() => {
					toast.style.animation = 'slideOutRight 0.3s ease';
					setTimeout(() => toast.remove(), 300);
				}, 3000);
			}
		})();
	</script>
</body>
</html>
