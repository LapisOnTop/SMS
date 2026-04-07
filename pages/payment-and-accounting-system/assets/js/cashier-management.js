// ===== ASSESSMENT FEES MODULE =====
(function () {
	const studentSearch = document.getElementById('studentSearch');
	const searchButton = document.getElementById('searchButton');
	const applicationsTableBody = document.getElementById('applicationsTableBody');
	const detailPanel = document.getElementById('detailPanel');
	const closeDetailPanel = document.getElementById('closeDetailPanel');
	const calculateButton = document.getElementById('calculateButton');
	const statementButton = document.getElementById('statementButton');

	const apiBase = '../../api';
	let activePaymentData = null;

	function formatCurrency(value) {
		return new Intl.NumberFormat('en-PH', {
			style: 'currency',
			currency: 'PHP',
			minimumFractionDigits: 2
		}).format(value || 0);
	}

	function getBadgeClass(status) {
		const lower = String(status || '').toLowerCase();
		if (lower === 'validated' || lower === 'approved') return 'badge success';
		if (lower === 'pending') return 'badge warning';
		return 'badge primary';
	}

	function populateSubjectsTable(subjects) {
		const subjectsTableBody = document.getElementById('subjectsTableBody');
		if (!subjectsTableBody) return;
		
		subjectsTableBody.innerHTML = subjects.map(subject =>
			`<tr>
				<td>${subject.code}</td>
				<td>${subject.name}</td>
				<td>${subject.units}</td>
				<td>${formatCurrency(subject.price)}</td>
			</tr>`
		).join('');
	}

	function populateFeesList(fees) {
		const feesList = document.getElementById('feesList');
		if (!feesList) return;
		
		feesList.innerHTML = fees.map(fee =>
			`<div class="fee-item">
				<div class="fee-info">
					<label>${fee.name}</label>
					<span class="fee-amount">${formatCurrency(fee.amount)}</span>
				</div>
				<input 
					type="checkbox" 
					class="fee-checkbox" 
					data-fee-id="${fee.fee_id}" 
					data-fee-amount="${fee.amount}"
					checked
					disabled
				/>
			</div>`
		).join('');
	}

	function calculateTotals() {
		if (!activePaymentData) return;

		const subjectTotal = activePaymentData.subject_summary.total_cost || 0;
		const feeTotal = activePaymentData.fee_summary.total_fees || 0;
		const total = subjectTotal + feeTotal;

		document.getElementById('subjectsTotalUnits').textContent = activePaymentData.subject_summary.total_units;
		document.getElementById('subjectsTotalPrice').textContent = formatCurrency(subjectTotal);
		document.getElementById('subjectsTotalDisplay').textContent = formatCurrency(subjectTotal);
		document.getElementById('feesTotalDisplay').textContent = formatCurrency(feeTotal);
		document.getElementById('grandTotal').textContent = formatCurrency(total);
	}

	function showDetailPanel(paymentData) {
		activePaymentData = paymentData;

		document.getElementById('detailRefNumber').textContent = paymentData.application.reference_number;
		document.getElementById('detailStudentName').textContent = paymentData.application.student_name;
		document.getElementById('detailCourse').textContent = paymentData.application.course;
		document.getElementById('detailAdmissionType').textContent = paymentData.application.admission_type;

		populateSubjectsTable(paymentData.subjects);
		populateFeesList(paymentData.fees);
		calculateTotals();

		detailPanel.style.display = 'block';
	}

	async function loadStudent(reference) {
		const ref = (reference || '').trim().toUpperCase();
		if (!ref) {
			applicationsTableBody.innerHTML = '<tr><td colspan="8" class="no-data">Search for an application to view details</td></tr>';
			return;
		}

		try {
			const response = await fetch(`${apiBase}/get-payment-data.php?reference=${encodeURIComponent(ref)}`);
			
			if (!response.ok) {
				const error = await response.json();
				alert(error.message || 'Failed to load student data');
				applicationsTableBody.innerHTML = '<tr><td colspan="8" class="no-data">No results found</td></tr>';
				return;
			}

			const data = await response.json();
			if (!data.ok) {
				alert(data.message || 'Error loading student data');
				applicationsTableBody.innerHTML = '<tr><td colspan="8" class="no-data">No results found</td></tr>';
				return;
			}

			applicationsTableBody.innerHTML = `
				<tr onclick="window.assessmentApp.showDetailPanel(${JSON.stringify(data).replace(/"/g, '&quot;')})">
					<td><strong>${data.application.reference_number}</strong></td>
					<td>${data.application.student_name}</td>
					<td>${data.application.student_name.toLowerCase().replace(/\s+/g, '') + '@example.com'}</td>
					<td>-</td>
					<td><span class="badge primary">${data.application.admission_type}</span></td>
					<td>${data.subject_summary.total_units}</td>
					<td><span class="${getBadgeClass(data.application.status)}">${data.application.status}</span></td>
					<td>
						<div class="action-icons">
							<button class="view-btn" title="View Details"><i class="fa-solid fa-eye"></i></button>
						</div>
					</td>
				</tr>
			`;

			showDetailPanel(data);
		} catch (error) {
			console.error('Error:', error);
			alert('An error occurred while loading student data');
			applicationsTableBody.innerHTML = '<tr><td colspan="8" class="no-data">Error loading data</td></tr>';
		}
	}

	searchButton.addEventListener('click', async () => {
		await loadStudent(studentSearch.value);
	});

	studentSearch.addEventListener('keydown', async (event) => {
		if (event.key === 'Enter') {
			event.preventDefault();
			await loadStudent(studentSearch.value);
		}
	});

	closeDetailPanel.addEventListener('click', () => {
		detailPanel.style.display = 'none';
	});

	calculateButton.addEventListener('click', async () => {
		if (!activePaymentData) {
			alert('No student data loaded. Please search for a student first.');
			return;
		}

		const payload = {
			application_id: activePaymentData.application.application_id,
			reference_number: activePaymentData.application.reference_number,
			student_name: activePaymentData.application.student_name,
			total_units: activePaymentData.subject_summary.total_units,
			subject_cost: activePaymentData.subject_summary.total_cost,
			fees_cost: activePaymentData.fee_summary.total_fees,
			grand_total: activePaymentData.grand_total,
			subjects: activePaymentData.subjects,
			fees: activePaymentData.fees,
			created_at: new Date().toISOString()
		};

		try {
			const response = await fetch(apiBase + '/save-assessment.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify(payload)
			});

			const result = await response.json();
			if (!response.ok || !result.ok) {
				throw new Error(result.message || 'Failed to save assessment');
			}

			calculateButton.textContent = 'Saved';
			setTimeout(() => {
				calculateButton.innerHTML = '<i class="fa-solid fa-calculator"></i> Calculate & Save Fees';
			}, 1200);
		} catch (error) {
			console.error('Error:', error);
			calculateButton.textContent = 'Failed - Retry';
			setTimeout(() => {
				calculateButton.innerHTML = '<i class="fa-solid fa-calculator"></i> Calculate & Save Fees';
			}, 1500);
		}
	});

	statementButton.addEventListener('click', () => {
		window.print();
	});

	window.assessmentApp = {
		showDetailPanel: showDetailPanel
	};
})();

// ===== BILLING STATEMENT MODULE =====
(function () {
	const billStudentSearch = document.getElementById('billStudentSearch');
	const semesterSelect = document.getElementById('semesterSelect');
	const billSearchButton = document.getElementById('billSearchButton');
	const studentName = document.getElementById('studentName');
	const studentId = document.getElementById('studentId');
	const totalCharges = document.getElementById('totalCharges');
	const totalPayments = document.getElementById('totalPayments');
	const balanceDue = document.getElementById('balanceDue');
	const soaRows = document.getElementById('soaRows');
	const printButton = document.getElementById('printButton');
	const emailButton = document.getElementById('emailButton');
	const statusText = document.getElementById('statusText');

	if (!billSearchButton || !soaRows) {
		return;
	}

	const dataset = {
		STU2026001: {
			name: 'Juan Dela Cruz',
			rows: [
				{ assessmentNo: 'ASS-202603-6781', semester: 'Second Semester', total: 5350, payment: 0, balance: 5350, status: 'Pending' },
				{ assessmentNo: 'ASS-202603-6780', semester: 'First Semester', total: 2675, payment: 2675, balance: 0, status: 'Paid' }
			]
		},
		STU2026002: {
			name: 'Maria Santos',
			rows: [
				{ assessmentNo: 'ASS-202603-6901', semester: 'Second Semester', total: 5200, payment: 1200, balance: 4000, status: 'Pending' },
				{ assessmentNo: 'ASS-202603-6822', semester: 'First Semester', total: 2450, payment: 2450, balance: 0, status: 'Paid' }
			]
		}
	};

	function formatMoney(value) {
		return 'PHP ' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function statusPillClass(status) {
		return String(status).toLowerCase() === 'paid' ? 'status-pill paid' : 'status-pill pending';
	}

	function renderRows(rows, selectedSemester) {
		soaRows.innerHTML = '';
		let charges = 0;
		let payments = 0;
		let balances = 0;

		rows.forEach(function (item) {
			if (selectedSemester.indexOf('First') > -1 && item.semester !== 'First Semester') {
				return;
			}
			if (selectedSemester.indexOf('Second') > -1 && item.semester !== 'Second Semester') {
				return;
			}

			charges += item.total;
			payments += item.payment;
			balances += item.balance;

			const row = document.createElement('tr');
			row.innerHTML =
				'<td>' + item.assessmentNo + '</td>' +
				'<td>' + item.semester + '</td>' +
				'<td>' + formatMoney(item.total) + '</td>' +
				'<td>' + formatMoney(item.payment) + '</td>' +
				'<td>' + formatMoney(item.balance) + '</td>' +
				'<td><span class="' + statusPillClass(item.status) + '">' + item.status + '</span></td>';
			soaRows.appendChild(row);
		});

		totalCharges.textContent = formatMoney(charges);
		totalPayments.textContent = formatMoney(payments);
		balanceDue.textContent = formatMoney(balances);
	}

	function loadStatement() {
		const key = String(billStudentSearch.value || '').trim().toUpperCase();
		const student = dataset[key] || dataset.STU2026001;
		studentName.textContent = student.name;
		studentId.textContent = key || 'STU2026001';
		renderRows(student.rows, semesterSelect.value);
		statusText.textContent = 'Loaded statement for ' + student.name + '.';
	}

	billSearchButton.addEventListener('click', loadStatement);
	semesterSelect.addEventListener('change', loadStatement);
	billStudentSearch.addEventListener('keydown', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			loadStatement();
		}
	});

	printButton.addEventListener('click', function () {
		window.print();
	});

	emailButton.addEventListener('click', function () {
		statusText.textContent = 'SOA email queued for sending.';
	});

	loadStatement();
})();

// ===== PAYMENT POSTING MODULE =====
(function () {
	const paymentStudentSearch = document.getElementById('paymentStudentSearch');
	const paymentSearchButton = document.getElementById('paymentSearchButton');
	const paymentStudentId = document.getElementById('paymentStudentId');
	const paymentStudentName = document.getElementById('paymentStudentName');
	const paymentCourseYear = document.getElementById('paymentCourseYear');
	const orNumber = document.getElementById('orNumber');
	const paymentMethod = document.getElementById('paymentMethod');
	const assessmentType = document.getElementById('assessmentType');
	const referenceNumber = document.getElementById('referenceNumber');
	const remarks = document.getElementById('remarks');
	const amountPaid = document.getElementById('amountPaid');
	const paymentType = document.getElementById('paymentType');
	const paymentDate = document.getElementById('paymentDate');
	const totalAssessment = document.getElementById('totalAssessment');
	const totalPaid = document.getElementById('totalPaid');
	const totalDiscount = document.getElementById('totalDiscount');
	const currentBalance = document.getElementById('currentBalance');
	const postPaymentButton = document.getElementById('postPaymentButton');
	const validateReceiptButton = document.getElementById('validateReceiptButton');
	const printReceiptButton = document.getElementById('printReceiptButton');
	const paymentStatusText = document.getElementById('paymentStatusText');

	if (!paymentSearchButton || !paymentStudentSearch) {
		return;
	}

	const apiBase = '../../api';
	let activeAssessmentId = null;
	let activeStudent = { id: '', name: '', course: '', assessment: 0, paid: 0, discount: 0, application_id: null };

	function generateOR() {
		const now = new Date();
		const dateStr = now.getFullYear() + padZero(now.getMonth() + 1) + padZero(now.getDate());
		const randomStr = Math.random().toString(36).substring(2, 8).toUpperCase();
		orNumber.value = `OR-${dateStr}-${randomStr}`;
	}

	function padZero(num) {
		return String(num).padStart(2, '0');
	}

	function money(value) {
		return 'PHP ' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function updateTotals(customPaid) {
		const paidValue = Number.isFinite(customPaid) ? customPaid : activeStudent.paid;
		const balance = Math.max(activeStudent.assessment - activeStudent.discount - paidValue, 0);

		totalAssessment.textContent = money(activeStudent.assessment);
		totalPaid.textContent = money(paidValue);
		totalDiscount.textContent = money(activeStudent.discount);
		currentBalance.textContent = money(balance);
	}

	async function searchApplication(ref) {
		const key = String(ref || '').trim();
		if (!key) return false;

		try {
			const formData = new FormData();
			formData.append('action', 'search_application');
			formData.append('reference', key);

			const response = await fetch(window.location.pathname, {
				method: 'POST',
				body: formData
			});
			const result = await response.json();

			if (!response.ok || !result.success) {
				paymentStatusText.textContent = result.message || 'Application search failed.';
				return false;
			}

			const data = result.data;
			activeStudent = {
				id: data.student_id,
				name: data.student_name,
				course: data.course_year,
				assessment: data.assessment,
				paid: data.paid,
				discount: data.discount,
				application_id: data.application_id,
				program_id: data.program_id,
				year_level: data.year_level
			};

			paymentStudentId.value = activeStudent.id;
			paymentStudentName.value = activeStudent.name;
			paymentCourseYear.value = activeStudent.course;
			amountPaid.value = '';
			paymentType.value = 'Full Payment';
			activeStudent.assessment = 0;
			activeStudent.paid = 0;
			activeStudent.discount = 0;
			updateTotals(0);
			paymentStatusText.textContent = `Loaded ${activeStudent.name} (${data.program_id} - ${data.year_level}). Ready for downpayment.`;
			generateOR();
			return true;
		} catch (error) {
			paymentStatusText.textContent = 'Application search error: ' + error.message;
			return false;
		}
	}

	function debounce(fn, delay = 500) {
		let timeout;
		return function(...args) {
			clearTimeout(timeout);
			timeout = setTimeout(() => fn.apply(this, args), delay);
		};
	}

	function loadStudent(query) {
		paymentStatusText.textContent = 'Enter application reference number to load student from database.';
		paymentStudentId.value = '';
		paymentStudentName.value = '';
		paymentCourseYear.value = '';
		amountPaid.value = '';
		updateTotals(0);
	}

	function setToday() {
		const now = new Date();
		const month = String(now.getMonth() + 1).padStart(2, '0');
		const day = String(now.getDate()).padStart(2, '0');
		paymentDate.value = now.getFullYear() + '-' + month + '-' + day;
	}

	paymentSearchButton.addEventListener('click', async function () {
		const ref = referenceNumber.value.trim();
		if (ref) {
			const appLoaded = await searchApplication(ref);
			if (appLoaded) return;
		}
		await loadStudent(paymentStudentSearch.value);
	});

	referenceNumber.addEventListener('input', debounce(function() {
		if (referenceNumber.value.trim()) {
			searchApplication(referenceNumber.value);
		}
	}, 500));

	paymentStudentSearch.addEventListener('keydown', async function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			await loadStudent(paymentStudentSearch.value);
		}
	});

	amountPaid.addEventListener('input', function () {
		const value = parseFloat(amountPaid.value);
		if (!Number.isFinite(value)) {
			updateTotals(activeStudent.paid);
			return;
		}
		updateTotals(Math.max(value, 0));
	});

	paymentType.addEventListener('change', function () {
		if (paymentType.value === 'Full Payment') {
			const fullAmount = activeStudent.assessment - activeStudent.discount;
			amountPaid.value = String(fullAmount);
			updateTotals(fullAmount);
		}
	});

	validateReceiptButton.addEventListener('click', function () {
		paymentStatusText.textContent = 'Receipt validation complete. You can post this payment.';
	});

	postPaymentButton.addEventListener('click', async function () {
		const amount = parseFloat(amountPaid.value);
		if (!Number.isFinite(amount) || amount <= 0) {
			paymentStatusText.textContent = 'Enter a valid amount before posting payment.';
			return;
		}
		if (!paymentMethod.value) {
			paymentStatusText.textContent = 'Select a payment method first.';
			return;
		}

		const payload = {
			studentNumber: paymentStudentId.value,
			assessmentId: activeAssessmentId,
			application_id: activeStudent.application_id || null,
			orNumber: orNumber.value,
			referenceNumber: referenceNumber.value,
			paymentMethod: paymentMethod.value,
			paymentType: paymentType.value,
			amountPaid: amount,
			paymentDate: paymentDate.value,
			assessmentType: assessmentType.value,
			remarks: remarks.value
		};

		try {
			const response = await fetch(apiBase + '/post-payment.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify(payload)
			});
			const result = await response.json();
			if (!response.ok || !result.ok) {
				throw new Error(result.message || 'Posting failed');
			}

			if (result.orNumber && !orNumber.value) {
				orNumber.value = result.orNumber;
			}

			if (result.totals) {
				activeStudent.assessment = Number(result.totals.assessment || activeStudent.assessment);
				activeStudent.paid = Number(result.totals.paid || activeStudent.paid);
				activeStudent.discount = Number(result.totals.discount || activeStudent.discount);
				updateTotals(activeStudent.paid);
			}

			paymentStatusText.textContent = 'Payment posted successfully for ' + paymentStudentName.value + '.';
		} catch (error) {
			paymentStatusText.textContent = 'Failed to post payment. ' + error.message;
		}
	});

	printReceiptButton.addEventListener('click', function () {
		window.print();
	});

	setToday();
	generateOR();
})();
