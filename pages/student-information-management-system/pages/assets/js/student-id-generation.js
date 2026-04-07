(function () {
	'use strict';

	const cardName = document.getElementById('cardName');
	const cardId = document.getElementById('cardId');
	const cardProgram = document.getElementById('cardProgram');
	const cardYear = document.getElementById('cardYear');
	const cardContact = document.getElementById('cardContact');
	const cardExpiry = document.getElementById('cardExpiry');
	const enlargeButton = document.getElementById('enlargeButton');
	const downloadButton = document.getElementById('downloadButton');
	const previewModal = document.getElementById('previewModal');
	const closeModal = document.getElementById('closeModal');
	const modalBody = document.getElementById('modalBody');
	const idCard = document.getElementById('idCard');

	if (!idCard) {
		return;
	}

	const params = new URLSearchParams(window.location.search);
	let studentId =
		params.get('student_id') ||
		(document.body && document.body.getAttribute('data-sim-active-student-id')) ||
		null;
	if (studentId === '') {
		studentId = null;
	}

	function formatDate(dateString) {
		if (!dateString) {
			return '';
		}
		const date = new Date(dateString);
		if (Number.isNaN(date.getTime())) {
			return String(dateString);
		}
		return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
	}

	function applyStudentToCard(s) {
		if (!s) {
			return;
		}
		if (cardName) {
			cardName.textContent = s.fullName || 'Student';
		}
		if (cardId) {
			cardId.textContent = s.id || '';
		}
		const barcode = idCard.querySelector('.barcode span');
		if (barcode) {
			barcode.textContent = s.id || '';
		}
		if (cardProgram) {
			cardProgram.textContent = s.program || 'N/A';
		}
		if (cardYear) {
			cardYear.textContent = s.yearLevel || '—';
		}
		if (cardContact) {
			cardContact.textContent = s.contactNumber || 'N/A';
		}
		if (cardExpiry) {
			cardExpiry.textContent = formatDate(s.idValidity) || '—';
		}
		const fullNameInput = document.getElementById('fullName');
		const studentIdInput = document.getElementById('studentId');
		if (fullNameInput) {
			fullNameInput.value = s.fullName || '';
		}
		if (studentIdInput) {
			studentIdInput.value = s.id || '';
		}
	}

	function loadIdCard() {
		if (!studentId) {
			return;
		}
		fetch('../../api/students.php?id=' + encodeURIComponent(studentId), {
			method: 'GET',
			credentials: 'same-origin',
			headers: { Accept: 'application/json' }
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (data.ok && data.student) {
					applyStudentToCard(data.student);
				}
			})
			.catch(function () {});
	}

	function openModal() {
		const clone = idCard.cloneNode(true);
		clone.style.width = '100%';
		clone.style.maxWidth = '600px';
		clone.style.margin = '0 auto';
		modalBody.innerHTML = '';
		modalBody.appendChild(clone);
		previewModal.classList.add('open');
		previewModal.setAttribute('aria-hidden', 'false');
	}

	function closePreview() {
		previewModal.classList.remove('open');
		previewModal.setAttribute('aria-hidden', 'true');
	}

	if (enlargeButton) {
		enlargeButton.addEventListener('click', openModal);
	}

	if (downloadButton) {
		downloadButton.addEventListener('click', function () {
			window.print();
		});
	}

	if (closeModal) {
		closeModal.addEventListener('click', closePreview);
	}

	if (previewModal) {
		previewModal.addEventListener('click', function (event) {
			if (event.target === previewModal) {
				closePreview();
			}
		});
	}

	loadIdCard();
})();
