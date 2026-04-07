(function () {
	const searchTransactions = document.getElementById('searchTransactions');
	const typeFilter = document.getElementById('typeFilter');
	const statusFilter = document.getElementById('statusFilter');
	const fromDate = document.getElementById('fromDate');
	const toDate = document.getElementById('toDate');
	const filterButton = document.getElementById('filterButton');
	const transactionBody = document.getElementById('transactionBody');
	const resultsText = document.getElementById('resultsText');
	const totalTransactions = document.getElementById('totalTransactions');
	const totalPayments = document.getElementById('totalPayments');
	const paymentCount = document.getElementById('paymentCount');
	const totalDiscounts = document.getElementById('totalDiscounts');

	if (!transactionBody || !filterButton) {
		return;
	}

	let transactions = [];

	// Set max date to today to prevent selecting future dates
	const today = new Date().toISOString().split('T')[0];
	if (fromDate) {
		fromDate.setAttribute('max', today);
	}
	if (toDate) {
		toDate.setAttribute('max', today);
		// Set toDate to today by default
		toDate.value = today;
	}

	function money(value) {
		return 'PHP ' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
	}

	function statusClass(status) {
		const value = String(status).toLowerCase();
		if (value === 'full') return 'status-pill approved';
		if (value === 'partial') return 'status-pill pending';
		return 'status-pill pending';
	}

	function renderRows(items) {
		transactionBody.innerHTML = '';

		items.forEach(function (transaction) {
			const row = document.createElement('tr');
			row.dataset.search = [transaction.date, transaction.studentId, transaction.name, transaction.type, transaction.method, transaction.orNumber, transaction.status].join(' ').toLowerCase();
			row.dataset.type = transaction.type;
			row.dataset.status = transaction.status;
			row.dataset.date = transaction.date;
			row.innerHTML =
				'<td>' + transaction.date.replace(/-/g, '-\n') + '</td>' +
				'<td>' + transaction.studentId + '</td>' +
				'<td>' + transaction.name + '</td>' +
				'<td>' + transaction.type + '</td>' +
				'<td>' + transaction.method + '</td>' +
				'<td>' + money(transaction.amount) + '</td>' +
				'<td>' + transaction.orNumber + '</td>' +
				'<td><span class="' + statusClass(transaction.status) + '">' + transaction.status + '</span></td>';
			transactionBody.appendChild(row);
		});

		resultsText.textContent = String(items.length);
	}

	function applyFilters() {
		const query = String(searchTransactions.value || '').trim().toLowerCase();
		const type = typeFilter.value;
		const status = statusFilter.value;
		const from = fromDate.value;
		const to = toDate.value;

		const filtered = transactions.filter(function (transaction) {
			const matchesQuery = !query || transaction.name.toLowerCase().includes(query) || transaction.studentId.toLowerCase().includes(query) || transaction.orNumber.toLowerCase().includes(query);
			const matchesType = type === 'All' || transaction.type === type;
			const matchesStatus = status === 'All' || transaction.status === status;
			const matchesFrom = !from || transaction.date >= from;
			const matchesTo = !to || transaction.date <= to;
			return matchesQuery && matchesType && matchesStatus && matchesFrom && matchesTo;
		});

		renderRows(filtered);
	}

	function updateMetrics(summary) {
		totalTransactions.textContent = String(summary?.totalTransactions || 0);
		totalPayments.textContent = money(summary?.totalPayments || 0);
		paymentCount.textContent = String(summary?.paymentCount || 0);
		totalDiscounts.textContent = money(summary?.partialCount || 0);
	}

	function loadTransactionsFromAPI() {
		fetch('../../api/get-transaction-log-data.php')
			.then(function (response) { return response.json(); })
			.then(function (payload) {
				if (!payload.success) {
					transactions = [];
					renderRows(transactions);
					updateMetrics({});
					return;
				}

				transactions = Array.isArray(payload.transactions) ? payload.transactions : [];
				renderRows(transactions);
				updateMetrics(payload.summary || {});
			})
			.catch(function () {
				transactions = [];
				renderRows(transactions);
				updateMetrics({});
			});
	}

	filterButton.addEventListener('click', applyFilters);
	searchTransactions.addEventListener('input', applyFilters);
	typeFilter.addEventListener('change', applyFilters);
	statusFilter.addEventListener('change', applyFilters);
	fromDate.addEventListener('change', applyFilters);
	toDate.addEventListener('change', applyFilters);

	loadTransactionsFromAPI();
})();