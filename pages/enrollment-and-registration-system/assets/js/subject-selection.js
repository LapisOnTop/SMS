(function() {
	'use strict';

	// State
	let curriculum = null;
	const selectedSubjects = new Map();

	// DOM Elements
	const yearSelect = document.getElementById('yearSelect');
	const semesterSelect = document.getElementById('semesterSelect');
	const subjectsContainer = document.getElementById('subjectsListContainer');
	const totalCreditsDisplay = document.getElementById('totalCredits');
	const selectionSummary = document.getElementById('selectionSummary');
	const summaryTableBody = document.getElementById('summaryTableBody');
	const summaryTotalUnits = document.getElementById('summaryTotalUnits');
	const warningContainer = document.getElementById('warningContainer');
	const proceedBtn = document.getElementById('proceedBtn');

	/**
	 * Fetch curriculum data from API
	 */
	async function fetchCurriculum() {
		try {
			const response = await fetch(window.subjectsApiUrl);
			const data = await response.json();

			if (!data.success) {
				showMessage('Failed to load subjects from database', 'error');
				return false;
			}

			curriculum = data.curriculum;
			return true;
		} catch (error) {
			console.error('Error fetching curriculum:', error);
			showMessage('Error loading subjects. Please refresh the page.', 'error');
			return false;
		}
	}

	/**
	 * Initialize the subject selection page
	 */
	async function init() {
		const loaded = await fetchCurriculum();
		if (loaded) {
			loadSubjects();
			setupEventListeners();
		}
	}

	/**
	 * Setup event listeners
	 */
	function setupEventListeners() {
		yearSelect.addEventListener('change', loadSubjects);
		semesterSelect.addEventListener('change', loadSubjects);
		proceedBtn.addEventListener('click', handleProceed);
	}

	/**
	 * Load and display subjects based on selected year and semester
	 */
	function loadSubjects() {
		const year = yearSelect.value;
		const semester = semesterSelect.value;

		// Clear previous subjects
		subjectsContainer.innerHTML = '';
		warningContainer.innerHTML = '';

		// Get subjects for this year and semester
		const yearSubjects = curriculum[year];
		if (!yearSubjects) {
			subjectsContainer.innerHTML = '<div class="empty-subjects"><i class="fa-solid fa-inbox"></i><p>No subjects available</p></div>';
			return;
		}

		const semesterSubjects = yearSubjects[semester];
		if (!semesterSubjects || semesterSubjects.length === 0) {
			subjectsContainer.innerHTML = '<div class="empty-subjects"><i class="fa-solid fa-inbox"></i><p>No subjects available for this semester</p></div>';
			return;
		}

		// Create and render subject list
		const subjectsList = document.createElement('div');
		subjectsList.className = 'subjects-list';

		semesterSubjects.forEach(subject => {
			const subjectItem = createSubjectElement(subject, year, semester);
			subjectsList.appendChild(subjectItem);
		});

		subjectsContainer.appendChild(subjectsList);
		updateDisplay();
	}

	/**
	 * Create a subject item element
	 */
	function createSubjectElement(subject, year, semester) {
		const container = document.createElement('label');
		container.className = 'subject-item';

		const checkboxId = `${year}-${semester}-${subject.code}`;
		const isSelected = selectedSubjects.has(checkboxId);

		// Build prerequisite display
		const hasPrerequisites = subject.prerequisites && subject.prerequisites.length > 0;
		const prerequisiteDisplay = hasPrerequisites 
			? subject.prerequisites.map(p => `${p.code}`).join(', ')
			: 'None';

		container.innerHTML = `
			<input 
				type="checkbox" 
				id="${checkboxId}"
				class="subject-checkbox"
				data-code="${subject.code}"
				data-year="${year}"
				data-semester="${semester}"
				data-units="${subject.units}"
				data-hours="${subject.hours}"
				data-prerequisites='${JSON.stringify(subject.prerequisites || [])}'
				data-description="${subject.description}"
				${isSelected ? 'checked' : ''}
			>
			<div class="subject-details">
				<div class="subject-title">
					<span class="subject-code">${subject.code}</span>
					${subject.description}
				</div>
				<div class="subject-meta">
					<div class="meta-item">
						<span class="meta-icon"><i class="fa-solid fa-book"></i></span>
						<span>${subject.units} units</span>
					</div>
					<div class="meta-item">
						<span class="meta-icon"><i class="fa-solid fa-clock"></i></span>
						<span>${subject.hours} hours</span>
					</div>
					${hasPrerequisites ? `
						<div class="meta-item prerequisite-info">
							<span class="meta-icon"><i class="fa-solid fa-link"></i></span>
							<span class="prerequisite-text">Prerequisite(s): ${prerequisiteDisplay}</span>
						</div>
					` : ''}
				</div>
			</div>
		`;

		// Add change event listener
		const checkbox = container.querySelector('input[type="checkbox"]');
		checkbox.addEventListener('change', function() {
			handleSubjectSelection(this);
		});

		return container;
	}

	/**
	 * Handle subject selection/deselection
	 */
	function handleSubjectSelection(checkbox) {
		const checkboxId = checkbox.id;
		const prerequisitesJson = checkbox.dataset.prerequisites || '[]';
		const prerequisites = JSON.parse(prerequisitesJson);
		
		const subject = {
			code: checkbox.dataset.code,
			description: checkbox.dataset.description,
			units: parseInt(checkbox.dataset.units),
			hours: parseInt(checkbox.dataset.hours),
			prerequisites: prerequisites,
			year: checkbox.dataset.year,
			semester: checkbox.dataset.semester
		};

		if (checkbox.checked) {
			selectedSubjects.set(checkboxId, subject);
		} else {
			selectedSubjects.delete(checkboxId);
		}

		updateDisplay();
		checkPrerequisites();
	}

	/**
	 * Check prerequisites for selected subjects
	 */
	function checkPrerequisites() {
		const warnings = [];

		selectedSubjects.forEach(subject => {
			if (subject.prerequisites && subject.prerequisites.length > 0) {
				// Check each prerequisite
				subject.prerequisites.forEach(prereq => {
					const prereqSatisfied = Array.from(selectedSubjects.values())
						.some(s => s.code === prereq.code);

					if (!prereqSatisfied) {
						warnings.push({
							type: 'warning',
							message: `<strong>${subject.code}</strong> requires <strong>${prereq.code}</strong> (${prereq.name}) as a prerequisite. Make sure you have completed it or are taking it concurrently.`
						});
					}
				});
			}
		});

		// Display warnings
		warningContainer.innerHTML = '';
		if (warnings.length > 0) {
			warnings.forEach(warning => {
				const msg = createMessageElement(warning.message, warning.type);
				warningContainer.appendChild(msg);
			});
		}
	}

	/**
	 * Update display elements (summary table, totals, etc.)
	 */
	function updateDisplay() {
		const totalUnits = Array.from(selectedSubjects.values())
			.reduce((sum, subject) => sum + subject.units, 0);

		// Update total credits display
		totalCreditsDisplay.textContent = totalUnits;

		// Update summary table
		if (selectedSubjects.size > 0) {
			selectionSummary.style.display = 'flex';
			summaryTableBody.innerHTML = '';

			selectedSubjects.forEach(subject => {
				const row = document.createElement('tr');
				row.innerHTML = `
					<td>${subject.code}</td>
					<td>${subject.units}</td>
					<td>
						<button class="remove-btn" data-code="${subject.code}" type="button">✕</button>
					</td>
				`;

				// Add remove button handler
				row.querySelector('.remove-btn').addEventListener('click', function(e) {
					e.preventDefault();
					const codeToRemove = this.dataset.code;
					const checkbox = document.querySelector(`input[data-code="${codeToRemove}"]:checked`);
					if (checkbox) {
						checkbox.checked = false;
						handleSubjectSelection(checkbox);
					}
				});

				summaryTableBody.appendChild(row);
			});

			summaryTotalUnits.textContent = totalUnits;
		} else {
			selectionSummary.style.display = 'none';
		}

		// Enable/disable proceed button
		proceedBtn.disabled = selectedSubjects.size === 0;

		// Check if total units exceed reasonable limit
		if (totalUnits > 24) {
			showMessage(
				`You have selected ${totalUnits} units. Consider reviewing your selection as this may exceed recommended limits.`,
				'warning'
			);
		}
	}

	/**
	 * Show a message to the user
	 */
	function showMessage(message, type = 'info') {
		const msg = createMessageElement(message, type);
		warningContainer.appendChild(msg);

		// Auto-dismiss info messages after 3 seconds
		if (type === 'info') {
			setTimeout(() => {
				msg.style.opacity = '0';
				msg.style.transition = 'opacity 0.3s ease';
				setTimeout(() => msg.remove(), 300);
			}, 3000);
		}
	}

	/**
	 * Create message element
	 */
	function createMessageElement(message, type) {
		const msg = document.createElement('div');
		msg.className = `warning-message ${type}`;

		let icon = 'fa-info-circle';
		if (type === 'warning') {
			icon = 'fa-exclamation-triangle';
		} else if (type === 'error') {
			icon = 'fa-times-circle';
		}

		msg.innerHTML = `
			<i class="fa-solid ${icon}"></i>
			${message}
		`;

		return msg;
	}

	/**
	 * Handle proceed button click
	 */
	async function handleProceed() {
		if (selectedSubjects.size === 0) {
			showMessage('Please select at least one subject', 'error');
			return;
		}

		// Disable button during validation
		proceedBtn.disabled = true;
		showMessage('Validating your subject selection...', 'info');

		try {
			// Get subject codes
			const subjectCodes = Array.from(selectedSubjects.values())
				.map(s => s.code);

			// Validate subjects with backend
			const response = await fetch('../../api/validate-subjects.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({ subjectCodes: subjectCodes })
			});

			const data = await response.json();

			if (!data.ok) {
				showMessage(data.message || 'Failed to validate subjects', 'error');
				proceedBtn.disabled = false;
				return;
			}

			// Store selected subjects with their IDs and all details in sessionStorage
			const selectedData = Array.from(selectedSubjects.values());
			sessionStorage.setItem('selectedSubjects', JSON.stringify(selectedData));
			sessionStorage.setItem('selectedSubjectIds', JSON.stringify(data.subjectIds));
			sessionStorage.setItem('selectedSubjectDetails', JSON.stringify(data.subjects));

			// Redirect to form page with subjects parameter
			const codes = Array.from(selectedSubjects.values())
				.map(s => s.code)
				.join(',');

			window.location.href = `form.php?subjects=${encodeURIComponent(codes)}`;
		} catch (error) {
			console.error('Error validating subjects:', error);
			showMessage('Error validating subjects. Please try again.', 'error');
			proceedBtn.disabled = false;
		}
	}

	// Initialize when DOM is ready
	document.addEventListener('DOMContentLoaded', init);

	// Expose some functions to global scope if needed for debugging
	window.SubjectSelection = {
		getSelectedSubjects: () => Array.from(selectedSubjects.values()),
		getTotalUnits: () => Array.from(selectedSubjects.values()).reduce((sum, s) => sum + s.units, 0),
		getTotalHours: () => Array.from(selectedSubjects.values()).reduce((sum, s) => sum + s.hours, 0)
	};
})();
