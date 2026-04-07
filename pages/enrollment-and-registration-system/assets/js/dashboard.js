// Switch between sections without page reload
function switchSection(sectionId, evt) {
	if (evt) {
		evt.preventDefault();
		evt.stopPropagation();
	}
	
	console.log('Switching to section:', sectionId);
	
	// Hide all sections
	const sections = document.querySelectorAll('.page-section');
	sections.forEach(sec => sec.classList.remove('active'));

	// Show selected section
	const targetSection = document.getElementById(sectionId);
	if (targetSection) {
		targetSection.classList.add('active');
		console.log('Section shown:', sectionId);
	}

	// Update active nav item
	const navItems = document.querySelectorAll('.nav-item');
	navItems.forEach(item => item.classList.remove('active'));
	
	// Find and activate the corresponding nav item
	const activeNav = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
	if (activeNav) {
		activeNav.classList.add('active');
	}

	// Save active section to URL hash
	window.location.hash = sectionId;

	// Initialize charts if in reports section
	if (sectionId === 'reports') {
		setTimeout(initializeCharts, 100);
	}
	
	// Re-setup search listeners if validation or enrollment-status section
	if (sectionId === 'validation' || sectionId === 'enrollment-status') {
		setTimeout(setupSearchListeners, 50);
	}
}

// AJAX-based filtering — stays on the validation tab
function filterApplications() {
	const statusFilter = document.getElementById('statusFilter');
	const searchInput = document.getElementById('searchInput');
	
	// Return early if elements don't exist
	if (!searchInput) {
		console.log('Search input not found');
		return;
	}
	
	const status = statusFilter ? statusFilter.value : 'Pending';
	const search = searchInput.value.trim();

	console.log('Filter called - Status:', status, 'Search:', search);

	// Show loading in table
	const tableContainer = document.querySelector('#validation .table-container');
	if (tableContainer) {
		tableContainer.innerHTML = '<div class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i><p>Loading results...</p></div>';
	}

	const apiUrl = '../../api/get-admissions.php?status=' + encodeURIComponent(status) + '&search=' + encodeURIComponent(search);
	console.log('Fetching from:', apiUrl);

	fetch(apiUrl)
	.then(response => {
		console.log('Response status:', response.status, response.ok);
		if (!response.ok) throw new Error('HTTP Error: ' + response.status);
		return response.text();
	})
	.then(data => {
		console.log('Response data:', data.substring(0, 200));
		try {
			const jsonData = JSON.parse(data);
			console.log('Parsed JSON:', jsonData);
			if (jsonData.ok && jsonData.admissions && jsonData.admissions.length > 0) {
				let html = '<table><thead><tr>';
				html += '<th>Reference #</th><th>Name</th><th>Email</th><th>Contact</th><th>Admission Type</th>';
				html += '<th>Status</th><th>Action</th></tr></thead><tbody>';

				jsonData.admissions.forEach(a => {
					const fullName = escapeHtml(a.first_name + ' ' + a.last_name);
					const statusClass = 'status-' + (a.admission_status || '').toLowerCase().replace(/\s+/g, '-');
					const admissionTypeClass = 'admission-' + (a.admission_type || '').toLowerCase().replace(/\s+/g, '-');

					html += '<tr>';
					html += '<td><strong>' + escapeHtml(a.reference_number || 'N/A') + '</strong></td>';
					html += '<td>' + fullName + '</td>';
					html += '<td>' + escapeHtml(a.email_address || '') + '</td>';
					html += '<td>' + escapeHtml(a.contact_number || '') + '</td>';
					html += '<td><span class="admission-badge ' + admissionTypeClass + '">' + escapeHtml(a.admission_type || '') + '</span></td>';
					html += '<td><span class="status-badge ' + statusClass + '">' + escapeHtml(a.admission_status || '') + '</span></td>';
					html += '<td><div class="action-icons">';
					html += '<button class="action-icon-btn validate-loads-btn" title="Validate Subject Loads" onclick="openLoadValidationModal(' + a.application_id + ', \'' + escapeHtml(a.first_name + ' ' + a.last_name).replace(/'/g, "\\'") + '\')"><i class="fa-solid fa-book"></i></button>';
					html += '<button class="action-icon-btn delete-btn" title="Delete Application" onclick="openDeleteModal(' + a.application_id + ', \'' + escapeHtml(a.first_name + ' ' + a.last_name).replace(/'/g, "\\'") + '\')"><i class="fa-solid fa-trash"></i></button>';
					html += '</div></td>';
					html += '</tr>';
				});

				html += '</tbody></table>';
				tableContainer.innerHTML = html;
			} else {
				tableContainer.innerHTML = '<div class="empty-state"><i class="fa-solid fa-inbox"></i><p><strong>No admissions found</strong></p><p style="font-size:14px;">Try adjusting your filters or search terms.</p></div>';
			}
		} catch(e) {
			console.error('Parse error:', e, 'Data:', data);
			tableContainer.innerHTML = '<div class="empty-state"><i class="fa-solid fa-exclamation-circle"></i><p><strong>Error loading results</strong></p><p style="font-size:14px;">Check console for details.</p></div>';
		}
	})
	.catch(error => {
		console.error('Fetch error:', error);
		if (tableContainer) {
			tableContainer.innerHTML = '<div class="empty-state"><i class="fa-solid fa-exclamation-circle"></i><p><strong>Error loading results</strong></p><p style="font-size:14px;">' + error.message + '</p></div>';
		}
	});
}

function escapeHtml(text) {
	if (!text) return '';
	const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'};
	return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Filter enrollment status records by search term
 */
function filterEnrollmentStatus() {
	const searchInput = document.getElementById('enrollmentSearchInput');
	const statusFilter = document.getElementById('enrollmentStatusFilter');
	
	// Return early if search input doesn't exist
	if (!searchInput) {
		console.log('Enrollment search input not found');
		return;
	}
	
	const searchTerm = searchInput.value.trim().toLowerCase();
	const selectedStatus = statusFilter ? statusFilter.value : 'All';
	
	const tableBody = document.querySelector('#enrollment-status table tbody');
	if (!tableBody) return;
	
	const rows = tableBody.querySelectorAll('tr');
	let visibleCount = 0;
	
	rows.forEach(row => {
		// Get all cell text content and search through it
		const cells = row.querySelectorAll('td');
		let rowText = '';
		cells.forEach(cell => {
			rowText += cell.textContent.toLowerCase() + ' ';
		});
		const statusBadge = row.querySelector('.status-badge');
		const rowStatus = statusBadge ? statusBadge.textContent.trim() : '';
		const statusMatches = selectedStatus === 'All' || rowStatus === selectedStatus;
		
		// Show/hide row based on search match
		if ((searchTerm === '' || rowText.includes(searchTerm)) && statusMatches) {
			row.style.display = '';
			visibleCount++;
		} else {
			row.style.display = 'none';
		}
	});
	
	// Show empty state if no results
	const tableContainer = document.querySelector('#enrollment-status .table-container');
	if (visibleCount === 0 && searchTerm !== '') {
		if (tableContainer) {
			const existingEmpty = tableContainer.querySelector('.search-empty');
			if (!existingEmpty) {
				const emptyDiv = document.createElement('div');
				emptyDiv.className = 'empty-state search-empty';
				emptyDiv.innerHTML = '<i class="fa-solid fa-search"></i><p><strong>No results found</strong></p><p style="font-size: 14px;">Try adjusting your search terms.</p>';
				tableContainer.appendChild(emptyDiv);
			}
		}
	} else {
		const searchEmpty = tableContainer ? tableContainer.querySelector('.search-empty') : null;
		if (searchEmpty) {
			searchEmpty.remove();
		}
	}
}

function initializeCharts() {
	// Get chart data from data attributes or window object
	const dashboardData = window.dashboardData || {};
	
	// Status Chart
	const statusData = dashboardData.by_status || [];
	const statusLabels = statusData.map(s => s.status);
	const statusCounts = statusData.map(s => s.count);

	const statusCtx = document.getElementById('statusChart');
	if (statusCtx && !statusCtx.chart) {
		statusCtx.chart = new Chart(statusCtx, {
			type: 'doughnut',
			data: {
				labels: statusLabels,
				datasets: [{
					data: statusCounts,
					backgroundColor: [
						'#fff3e0',
						'#f3e5f5',
						'#e8f5e9',
						'#e0f2f1'
					],
					borderColor: [
						'#f57c00',
						'#7b1fa2',
						'#388e3c',
						'#00796b'
					],
					borderWidth: 2
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
	const admissionData = dashboardData.by_admission_type || [];
	const admissionLabels = admissionData.map(a => a.type);
	const admissionCounts = admissionData.map(a => a.count);

	const admissionCtx = document.getElementById('admissionChart');
	if (admissionCtx && !admissionCtx.chart) {
		admissionCtx.chart = new Chart(admissionCtx, {
			type: 'doughnut',
			data: {
				labels: admissionLabels,
				datasets: [{
					data: admissionCounts,
					backgroundColor: [
						'#fff3e0',
						'#f3e5f5',
						'#e8f5e9',
						'#e0f2f1',
						'#fce4ec'
					],
					borderColor: [
						'#f57c00',
						'#7b1fa2',
						'#388e3c',
						'#00796b',
						'#c2185b'
					],
					borderWidth: 2
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

	// Program Chart
	const programData = dashboardData.by_program || [];
	const programLabels = programData.map(p => p.name);
	const programCounts = programData.map(p => p.count);

	const programCtx = document.getElementById('programChart');
	if (programCtx && !programCtx.chart) {
		programCtx.chart = new Chart(programCtx, {
			type: 'bar',
			data: {
				labels: programLabels,
				datasets: [{
					label: 'Admissions',
					data: programCounts,
					backgroundColor: [
						'#3f69ff',
						'#1e2532',
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
						display: false
					}
				},
				scales: {
					x: {
						ticks: {
							callback: function(value) {
								const label = this.getLabelForValue(value) || '';
								if (label.length <= 22) {
									return label;
								}

								const words = label.split(' ');
								const lines = [];
								let line = '';

								words.forEach(function(word) {
									const next = line ? line + ' ' + word : word;
									if (next.length > 22) {
										if (line) {
											lines.push(line);
										}
										line = word;
									} else {
										line = next;
									}
								});

								if (line) {
									lines.push(line);
								}

								return lines;
							}
						}
					},
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

	// Year Level Chart
	const yearLevelData = dashboardData.by_year_level || [];
	const yearLevelLabels = yearLevelData.map(y => y.level);
	const yearLevelCounts = yearLevelData.map(y => y.count);

	const yearLevelCtx = document.getElementById('yearLevelChart');
	if (yearLevelCtx && !yearLevelCtx.chart) {
		yearLevelCtx.chart = new Chart(yearLevelCtx, {
			type: 'line',
			data: {
				labels: yearLevelLabels,
				datasets: [{
					label: 'Admissions',
					data: yearLevelCounts,
					borderColor: '#667eea',
					backgroundColor: 'rgba(102, 126, 234, 0.1)',
					borderWidth: 2,
					fill: true,
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
						beginAtZero: true
					}
				}
			}
		});
	}
}

// Initialize on page load and restore active section from hash
let searchFilterTimeout;

window.addEventListener('load', function() {
	console.log('Dashboard JS loaded');
	initializeCharts();
	
	// Restore section from URL hash (e.g., #validation)
	const hash = window.location.hash.replace('#', '');
	if (hash === 'validation' || hash === 'reports') {
		switchSection(hash);
	}

	// Setup event listeners for validation search
	setupSearchListeners();
});

function setupSearchListeners() {
	const searchInput = document.getElementById('searchInput');
	const statusFilter = document.getElementById('statusFilter');
	
	console.log('Setup search listeners - searchInput:', !!searchInput, 'statusFilter:', !!statusFilter);
	
	if (searchInput) {
		// Enter key triggers immediate search
		searchInput.addEventListener('keypress', function(e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				console.log('Enter key pressed');
				filterApplications();
			}
		});
		
		// Live search with debounce on typing
		searchInput.addEventListener('keyup', function() {
			console.log('Search input changed:', this.value);
			clearTimeout(searchFilterTimeout);
			searchFilterTimeout = setTimeout(filterApplications, 300);
		});
	}

	if (statusFilter) {
		// Immediate filter on status change
		statusFilter.addEventListener('change', function() {
			console.log('Status filter changed:', this.value);
			filterApplications();
		});
	}

	// Setup enrollment status search
	const enrollmentSearchInput = document.getElementById('enrollmentSearchInput');
	const enrollmentStatusFilter = document.getElementById('enrollmentStatusFilter');
	if (enrollmentSearchInput) {
		// Enter key triggers immediate search
		enrollmentSearchInput.addEventListener('keypress', function(e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				console.log('Enrollment search enter key pressed');
				filterEnrollmentStatus();
			}
		});
		
		// Live search with debounce on typing
		enrollmentSearchInput.addEventListener('keyup', function() {
			console.log('Enrollment search input changed:', this.value);
			clearTimeout(searchFilterTimeout);
			searchFilterTimeout = setTimeout(filterEnrollmentStatus, 300);
		});
	}

	if (enrollmentStatusFilter) {
		enrollmentStatusFilter.addEventListener('change', function() {
			console.log('Enrollment status filter changed:', this.value);
			filterEnrollmentStatus();
		});
	}
}
