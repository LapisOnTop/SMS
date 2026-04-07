(function () {
	'use strict';
	
	const enrolledCount = document.getElementById('enrolledCount');
	const activeCount = document.getElementById('activeCount');
	const inactiveCount = document.getElementById('inactiveCount');
	const pieChart = document.getElementById('pieChart');
	const statusCountBody = document.getElementById('statusCountBody');
	const studentsBody = document.getElementById('studentsBody');
	const studentSearch = document.getElementById('studentSearch');

	if (!studentsBody || !pieChart) {
		return;
	}

	let students = [];
	const statusOptions = ['active', 'inactive', 'on_leave', 'graduated', 'dropped', 'irregular'];

	/**
	 * Fetch students from API
	 */
	function loadStudents() {
		fetch('../../api/students.php?action=list', {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json'
			},
			credentials: 'same-origin'
		})
		.then(response => response.json())
		.then(data => {
			if (!data || !data.success) {
				console.error('students list failed:', data);
				alert((data && (data.message || data.error)) || 'Could not load students list');
				return;
			}
			if (data.success) {
				students = data.data.map(student => ({
					name: String(student.fullName || student.full_name || '').trim() || 'Student',
					id: '#' + String(student.studentNumber || student.student_number || student.id),
					program: student.program || student.program_name || student.program_id || 'N/A',
					year: student.yearLevel || student.year_level || '1st Year',
					status: String(student.status || 'Active').toLowerCase().replace(/\s+/g, '_'),
					studentId: String(student.id)
				}));
				render(students, studentSearch.value);
				fetchStatistics();
			}
		})
		.catch(error => {
			console.error('Error loading students:', error);
		});
	}

	/**
	 * Fetch statistics from API
	 */
	function fetchStatistics() {
		fetch('../../api/student-status.php?action=statistics', {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json'
			},
			credentials: 'same-origin'
		})
		.then(response => response.json())
		.then(data => {
			if (data.success) {
				const stats = data.data;
				enrolledCount.textContent = stats.total_students;
				activeCount.textContent = stats.active_students;
				inactiveCount.textContent = stats.inactive_students;

				const activePercent = stats.total_students ? Math.round((stats.active_students / stats.total_students) * 100) : 0;
				pieChart.style.background = 'conic-gradient(#6f58ff 0 ' + activePercent + '%, #b8c3d4 ' + activePercent + '% 100%)';

				// Render status counts
				statusCountBody.innerHTML = '';
				Object.keys(stats.by_status).forEach(status => {
					const tr = document.createElement('tr');
					tr.innerHTML = '<td>' + status + '</td><td>' + stats.by_status[status] + '</td>';
					statusCountBody.appendChild(tr);
				});
			}
		})
		.catch(error => {
			console.error('Error loading statistics:', error);
		});
	}

	function buildStatusSelect(selected, rowIndex, studentId) {
		const select = document.createElement('select');
		select.className = 'status-select';
		statusOptions.forEach(function (option) {
			const opt = document.createElement('option');
			opt.value = option;
			opt.textContent = option.charAt(0).toUpperCase() + option.slice(1).replace('_', ' ');
			if (option === selected) {
				opt.selected = true;
			}
			select.appendChild(opt);
		});

		select.addEventListener('change', function () {
			const newStatus = select.value;
			
			// Update in API
			fetch('../../api/student-status.php?action=update&student_id=' + encodeURIComponent(studentId), {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json'
				},
				credentials: 'same-origin',
				body: JSON.stringify({
					new_status: newStatus,
					reason: 'Manual status update by registrar'
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					students[rowIndex].status = newStatus;
					render(students, studentSearch.value);
					loadStudents(); // Reload to update stats
				}
			})
			.catch(error => {
				console.error('Error updating status:', error);
				alert('Failed to update status');
				// Revert the select value
				select.value = selected;
			});
		});

		return select;
	}

	function render(items, query) {
		const term = String(query || '').toLowerCase().trim();
		const filtered = items.filter(function (student) {
			return !term || student.name.toLowerCase().includes(term) || student.id.toLowerCase().includes(term);
		});

		studentsBody.innerHTML = '';
		filtered.forEach(function (student) {
			const originalIndex = students.findIndex(function (entry) {
				return entry.id === student.id;
			});

			const tr = document.createElement('tr');
			const select = buildStatusSelect(student.status, originalIndex, student.studentId);

			tr.innerHTML =
				'<td><div class="student-cell"><span class="student-avatar"><i class="fa-solid fa-user"></i></span>' + student.name + '</div></td>' +
				'<td>' + student.id + '</td>' +
				'<td>' + student.program + '</td>' +
				'<td>' + student.year + '</td>';

			const statusCell = document.createElement('td');
			statusCell.appendChild(select);
			tr.appendChild(statusCell);

			studentsBody.appendChild(tr);
		});

		if (filtered.length === 0) {
			const tr = document.createElement('tr');
			tr.innerHTML = '<td colspan="5" style="text-align: center;">No students found</td>';
			studentsBody.appendChild(tr);
		}
	}

	if (studentSearch) {
		studentSearch.addEventListener('input', function () {
			render(students, studentSearch.value);
		});
	}

	// Load data on initialization
	loadStudents();
})();
