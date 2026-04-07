/**
 * Enrollment Status Section - Send enrollment emails to validated students
 */

/**
 * Send enrollment email to a student
 */
function sendEnrollmentEmail(applicationId, emailAddress, firstName) {
	if (!emailAddress) {
		alert('Email address not found for this student.');
		return;
	}

	// Show loading state
	const btn = event.target.closest('.send-btn');
	const originalText = btn.innerHTML;
	btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
	btn.disabled = true;

	fetch('../../api/send-enrollment-email.php', {
		method: 'POST',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			application_id: applicationId,
			email_address: emailAddress,
			first_name: firstName
		})
	})
	.then(response => response.json())
	.then(data => {
		if (data.success) {
			alert(`Enrollment email successfully sent to ${emailAddress}!`);
			// Optional: refresh the page or update the UI
			location.reload();
		} else {
			alert(`Error: ${data.message || 'Failed to send email'}`);
		}
	})
	.catch(error => {
		console.error('Error:', error);
		alert('An error occurred while sending the email. Please try again.');
	})
	.finally(() => {
		btn.innerHTML = originalText;
		btn.disabled = false;
	});
}
