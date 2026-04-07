<?php
// Enrollment Status Component - Included in dashboard.php
// $db connection and $section available from dashboard.php

$enrollmentStatus = [];

// Keep enrollment state aligned with paid references.
$syncSql = "
	UPDATE applications a
	INNER JOIN payments p ON p.reference_id = a.reference_id
	SET a.status = 'Enrolled'
	WHERE a.status = 'Validated'
	  AND p.payment_status IN ('Partial', 'Full')
";
$db->query($syncSql);

$sql = 'SELECT a.application_id, api.first_name, api.last_name, ac.email_address, a.status, a.created_at, rn.reference_number FROM applications a LEFT JOIN applicant_personal_info api ON a.application_id = api.application_id LEFT JOIN applicant_contact ac ON a.application_id = ac.application_id LEFT JOIN reference_numbers rn ON a.reference_id = rn.reference_id WHERE a.status IN ("Validated", "Paid", "Enrolled") ORDER BY a.created_at DESC LIMIT 100';

$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$enrollmentStatus[] = $row;
	}
}
?>

<section id="enrollment-status" class="page-section <?php echo ($section === 'enrollment-status' ? 'active' : ''); ?>">
	<div class="content-header">
		<h1><i class="fa-solid fa-envelope"></i> Enrollment Status</h1>
		<p>View enrollment status of validated and enrolled students</p>
	</div>

	<div class="filter-section">
		<select id="enrollmentStatusFilter">
			<option value="All">All Status</option>
			<option value="Validated">Validated</option>
			<option value="Paid">Paid</option>
			<option value="Enrolled">Enrolled</option>
		</select>
		<input type="text" id="enrollmentSearchInput" placeholder="Search by name, email, reference number..." value="">
		<button onclick="filterEnrollmentStatus()"><i class="fa-solid fa-search"></i> Search</button>
	</div>

	<?php if (!empty($enrollmentStatus)): ?>
	<div class="table-container">
		<table>
			<thead>
				<tr>
					<th>Reference #</th><th>Full Name</th><th>Email</th><th>Status</th><th>Approved Date</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($enrollmentStatus as $student): ?>
				<tr>
					<td><strong><?php echo htmlspecialchars($student['reference_number'] ?? 'N/A'); ?></strong></td>
					<td><strong><?php echo htmlspecialchars(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')); ?></strong></td>
					<td><?php echo htmlspecialchars($student['email_address'] ?? ''); ?></td>
				<td>
					<?php 
					$status = $student['status'] ?? 'Unknown';
					$status_class = strtolower(str_replace(' ', '-', $status));
					?>
					<span class="status-badge status-<?php echo htmlspecialchars($status_class); ?>">
						<?php echo htmlspecialchars($status); ?>
					</span>
				</td>
					<td><?php echo date('M d, Y', strtotime($student['created_at'] ?? 'now')); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php else: ?>
	<div class="table-container">
		<div class="empty-state">
			<i class="fa-solid fa-inbox"></i>
			<p><strong>No enrollment records found</strong></p>
			<p style="font-size: 14px;">Approved applications will appear here for enrollment confirmation emails.</p>
		</div>
	</div>
	<?php endif; ?>
</section>

<link rel="stylesheet" href="../../assets/css/enrollment-status.css">
<script src="../../assets/js/enrollment-status.js"></script>
