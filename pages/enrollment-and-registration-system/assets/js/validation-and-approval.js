/**
 * Validation & Approval Modal Functions
 */

// Global variable to store current application ID
let currentApplicationId = null;
let currentStudentId = null;

function showToast(message, type = 'info', duration = 3500) {
	let container = document.getElementById('toastContainer');
	if (!container) {
		container = document.createElement('div');
		container.id = 'toastContainer';
		container.className = 'toast-container';
		document.body.appendChild(container);
	}

	const toast = document.createElement('div');
	toast.className = `toast ${type}`;

	const iconMap = {
		success: 'fa-circle-check',
		error: 'fa-circle-xmark',
		warning: 'fa-triangle-exclamation',
		info: 'fa-circle-info'
	};

	toast.innerHTML = `
		<i class="fa-solid ${iconMap[type] || iconMap.info} toast-icon"></i>
		<span class="toast-message">${message}</span>
		<button class="toast-close" type="button" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
	`;

	const closeBtn = toast.querySelector('.toast-close');
	if (closeBtn) {
		closeBtn.addEventListener('click', () => {
			toast.classList.add('hide');
			setTimeout(() => toast.remove(), 200);
		});
	}

	container.appendChild(toast);

	if (duration > 0) {
		setTimeout(() => {
			if (!toast.parentElement) return;
			toast.classList.add('hide');
			setTimeout(() => toast.remove(), 200);
		}, duration);
	}
}

/**
 * Open Subject Load Validation Modal
 */
function openLoadValidationModal(applicationId, applicantName) {
	currentApplicationId = applicationId;
	
	// Set the applicant name
	document.getElementById('loadStudentName').textContent = applicantName;
	
	// Show modal
	document.getElementById('loadValidationModal').style.display = 'flex';
	
	// Fetch subject preselections for this student
	fetchSubjectLoads(applicationId);
}

/**
 * Close Subject Load Validation Modal
 */
function closeLoadValidationModal() {
	document.getElementById('loadValidationModal').style.display = 'none';
	currentApplicationId = null;
	currentStudentId = null;
	
	// Reset modal state
	document.getElementById('loadValidationNotes').value = '';
	document.getElementById('loadValidationNotes').disabled = false;
	document.getElementById('loadValidationNotes').style.opacity = '1';
	const modalButtons = document.querySelector('#loadValidationModal .modal-buttons');
	if (modalButtons) {
		modalButtons.style.display = 'grid';
		modalButtons.style.gridTemplateColumns = '1fr 1fr';
	}
	const badge = document.getElementById('loadStatusBadge');
	if (badge) {
		badge.remove();
	}
}

/**
 * Fetch Subject Preselections for a Student
 */
function fetchSubjectLoads(applicationId) {
	const loadsList = document.getElementById('subjectLoadsList');
	loadsList.innerHTML = '<div style="padding: 20px; text-align: center;"><p><i class="fa-solid fa-spinner fa-spin"></i> Loading subjects...</p></div>';

	fetch('../../api/get-subject-preselection.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: applicationId
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success && data.subjects && data.subjects.length > 0) {
			// Build subjects table
			let html = '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse;">';
			html += '<thead><tr style="background: #f5f5f5;">';
			html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Code</th>';
			html += '<th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd;">Subject</th>';
			html += '<th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Units</th>';
			html += '<th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd;">Price</th>';
			html += '</tr></thead><tbody>';

			let totalUnits = 0;
			let totalPrice = 0;

			data.subjects.forEach(subject => {
				totalUnits += parseFloat(subject.units) || 0;
				totalPrice += parseFloat(subject.price) || 0;

				html += '<tr style="border-bottom: 1px solid #eee;">';
				html += `<td style="padding: 10px;"><strong>${escapeHtml(subject.subject_code)}</strong></td>`;
				html += `<td style="padding: 10px;">${escapeHtml(subject.subject_name)}</td>`;
				html += `<td style="padding: 10px; text-align: center;">${subject.units}</td>`;
				html += `<td style="padding: 10px; text-align: center;">₱${parseFloat(subject.price).toFixed(2)}</td>`;
				html += '</tr>';
			});

			// Add totals row
			html += '<tr style="background: #f9f9f9; font-weight: 600;">';
			html += `<td colspan="2" style="padding: 10px; text-align: right;">TOTAL:</td>`;
			html += `<td style="padding: 10px; text-align: center;">${totalUnits}</td>`;
			html += `<td style="padding: 10px; text-align: center;">₱${totalPrice.toFixed(2)}</td>`;
			html += '</tr>';

			html += '</tbody></table></div>';
			loadsList.innerHTML = html;

			// Update modal based on approval status
			const remarksField = document.getElementById('loadValidationNotes');
			const approveBtn = document.querySelector('.approve-btn');
			const statusBadge = document.getElementById('loadStatusBadge');

			if (data.status === 'Approved' || data.status === 'Rejected') {
				// Read-only mode
				remarksField.disabled = true;
				remarksField.style.opacity = '0.6';
				
				// Hide approve button only, keep modal buttons visible
				const approveBtn = document.querySelector('#loadValidationModal .approve-btn');
				if (approveBtn) {
					approveBtn.style.display = 'none';
				}
				
				// Make grid single column for close button
				const modalButtons = document.querySelector('#loadValidationModal .modal-buttons');
				if (modalButtons) {
					modalButtons.style.gridTemplateColumns = '1fr';
				}

				// Add status badge to top right corner
				let statusColor = data.status === 'Approved' ? '#4caf50' : '#f44336';
				let statusBadgeHTML = `<div style="position: absolute; top: 20px; right: 20px; padding: 8px 16px; background: ${statusColor}20; border-left: 4px solid ${statusColor}; border-radius: 6px; text-align: center;">
					<p style="color: ${statusColor}; font-weight: 600; margin: 0; font-size: 14px;">
						<i class="fa-solid fa-check-circle"></i> ${data.status.toUpperCase()}
					</p>
					<p style="color: #666; font-size: 0.75rem; margin: 4px 0 0 0;">
						${new Date(data.validated_at).toLocaleString()}
					</p>
				</div>`;
				
				const modalContent = document.querySelector('#loadValidationModal .modal-content');
				if (modalContent && !document.getElementById('loadStatusBadge')) {
					// Make modal-content position: relative for absolute positioning of badge
					modalContent.style.position = 'relative';
					const badge = document.createElement('div');
					badge.id = 'loadStatusBadge';
					badge.innerHTML = statusBadgeHTML;
					modalContent.insertBefore(badge, modalContent.firstChild);
				}
			} else {
				// Edit mode
				remarksField.disabled = false;
				remarksField.style.opacity = '1';
				
				// Show approve button
				const approveBtn = document.querySelector('#loadValidationModal .approve-btn');
				if (approveBtn) {
					approveBtn.style.display = 'inline-block';
				}
				
				// Restore grid layout
				const modalButtons = document.querySelector('#loadValidationModal .modal-buttons');
				if (modalButtons) {
					modalButtons.style.gridTemplateColumns = '1fr 1fr';
				}
				
				// Remove status badge if exists
				const badge = document.getElementById('loadStatusBadge');
				if (badge) {
					badge.remove();
				}
			}
		} else {
			loadsList.innerHTML = '<div style="padding: 20px; text-align: center;"><i class="fa-solid fa-inbox"></i><p><strong>No subject preselections found</strong></p><p style="font-size: 14px;">This student has not submitted their subject loads yet.</p></div>';
		}
	})
	.catch(error => {
		console.error('Error:', error);
		loadsList.innerHTML = '<div style="padding: 20px; text-align: center;"><i class="fa-solid fa-exclamation-circle"></i><p><strong>Error loading subjects</strong></p><p style="font-size: 14px;">' + escapeHtml(error.message) + '</p></div>';
	});
}

/**
 * Approve Subject Loads
 */
function approveSubjectLoads() {
	if (!currentApplicationId) {
		showToast('Application ID not found.', 'error');
		return;
	}

	const remarks = document.getElementById('loadValidationNotes').value;
	const btn = document.querySelector('#loadValidationModal .approve-btn');
	if (!btn) {
		showToast('Approve button not found.', 'error');
		return;
	}
	const originalText = btn.innerHTML;
	
	btn.disabled = true;
	btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

	fetch('../../api/validate-subject-loads.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: currentApplicationId,
			remarks: remarks
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			showToast(data.message || 'Subject load validation completed.', 'success');
			closeLoadValidationModal();
			setTimeout(() => {
				location.reload();
			}, 700);
		} else {
			showToast(data.message || 'Failed to approve loads.', 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showToast('An error occurred while approving loads: ' + error.message, 'error');
	})
	.finally(() => {
		btn.disabled = false;
		btn.innerHTML = originalText;
	});
}

/**
 * Open Update Status Modal
 */
function openUpdateStatusModal(applicationId, applicantName) {
	currentApplicationId = applicationId;
	document.getElementById('statusApplicantName').textContent = applicantName;
	document.getElementById('updateStatusModal').style.display = 'flex';
}

/**
 * Close Update Status Modal
 */
function closeUpdateStatusModal() {
	document.getElementById('updateStatusModal').style.display = 'none';
	currentApplicationId = null;
}

/**
 * Submit Status Update
 */
function submitStatusUpdate() {
	if (!currentApplicationId) {
		showToast('Application ID not found.', 'error');
		return;
	}

	const newStatus = document.getElementById('statusSelect').value;
	const remarks = document.getElementById('statusRemarks').value;

	if (!newStatus) {
		showToast('Please select a status.', 'warning');
		return;
	}

	const btn = document.querySelector('#updateStatusModal .approve-btn');
	if (!btn) {
		showToast('Update button not found.', 'error');
		return;
	}
	const originalText = btn.innerHTML;
	
	btn.disabled = true;
	btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

	fetch('../../api/update-admission-status.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: currentApplicationId,
			status: newStatus,
			remarks: remarks
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.ok) {
			showToast('Status updated successfully!', 'success');
			closeUpdateStatusModal();
			
			// If status was set to "Validated", navigate to enrollment-status section
			const newStatus = document.getElementById('statusSelect').value;
			if (newStatus === 'Validated') {
				// Reload and show enrollment-status section
				setTimeout(() => {
					window.location.href = window.location.pathname + '?section=enrollment-status';
				}, 500);
			} else {
				location.reload();
			}
		} else {
			showToast(data.message || 'Failed to update status.', 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showToast('An error occurred: ' + error.message, 'error');
	})
	.finally(() => {
		btn.disabled = false;
		btn.innerHTML = originalText;
	});
}

/**
 * Open Review Modal
 */
function openReviewModal(applicationId, applicantName) {
	currentApplicationId = applicationId;
	document.getElementById('applicantName').textContent = applicantName;
	document.getElementById('reviewModal').style.display = 'flex';
}

/**
 * Close Review Modal
 */
function closeReviewModal() {
	document.getElementById('reviewModal').style.display = 'none';
	currentApplicationId = null;
}

/**
 * Submit Review Decision
 */
function submitDecision() {
	if (!currentApplicationId) {
		showToast('Application ID not found.', 'error');
		return;
	}

	const status = document.getElementById('decisionSelect').value;
	const notes = document.getElementById('validationNotes').value;

	if (!status) {
		showToast('Please select a status.', 'warning');
		return;
	}

	const btn = document.querySelector('#reviewModal .approve-btn');
	if (!btn) {
		showToast('Update button not found.', 'error');
		return;
	}
	const originalText = btn.innerHTML;
	
	btn.disabled = true;
	btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

	fetch('../../api/update-admission-status.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: currentApplicationId,
			status: status,
			notes: notes
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			showToast('Application status updated!', 'success');
			closeReviewModal();
			location.reload();
		} else {
			showToast(data.message || 'Failed to update status.', 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showToast('An error occurred: ' + error.message, 'error');
	})
	.finally(() => {
		btn.disabled = false;
		btn.innerHTML = originalText;
	});
}

/**
 * Open Delete Modal
 */
function openDeleteModal(applicationId, applicantName) {
	currentApplicationId = applicationId;
	document.getElementById('deleteApplicantName').textContent = applicantName;
	document.getElementById('deleteModal').style.display = 'flex';
}

/**
 * Close Delete Modal
 */
function closeDeleteModal() {
	document.getElementById('deleteModal').style.display = 'none';
	currentApplicationId = null;
}

/**
 * Confirm Delete Admission
 */
function confirmDeleteAdmission() {
	if (!currentApplicationId) {
		showToast('Application ID not found.', 'error');
		return;
	}

	const btn = document.querySelector('#deleteModal .delete-btn');
	if (!btn) {
		showToast('Delete button not found.', 'error');
		return;
	}
	const originalText = btn.innerHTML;
	
	btn.disabled = true;
	btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';

	fetch('../../api/delete-admission.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: currentApplicationId
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			showToast('Application deleted successfully!', 'success');
			closeDeleteModal();
			location.reload();
		} else {
			showToast(data.message || 'Failed to delete application.', 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		showToast('An error occurred: ' + error.message, 'error');
	})
	.finally(() => {
		btn.disabled = false;
		btn.innerHTML = originalText;
	});
}

/**
 * Escape HTML Special Characters
 */
function escapeHtml(text) {
	const map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#039;'
	};
	return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Close modals when clicking outside
 */
window.addEventListener('click', function(event) {
	const loadModal = document.getElementById('loadValidationModal');
	const updateStatusModal = document.getElementById('updateStatusModal');
	const reviewModal = document.getElementById('reviewModal');
	const deleteModal = document.getElementById('deleteModal');

	if (event.target === loadModal) {
		closeLoadValidationModal();
	}
	if (event.target === updateStatusModal) {
		closeUpdateStatusModal();
	}
	if (event.target === reviewModal) {
		closeReviewModal();
	}
	if (event.target === deleteModal) {
		closeDeleteModal();
	}
});
