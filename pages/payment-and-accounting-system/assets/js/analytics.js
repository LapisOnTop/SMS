// Analytics Dashboard Module
(function() {
	const collectionsCtx = document.getElementById('collectionsChart')?.getContext('2d');
	const statusCtx = document.getElementById('paymentStatusChart')?.getContext('2d');

	let collectionsChart = null;
	let statusChart = null;

	const chartOptions = {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			legend: {
				labels: {
					color: '#666',
					font: { family: "'Poppins', sans-serif", size: 12, weight: '500' }
				}
			},
			tooltip: {
				backgroundColor: 'rgba(0, 0, 0, 0.8)',
				titleColor: '#fff',
				bodyColor: '#bdc3c7',
				borderColor: '#3f69ff',
				borderWidth: 1
			}
		}
	};

	// Initialize charts on page load
	function initCharts() {
		createCollectionsChart();
		createPaymentStatusChart();
		loadAnalyticsData();
	}

	function createCollectionsChart(labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], series = [0, 0, 0, 0, 0, 0, 0]) {
		if (!collectionsCtx) return;

		collectionsChart = new Chart(collectionsCtx, {
			type: 'bar',
			data: {
				labels,
				datasets: [{
					label: 'Daily Collection (₱)',
					data: series,
					backgroundColor: '#3f69ff',
					borderColor: '#3f69ff',
					borderWidth: 0,
					borderRadius: 6,
					hoverBackgroundColor: '#2d5adb',
					tension: 0.3
				}]
			},
			options: {
				...chartOptions,
				scales: {
					y: {
						beginAtZero: true,
						grid: { color: '#f0f0f0' },
						ticks: { color: '#666' }
					},
					x: {
						grid: { display: false },
						ticks: { color: '#666' }
					}
				}
			}
		});
	}

	function createPaymentStatusChart(fullCount = 0, partialCount = 0, pendingCount = 0) {
		if (!statusCtx) return;

		statusChart = new Chart(statusCtx, {
			type: 'doughnut',
			data: {
				labels: ['Full', 'Partial', 'Pending'],
				datasets: [{
					data: [fullCount, partialCount, pendingCount],
					backgroundColor: [
						'#10b981',
						'#3b82f6',
						'#f59e0b'
					],
					borderColor: 'white',
					borderWidth: 3
				}]
			},
			options: {
				...chartOptions,
				plugins: {
					...chartOptions.plugins,
					tooltip: {
						...chartOptions.plugins.tooltip,
						callbacks: {
							label: function(context) {
								return context.label + ': ' + context.parsed + '%';
							}
						}
					}
				}
			}
		});
	}

	// Load analytics data
	function loadAnalyticsData() {
		const conn = new XMLHttpRequest();
		conn.onreadystatechange = function() {
			if (conn.readyState === 4 && conn.status === 200) {
				try {
					const data = JSON.parse(conn.responseText);
					if (data.success) {
						updateMetrics(data.metrics);
						updateTransactions(data.transactions);
						updateChartsFromApi(data);
					}
				} catch (error) {
					console.error('Error parsing analytics data:', error);
				}
			}
		};
		conn.open('GET', '../../api/get-analytics-data.php', true);
		conn.send();
	}

	function updateChartsFromApi(payload) {
		const weekLabels = payload?.charts?.week?.labels || ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
		const weekData = payload?.charts?.week?.data || [0, 0, 0, 0, 0, 0, 0];

		if (collectionsChart) {
			collectionsChart.data.labels = weekLabels;
			collectionsChart.data.datasets[0].data = weekData;
			collectionsChart.update();
		}

		const fullCount = payload?.metrics?.statusBreakdown?.full || 0;
		const partialCount = payload?.metrics?.statusBreakdown?.partial || 0;
		const pendingCount = payload?.metrics?.pending || 0;

		if (statusChart) {
			statusChart.data.datasets[0].data = [fullCount, partialCount, pendingCount];
			statusChart.update();
		}
	}

	function updateMetrics(metrics) {
		document.getElementById('todayCollection').textContent = formatCurrency(metrics.today || 0);
		document.getElementById('todayTrend').textContent = (metrics.todayChange >= 0 ? '↑' : '↓') + ' vs yesterday';
		
		document.getElementById('monthlyTotal').textContent = formatCurrency(metrics.monthly || 0);
		
		document.getElementById('totalTransactions').textContent = metrics.transactions || 0;
		document.getElementById('pendingPayments').textContent = metrics.pending || 0;
	}

	function updateTransactions(transactions) {
		const tbody = document.getElementById('recentTransactionsBody');
		if (!transactions || transactions.length === 0) {
			tbody.innerHTML = '<tr><td colspan="5" class="no-data">No transactions found</td></tr>';
			return;
		}

		tbody.innerHTML = transactions.slice(0, 10).map(t => `
			<tr>
				<td><strong>${t.receipt_number || 'N/A'}</strong></td>
				<td>${t.student_name || 'Unknown'}</td>
				<td>${formatCurrency(t.amount)}</td>
				<td><span class="status-badge ${(t.status || 'partial').toLowerCase()}">${t.status || 'Partial'}</span></td>
				<td>${formatDate(t.payment_date)}</td>
			</tr>
		`).join('');
	}

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

	// Chart period control
	document.querySelectorAll('.chart-btn').forEach(btn => {
		btn.addEventListener('click', function() {
			document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
			this.classList.add('active');
			
			const period = this.dataset.period;
			updateCollectionsChart(period);
		});
	});

	function updateCollectionsChart(period) {
		if (!collectionsChart) return;

		let labels, data;
		
		if (period === 'week') {
			labels = collectionsChart.data.labels;
			data = collectionsChart.data.datasets[0].data;
		} else if (period === 'month') {
			labels = Array.from({length: 30}, (_, i) => `Day ${i+1}`);
			data = Array.from({length: 30}, () => Math.random() * 80000 + 30000);
		} else if (period === 'year') {
			labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
			data = [450000, 520000, 480000, 650000, 720000, 580000, 690000, 640000, 710000, 580000, 620000, 735000];
		}

		collectionsChart.data.labels = labels;
		collectionsChart.data.datasets[0].data = data;
		collectionsChart.update();
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initCharts);
	} else {
		initCharts();
	}
})();