// Helper function to safely parse JSON responses
async function parseJSON(response) {
	const text = await response.text();
	if (!text) {
		throw new Error('Empty response from server');
	}
	try {
		return JSON.parse(text);
	} catch (e) {
		console.error('Invalid JSON response:', text.substring(0, 200));
		throw new Error('Server returned invalid data: ' + text.substring(0, 100));
	}
}

// ===== PAYMENT ENTRY MODULE =====
(function () {
	const referenceSearchInput = document.getElementById('referenceSearch');
	const searchBtn = document.getElementById('searchBtn');
	const paymentCard = document.getElementById('paymentCard');
	const resetSearchBtn = document.getElementById('resetSearchBtn');
	const submitPaymentBtn = document.getElementById('submitPaymentBtn');
	const statusMessage = document.getElementById('statusMessage');

	let currentPaymentData = null;
	let liveSearchTimeout = null;

	function formatCurrency(value) {
		return new Intl.NumberFormat('en-PH', {
			style: 'currency',
			currency: 'PHP',
			minimumFractionDigits: 2
		}).format(value || 0);
	}

	function resetForm() {
		referenceSearchInput.value = '';
		paymentCard.style.display = 'none';
		statusMessage.className = '';
		statusMessage.textContent = '';
		currentPaymentData = null;
	}

	async function searchApplication(reference) {
		const ref = (reference || '').trim().toUpperCase();
		if (!ref) {
			paymentCard.style.display = 'none';
			statusMessage.textContent = 'Enter a reference number or student number to search';
			statusMessage.className = 'status-message info';
			return;
		}

		try {
			const formData = new FormData();
			formData.append('action', 'search_application');
			formData.append('reference', ref);

			const response = await fetch('../../pages/cashier/cashier-management.php', {
				method: 'POST',
				body: formData
			});

			const result = await parseJSON(response);

			if (!response.ok || !result.success) {
				paymentCard.style.display = 'block';
				paymentCard.innerHTML = `
					<div class="card-header">
						<h3>Search Result</h3>
					</div>
					<div class="card-body" style="text-align: center; padding: 40px 20px;">
						<p style="font-size: 1.1em; color: #666;">
							<i class="fa-solid fa-circle-exclamation" style="color: #ff6b6b; font-size: 2em; margin-bottom: 10px; display: block;"></i>
							${result.message || 'Failed to fetch - Application not found'}
						</p>
					</div>
				`;
				statusMessage.className = '';
				statusMessage.textContent = '';
				return;
			}

			currentPaymentData = result.data;
			displayPaymentInfo(result.data);
			paymentCard.style.display = 'block';
			statusMessage.className = '';
			statusMessage.textContent = '';
		} catch (error) {
			console.error('Error:', error);
			paymentCard.style.display = 'block';
			paymentCard.innerHTML = `
				<div class="card-header">
					<h3>Search Result</h3>
				</div>
				<div class="card-body" style="text-align: center; padding: 40px 20px;">
					<p style="font-size: 1.1em; color: #666;">
						<i class="fa-solid fa-circle-exclamation" style="color: #ff6b6b; font-size: 2em; margin-bottom: 10px; display: block;"></i>
						Failed to fetch - Error searching for application
					</p>
				</div>
			`;
			statusMessage.className = '';
			statusMessage.textContent = '';
		}
	}

	function displayPaymentInfo(data) {
		// Restore original card content with student details
		paymentCard.innerHTML = `
			<div class="card-header">
				<h3>Payment Information</h3>
			</div>
			<div class="card-body">
				<!-- Student Info -->
				<div class="info-section">
					<h4>Student Details</h4>
					<div class="info-grid">
						<div class="info-item">
							<label>Reference Number</label>
							<span id="refNumber">-</span>
						</div>
						<div class="info-item">
							<label>Student Number</label>
							<span id="studentNumber">-</span>
						</div>
						<div class="info-item">
							<label>Full Name</label>
							<span id="fullName">-</span>
						</div>
						<div class="info-item">
							<label>Admission Type</label>
							<span id="admissionType">-</span>
						</div>
						<div class="info-item">
							<label>Course / Year</label>
							<span id="courseYear">-</span>
						</div>
						<div class="info-item">
							<label>Current Status</label>
							<span id="currentStatusText">-</span>
						</div>
					</div>
				</div>

				<!-- Subjects Info -->
				<div class="info-section">
					<h4>Subject Load</h4>
					<div class="subjects-list" id="subjectsList"></div>
					<div class="summary-row">
						<span>Total Units: <strong id="totalUnitsText">0</strong></span>
						<span>Subtotal: <strong id="subjectSubtotal">₱0.00</strong></span>
					</div>
				</div>

				<!-- Fees Info -->
				<div class="info-section">
					<h4>Fees Breakdown</h4>
					<div class="fees-list" id="feesList"></div>
					<div class="summary-row">
						<span>Total Fees: <strong id="totalFeesText">₱0.00</strong></span>
					</div>
				</div>

				<!-- Total Amount -->
				<div class="info-section total-amount-section">
					<div class="total-amount-display">
						<label>Grand Total</label>
						<span id="grandTotalText">₱0.00</span>
					</div>
					<div class="total-amount-display">
						<label>Discounted</label>
						<span id="discountAmountText">₱0.00</span>
					</div>
					<div class="total-amount-display">
						<label>Total Paid</label>
						<span id="totalPaidText">₱0.00</span>
					</div>
					<div class="total-amount-display">
						<label>Remaining Balance</label>
						<span id="remainingBalanceText">₱0.00</span>
					</div>
				</div>

				<!-- Payment Details -->
				<div class="info-section">
					<h4>Payment Details</h4>
					<div class="info-grid">
						<div class="info-item">
							<label>Payment Method</label>
							<span class="payment-method-badge">Cash Only</span>
						</div>
						<div class="info-item">
							<label>Amount to Pay</label>
							<input 
								type="number" 
								id="amountToPay" 
								class="amount-input" 
								placeholder="Enter amount"
								min="0"
								step="0.01"
							>
						</div>
					</div>
				</div>

				<!-- Action Buttons -->
				<div class="card-actions">
					<button type="button" class="btn btn-primary" id="submitPaymentBtn">
						<i class="fa-solid fa-credit-card"></i> Submit Payment & Generate Receipt
					</button>
					<button type="button" class="btn btn-secondary" id="resetSearchBtn">
						<i class="fa-solid fa-arrow-left"></i> New Search
					</button>
				</div>

				<p class="status-message" id="statusMessage"></p>
			</div>
		`;

		// Now set the values
		document.getElementById('refNumber').textContent = data.reference_number;
		document.getElementById('studentNumber').textContent = data.student_number || 'Not linked yet';
		document.getElementById('fullName').textContent = data.student_name;
		document.getElementById('admissionType').textContent = data.admission_type;
		document.getElementById('courseYear').textContent = data.course_year;
		document.getElementById('currentStatusText').textContent = data.current_status || 'Validated';

		// Subjects
		const subjectsList = document.getElementById('subjectsList');
		subjectsList.innerHTML = data.subjects.map(subject =>
			`<div class="subject-item">
				<div class="subject-info">
					<label>${subject.subject_code}</label>
					<span>${subject.subject_name}</span>
				</div>
				<div class="subject-price">
					${subject.units} units × ${formatCurrency(subject.price / subject.units)} = ${formatCurrency(subject.price)}
				</div>
			</div>`
		).join('');

		document.getElementById('totalUnitsText').textContent = data.total_units;
		document.getElementById('subjectSubtotal').textContent = formatCurrency(data.total_subject_cost);

		// Fees
		const feesList = document.getElementById('feesList');
		feesList.innerHTML = data.fees.map(fee =>
			`<div class="fee-item">
				<div class="fee-info">
					<label>${fee.fee_name}</label>
				</div>
				<div class="fee-amount">${formatCurrency(fee.amount)}</div>
			</div>`
		).join('');

		document.getElementById('totalFeesText').textContent = formatCurrency(data.total_fees);

		// Grand Total
		document.getElementById('grandTotalText').textContent = formatCurrency(data.grand_total);
		document.getElementById('discountAmountText').textContent = formatCurrency(data.discount_amount || 0);
		document.getElementById('totalPaidText').textContent = formatCurrency(data.total_paid);
		document.getElementById('remainingBalanceText').textContent = formatCurrency(data.remaining_balance);

		// Default to the current remaining balance.
		document.getElementById('amountToPay').value = Number(data.remaining_balance || 0).toFixed(2);

		const amountInput = document.getElementById('amountToPay');
		const submitBtn = document.getElementById('submitPaymentBtn');
		if (data.is_fully_paid) {
			amountInput.value = '0.00';
			amountInput.disabled = true;
			submitBtn.disabled = true;
			submitBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Already Paid';
			showStatus('Current status: Paid. This reference already has a full payment and cannot be submitted again.', 'info');
		} else {
			amountInput.disabled = false;
			submitBtn.disabled = false;
			submitBtn.innerHTML = '<i class="fa-solid fa-credit-card"></i> Submit Payment & Generate Receipt';
		}

		// Re-attach event listeners
		attachPaymentEventListeners();
	}

	function attachPaymentEventListeners() {
		const submitBtn = document.getElementById('submitPaymentBtn');
		const resetBtn = document.getElementById('resetSearchBtn');

		if (submitBtn) {
			submitBtn.removeEventListener('click', handlePaymentSubmit);
			submitBtn.addEventListener('click', handlePaymentSubmit);
		}

		if (resetBtn) {
			resetBtn.removeEventListener('click', resetForm);
			resetBtn.addEventListener('click', resetForm);
		}
	}

	function showStatus(message, type) {
		const msg = document.getElementById('statusMessage');
		if (msg) {
			msg.textContent = message;
			msg.className = `status-message ${type}`;

			if (type === 'success') {
				setTimeout(() => {
					msg.className = '';
				}, 3000);
			}
		}
	}

	async function handlePaymentSubmit() {
		const amountInput = document.getElementById('amountToPay');
		const amountPaid = parseFloat(amountInput.value);

		if (!currentPaymentData) {
			showStatus('Please search for an application first', 'error');
			return;
		}

		if (currentPaymentData.is_fully_paid) {
			showStatus('Current status: Paid. Additional payment is not allowed.', 'error');
			return;
		}

		if (!Number.isFinite(amountPaid) || amountPaid <= 0) {
			showStatus('Please enter a valid amount', 'error');
			return;
		}

		try {
			const payload = {
				action: 'submit_payment',
				reference_number: currentPaymentData.reference_number,
				amount_paid: amountPaid,
				grand_total: currentPaymentData.grand_total,
				total_subject_cost: currentPaymentData.total_subject_cost,
				total_fees: currentPaymentData.total_fees
			};

			const response = await fetch('../../pages/cashier/cashier-management.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify(payload)
			});

			const result = await parseJSON(response);

			if (!response.ok || !result.success) {
				showStatus(result.message || 'Failed to submit payment', 'error');
				return;
			}

			// Show receipt and generate PDF
			showReceiptModal(result.receipt_number, currentPaymentData, amountPaid);

			// Generate and download PDF after a short delay
			setTimeout(async () => {
				await generateReceiptPDF(result.payment_data || {
					receipt_number: result.receipt_number,
					student_name: currentPaymentData.student_name,
					reference_number: currentPaymentData.reference_number,
					course_year: currentPaymentData.course_year,
					total_subject_cost: currentPaymentData.total_subject_cost,
					total_fees: currentPaymentData.total_fees,
					amount_paid: amountPaid,
					grand_total: currentPaymentData.grand_total,
					discount_amount: currentPaymentData.discount_amount || 0,
					net_assessment: currentPaymentData.net_assessment || currentPaymentData.grand_total,
					payment_status: amountPaid >= currentPaymentData.remaining_balance ? 'Full' : 'Partial',
					payment_date: new Date().toLocaleString('en-PH')
				});
			}, 1000);

			showStatus('Payment submitted successfully! Receipt: ' + result.receipt_number, 'success');

			if (result.payment_data) {
				currentPaymentData.discount_amount = Number(result.payment_data.discount_amount || currentPaymentData.discount_amount || 0);
				currentPaymentData.net_assessment = Number(result.payment_data.net_assessment || currentPaymentData.net_assessment || currentPaymentData.grand_total);
				currentPaymentData.total_paid = Number(result.payment_data.total_paid || 0);
				currentPaymentData.remaining_balance = Number(result.payment_data.remaining_balance || 0);
				currentPaymentData.is_fully_paid = currentPaymentData.remaining_balance <= 0;
			}

			// Reload payment history
			document.dispatchEvent(new CustomEvent('paymentSubmitted', { detail: { receipt_number: result.receipt_number } }));

			// Reset form after 3 seconds
			setTimeout(() => {
				resetForm();
			}, 3000);
		} catch (error) {
			console.error('Error:', error);
			console.error('Error details:', error.toString());
			showStatus('Error submitting payment: ' + error.message, 'error');
		}
	}

	function generateReceiptPDF(paymentData) {
		const { jsPDF } = window.jspdf;
		const doc = new jsPDF();

		// Set font
		doc.setFont('Helvetica');
		let yPosition = 20;

		// Header
		doc.setFontSize(18);
		doc.text('PAYMENT RECEIPT', doc.internal.pageSize.getWidth() / 2, yPosition, { align: 'center' });

		yPosition += 12;
		doc.setFontSize(10);
		doc.text('Generated: ' + new Date().toLocaleString('en-PH'), doc.internal.pageSize.getWidth() / 2, yPosition, { align: 'center' });

		yPosition += 15;
		doc.setDrawColor(100);
		doc.line(20, yPosition, doc.internal.pageSize.getWidth() - 20, yPosition);

		// Student Information
		yPosition += 12;
		doc.setFontSize(12);
		doc.setFont('Helvetica', 'bold');
		doc.text('Receipt Information', 20, yPosition);

		yPosition += 8;
		doc.setFont('Helvetica', 'normal');
		doc.setFontSize(10);
		doc.text('Receipt Number: ' + paymentData.receipt_number, 20, yPosition);
		yPosition += 6;
		doc.text('Reference Number: ' + paymentData.reference_number, 20, yPosition);
		yPosition += 6;
		doc.text('Student Name: ' + paymentData.student_name, 20, yPosition);
		yPosition += 6;
		doc.text('Course/Year: ' + paymentData.course_year, 20, yPosition);

		// Payment Details
		yPosition += 12;
		doc.setFont('Helvetica', 'bold');
		doc.text('Payment Breakdown', 20, yPosition);

		yPosition += 8;
		doc.setFont('Helvetica', 'normal');
		const pageWidth = doc.internal.pageSize.getWidth();
		const rightCol = pageWidth - 40;

		doc.text('Subject Cost:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.total_subject_cost).toFixed(2), rightCol, yPosition, { align: 'right' });
		yPosition += 6;

		doc.text('Fees:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.total_fees).toFixed(2), rightCol, yPosition, { align: 'right' });
		yPosition += 8;

		doc.text('Discounted:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.discount_amount || 0).toFixed(2), rightCol, yPosition, { align: 'right' });
		yPosition += 8;

		doc.line(20, yPosition, pageWidth - 20, yPosition);
		yPosition += 6;

		doc.setFont('Helvetica', 'bold');
		doc.setFontSize(11);
		doc.text('Grand Total:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.grand_total).toFixed(2), rightCol, yPosition, { align: 'right' });
		yPosition += 6;
		doc.text('Net Payable:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.net_assessment || paymentData.grand_total).toFixed(2), rightCol, yPosition, { align: 'right' });

		// Amount Paid
		yPosition += 12;
		doc.setFont('Helvetica', 'normal');
		doc.setFontSize(10);
		doc.setTextColor(56, 142, 60);
		doc.text('Amount Paid:', 20, yPosition);
		doc.text('₱' + parseFloat(paymentData.amount_paid).toFixed(2), rightCol, yPosition, { align: 'right' });
		doc.setTextColor(0, 0, 0);
		yPosition += 6;

		doc.text('Payment Status: ' + paymentData.payment_status, 20, yPosition);
		yPosition += 6;
		doc.text('Payment Date: ' + paymentData.payment_date, 20, yPosition);
		yPosition += 6;
		doc.text('Payment Method: Cash', 20, yPosition);

		// Footer
		yPosition += 12;
		doc.setDrawColor(100);
		doc.line(20, yPosition, pageWidth - 20, yPosition);
		yPosition += 8;

		doc.setFontSize(9);
		doc.setTextColor(150, 150, 150);
		doc.text('Thank you for your payment!', doc.internal.pageSize.getWidth() / 2, yPosition, { align: 'center' });
		yPosition += 6;
		doc.text('Please keep this receipt for your records.', doc.internal.pageSize.getWidth() / 2, yPosition, { align: 'center' });

		// Download PDF
		doc.save(paymentData.receipt_number + '.pdf');
	}

	// Live search on input
	if (referenceSearchInput) {
		referenceSearchInput.addEventListener('input', (event) => {
			clearTimeout(liveSearchTimeout);
			const searchValue = event.target.value.trim();

			if (searchValue.length > 0) {
				liveSearchTimeout = setTimeout(() => {
					searchApplication(searchValue);
				}, 800);
			} else {
				paymentCard.style.display = 'none';
				statusMessage.className = '';
				statusMessage.textContent = '';
			}
		});

		referenceSearchInput.addEventListener('keydown', (event) => {
			if (event.key === 'Enter') {
				event.preventDefault();
				clearTimeout(liveSearchTimeout);
				searchApplication(referenceSearchInput.value);
			}
		});
	}

	if (searchBtn) {
		searchBtn.addEventListener('click', () => {
			clearTimeout(liveSearchTimeout);
			searchApplication(referenceSearchInput.value);
		});
	}

	function showReceiptModal(receiptNumber, paymentData, amountPaid) {
		const receiptHTML = `
			<div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; margin: 20px auto;">
				<div style="text-align: center; margin-bottom: 20px;">
					<h2 style="color: #4b6bff; margin: 0;">PAYMENT RECEIPT</h2>
					<p style="color: #999; margin: 5px 0 0 0;">Generated ${new Date().toLocaleString()}</p>
				</div>
				<div style="border-top: 2px solid #e0e0e0; padding-top: 20px; margin-bottom: 20px;">
					<p><strong>Receipt Number:</strong> ${receiptNumber}</p>
					<p><strong>Student Name:</strong> ${paymentData.student_name}</p>
					<p><strong>Reference:</strong> ${paymentData.reference_number}</p>
					<p><strong>Course:</strong> ${paymentData.course_year}</p>
				</div>
				<div style="background: #f9f9f9; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
					<div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
						<span>Subject Total</span>
						<strong>${formatCurrency(paymentData.total_subject_cost)}</strong>
					</div>
					<div style="display: flex; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid #e0e0e0;">
						<span>Fees Total</span>
						<strong>${formatCurrency(paymentData.total_fees)}</strong>
					</div>
					<div style="display: flex; justify-content: space-between; margin-top: 10px;">
						<span>Discounted</span>
						<strong>${formatCurrency(paymentData.discount_amount || 0)}</strong>
					</div>
					<div style="display: flex; justify-content: space-between; margin-top: 10px; padding-top: 10px;">
						<span style="font-weight: 600;">Grand Total</span>
						<strong style="font-size: 1.2rem; color: #4b6bff;">${formatCurrency(paymentData.grand_total)}</strong>
					</div>
					<div style="display: flex; justify-content: space-between; margin-top: 10px;">
						<span style="font-weight: 600;">Net Payable</span>
						<strong>${formatCurrency(paymentData.net_assessment || paymentData.grand_total)}</strong>
					</div>
				</div>
				<div style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
					<p style="margin: 0; color: #388e3c; font-weight: 600;">
						Amount Paid: ${formatCurrency(amountPaid)}
					</p>
					<p style="margin: 5px 0 0 0; color: #666; font-size: 0.9rem;">
						Payment Method: Cash
					</p>
				</div>
				<div style="text-align: center; color: #666; font-size: 0.85rem;">
					<p>Thank you for your payment!</p>
					<p>Please keep this receipt for your records.</p>
				</div>
			</div>
		`;

		// Create modal (simplified for demo)
		const modal = document.createElement('div');
		modal.style.cssText = `
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.5);
			display: flex;
			align-items: center;
			justify-content: center;
			z-index: 1000;
		`;
		modal.innerHTML = receiptHTML;
		document.body.appendChild(modal);

		// Auto-close modal after 5 seconds
		setTimeout(() => {
			modal.remove();
		}, 5000);
	}
})();

// ===== PAYMENT HISTORY MODULE =====
(function () {
	const paymentHistoryBody = document.getElementById('paymentHistoryBody');
	const paymentHistorySection = document.getElementById('payment-history-section');
	const paymentHistorySearch = document.getElementById('paymentHistorySearch');

	if (!paymentHistoryBody) {
		return;
	}

	let allPayments = [];

	function formatCurrency(value) {
		return new Intl.NumberFormat('en-PH', {
			style: 'currency',
			currency: 'PHP',
			minimumFractionDigits: 2
		}).format(value || 0);
	}

	function formatDate(dateString) {
		const date = new Date(dateString);
		return date.toLocaleDateString('en-PH');
	}

	function renderPaymentRows(payments) {
		paymentHistoryBody.innerHTML = '';

		if (payments.length === 0) {
			paymentHistoryBody.innerHTML = `
				<tr>
					<td colspan="5" style="text-align: center; padding: 40px 20px;">
						<p style="font-size: 1.1em; color: #999; margin: 0;">
							<i class="fa-solid fa-inbox" style="font-size: 2em; margin-bottom: 10px; display: block;"></i>
							No payment records found
						</p>
					</td>
				</tr>
			`;
			return;
		}

		payments.forEach(payment => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td><strong>${payment.receipt_number}</strong></td>
				<td>${payment.first_name} ${payment.last_name}</td>
				<td>${formatCurrency(payment.amount)}</td>
				<td>
					<span class="status-badge ${payment.payment_status.toLowerCase()}">
						${payment.payment_status}
					</span>
				</td>
				<td>${formatDate(payment.payment_date)}</td>
			`;
			paymentHistoryBody.appendChild(tr);
		});
	}

	function filterAndDisplayPayments(searchTerm) {
		const term = String(searchTerm || '').toLowerCase().trim();

		if (allPayments.length === 0) {
			renderPaymentRows([]);
			return;
		}

		const filtered = allPayments.filter(payment => {
			const receiptMatch = payment.receipt_number.toLowerCase().includes(term);
			const nameMatch = `${payment.first_name} ${payment.last_name}`.toLowerCase().includes(term);
			const statusMatch = payment.payment_status.toLowerCase().includes(term);

			return !term || receiptMatch || nameMatch || statusMatch;
		});

		renderPaymentRows(filtered);
	}

	async function loadPaymentHistory() {
		try {
			const response = await fetch('../../pages/cashier/cashier-management.php?action=get_payments');
			const result = await parseJSON(response);

			if (!response.ok || !result.success) {
				renderPaymentRows([]);
				return;
			}

			allPayments = result.payments || [];
			filterAndDisplayPayments('');
		} catch (error) {
			console.error('Error loading payment history:', error);
			renderPaymentRows([]);
		}
	}

	// Live search listener
	if (paymentHistorySearch) {
		paymentHistorySearch.addEventListener('input', (event) => {
			filterAndDisplayPayments(event.target.value);
		});
	}

	// Load payment history when the section becomes visible
	if (paymentHistorySection) {
		const observer = new MutationObserver(() => {
			if (paymentHistorySection.classList.contains('active')) {
				loadPaymentHistory();
			}
		});

		observer.observe(paymentHistorySection, {
			attributes: true,
			attributeFilter: ['class']
		});

		// Load on initial page load if history section is active
		if (paymentHistorySection.classList.contains('active')) {
			loadPaymentHistory();
		}
	}

	// Listen for payment submission event to reload history
	document.addEventListener('paymentSubmitted', () => {
		loadPaymentHistory();
	});
})();
