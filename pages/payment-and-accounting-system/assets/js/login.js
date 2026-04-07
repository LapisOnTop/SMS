(function () {
	const form = document.getElementById('loginForm');
	const email = document.getElementById('email');
	const password = document.getElementById('password');
	const togglePassword = document.getElementById('togglePassword');
	const emailError = document.getElementById('emailError');
	const passwordError = document.getElementById('passwordError');

	if (!form) {
		return;
	}

	function setError(element, message) {
		if (element) {
			element.textContent = message;
		}
	}

	function clearError(element) {
		if (element) {
			element.textContent = '';
		}
	}

	function isValidEmail(value) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
	}

	togglePassword.addEventListener('click', () => {
		const isHidden = password.type === 'password';
		password.type = isHidden ? 'text' : 'password';
		togglePassword.innerHTML = isHidden
			? '<i class="fa-solid fa-eye-slash"></i>'
			: '<i class="fa-solid fa-eye"></i>';
		togglePassword.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
	});

	form.addEventListener('submit', (event) => {
		event.preventDefault();

		let isValid = true;
		clearError(emailError);
		clearError(passwordError);

		if (!email.value.trim()) {
			setError(emailError, 'Email address is required.');
			isValid = false;
		} else if (!isValidEmail(email.value.trim())) {
			setError(emailError, 'Enter a valid email address.');
			isValid = false;
		}

		if (!password.value.trim()) {
			setError(passwordError, 'Password is required.');
			isValid = false;
		}

		if (!isValid) {
			return;
		}

		// Get the role from the hidden field
		const roleField = document.getElementById('role');
		const role = roleField ? roleField.value : 'admin';

		// TODO: Authenticate user and validate role against database
		// For now, redirect to dashboard with role parameter
		// In production, this should be an API call to validate credentials
		window.location.href = 'pages/dashboard.php?role=' + encodeURIComponent(role);
	});
})();
