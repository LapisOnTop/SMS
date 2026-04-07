// Initialize all charts on page load
window.addEventListener('load', function() {
	initializeCharts();
});

function initializeCharts() {
	// Status Chart
	const statusData = window.dashboardData.by_status || [];
	const statusLabels = statusData.map(s => s.status);
	const statusCounts = statusData.map(s => s.count);

	const statusCtx = document.getElementById('statusChart');
	if (statusCtx) {
		if (statusCtx.chart) {
			statusCtx.chart.destroy();
		}
		statusCtx.chart = new Chart(statusCtx, {
			type: 'doughnut',
			data: {
				labels: statusLabels,
				datasets: [{
					data: statusCounts,
					backgroundColor: [
						'#667eea',
						'#764ba2',
						'#f093fb',
						'#4facfe',
						'#00f2fe',
						'#ff6b6b'
					]
				}]
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						position: 'bottom'
					}
				}
			}
		});
	}

	// Admission Type Chart
	const admissionData = window.dashboardData.by_admission_type || [];
	const admissionLabels = admissionData.map(a => a.type);
	const admissionCounts = admissionData.map(a => a.count);

	const admissionCtx = document.getElementById('admissionChart');
	if (admissionCtx) {
		if (admissionCtx.chart) {
			admissionCtx.chart.destroy();
		}
		admissionCtx.chart = new Chart(admissionCtx, {
			type: 'bar',
			data: {
				labels: admissionLabels,
				datasets: [{
					label: 'Applications',
					data: admissionCounts,
					backgroundColor: '#667eea'
				}]
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true
					}
				}
			}
		});
	}

	// Program Enrollment Chart - PIE CHART
	const programData = window.dashboardData.by_program || [];
	const programLabels = programData.map(p => p.code); // Only code
	const programCounts = programData.map(p => p.count);

	const programCtx = document.getElementById('programChart');
	if (programCtx) {
		if (programCtx.chart) {
			programCtx.chart.destroy();
		}
		programCtx.chart = new Chart(programCtx, {
			type: 'pie',
			data: {
				labels: programLabels,
				datasets: [{
					data: programCounts,
					backgroundColor: [
						'#667eea',
						'#764ba2',
						'#f093fb',
						'#4facfe',
						'#00f2fe',
						'#ff6b6b',
						'#ffc107',
						'#4caf50'
					]
				}]
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							font: {
								size: 13,
								weight: 500
							},
							padding: 15
						}
					},
					tooltip: {
						backgroundColor: 'rgba(0,0,0,0.8)',
						padding: 12,
						titleFont: {
							size: 13,
							weight: 'bold'
						},
						bodyFont: {
							size: 12
						},
						callbacks: {
							title: function(context) {
								const dataIndex = context[0].dataIndex;
								const courseData = programData[dataIndex];
								return courseData.name;
							},
							label: function(context) {
								return 'Admissions: ' + context.parsed;
							}
						}
					}
				}
			}
		});
	}

	// Year Level Chart
	const yearLevelData = window.dashboardData.by_year_level || [];
	const yearLevelLabels = yearLevelData.map(y => y.level);
	const yearLevelCounts = yearLevelData.map(y => y.count);

	const yearLevelCtx = document.getElementById('yearLevelChart');
	if (yearLevelCtx) {
		if (yearLevelCtx.chart) {
			yearLevelCtx.chart.destroy();
		}
		yearLevelCtx.chart = new Chart(yearLevelCtx, {
			type: 'line',
			data: {
				labels: yearLevelLabels,
				datasets: [{
					label: 'Applications',
					data: yearLevelCounts,
					borderColor: '#667eea',
					backgroundColor: 'rgba(102, 126, 234, 0.1)',
					borderWidth: 3,
					fill: true,
					tension: 0.4,
					pointBackgroundColor: '#667eea',
					pointBorderColor: '#fff',
					pointBorderWidth: 2,
					pointRadius: 6,
					pointHoverRadius: 8
				}]
			},
			options: {
				responsive: true,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						max: 10,
						ticks: {
							stepSize: 1
						}
					}
				}
			}
		});
	}
}
