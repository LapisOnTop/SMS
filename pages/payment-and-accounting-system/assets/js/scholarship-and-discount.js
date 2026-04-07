(function () {
	const studentSearch = document.getElementById('studentSearch');
	const searchButton = document.getElementById('searchButton');
	const studentId = document.getElementById('studentId');
	const studentName = document.getElementById('studentName');
	const discountCategory = document.getElementById('discountCategory');
	const discountType = document.getElementById('discountType');
	const discountValue = document.getElementById('discountValue');
	const originalAssessment = document.getElementById('originalAssessment');
	const discountAmount = document.getElementById('discountAmount');
	const netAssessment = document.getElementById('netAssessment');
	const applyButton = document.getElementById('applyButton');
	const saveButton = document.getElementById('saveButton');
	const statusText = document.getElementById('statusText');
	const hasPenalty = document.getElementById('hasPenalty');
	const penaltyAmount = document.getElementById('penaltyAmount');
	const totalWithPenalty = document.getElementById('totalWithPenalty');

	let penaltyValue = 500; // Default penalty value

	if (!searchButton) {
		return;
	}

	const typeDefaults = {
		'Academic Scholarship': 50,
		'Athletic Scholarship': 35,
		'Sibling Discount': 10,
		'Loyalty Discount': 5
	};

	let currentStudent = null;
	let referenceNumber = 'SMS-' + Date.now().toString().slice(-8);

	function money(value) {
		return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function parseDiscountValue() {
		const raw = String(discountValue.value || '').replace('%', '').replace(/,/g, '').trim();
		const parsed = parseFloat(raw);
		return Number.isFinite(parsed) ? Math.max(parsed, 0) : 0;
	}

	function computeDiscount() {
		if (!currentStudent || !currentStudent.assessment) {
			return;
		}

		const base = currentStudent.assessment;
		const value = parseDiscountValue();
		let discount = 0;

		if (discountCategory.value === 'percentage') {
			discount = base * (value / 100);
		} else {
			discount = value;
		}

		discount = Math.min(discount, base);
		const net = base - discount;

		originalAssessment.textContent = money(base);
		discountAmount.textContent = money(discount);
		netAssessment.textContent = money(net);

		// Set total with penalty input
		if (hasPenalty && hasPenalty.checked) {
			const total = net + penaltyValue;
			totalWithPenalty.value = total.toFixed(2);
		} else {
			totalWithPenalty.value = net.toFixed(2);
		}
	}

	function loadStudent() {
		const key = String(studentSearch.value || '').trim().toUpperCase();
		if (!key) {
			currentStudent = null;
			studentId.value = '';
			studentName.value = '';
			originalAssessment.textContent = '₱0.00';
			discountAmount.textContent = '₱0.00';
			netAssessment.textContent = '₱0.00';
			totalWithPenalty.value = '0';
			hasPenalty.checked = false;
			totalWithPenalty.disabled = true;
			totalWithPenalty.style.opacity = '0.5';
			totalWithPenalty.style.cursor = 'not-allowed';
			return;
		}

		// TODO: Fetch student data from database using key
		currentStudent = null;
		studentId.value = '';
		studentName.value = '';
		originalAssessment.textContent = '₱0.00';
		discountAmount.textContent = '₱0.00';
		netAssessment.textContent = '₱0.00';
		totalWithPenalty.textContent = '₱0.00';
		statusText.textContent = 'Ready to process scholarship or discount.';
	}

	function applyTypeDefault() {
		const selected = discountType.value;
		if (!selected || !currentStudent || !currentStudent.assessment) {
			return;
		}

		const defaultValue = typeDefaults[selected] || 0;
		discountValue.value = discountCategory.value === 'percentage' ? String(defaultValue) + '%' : String(Math.round((currentStudent.assessment * defaultValue) / 100));
		computeDiscount();
	}

	// Penalty toggle functionality
	if (hasPenalty) {
		hasPenalty.addEventListener('change', function () {
			if (this.checked) {
				totalWithPenalty.disabled = false;
				totalWithPenalty.style.opacity = '1';
				totalWithPenalty.style.cursor = 'text';
			} else {
				totalWithPenalty.disabled = true;
				totalWithPenalty.style.opacity = '0.5';
				totalWithPenalty.style.cursor = 'not-allowed';
			}
			computeDiscount();
		});
		// Initialize with disabled state
		totalWithPenalty.disabled = true;
		totalWithPenalty.style.opacity = '0.5';
		totalWithPenalty.style.cursor = 'not-allowed';
	}

	searchButton.addEventListener('click', loadStudent);
	studentSearch.addEventListener('keydown', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			loadStudent();
		}
	});

	discountCategory.addEventListener('change', function () {
		applyTypeDefault();
	});

	discountType.addEventListener('change', applyTypeDefault);
	discountValue.addEventListener('input', computeDiscount);

	applyButton.addEventListener('click', function () {
		computeDiscount();
		statusText.textContent = 'Discount applied successfully.';
	});

	saveButton.addEventListener('click', function () {
		referenceNumber = 'SMS-' + Date.now().toString().slice(-8);
		statusText.textContent = 'Record saved with reference: ' + referenceNumber;
	});
})();