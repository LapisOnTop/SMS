(function () {
	const enrollmentForm = document.getElementById('enrollmentForm');
	const steps = Array.from(document.querySelectorAll('.form-step'));
	const accordionHeaders = Array.from(document.querySelectorAll('[data-accordion-header]'));
	const btnBack = document.getElementById('btnBack');
	const btnNext = document.getElementById('btnNext');
	const btnSubmit = document.getElementById('btnSubmit');
	const statusCloseBtn = document.getElementById('statusCloseBtn');
	const statusDownloadBtn = document.getElementById('statusDownloadBtn');
	const formActions = document.querySelector('.form-actions');
	const summaryContent = document.getElementById('summaryContent');
	const admissionType = document.getElementById('admissionType');
	const birthdate = document.getElementById('birthdate');
	const yearLevel = document.getElementById('yearLevel');
	const region = document.getElementById('region');
	const regionFieldWrap = document.getElementById('regionFieldWrap');
	const cityFieldWrap = document.getElementById('cityFieldWrap');
	const barangayFieldWrap = document.getElementById('barangayFieldWrap');
	const emailField = document.getElementById('email');
	const contactField = document.getElementById('contact');
	const studentNumberFieldWrap = document.getElementById('studentNumberFieldWrap');
	const studentNumberField = document.getElementById('studentNumber');
	const studentNumberLabel = document.getElementById('studentNumberLabel');
	let isSubmitting = false;
	let activeStep = 0;
	let isCheckingDuplicate = false;
	let lastDuplicateCheckKey = '';
	let lastDuplicateCheckResult = true;
	const minimumAge = 18;
	const psgcBaseUrl = 'https://psgc.gitlab.io/api';
	const regionCache = new Map();
	const cityCache = new Map();
	const barangayCache = new Map();

	function setSelectOptions(selectElement, options, placeholderText) {
		selectElement.innerHTML = [`<option value="">${placeholderText}</option>`]
			.concat(options.map((option) => `<option value="${option.value}">${option.label}</option>`))
			.join('');
	}

	async function fetchJson(endpoint) {
		const response = await fetch(`${psgcBaseUrl}${endpoint}`);
		if (!response.ok) {
			throw new Error(`Failed to fetch ${endpoint}`);
		}
		return response.json();
	}

	function normalizeRegionCode(regionCode) {
		return regionCode || '';
	}

	function normalizeCityCode(cityCode) {
		return cityCode || '';
	}

	function enforceRequiredMarkers() {
		const labels = Array.from(enrollmentForm.querySelectorAll('label[for]'));

		labels.forEach((label) => {
			if (!label.textContent.includes('*')) {
				return;
			}

			const fieldId = label.getAttribute('for');
			const field = fieldId ? document.getElementById(fieldId) : null;
			if (!field) {
				return;
			}

			const normalizedLabel = label.textContent.replace('*', '').trim().replace(/\s+/g, ' ');
			if (!field.dataset.required) {
				field.dataset.required = normalizedLabel;
			}

			field.required = !field.disabled;
		});

		Array.from(enrollmentForm.querySelectorAll('[data-required]')).forEach((field) => {
			field.required = !field.disabled;
		});
	}

	async function loadRegions() {
		try {
			const regions = await fetchJson('/regions.json');
			const regionOptions = regions.map((item) => ({
				value: normalizeRegionCode(item.code),
				label: `${item.regionName || item.name}`
			}));
			setSelectOptions(region, regionOptions, 'Select Region');
		} catch (error) {
			setSelectOptions(region, [], 'Unable to load regions');
			region.disabled = true;
		}
	}

	function renderCityField() {
		const selectedRegion = region.value;
		const currentCityValue = document.getElementById('city')?.value || '';

		if (!selectedRegion) {
			cityFieldWrap.innerHTML = `
				<label for="city">Municipality / City *</label>
				<select id="city" name="city" data-required="Municipality / City" disabled>
					<option value="">Select a region first</option>
				</select>
				<p class="field-error"></p>
			`;
			renderBarangayField('');
			enforceRequiredMarkers();
			return;
		}

		loadCities(selectedRegion, currentCityValue);
	}

	async function loadCities(selectedRegion, currentCityValue = '') {
		const cacheKey = selectedRegion;
		try {
			let cities = cityCache.get(cacheKey);
			if (!cities) {
				cities = await fetchJson(`/regions/${selectedRegion}/cities-municipalities.json`);
				cityCache.set(cacheKey, cities);
			}

			cityFieldWrap.innerHTML = `
				<label for="city">Municipality / City *</label>
				<select id="city" name="city" data-required="Municipality / City">
					<option value="">Select Municipality / City</option>
					${cities.map((item) => `<option value="${item.code}">${item.name}</option>`).join('')}
				</select>
				<p class="field-error"></p>
			`;

			const cityControl = document.getElementById('city');
			if (cityControl && currentCityValue) {
				cityControl.value = currentCityValue;
			}

			renderBarangayField(cityControl?.value || '');
			enforceRequiredMarkers();
		} catch (error) {
			cityFieldWrap.innerHTML = `
				<label for="city">Municipality / City *</label>
				<select id="city" name="city" data-required="Municipality / City" disabled>
					<option value="">Unable to load municipalities/cities</option>
				</select>
				<p class="field-error"></p>
			`;
			renderBarangayField('');
			enforceRequiredMarkers();
		}
	}

	function renderBarangayField(selectedCityCode) {
		const currentBarangayValue = document.getElementById('barangay')?.value || '';

		if (!selectedCityCode) {
			barangayFieldWrap.innerHTML = `
				<label for="barangay">Barangay *</label>
				<select id="barangay" name="barangay" data-required="Barangay" disabled>
					<option value="">Select a city first</option>
				</select>
				<p class="field-error"></p>
			`;
			enforceRequiredMarkers();
			return;
		}

		loadBarangays(selectedCityCode, currentBarangayValue);
	}

	async function loadBarangays(selectedCityCode, currentBarangayValue = '') {
		if (!selectedCityCode) {
			barangayFieldWrap.innerHTML = `
				<label for="barangay">Barangay *</label>
				<select id="barangay" name="barangay" data-required="Barangay" disabled>
					<option value="">Select a city first</option>
				</select>
				<p class="field-error"></p>
			`;
			enforceRequiredMarkers();
			return;
		}

		try {
			let barangays = barangayCache.get(selectedCityCode);
			if (!barangays) {
				barangays = await fetchJson(`/cities-municipalities/${selectedCityCode}/barangays.json`);
				barangayCache.set(selectedCityCode, barangays);
			}

			barangayFieldWrap.innerHTML = `
				<label for="barangay">Barangay *</label>
				<select id="barangay" name="barangay" data-required="Barangay">
					<option value="">Select Barangay</option>
					${barangays.map((item) => `<option value="${item.code}">${item.name}</option>`).join('')}
				</select>
				<p class="field-error"></p>
			`;

			const barangayControl = document.getElementById('barangay');
			if (barangayControl && currentBarangayValue) {
				barangayControl.value = currentBarangayValue;
			}
			enforceRequiredMarkers();
		} catch (error) {
			barangayFieldWrap.innerHTML = `
				<label for="barangay">Barangay *</label>
				<select id="barangay" name="barangay" data-required="Barangay" disabled>
					<option value="">Unable to load barangays</option>
				</select>
				<p class="field-error"></p>
			`;
			enforceRequiredMarkers();
		}
	}

	function setYearLevelOptions() {
		const value = admissionType.value;
		const isOldStudent = value === 'Old Student';
		yearLevel.innerHTML = '';

		if (studentNumberFieldWrap && studentNumberField) {
			studentNumberFieldWrap.hidden = !isOldStudent;
			studentNumberField.required = isOldStudent;
			if (isOldStudent) {
				studentNumberField.dataset.required = 'Student Number';
				if (studentNumberLabel) {
					studentNumberLabel.textContent = 'Student Number *';
				}
			} else {
				studentNumberField.value = '';
				delete studentNumberField.dataset.required;
				clearFieldError(studentNumberField);
				if (studentNumberLabel) {
					studentNumberLabel.textContent = 'Student Number';
				}
			}
		}

		if (value === 'New Regular') {
			yearLevel.innerHTML = '<option value="1st Year">1st Year</option>';
			yearLevel.value = '1st Year';
			yearLevel.setAttribute('disabled', 'disabled');
		} else if (value === 'Transferee' || value === 'Old Student') {
			yearLevel.removeAttribute('disabled');
			yearLevel.innerHTML = [
				'<option value="">Select Year Level</option>',
				'<option value="1st Year">1st Year</option>',
				'<option value="2nd Year">2nd Year</option>',
				'<option value="3rd Year">3rd Year</option>',
				'<option value="4th Year">4th Year</option>'
			].join('');
		} else {
			yearLevel.removeAttribute('disabled');
			yearLevel.innerHTML = '<option value="">Select Year Level</option>';
		}

		enforceRequiredMarkers();
	}

	function updateStepView() {
		steps.forEach((step, index) => {
			const isActive = index === activeStep;
			step.classList.toggle('is-active', isActive);
			const body = step.querySelector('.accordion-body');
			if (body) {
				body.hidden = !isActive;
				if (isActive && formActions) {
					body.appendChild(formActions);
				}
			}
		});
		accordionHeaders.forEach((header, index) => {
			header.classList.toggle('is-active', index === activeStep);
		});

		btnBack.classList.toggle('is-hidden', activeStep === 0);
		btnBack.disabled = activeStep === 0;
		btnNext.classList.toggle('is-hidden', activeStep === steps.length - 1);
		btnSubmit.classList.toggle('is-hidden', activeStep !== steps.length - 1);
	}

	function clearFieldError(field) {
		const error = field.closest('.field')?.querySelector('.field-error');
		if (error) {
			error.textContent = '';
		}
		field.classList.remove('has-error');
	}

	function setFieldError(field, label) {
		const error = field.closest('.field')?.querySelector('.field-error');
		if (error) {
			if (field.dataset.validate === 'email') {
				error.textContent = label + ' must be a valid email address';
			} else if (field.dataset.validate === 'phone') {
				error.textContent = label + ' must be exactly 11 digits';
			} else if (field.dataset.validate === 'age18') {
				error.textContent = 'Applicant must be at least 18 years old this year';
			} else {
				error.textContent = label + ' is required';
			}
		}
		field.classList.add('has-error');
	}

	function setCustomFieldError(field, message) {
		if (!field) {
			return;
		}

		const error = field.closest('.field')?.querySelector('.field-error');
		if (error) {
			error.textContent = message;
		}
		field.classList.add('has-error');
	}

	function isValidEmail(value) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value);
	}

	function isValidPhone(value) {
		return /^\d{11}$/.test(value);
	}

	function isEligibleAgeThisYear(value) {
		if (!value) {
			return false;
		}

		const birthYear = Number(value.split('-')[0]);
		if (!Number.isInteger(birthYear)) {
			return false;
		}

		const currentYear = new Date().getFullYear();
		return currentYear - birthYear >= minimumAge;
	}

	function setBirthdateLimit() {
		if (!birthdate) {
			return;
		}

		const currentYear = new Date().getFullYear();
		birthdate.max = `${currentYear - minimumAge}-12-31`;
	}

	function normalizePhoneField(field) {
		field.value = field.value.replace(/\D/g, '').slice(0, 11);
	}

	function setEducationalBackgroundLimits() {
		const primaryGradField = document.getElementById('primaryGrad');
		const secondaryGradField = document.getElementById('secondaryGrad');
		const lastSchoolYearField = document.getElementById('lastSchoolYear');
		
		const today = new Date();
		const currentYear = today.getFullYear();
		const currentMonth = String(today.getMonth() + 1).padStart(2, '0');
		const maxMonth = `${currentYear}-${currentMonth}`;
		
		if (primaryGradField) primaryGradField.max = maxMonth;
		if (secondaryGradField) secondaryGradField.max = maxMonth;
		if (lastSchoolYearField) lastSchoolYearField.max = maxMonth;
	}

	function clearDuplicateCheckCache() {
		lastDuplicateCheckKey = '';
		lastDuplicateCheckResult = true;
	}

	async function validateEmailAndContactUniqueness() {
		if (isCheckingDuplicate) {
			return false;
		}

		const email = emailField ? emailField.value.trim().toLowerCase() : '';
		const contact = contactField ? contactField.value.trim() : '';
		const currentAdmissionType = admissionType ? admissionType.value.trim() : '';
		const studentNumber = studentNumberField ? studentNumberField.value.trim() : '';

		if (!email || !contact || !isValidEmail(email) || !isValidPhone(contact)) {
			return true;
		}

		if (currentAdmissionType === 'Old Student' && !studentNumber) {
			setCustomFieldError(studentNumberField, 'Student Number is required for old student enrollment');
			return false;
		}

		const checkKey = `${currentAdmissionType}|${studentNumber}|${email}|${contact}`;
		if (checkKey === lastDuplicateCheckKey) {
			return lastDuplicateCheckResult;
		}

		isCheckingDuplicate = true;
		btnNext.disabled = true;

		try {
			const response = await fetch('../../api/check-contact-uniqueness.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify({
					email,
					contact,
					admissionType: currentAdmissionType,
					studentNumber
				})
			});

			const result = await response.json();
			if (!response.ok || !result.ok) {
				throw new Error(result.message || 'Unable to validate email/contact uniqueness.');
			}

			if (result.isDuplicate) {
				if (result.duplicateEmail) {
					setCustomFieldError(emailField, 'Email address is already used by another applicant');
				}
				if (result.duplicateContact) {
					setCustomFieldError(contactField, 'Contact number is already used by another applicant');
				}
				showToast(result.message || 'Email address or contact number already exists.', 'warning', 6000);
				lastDuplicateCheckKey = checkKey;
				lastDuplicateCheckResult = false;
				return false;
			}

			lastDuplicateCheckKey = checkKey;
			lastDuplicateCheckResult = true;
			return true;
		} catch (error) {
			showToast(error.message || 'Unable to check duplicate records right now.', 'error', 6000);
			return false;
		} finally {
			isCheckingDuplicate = false;
			btnNext.disabled = activeStep === steps.length - 1;
		}
	}

	function validateStep(stepIndex) {
		const currentStep = steps[stepIndex];
		const requiredFields = Array.from(currentStep.querySelectorAll('[data-required]'));
		let isValid = true;

		requiredFields.forEach((field) => {
			clearFieldError(field);
			const label = field.getAttribute('data-required');
			const isEmpty = field.value.trim() === '';
			if (isEmpty) {
				setFieldError(field, label);
				isValid = false;
			} else if (field.dataset.validate === 'email' && !isValidEmail(field.value.trim())) {
				setFieldError(field, label);
				isValid = false;
			} else if (field.dataset.validate === 'phone' && !isValidPhone(field.value.trim())) {
				setFieldError(field, label);
				isValid = false;
			} else if (field.dataset.validate === 'age18' && !isEligibleAgeThisYear(field.value.trim())) {
				setFieldError(field, label);
				isValid = false;
			}
		});

		return isValid;
	}

	function valueOrDash(id) {
		const field = document.getElementById(id);
		if (!field) return '-';
		if (field.type === 'checkbox') return field.checked ? 'Yes' : 'No';
		return field.value.trim() || '-';
	}

	function valueOrEmpty(id) {
		const field = document.getElementById(id);
		if (!field) return '';
		return field.value.trim();
	}

	function selectTextOrEmpty(id) {
		const field = document.getElementById(id);
		if (!field || field.tagName !== 'SELECT') return '';
		const selected = field.options[field.selectedIndex];
		return selected ? selected.text.trim() : '';
	}

	function renderSummary() {
		const regionName = selectTextOrEmpty('region') || '-';
		const cityName = selectTextOrEmpty('city') || '-';
		const barangayName = selectTextOrEmpty('barangay') || '-';

		summaryContent.innerHTML = `
			<div class="summary-group">
				<h3>Basic Information</h3>
				<div class="summary-grid">
					<p><strong>Admission Type:</strong> ${valueOrDash('admissionType')}</p>
					<p><strong>Student Number:</strong> ${valueOrDash('studentNumber')}</p>
					<p><strong>Name:</strong> ${valueOrDash('firstName')} ${valueOrDash('middleName')} ${valueOrDash('lastName')}${valueOrEmpty('suffix') ? ' ' + valueOrEmpty('suffix') : ''}</p>
					<p><strong>Birthdate:</strong> ${valueOrDash('birthdate')}</p>
					<p><strong>Sex:</strong> ${valueOrDash('sex')}</p>
					<p><strong>Civil Status:</strong> ${valueOrDash('civilStatus')}</p>
					<p><strong>Email:</strong> ${valueOrDash('email')}</p>
					<p><strong>Contact Number:</strong> ${valueOrDash('contact')}</p>
					<p><strong>Facebook/Messenger:</strong> ${valueOrDash('fbName')}</p>
					<p><strong>Working Student:</strong> ${valueOrDash('workingStudent')}</p>
					<p><strong>Religion:</strong> ${valueOrDash('religion')}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Address</h3>
				<div class="summary-grid">
					<p><strong>Address:</strong> ${valueOrDash('address')}</p>
					<p><strong>Barangay:</strong> ${barangayName}</p>
					<p><strong>Municipality / City:</strong> ${cityName}</p>
					<p><strong>Region:</strong> ${regionName}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Father's Information</h3>
				<div class="summary-grid">
					<p><strong>Father's Name:</strong> ${valueOrDash('fatherFirst')} ${valueOrDash('fatherMiddle')} ${valueOrDash('fatherLast')}${valueOrEmpty('fatherSuffix') ? ' ' + valueOrEmpty('fatherSuffix') : ''}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Mother's Maiden Name</h3>
				<div class="summary-grid">
					<p><strong>Mother's Name:</strong> ${valueOrDash('motherFirst')} ${valueOrDash('motherMiddle')} ${valueOrDash('motherLast')}${valueOrEmpty('motherSuffix') ? ' ' + valueOrEmpty('motherSuffix') : ''}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Parent/Guardian Information</h3>
				<div class="summary-grid">
					<p><strong>Relation:</strong> ${valueOrDash('guardianRelation')}</p>
					<p><strong>Parent/Guardian Name:</strong> ${valueOrDash('guardianFirst')} ${valueOrDash('guardianMiddle')} ${valueOrDash('guardianLast')}${valueOrEmpty('guardianSuffix') ? ' ' + valueOrEmpty('guardianSuffix') : ''}</p>
					<p><strong>Occupation:</strong> ${valueOrDash('guardianOccupation')}</p>
					<p><strong>Contact Number:</strong> ${valueOrDash('guardianContact')}</p>
					<p><strong>4Ps Member:</strong> ${valueOrDash('is4ps')}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Enrollment Information</h3>
				<div class="summary-grid">
					<p><strong>Preferred Branch:</strong> ${valueOrDash('preferredBranch')}</p>
					<p><strong>Course:</strong> ${valueOrDash('course')}</p>
					<p><strong>Year Level:</strong> ${valueOrDash('yearLevel')}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Educational Background</h3>
				<div class="summary-grid">
					<p><strong>Primary School:</strong> ${valueOrDash('primarySchool')}</p>
					<p><strong>Primary Year Graduated:</strong> ${valueOrDash('primaryGrad')}</p>
					<p><strong>Secondary School:</strong> ${valueOrDash('secondarySchool')}</p>
					<p><strong>Secondary Year Graduated:</strong> ${valueOrDash('secondaryGrad')}</p>
					<p><strong>Last School Attended:</strong> ${valueOrDash('lastSchool')}</p>
					<p><strong>Last School Year Attended:</strong> ${valueOrDash('lastSchoolYear')}</p>
				</div>
			</div>
			<div class="summary-group">
				<h3>Referral Information</h3>
				<div class="summary-grid">
					<p><strong>Referral:</strong> ${valueOrDash('referral')}</p>
				</div>
			</div>
		`;

		document.getElementById('statusRefNo').textContent = 'SMS-' + Date.now().toString().slice(-8);
		document.getElementById('statusFirstName').textContent = valueOrDash('firstName');
		document.getElementById('statusLastName').textContent = valueOrDash('lastName');
		document.getElementById('statusCourse').textContent = valueOrDash('course');
		document.getElementById('statusYear').textContent = valueOrDash('yearLevel');
	}

	function openStep(stepIndex) {
		activeStep = stepIndex;
		updateStepView();
	}

	function openStatusModal() {
		const modal = document.getElementById('statusModal');
		if (!modal) return;
		modal.classList.add('is-open');
		document.body.classList.add('modal-open');
	}

	function closeStatusModal() {
		const modal = document.getElementById('statusModal');
		if (!modal) return;
		modal.classList.remove('is-open');
		document.body.classList.remove('modal-open');
	}

	function shouldPreviewStatusModal() {
		const params = new URLSearchParams(window.location.search);
		return params.get('previewStatus') === '1';
	}

	function statusValue(id) {
		const element = document.getElementById(id);
		return element ? element.textContent.trim() : '-';
	}

	function downloadStatusPdf(event) {
		if (event) {
			event.preventDefault();
			event.stopPropagation();
		}

		if (!window.jspdf || !window.jspdf.jsPDF) {
			window.alert('PDF library is not loaded. Please refresh and try again.');
			return;
		}

		try {
			const { jsPDF } = window.jspdf;
			const doc = new jsPDF({ unit: 'mm', format: 'a4' });

			const reference = statusValue('statusRefNo');
			const firstName = statusValue('statusFirstName');
			const lastName = statusValue('statusLastName');
			const course = statusValue('statusCourse');
			const yearLevel = statusValue('statusYear');

			let y = 20;
			doc.setFont('helvetica', 'bold');
			doc.setFontSize(18);
			doc.text('Student Admission E-Form', 15, y);

			y += 10;
			doc.setFont('helvetica', 'normal');
			doc.setFontSize(11);
			doc.text('Generated on: ' + new Date().toLocaleString(), 15, y);

			y += 12;
			doc.setFont('helvetica', 'bold');
			doc.text('Application Details', 15, y);

			y += 8;
			doc.setFont('helvetica', 'normal');
			doc.text('Reference Number: ' + reference, 15, y);
			y += 7;
			doc.text('First Name: ' + firstName, 15, y);
			y += 7;
			doc.text('Last Name: ' + lastName, 15, y);
			y += 7;
			doc.text('Course: ' + course, 15, y);
			y += 7;
			doc.text('Year Level: ' + yearLevel, 15, y);

			y += 12;
			doc.setFontSize(10);
			doc.text('Please submit this printed e-form along with your original requirements.', 15, y);

			const safeReference = (reference || 'application').replace(/[^a-zA-Z0-9-_]/g, '');
			doc.save('enrollment-' + safeReference + '.pdf');
		} catch (error) {
			window.alert('Unable to generate PDF right now. Please try again.');
		}
	}

	function redirectToLanding() {
		window.location.href = 'landing.php';
	}

	function showToast(message, type = 'info', duration = 5000) {
		let container = document.getElementById('toastContainer');
		
		// Create container if it doesn't exist
		if (!container) {
			container = document.createElement('div');
			container.id = 'toastContainer';
			container.className = 'toast-container';
			document.body.appendChild(container);
		}

		const toast = document.createElement('div');
		toast.className = `toast ${type}`;
		
		const iconMap = {
			error: 'fa-circle-exclamation',
			success: 'fa-circle-check',
			warning: 'fa-triangle-exclamation',
			info: 'fa-circle-info'
		};

		toast.innerHTML = `
			<i class="fa-solid ${iconMap[type] || 'fa-circle-info'} toast-icon"></i>
			<span class="toast-message">${message}</span>
			<button class="toast-close" onclick="this.closest('.toast').remove()">
				<i class="fa-solid fa-xmark"></i>
			</button>
		`;

		container.appendChild(toast);

		if (duration > 0) {
			setTimeout(() => {
				if (toast.parentElement) {
					toast.classList.add('hide');
					setTimeout(() => toast.remove(), 300);
				}
			}, duration);
		}

		return toast;
	}

	function collectEnrollmentPayload() {
		// Get selected subject IDs from sessionStorage
		let selectedSubjectIds = [];
		try {
			const stored = sessionStorage.getItem('selectedSubjectIds');
			if (stored) {
				selectedSubjectIds = JSON.parse(stored);
			}
		} catch (e) {
			console.error('Error parsing selected subject IDs:', e);
		}

		return {
			admissionType: valueOrEmpty('admissionType'),
			studentNumber: valueOrEmpty('studentNumber'),
			workingStudent: document.getElementById('workingStudent')?.checked === true,
			lastName: valueOrEmpty('lastName'),
			firstName: valueOrEmpty('firstName'),
			middleName: valueOrEmpty('middleName'),
			suffix: valueOrEmpty('suffix'),
			sex: valueOrEmpty('sex'),
			civilStatus: valueOrEmpty('civilStatus'),
			religion: valueOrEmpty('religion'),
			birthdate: valueOrEmpty('birthdate'),
			email: valueOrEmpty('email'),
			contact: valueOrEmpty('contact'),
			fbName: valueOrEmpty('fbName'),
			address: valueOrEmpty('address'),
			regionName: selectTextOrEmpty('region'),
			cityName: selectTextOrEmpty('city'),
			barangayName: selectTextOrEmpty('barangay'),
			fatherLast: valueOrEmpty('fatherLast'),
			fatherFirst: valueOrEmpty('fatherFirst'),
			fatherMiddle: valueOrEmpty('fatherMiddle'),
			fatherSuffix: valueOrEmpty('fatherSuffix'),
			motherLast: valueOrEmpty('motherLast'),
			motherFirst: valueOrEmpty('motherFirst'),
			motherMiddle: valueOrEmpty('motherMiddle'),
			motherSuffix: valueOrEmpty('motherSuffix'),
			guardianRelation: valueOrEmpty('guardianRelation'),
			guardianLast: valueOrEmpty('guardianLast'),
			guardianFirst: valueOrEmpty('guardianFirst'),
			guardianMiddle: valueOrEmpty('guardianMiddle'),
			guardianSuffix: valueOrEmpty('guardianSuffix'),
			guardianContact: valueOrEmpty('guardianContact'),
			guardianOccupation: valueOrEmpty('guardianOccupation'),
			is4ps: document.getElementById('is4ps')?.checked === true,
			branchId: valueOrEmpty('branchId'),
			course: valueOrEmpty('course'),
			yearLevel: valueOrEmpty('yearLevel'),
			primarySchool: valueOrEmpty('primarySchool'),
			primaryGrad: valueOrEmpty('primaryGrad'),
			secondarySchool: valueOrEmpty('secondarySchool'),
			secondaryGrad: valueOrEmpty('secondaryGrad'),
			lastSchool: valueOrEmpty('lastSchool'),
			lastSchoolYear: valueOrEmpty('lastSchoolYear'),
			referral: valueOrEmpty('referral'),
			selectedSubjectIds: selectedSubjectIds
		};
	}

	function validateAllSteps() {
		for (let index = 0; index < steps.length - 1; index += 1) {
			if (!validateStep(index)) {
				openStep(index);
				return false;
			}
		}
		return true;
	}

	async function submitEnrollment() {
		if (isSubmitting) {
			return;
		}

		if (!validateAllSteps()) {
			return;
		}

		isSubmitting = true;
		btnSubmit.disabled = true;
		const previousLabel = btnSubmit.textContent;
		btnSubmit.textContent = 'Submitting...';

		try {
			const response = await fetch('../../api/save-application.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json'
				},
				body: JSON.stringify(collectEnrollmentPayload())
			});

			const result = await response.json();

			if (!response.ok || !result.ok) {
				// Check if it's a duplicate error
				if (result.isDuplicate) {
					showToast(result.message, 'warning', 6000);
				} else {
					throw new Error(result.message || 'Failed to save enrollment application.');
				}
			} else {
				renderSummary();
				document.getElementById('statusRefNo').textContent = result.applicationReference || '-';
				openStatusModal();
			}
		} catch (error) {
			showToast(error.message || 'Unable to submit application right now. Please try again.', 'error', 6000);
		} finally {
			isSubmitting = false;
			btnSubmit.disabled = false;
			btnSubmit.textContent = previousLabel;
		}
	}

	admissionType.addEventListener('change', setYearLevelOptions);
	region.addEventListener('change', renderCityField);

	enrollmentForm.addEventListener('input', (event) => {
		const target = event.target;
		if (target.matches('[data-required]')) {
			clearFieldError(target);
		}

		if (target.id === 'email' || target.id === 'contact') {
			clearDuplicateCheckCache();
		}

		if (target.id === 'studentNumber') {
			clearDuplicateCheckCache();
		}

		if (target.id === 'contact' || target.id === 'guardianContact') {
			normalizePhoneField(target);
		}
	});

	enrollmentForm.addEventListener('change', (event) => {
		const target = event.target;
		if (target.id === 'city') {
			renderBarangayField(target.value);
		}
		if (target.matches('[data-required]')) {
			clearFieldError(target);
		}
	});

	btnNext.addEventListener('click', async function () {
		if (!validateStep(activeStep)) {
			return;
		}

		if (activeStep === 0) {
			const isUnique = await validateEmailAndContactUniqueness();
			if (!isUnique) {
				return;
			}
		}

		if (activeStep < steps.length - 1) {
			activeStep += 1;
			if (activeStep === steps.length - 1) {
				renderSummary();
			}
			updateStepView();
		}
	});

	btnBack.addEventListener('click', function () {
		if (activeStep > 0) {
			activeStep -= 1;
			updateStepView();
		}
	});

	accordionHeaders.forEach((header, index) => {
		header.addEventListener('click', () => {
			// Allow clicking any accordion for easier navigation
			openStep(index);
		});
	});

	btnSubmit.addEventListener('click', function () {
		submitEnrollment();
	});

	document.querySelectorAll('[data-close-status="true"]').forEach((element) => {
		element.addEventListener('click', closeStatusModal);
	});

	window.handleStatusClose = function(event) {
		event.preventDefault();
		window.location.href = 'landing.php';
	};

	window.handleStatusDownload = function(event) {
		downloadStatusPdf(event);
	};

	if (statusDownloadBtn) {
		statusDownloadBtn.removeAttribute('data-close-status');
	}

	setYearLevelOptions();
	setBirthdateLimit();
	setEducationalBackgroundLimits();
	enforceRequiredMarkers();
	loadRegions().then(() => renderCityField());
	updateStepView();

	if (shouldPreviewStatusModal()) {
		renderSummary();
		openStatusModal();
	}
})();
