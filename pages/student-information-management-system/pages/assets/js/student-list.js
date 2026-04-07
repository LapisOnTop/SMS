(function () {
	const studentsBody = document.getElementById('studentsBody');
	const studentSearch = document.getElementById('studentSearch');

	if (!studentsBody || !studentSearch) {
		return;
	}

	let students = [];

	function statusClass(status) {
		const value = String(status || '').toLowerCase().replace(/\s+/g, '-');
		if (value === 'active') return 'status-pill active';
		if (value === 'inactive') return 'status-pill inactive';
		return 'status-pill on-leave';
	}

	function displayStatus(s) {
		return String(s || '')
			.replace(/_/g, ' ')
			.replace(/\b\w/g, function (c) {
				return c.toUpperCase();
			});
	}

	function renderRows(query) {
		const term = String(query || '').toLowerCase().trim();
		const filtered = students.filter(function (student) {
			return (
				!term ||
				student.name.toLowerCase().includes(term) ||
				student.id.toLowerCase().includes(term)
			);
		});

		studentsBody.innerHTML = '';
		filtered.forEach(function (student) {
			const row = document.createElement('tr');
			const avatarHtml = student.photoUrl
				? '<img src="' + student.photoUrl + '" alt="Photo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;" />'
				: '<i class="fa-solid fa-user"></i>';
			row.innerHTML =
				'<td><div class="student-cell"><span class="student-avatar">' + avatarHtml + '</span>' +
				student.name +
				'</div></td>' +
				'<td>' +
				student.id +
				'</td>' +
				'<td>' +
				student.program +
				'</td>' +
				'<td>' +
				student.year +
				'</td>' +
				'<td><span class="' +
				statusClass(student.status) +
				'">' +
				displayStatus(student.status) +
				'</span></td>' +
				'<td><div class="actions"><button type="button" class="action-btn" title="Set active" data-sid="' +
				student.rawId +
				'"><i class="fa-solid fa-user-check"></i></button></div></td>';
			studentsBody.appendChild(row);
		});

		studentsBody.querySelectorAll('[data-sid]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				const sid = btn.getAttribute('data-sid');
				fetch('../../api/active_student.php', {
					method: 'POST',
					headers: { 'Content-Type': 'application/json' },
					credentials: 'same-origin',
					body: JSON.stringify({ studentId: sid })
				})
					.then(function (r) {
						return r.json();
					})
					.then(function (j) {
						if (j.ok) {
							alert('Active student set for student-facing SIM pages.');
						} else {
							alert(j.error || 'Could not set active student');
						}
					})
					.catch(function () {
						alert('Request failed');
					});
			});
		});

		if (!filtered.length) {
			const empty = document.createElement('tr');
			empty.innerHTML = '<td colspan="6">No students found.</td>';
			studentsBody.appendChild(empty);
		}
	}

	function load() {
		fetch('../../api/students.php?action=list', {
			method: 'GET',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/json' }
		})
			.then(function (r) {
				return r.json();
			})
			.then(function (data) {
				if (!data.success || !Array.isArray(data.data)) {
					console.error('students list failed:', data);
					alert((data && (data.message || data.error)) || 'Could not load students list');
					students = [];
					renderRows(studentSearch.value);
					return;
				}
				students = data.data.map(function (row) {
					return {
						name: String(row.fullName || row.full_name || '').trim() || 'Student',
						id: '#' + String(row.studentNumber || row.student_number || row.id),
						rawId: String(row.id),
						program: row.program || row.program_name || row.program_id || 'N/A',
						year: row.yearLevel || row.year_level || '—',
						status: row.status || 'Active',
						photoUrl: row.photoUrl || null
					};
				});
				renderRows(studentSearch.value);
			})
			.catch(function () {
				students = [];
				renderRows(studentSearch.value);
			});
	}

	studentSearch.addEventListener('input', function () {
		renderRows(studentSearch.value);
	});

	load();
})();
