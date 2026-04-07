(function () {
	const modal = document.getElementById('termsModal');
	const openButtons = document.querySelectorAll('[data-open-modal="true"]');
	const closeButtons = document.querySelectorAll('[data-close-modal="true"]');

	if (!modal) {
		return;
	}

	function openModal() {
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.classList.add('modal-open');
	}

	function closeModal() {
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('modal-open');
	}

	openButtons.forEach((button) => {
		button.addEventListener('click', openModal);
	});

	closeButtons.forEach((button) => {
		button.addEventListener('click', closeModal);
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && modal.classList.contains('is-open')) {
			closeModal();
		}
	});
})();
