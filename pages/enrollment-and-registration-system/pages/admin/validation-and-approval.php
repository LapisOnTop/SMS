<?php
// Validation & Approval Component - Included in dashboard.php
// $db connection and $section available from dashboard.php

$applications = [];
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'Pending';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = 'SELECT a.application_id, api.first_name, api.last_name, ac.email_address, ac.contact_number, a.admission_type, a.status, a.created_at, rn.reference_number FROM applications a LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id LEFT JOIN applicant_contact ac ON a.application_id = ac.application_id LEFT JOIN reference_numbers rn ON a.reference_id = rn.reference_id WHERE a.status = "' . $db->real_escape_string($filter_status) . '"';

if (!empty($search_term)) {
	$search = $db->real_escape_string($search_term);
	$sql .= " AND (api.first_name LIKE '%$search%' OR api.last_name LIKE '%$search%' OR ac.email_address LIKE '%$search%' OR rn.reference_number LIKE '%$search%')";
}

$sql .= ' ORDER BY a.created_at DESC LIMIT 100';

$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$applications[] = $row;
	}
}
?>

<section id="validation" class="page-section <?php echo ($section === 'validation' ? 'active' : ''); ?>">
	<div class="content-header">
		<h1><i class="fa-solid fa-clipboard-check"></i> Validate Admissions</h1>
		<p>Review and approve admissions to the system</p>
	</div>

	<div class="filter-section">
		<input type="text" id="searchInput" placeholder="Search by name, email, reference number..." value="<?php echo htmlspecialchars($search_term); ?>">
		<button onclick="filterApplications()"><i class="fa-solid fa-search"></i> Search</button>
	</div>

	<?php if (!empty($applications)): ?>
	<div class="table-container">
		<table>
			<thead>
				<tr>
					<th>Reference #</th><th>Name</th><th>Email</th><th>Contact</th><th>Admission Type</th><th>Status</th><th>Action</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($applications as $app): ?>
				<?php $displayAdmissionType = ($app['admission_type'] === 'Returnee') ? 'Old Student' : $app['admission_type']; ?>
				<tr>
					<td><strong><?php echo htmlspecialchars($app['reference_number'] ?? 'N/A'); ?></strong></td>
					<td><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
					<td><?php echo htmlspecialchars($app['email_address']); ?></td>
					<td><?php echo htmlspecialchars($app['contact_number']); ?></td>
					<td>
						<span class="admission-badge admission-<?php echo strtolower(str_replace(' ', '-', $displayAdmissionType)); ?>">
							<?php echo htmlspecialchars($displayAdmissionType); ?>
						</span>
					</td>
					<td>
						<?php 
						$status = $app['status'] ?? 'Unknown';
						$status_class = strtolower(str_replace(' ', '-', $status));
						?>
						<span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
							<?php echo htmlspecialchars($status); ?>
						</span>
					</td>
					<td>
						<div class="action-icons">
							<button class="action-icon-btn validate-loads-btn" title="Validate Subject Loads" onclick="openLoadValidationModal(<?php echo $app['application_id']; ?>, '<?php echo htmlspecialchars($app['first_name']); ?> <?php echo htmlspecialchars($app['last_name']); ?>')">
								<i class="fa-solid fa-book"></i>
							</button>
							<button class="action-icon-btn delete-btn" title="Delete Application" onclick="openDeleteModal(<?php echo $app['application_id']; ?>, '<?php echo htmlspecialchars($app['first_name']); ?> <?php echo htmlspecialchars($app['last_name']); ?>')">
								<i class="fa-solid fa-trash"></i>
							</button>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php else: ?>
	<div class="table-container">
		<div class="empty-state">
			<i class="fa-solid fa-inbox"></i>
			<p><strong>No admissions found</strong></p>
			<p style="font-size: 14px;">Try adjusting your filters or search terms.</p>
		</div>
	</div>
	<?php endif; ?>
</section>

<!-- Review Modal (for validation section) -->
<div id="reviewModal" class="modal">
	<div class="modal-content">
		<h3>Application Review</h3>
		<div class="modal-form">
			<div>
				<label>Applicant Name:</label>
				<p id="applicantName" style="color: #666;"></p>
			</div>
			
			<div>
				<label for="validationNotes">Validation Notes:</label>
				<textarea id="validationNotes" placeholder="Enter any additional notes or concerns about this application..."></textarea>
			</div>

			<div>
				<label for="decisionSelect">Status:</label>
				<select id="decisionSelect">
					<option value="">-- Select Status --</option>
					<option value="Pending">Pending</option>
					<option value="Validated">Validated</option>
					<option value="Enrolled">Enrolled</option>
					<option value="Paid">Paid</option>
				</select>
			</div>
		</div>

		<div class="modal-buttons">
			<button class="approve-btn" onclick="submitDecision()">
				<i class="fa-solid fa-check"></i> Update Status
			</button>
			<button class="cancel-btn" onclick="closeReviewModal()">
				Cancel
			</button>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
	<div class="modal-content">
		<h3>Delete Application</h3>
		<p>Are you sure you want to delete the application for <strong id="deleteApplicantName"></strong>? This action cannot be undone.</p>
		
		<div class="modal-buttons">
			<button class="cancel-btn" onclick="closeDeleteModal()">
				Cancel
			</button>
			<button class="delete-btn" style="width: 100%; background: #f44336; color: white;" onclick="confirmDeleteAdmission()">
				<i class="fa-solid fa-trash"></i> Delete Permanently
			</button>
		</div>
	</div>
</div>

<!-- Subject Load Validation Modal -->
<div id="loadValidationModal" class="modal">
	<div class="modal-content modal-large">
		<h3>Validate Subject Loads</h3>
		<div class="modal-form">
			<div>
				<label>Student Name:</label>
				<p id="loadStudentName" style="color: #666; font-weight: 500;"></p>
			</div>

			<div style="margin-top: 20px;">
				<label style="font-weight: 600;">Pre-Selected Subjects:</label>
				<div id="subjectLoadsList" style="margin-top: 10px;">
					<div class="empty-state" style="padding: 20px; text-align: center;">
						<p><i class="fa-solid fa-spinner fa-spin"></i> Loading subjects...</p>
					</div>
				</div>
			</div>

			<div style="margin-top: 20px;">
				<label for="loadValidationNotes">Remarks:</label>
				<textarea id="loadValidationNotes" placeholder="Add any remarks about the subject loads..." style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Poppins', sans-serif;"></textarea>
			</div>
		</div>

		<div class="modal-buttons">
			<button class="approve-btn" onclick="approveSubjectLoads()">
				<i class="fa-solid fa-check"></i> Approve Loads
			</button>
			<button class="cancel-btn" onclick="closeLoadValidationModal()">
				Close
			</button>
		</div>
	</div>
</div>

<link rel="stylesheet" href="../../assets/css/validation-and-approval.css?v=20260406a">
<script src="../../assets/js/admin-validation.js?v=20260406a"></script>
<script src="../../assets/js/validation-and-approval.js?v=20260406a"></script>
