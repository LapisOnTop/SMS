(function () {
	const form = document.getElementById('loginForm');
	const username = document.getElementById('username');
	const password = document.getElementById('password');
	const roleInput = document.getElementById('role');
	const togglePassword = document.getElementById('togglePassword');
	const usernameError = document.getElementById('usernameError');
	const passwordError = document.getElementById('passwordError');
	const signInBtn = document.getElementById('signInBtn');

	if (!form) {
		return;
	}

	function setError(element, message) {
		if (element) {
			element.textContent = message;
			element.classList.add('show');
		}
	}

	function clearError(element) {
		if (element) {
			element.textContent = '';
			element.classList.remove('show');
		}
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
		clearError(usernameError);
		clearError(passwordError);

		if (!username.value.trim()) {
			setError(usernameError, 'Username is required.');
			isValid = false;
		}

		if (!password.value) {
			setError(passwordError, 'Password is required.');
			isValid = false;
		}

		if (!isValid) {
			return;
		}

		const role = roleInput ? roleInput.value : 'registrar';
		const payload = {
			username: username.value.trim(),
			password: password.value,
			role: role,
		};

		if (signInBtn) {
			signInBtn.disabled = true;
			signInBtn.classList.add('is-loading');
		}

		function goTo(redirect) {
			if (!redirect) {
				window.location.reload();
				return;
			}
			if (redirect.startsWith('http://') || redirect.startsWith('https://')) {
				window.location.href = redirect;
				return;
			}
			// Server returns absolute path from web root (e.g. /SMS-SIM/...). Must prefix origin
			// so port numbers (e.g. :8080) are preserved — assigning a bare path drops the port.
			if (redirect.startsWith('/')) {
				window.location.href = window.location.origin + redirect;
				return;
			}
			window.location.href = new URL(redirect, window.location.href).href;
		}

		fetch('api/auth-login.php', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			credentials: 'same-origin',
			body: JSON.stringify(payload),
		})
			.then(async (r) => {
				const text = await r.text();
				console.log('LOGIN DEBUG - Status:', r.status, '| Response:', text);
				let data;
				try {
					data = text ? JSON.parse(text) : {};
				} catch (e) {
					throw new Error('Bad response from server (not JSON). Check PHP errors. Response was: ' + text);
				}
				return { status: r.status, data };
			})
			.then(({ status, data }) => {
				if (!data || !data.ok) {
					const msg = (data && data.message) || 'Login failed.';
					if (status === 401) {
						setError(passwordError, msg);
					} else {
						setError(usernameError, msg);
					}
					return;
				}
				goTo(data.redirect);
			})
			.catch((err) => {
				setError(usernameError, err.message || 'Could not reach the server. Try again.');
			})
			.finally(() => {
				if (signInBtn) {
					signInBtn.disabled = false;
					signInBtn.classList.remove('is-loading');
				}
			});
	});
})();
