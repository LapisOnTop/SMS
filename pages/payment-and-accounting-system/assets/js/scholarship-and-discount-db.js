(function () {
	const studentSearch = document.getElementById('studentSearch');
	const searchButton = document.getElementById('searchButton');
	const studentId = document.getElementById('studentId');
	const studentName = document.getElementById('studentName');
	const discountCategory = document.getElementById('discountCategory');
	const discountType = document.getElementById('discountType');
	const discountValue = document.getElementById('discountValue');
	const grantName = document.getElementById('grantName');
	const termId = document.getElementById('termId');
	const semester = document.getElementById('semester');
	const validityPeriod = document.getElementById('validityPeriod');
	const supportDocs = document.getElementById('supportDocs');
	const originalAssessment = document.getElementById('originalAssessment');
	const discountAmount = document.getElementById('discountAmount');
	const netAssessment = document.getElementById('netAssessment');
	const applyButton = document.getElementById('applyButton');
	const saveButton = document.getElementById('saveButton');
	const statusText = document.getElementById('statusText');
	const hasPenalty = document.getElementById('hasPenalty');
	const totalWithPenalty = document.getElementById('totalWithPenalty');

	let penaltyValue = 500;

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
	const searchEndpoint = '../../pages/cashier/scholarship-and-discount.php?action=search_student';
	const applyEndpoint = '../../pages/cashier/scholarship-and-discount.php';

	function money(value) {
		return new Intl.NumberFormat('en-PH', {
			minimumFractionDigits: 2,
			maximumFractionDigits: 2
		}).format(Number(value || 0));
	}

	function parseDiscountValue() {
		const raw = String(discountValue.value || '').replace('%', '').replace(/,/g, '').trim();
		const parsed = parseFloat(raw);
		return Number.isFinite(parsed) ? Math.max(parsed, 0) : 0;
	}

	function syncTermFields() {
		if (!termId) {
			return;
		}

		const selectedOption = termId.options[termId.selectedIndex];
		if (!selectedOption || !selectedOption.value) {
			if (semester) {
				semester.value = '';
			}
			if (validityPeriod) {
				validityPeriod.value = '';
			}
			return;
		}

		if (semester) {
			semester.value = selectedOption.dataset.semesterLabel || '';
		}

		if (validityPeriod) {
			validityPeriod.value = selectedOption.dataset.schoolYear || '';
		}
	}

	function getComputedValues() {
		if (!currentStudent || !currentStudent.assessment) {
			return {
				original: 0,
				discount: 0,
				net: 0,
				penalty: 500
			};
		}

		const base = currentStudent.assessment;
		const value = parseDiscountValue();
		let discount = 0;

		if (discountValue.value.trim()) {
			if (discountCategory.value === 'percentage') {
				discount = base * (value / 100);
			} else {
				discount = value;
			}
		} else {
			discount = Number(currentStudent.discountAmount || 0);
		}

		discount = Math.min(Math.max(discount, 0), base);
		const net = Math.max(base - discount, 0);
		const penalty = Math.max(parseFloat(totalWithPenalty.value || penaltyValue) - net, 0);

		return {
			original: base,
			discount: discount,
			net: net,
			penalty: hasPenalty && hasPenalty.checked ? penalty : penaltyValue
		};
	}

	function computeDiscount() {
		if (!currentStudent || !currentStudent.assessment) {
			return;
		}

		const base = currentStudent.assessment;
		const value = parseDiscountValue();

		if (!discountValue.value.trim()) {
			originalAssessment.textContent = money(currentStudent.originalAssessment ?? base);
			discountAmount.textContent = money(currentStudent.discountAmount ?? 0);
			netAssessment.textContent = money(currentStudent.netAssessment ?? base);

			const savedNet = Number(currentStudent.netAssessment ?? base);
			if (hasPenalty && hasPenalty.checked) {
				totalWithPenalty.value = (savedNet + penaltyValue).toFixed(2);
			} else {
				totalWithPenalty.value = savedNet.toFixed(2);
			}
			return;
		}

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

		if (hasPenalty && hasPenalty.checked) {
			totalWithPenalty.value = (net + penaltyValue).toFixed(2);
		} else {
			totalWithPenalty.value = net.toFixed(2);
		}
	}

	function resetStudentState() {
		currentStudent = null;
		studentId.value = '';
		studentName.value = '';
		originalAssessment.textContent = money(0);
		discountAmount.textContent = money(0);
		netAssessment.textContent = money(0);
		totalWithPenalty.value = '0';
		if (hasPenalty) {
			hasPenalty.checked = false;
		}
		totalWithPenalty.disabled = true;
		totalWithPenalty.style.opacity = '0.5';
		totalWithPenalty.style.cursor = 'not-allowed';
	}

	function resetDiscountForm() {
		resetStudentState();
		studentSearch.value = '';
		discountType.value = '';
		grantName.value = '';
		discountCategory.value = 'percentage';
		discountValue.value = '';
		if (supportDocs) {
			supportDocs.value = '';
		}
		if (termId) {
			termId.selectedIndex = termId.options.length > 1 ? 1 : 0;
		}
		syncTermFields();
		statusText.textContent = 'Ready to process scholarship or discount.';
	}

	async function loadStudent() {
		const key = String(studentSearch.value || '').trim();
		if (!key) {
			resetStudentState();
			statusText.textContent = 'Enter a student number to search.';
			return;
		}

		searchButton.disabled = true;
		statusText.textContent = 'Searching student...';

		try {
			const response = await fetch(`${searchEndpoint}&student_number=${encodeURIComponent(key)}`, {
				headers: {
					Accept: 'application/json'
				}
			});
			const data = await response.json();

			if (!response.ok || !data.success || !data.student) {
				resetStudentState();
				statusText.textContent = data && data.message ? data.message : 'Student not found.';
				return;
			}

			currentStudent = {
				id: data.student.student_id,
				studentNumber: data.student.student_number,
				name: data.student.student_name,
				assessment: Number(data.student.assessment || 0),
				originalAssessment: Number(data.student.original_assessment || data.student.assessment || 0),
				discountAmount: Number(data.student.discount_amount || 0),
				netAssessment: Number(data.student.net_assessment || data.student.assessment || 0)
			};

			studentId.value = currentStudent.studentNumber;
			studentName.value = currentStudent.name;
			statusText.textContent = 'Student loaded successfully.';
			computeDiscount();
		} catch (error) {
			resetStudentState();
			statusText.textContent = 'Unable to load student right now.';
		} finally {
			searchButton.disabled = false;
		}
	}

	function applyTypeDefault() {
		const selected = discountType.value;
		if (!selected || !currentStudent || !currentStudent.assessment) {
			return;
		}

		const defaultValue = typeDefaults[selected] || 0;
		discountValue.value = discountCategory.value === 'percentage'
			? `${defaultValue}%`
			: String(Math.round((currentStudent.assessment * defaultValue) / 100));
		computeDiscount();
	}

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
	}

	totalWithPenalty.disabled = true;
	totalWithPenalty.style.opacity = '0.5';
	totalWithPenalty.style.cursor = 'not-allowed';

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

	if (termId) {
		termId.addEventListener('change', syncTermFields);
	}

	discountType.addEventListener('change', applyTypeDefault);
	discountValue.addEventListener('input', computeDiscount);
	syncTermFields();

	applyButton.addEventListener('click', async function () {
		if (!currentStudent || !currentStudent.id) {
			statusText.textContent = 'Search and load a student first.';
			return;
		}

		if (!discountType.value) {
			statusText.textContent = 'Select a discount type first.';
			return;
		}

		if (!discountValue.value.trim()) {
			statusText.textContent = 'Enter a discount value first.';
			return;
		}

		if (!termId || !termId.value) {
			statusText.textContent = 'Select a valid academic term first.';
			return;
		}

		const totals = getComputedValues();
		const formData = new FormData();
		formData.append('action', 'apply_discount');
		formData.append('student_id', currentStudent.id);
		formData.append('discount_type', discountType.value);
		formData.append('grant_name', grantName ? grantName.value.trim() : '');
		formData.append('discount_category', discountCategory.value);
		formData.append('discount_value', parseDiscountValue());
		formData.append('term_id', termId.value);
		formData.append('original_assessment', totals.original.toFixed(2));
		formData.append('discount_amount', totals.discount.toFixed(2));
		formData.append('net_assessment', totals.net.toFixed(2));
		formData.append('has_penalty', hasPenalty && hasPenalty.checked ? '1' : '0');
		formData.append('penalty_amount', totals.penalty.toFixed(2));

		if (supportDocs && supportDocs.files && supportDocs.files[0]) {
			formData.append('support_docs', supportDocs.files[0]);
		}

		applyButton.disabled = true;
		statusText.textContent = 'Applying discount...';

		try {
			const response = await fetch(applyEndpoint, {
				method: 'POST',
				body: formData
			});
			const data = await response.json();

			if (!response.ok || !data.success) {
				statusText.textContent = data && data.message ? data.message : 'Failed to apply discount.';
				return;
			}

			currentStudent.originalAssessment = Number(data.record.original_assessment || totals.original);
			currentStudent.discountAmount = Number(data.record.discount_amount || totals.discount);
			currentStudent.netAssessment = Number(data.record.net_assessment || totals.net);
			computeDiscount();
			alert(data.message || 'Discount applied successfully.');
			resetDiscountForm();
		} catch (error) {
			statusText.textContent = 'Unable to save discount right now.';
		} finally {
			applyButton.disabled = false;
		}
	});

	saveButton.addEventListener('click', function () {
		referenceNumber = 'SMS-' + Date.now().toString().slice(-8);
		statusText.textContent = 'Record saved with reference: ' + referenceNumber;
	});
})();
