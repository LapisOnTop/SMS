(function () {
	'use strict';

	const yearTabs = document.querySelectorAll('.year-tab');
	const semesterSelect = document.getElementById('semesterSelect');
	const recordsBody = document.getElementById('recordsBody');
	const totalUnits = document.getElementById('totalUnits');
	const semesterGpa = document.getElementById('semesterGpa');
	const totalEarned = document.getElementById('totalEarned');
	const overallGpa = document.getElementById('overallGpa');

	if (!yearTabs.length || !semesterSelect || !recordsBody) {
		return;
	}

	let allRecords = [];
	let academicYears = new Set();
	let activeYear = null;

	function flattenRepoRecords(ar) {
		const rows = [];
		if (!ar || typeof ar !== 'object') {
			return rows;
		}
		Object.keys(ar).forEach(function (year) {
			const semObj = ar[year];
			if (!semObj || typeof semObj !== 'object') {
				return;
			}
			Object.keys(semObj).forEach(function (sem) {
				const list = semObj[sem];
				if (!Array.isArray(list)) {
					return;
				}
				list.forEach(function (entry) {
					rows.push({
						academic_year: year,
						semester: sem,
						subject_code: entry.code || '',
						subject_name: entry.name || '',
						units: entry.units != null ? Number(entry.units) : 0,
						final_grade: entry.grade != null && entry.grade !== '' ? Number(entry.grade) : null,
						status: 'completed'
					});
				});
			});
		});
		return rows;
	}

	function renderStudentHeader(s) {
		const nameEl = document.getElementById('arStudentName');
		const idEl = document.getElementById('arStudentId');
		const courseEl = document.getElementById('arStudentCourse');
		const extraEl = document.getElementById('arStudentExtra');
		const wrap = document.getElementById('studentPhotoWrap');
		if (nameEl) nameEl.textContent = s.fullName || '—';
		if (idEl) idEl.textContent = s.id ? '#' + s.id : '—';
		if (courseEl) {
			courseEl.innerHTML =
				(s.program || 'Program') +
				' <span>&bull;</span> ' +
				(s.yearLevel || 'Year') +
				' <span class="status-active"><i class="fa-solid fa-check"></i> ' +
				(s.status || 'Active') +
				'</span>';
		}
		if (extraEl) {
			extraEl.innerHTML =
				'<i class="fa-regular fa-calendar"></i> Birthdate: ' +
				(s.birthdate || '—') +
				' <span><i class="fa-solid fa-phone"></i> ' +
				(s.contactNumber || '—') +
				'</span> <span><i class="fa-regular fa-envelope"></i> ' +
				(s.email || '—') +
				'</span>';
		}
		if (wrap && s.photoUrl) {
			wrap.innerHTML = '<img src="' + String(s.photoUrl).replace(/"/g, '&quot;') + '" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">';
		}
	}

	function hydrateFromSim() {
		if (typeof window.StudentDB === 'undefined') {
			return false;
		}
		window.StudentDB.init();
		const s = window.StudentDB.getActive();
		if (!s) {
			recordsBody.innerHTML =
				'<tr><td colspan="4">No active SIM student record. Register via <strong>Student Profile Registration</strong> (registrar) or ensure your account email matches a row in <code>sms_db.students</code>.</td></tr>';
			return true;
		}
		renderStudentHeader(s);
		allRecords = flattenRepoRecords(s.academicRecords);
		academicYears = new Set();
		allRecords.forEach(function (r) {
			academicYears.add(r.academic_year);
		});
		const yearsArray = Array.from(academicYears).sort(function (a, b) {
			return b.localeCompare(a);
		});
		if (yearsArray.length > 0) {
			activeYear = yearsArray[0];
			yearTabs.forEach(function (t) {
				t.classList.toggle('active', t.getAttribute('data-year') === activeYear);
			});
			renderRecords();
			if (overallGpa) {
				overallGpa.textContent = computeOverallGpa(allRecords);
			}
		} else {
			recordsBody.innerHTML = '<tr><td colspan="4">No academic records in repository.</td></tr>';
		}
		return true;
	}

	function getStudentId() {
		const params = new URLSearchParams(window.location.search);
		const studentIdParam = params.get('student_id');
		if (studentIdParam) return studentIdParam;
		const element = document.querySelector('[data-student-id]');
		return element ? element.getAttribute('data-student-id') : null;
	}

	function loadAcademicRecordsLegacy() {
		const studentId = getStudentId();
		if (!studentId) {
			recordsBody.innerHTML =
				'<tr><td colspan="4">No student id in URL and no SIM repository data.</td></tr>';
			return;
		}

		fetch('../../api/academic-records.php?action=list&student_id=' + studentId, {
			method: 'GET',
			headers: { 'Content-Type': 'application/json' }
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success) {
					allRecords = data.data;
					academicYears = new Set();
					allRecords.forEach(function (record) {
						academicYears.add(record.academic_year);
					});
					const yearsArray = Array.from(academicYears).sort(function (a, b) {
						return b.localeCompare(a);
					});
					if (yearsArray.length > 0) {
						activeYear = yearsArray[0];
						renderRecords();
						loadOverallGPA(studentId);
					} else {
						recordsBody.innerHTML = '<tr><td colspan="4">No academic records found</td></tr>';
					}
				} else {
					recordsBody.innerHTML =
						'<tr><td colspan="4">Error loading records: ' + (data.message || '') + '</td></tr>';
				}
			})
			.catch(function () {
				recordsBody.innerHTML = '<tr><td colspan="4">Error loading records</td></tr>';
			});
	}

	function loadOverallGPA(studentId) {
		fetch('../../api/academic-records.php?action=gpa&student_id=' + studentId, {
			method: 'GET',
			headers: { 'Content-Type': 'application/json' }
		})
			.then(function (response) {
				return response.json();
			})
			.then(function (data) {
				if (data.success && overallGpa) {
					overallGpa.textContent = Number(data.data.gpa).toFixed(2);
				}
			})
			.catch(function () {});
	}

	function computeGpa(records) {
		const graded = records.filter(function (record) {
			return record.final_grade !== null && record.final_grade !== 'N/A' && !isNaN(Number(record.final_grade));
		});
		if (!graded.length) {
			return '0.00';
		}
		const total = graded.reduce(function (sum, record) {
			return sum + Number(record.final_grade);
		}, 0);
		return (total / graded.length).toFixed(2);
	}

	function computeOverallGpa(records) {
		return computeGpa(records);
	}

	function renderRecords() {
		const semester = semesterSelect.value;
		const yearRecords = allRecords.filter(function (record) {
			return record.academic_year == activeYear;
		});
		let records = yearRecords;
		if (semester) {
			records = yearRecords.filter(function (record) {
				return record.semester == semester;
			});
		}

		recordsBody.innerHTML = '';

		if (records.length === 0) {
			recordsBody.innerHTML =
				'<tr><td colspan="4" style="text-align: center;">No records for this period</td></tr>';
		} else {
			records.forEach(function (record) {
				const row = document.createElement('tr');
				const grade = record.final_grade != null ? record.final_grade : 'N/A';
				row.innerHTML =
					'<td>' +
					(record.subject_code ? record.subject_code + ' - ' : '') +
					(record.subject_name || '') +
					'</td>' +
					'<td>' +
					(record.units || 'N/A') +
					'</td>' +
					'<td>' +
					grade +
					'</td>' +
					'<td><i class="fa-solid fa-magnifying-glass table-icon"></i></td>';
				recordsBody.appendChild(row);
			});
		}

		const units = records.reduce(function (sum, record) {
			return sum + (record.units || 0);
		}, 0);
		const gpa = computeGpa(records);
		totalUnits.textContent = units;
		semesterGpa.textContent = gpa;

		const earnedUnits = allRecords
			.filter(function (record) {
				return record.final_grade !== null && record.status === 'completed';
			})
			.reduce(function (sum, record) {
				return sum + (record.units || 0);
			}, 0);
		if (totalEarned) totalEarned.textContent = earnedUnits;
	}

	yearTabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			yearTabs.forEach(function (item) {
				item.classList.remove('active');
			});
			this.classList.add('active');
			activeYear = this.dataset.year;
			renderRecords();
		});
	});

	semesterSelect.addEventListener('change', renderRecords);

	if (!hydrateFromSim()) {
		loadAcademicRecordsLegacy();
	}
})();
