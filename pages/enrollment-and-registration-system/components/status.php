<div id="statusModal" class="status-modal" aria-hidden="true">
	<div class="status-backdrop" data-close-status="true"></div>
	<div class="status-card">
		<h3>Online Admission</h3>
		<p class="status-congrats">Congratulations!</p>
		<p class="status-message">
			You are now officially registered as a student applicant. Please download and print
			the e-form below and submit it to your respective branch with original requirements.
		</p>
		<div class="status-important">
			<strong>Important:</strong>
			<p>Before proceeding, take a pen and paper and copy the following details:</p>
		</div>
		<ul class="status-details">
			<li><strong>Reference Number:</strong> <span id="statusRefNo">-</span></li>
			<li><strong>First Name:</strong> <span id="statusFirstName">-</span></li>
			<li><strong>Last Name:</strong> <span id="statusLastName">-</span></li>
			<li><strong>Course:</strong> <span id="statusCourse">-</span></li>
			<li><strong>Year Level:</strong> <span id="statusYear">-</span></li>
		</ul>
		<div class="status-actions">
			<button type="button" id="statusCloseBtn" class="btn-secondary" onclick="window.handleStatusClose(event)">Close</button>
			<button type="button" id="statusDownloadBtn" class="btn-primary" onclick="window.handleStatusDownload(event)">Download and Print</button>
		</div>
	</div>
</div>
