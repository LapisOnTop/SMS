(function() {
	const form = document.getElementById('loginForm');
	const usernameInput = document.getElementById('username');
	const passwordInput = document.getElementById('password');
	const togglePasswordBtn = document.getElementById('togglePassword');
	const usernameError = document.getElementById('usernameError');
	const passwordError = document.getElementById('passwordError');
	const signInBtn = document.getElementById('signInBtn');

	// Toggle password visibility
	togglePasswordBtn.addEventListener('click', function(e) {
		e.preventDefault();
		const type = passwordInput.type === 'password' ? 'text' : 'password';
		passwordInput.type = type;
		this.innerHTML = type === 'password' ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';
	});

	// Clear errors on input
	usernameInput.addEventListener('focus', function() {
		this.parentElement.classList.remove('error');
		usernameError.classList.remove('show');
	});

	passwordInput.addEventListener('focus', function() {
		this.parentElement.classList.remove('error');
		passwordError.classList.remove('show');
	});

	// Form submission
	form.addEventListener('submit', function(e) {
		e.preventDefault();

		// Clear previous errors
		usernameError.textContent = '';
		passwordError.textContent = '';
		usernameInput.parentElement.classList.remove('error');
		passwordInput.parentElement.classList.remove('error');

		// Basic validation
		let isValid = true;

		if (!usernameInput.value.trim()) {
			usernameInput.parentElement.classList.add('error');
			usernameError.textContent = 'Username is required';
			usernameError.classList.add('show');
			isValid = false;
		}

		if (!passwordInput.value.trim()) {
			passwordInput.parentElement.classList.add('error');
			passwordError.textContent = 'Password is required';
			passwordError.classList.add('show');
			isValid = false;
		}

		if (!isValid) return;

		// Disable submit button during submission
		signInBtn.disabled = true;
		signInBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Signing In...';

		// Send login request to API
		fetch('api/admin-login.php', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify({
				username: usernameInput.value.trim(),
				password: passwordInput.value
			})
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				// Redirect to admin dashboard on successful login
				window.location.href = 'pages/admin/analytics.php';
			} else {
				// Show error under username field
				usernameInput.parentElement.classList.add('error');
				usernameError.textContent = data.message || 'Invalid credentials. Please try again.';
				usernameError.classList.add('show');
			}
		})
		.catch(error => {
			console.error('Error:', error);
			usernameInput.parentElement.classList.add('error');
			usernameError.textContent = 'An error occurred. Please try again.';
			usernameError.classList.add('show');
		})
		.finally(() => {
			signInBtn.disabled = false;
			signInBtn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Sign In';
		});
	});
})();
