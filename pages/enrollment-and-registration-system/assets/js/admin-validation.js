/**
 * Admin Admission Validation JavaScript
 * Handles admission review modal and status updates
 */

let currentAdmissionId = null;
let currentDeleteApplicantName = null;

// Detect correct API base path based on current page location
function getApiBasePath() {
	const path = window.location.pathname;
	console.log('Current pathname:', path);
	
	// Handle path: /SMS/pages/enrollment-and-registration-system/pages/admin/validation-and-approval.php
	// or: /pages/enrollment-and-registration-system/pages/admin/validation-and-approval.php
	
	// Strategy 1: Find enrollment-and-registration-system and go to api subfolder
	if (path.includes('enrollment-and-registration-system')) {
		const match = path.match(/(.+?enrollment-and-registration-system)\//);
		if (match) {
			const baseUrl = window.location.origin + match[1] + '/api/';
			console.log('Using enrollment-system API path:', baseUrl);
			return baseUrl;
		}
	}
	
	// Strategy 2: Fallback to relative path
	const baseUrl = window.location.origin + '/SMS/pages/enrollment-and-registration-system/api/';
	console.log('Using fallback API path:', baseUrl);
	return baseUrl;
}

/**
 * Open the admission review modal
 */
function openReviewModal(admissionId, appName) {
	currentAdmissionId = admissionId;
	document.getElementById('applicantName').textContent = appName;
	document.getElementById('validationNotes').value = '';
	document.getElementById('decisionSelect').value = '';
	document.getElementById('reviewModal').style.display = 'block';
	
	setTimeout(() => {
		document.getElementById('decisionSelect').focus();
	}, 100);
}

/**
 * Close the admission review modal
 */
function closeReviewModal() {
	document.getElementById('reviewModal').style.display = 'none';
	currentAdmissionId = null;
}

/**
 * Submit the status update for admission
 */
function submitDecision() {
	if (!currentAdmissionId) {
		alert('Error: No admission selected');
		return;
	}

	const status = document.getElementById('decisionSelect').value;
	if (!status) {
		alert('Please select a status');
		return;
	}

	const notes = document.getElementById('validationNotes').value;

	// Get button reference safely (not via event.target)
	const submitBtn = document.querySelector('.modal-buttons .approve-btn') || 
	                   document.querySelector('.modal-buttons button:first-child');
	let originalText = '';
	if (submitBtn) {
		originalText = submitBtn.innerHTML;
		submitBtn.disabled = true;
		submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
	}

	const apiBase = getApiBasePath();
	const endpoint = apiBase + 'update-admission-status.php';
	
	console.log('=== Admission Update Debug ===');
	console.log('API Endpoint:', endpoint);
	console.log('Admission ID:', currentAdmissionId);
	console.log('Status:', status);
	console.log('Notes:', notes);
	console.log('=============================');

	fetch(endpoint, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		credentials: 'include',
		body: JSON.stringify({
			application_id: currentAdmissionId,
			status: status,
			validation_notes: notes
		})
	})
	.then(response => {
		// Log response details for debugging
		console.log('Response status:', response.status);
		console.log('Response headers:', response.headers.get('content-type'));
		
		if (!response.ok) {
			return response.text().then(text => {
				throw new Error(`HTTP Error ${response.status}: ${text.substring(0, 500)}`);
			});
		}
		return response.text().then(text => {
			try {
				return JSON.parse(text);
			} catch (e) {
				console.error('Failed to parse JSON response:', text);
				throw new Error(`Invalid JSON response: ${text.substring(0, 500)}`);
			}
		});
	})
	.then(data => {
		if (data.ok) {
			showSuccessMessage(`Admission ${status.toLowerCase()} successfully!`);
			closeReviewModal();
			setTimeout(() => {
				location.reload();
			}, 1500);
		} else {
			throw new Error(data.message || 'Unknown error occurred');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showErrorMessage(error.message || 'An error occurred while processing the admission request.');
		if (submitBtn) {
			submitBtn.disabled = false;
			submitBtn.innerHTML = originalText;
		}
	});
}

/**
 * Open the delete confirmation modal
 */
function openDeleteModal(admissionId, appName) {
	currentAdmissionId = admissionId;
	currentDeleteApplicantName = appName;
	document.getElementById('deleteApplicantName').textContent = appName;
	document.getElementById('deleteModal').style.display = 'block';
}

/**
 * Close the delete confirmation modal
 */
function closeDeleteModal() {
	document.getElementById('deleteModal').style.display = 'none';
	currentAdmissionId = null;
	currentDeleteApplicantName = null;
}

/**
 * Confirm and execute deletion
 */
function confirmDeleteAdmission() {
	if (!currentAdmissionId) {
		alert('Error: No admission selected');
		return;
	}

	// Get button reference safely
	const deleteBtn = document.querySelector('#deleteModal .delete-btn') ||
	                   document.querySelector('#deleteModal .reject-btn') ||
	                   document.querySelector('#deleteModal button:first-child');
	let originalContent = '';
	if (deleteBtn) {
		originalContent = deleteBtn.innerHTML;
		deleteBtn.disabled = true;
		deleteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
	}

	const apiBase = getApiBasePath();
	const endpoint = apiBase + 'delete-admission.php';

	fetch(endpoint, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		credentials: 'include',
		body: JSON.stringify({
			admission_id: currentAdmissionId
		})
	})
	.then(response => {
		// Log response details for debugging
		console.log('Delete Response status:', response.status);
		
		if (!response.ok) {
			return response.text().then(text => {
				throw new Error(`HTTP Error ${response.status}: ${text.substring(0, 500)}`);
			});
		}
		return response.text().then(text => {
			try {
				return JSON.parse(text);
			} catch (e) {
				console.error('Failed to parse JSON response:', text);
				throw new Error(`Invalid JSON response: ${text.substring(0, 500)}`);
			}
		});
	})
	.then(data => {
		if (data.ok) {
			showSuccessMessage('Admission deleted successfully!');
			closeDeleteModal();
			setTimeout(() => {
				location.reload();
			}, 1000);
		} else {
			throw new Error(data.message || 'Failed to delete admission');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showErrorMessage(error.message || 'An error occurred while deleting the admission.');
		if (deleteBtn) {
			deleteBtn.disabled = false;
			deleteBtn.innerHTML = originalContent;
		}
	});
}

/**
 * Open the subject load validation modal
 */
function openLoadValidationModal(admissionId, appName) {
	currentAdmissionId = admissionId;
	alert('Subject Load Validation feature coming soon!\n\nApplicant: ' + appName);
	// TODO: Implement subject load validation modal
	// This will show the student's pre-selected subjects
	// And allow registrar to validate if they're taking appropriate year levels
}

/**
 * Open the status update and remarks modal
 */
function openUpdateStatusModal(admissionId, appName) {
	// Redirect to the review modal which handles status and remarks
	openReviewModal(admissionId, appName);
}

/**
 * Show success notification
 */
function showSuccessMessage(message) {
	let notification = document.getElementById('successNotification');
	if (!notification) {
		notification = document.createElement('div');
		notification.id = 'successNotification';
		notification.style.cssText = `
			position: fixed;
			top: 20px;
			right: 20px;
			background: #4caf50;
			color: white;
			padding: 16px 24px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			z-index: 10000;
			animation: slideIn 0.3s ease-out;
			font-family: 'Poppins', sans-serif;
		`;
		document.body.appendChild(notification);
	}
	notification.innerHTML = `<i class="fa-solid fa-check-circle"></i> ${message}`;
	notification.style.display = 'block';
	
	setTimeout(() => {
		notification.style.display = 'none';
	}, 3000);
}

/**
 * Show error notification
 */
function showErrorMessage(message) {
	let notification = document.getElementById('errorNotification');
	if (!notification) {
		notification = document.createElement('div');
		notification.id = 'errorNotification';
		notification.style.cssText = `
			position: fixed;
			top: 20px;
			right: 20px;
			background: #f44336;
			color: white;
			padding: 16px 24px;
			border-radius: 8px;
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
			z-index: 10000;
			animation: slideIn 0.3s ease-out;
			font-family: 'Poppins', sans-serif;
		`;
		document.body.appendChild(notification);
	}
	notification.innerHTML = `<i class="fa-solid fa-exclamation-circle"></i> ${message}`;
	notification.style.display = 'block';
	
	setTimeout(() => {
		notification.style.display = 'none';
	}, 5000);
}

/**
 * Send enrollment email to student
 */
function sendEnrollmentEmail(applicationId, emailAddress, firstName) {
	if (!emailAddress) {
		showErrorMessage('Email address not available for this student');
		return;
	}

	const apiBase = getApiBasePath();
	const endpoint = apiBase + 'send-enrollment-email.php';

	const confirmSend = confirm(`Send enrollment confirmation email to ${emailAddress}?`);
	if (!confirmSend) return;

	// Show loading state
	event.target.closest('.send-btn').disabled = true;
	event.target.closest('.send-btn').innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

	fetch(endpoint, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		credentials: 'include',
		body: JSON.stringify({
			application_id: applicationId,
			email_address: emailAddress,
			first_name: firstName
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.ok || data.success) {
			showSuccessMessage(`Enrollment email sent to ${emailAddress}`);
		} else {
			throw new Error(data.message || 'Failed to send email');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showErrorMessage(error.message || 'Failed to send enrollment email');
		event.target.closest('.send-btn').disabled = false;
		event.target.closest('.send-btn').innerHTML = '<i class="fa-solid fa-envelope"></i>';
	});
}

/**
 * Handle modal close when clicking outside
 */
window.addEventListener('click', function(event) {
	const reviewModal = document.getElementById('reviewModal');
	const deleteModal = document.getElementById('deleteModal');
	if (event.target === reviewModal) {
		closeReviewModal();
	}
	if (event.target === deleteModal) {
		closeDeleteModal();
	}
});

/**
 * Handle Escape key to close modals
 */
document.addEventListener('keydown', function(event) {
	if (event.key === 'Escape') {
		closeReviewModal();
		closeDeleteModal();
	}
});

/**
 * Add animation styles
 */
const style = document.createElement('style');
style.textContent = `
	@keyframes slideIn {
		from {
			transform: translateX(400px);
			opacity: 0;
		}
		to {
			transform: translateX(0);
			opacity: 1;
		}
	}
	
	.loading {
		opacity: 0.6;
		pointer-events: none;
	}
`;
document.head.appendChild(style);
