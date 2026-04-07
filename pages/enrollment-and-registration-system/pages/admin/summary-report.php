<?php
// Summary Report Component - Included in dashboard.php
// $db connection and $section available from dashboard.php

$stats = [
	'total_admissions' => 0,
	'total_pending' => 0,
	'total_validated' => 0,
	'total_enrolled' => 0,
	'total_paid' => 0,
	'by_program' => [],
	'by_year_level' => [],
	'by_admission_type' => [],
	'by_status' => []
];

// Keep application status in sync with paid references.
$syncSql = "
	UPDATE applications a
	INNER JOIN payments p ON p.reference_id = a.reference_id
	SET a.status = 'Enrolled'
	WHERE a.status = 'Validated'
	  AND p.payment_status IN ('Partial', 'Full')
";
$db->query($syncSql);

// Get counts
$sql = 'SELECT COUNT(*) as pending FROM applications WHERE status = "Pending"';
$result = $db->query($sql);
if ($result) {
	$row = $result->fetch_assoc();
	$stats['total_pending'] = intval($row['pending']);
}

$sql = 'SELECT 
	SUM(CASE WHEN status = "Validated" THEN 1 ELSE 0 END) as validated, 
	SUM(CASE WHEN status = "Enrolled" THEN 1 ELSE 0 END) as enrolled, 
	SUM(CASE WHEN status = "Enrolled" THEN 1 ELSE 0 END) as paid 
	FROM applications';
$result = $db->query($sql);
if ($result) {
	$row = $result->fetch_assoc();
	$stats['total_validated'] = intval($row['validated']);
	$stats['total_enrolled'] = intval($row['enrolled']);
	$stats['total_paid'] = intval($row['paid']);
}

$sql = 'SELECT COUNT(*) as total FROM applications';
$result = $db->query($sql);
if ($result) {
	$row = $result->fetch_assoc();
	$stats['total_admissions'] = intval($row['total']);
}

// By status
$stats['by_status'] = [
	['status' => 'Pending', 'count' => $stats['total_pending']],
	['status' => 'Validated', 'count' => $stats['total_validated']],
	['status' => 'Enrolled', 'count' => $stats['total_enrolled']],
	['status' => 'Paid', 'count' => $stats['total_paid']]
];

// By program
$sql = 'SELECT s.course_id, c.course_code, c.course_name, COUNT(DISTINCT a.application_id) as count FROM applications a LEFT JOIN selection s ON a.selection_id = s.selection_id LEFT JOIN courses c ON s.course_id = c.course_id WHERE s.course_id IS NOT NULL GROUP BY s.course_id, c.course_code, c.course_name ORDER BY count DESC';
$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$stats['by_program'][] = ['code' => $row['course_code'] ?? 'N/A', 'name' => $row['course_name'] ?? 'Unknown Course', 'count' => intval($row['count'])];
	}
}

// By year level
$sql = 'SELECT s.year_level, COUNT(DISTINCT a.application_id) as count FROM applications a LEFT JOIN selection s ON a.selection_id = s.selection_id WHERE s.year_level IS NOT NULL AND s.year_level != "" GROUP BY s.year_level ORDER BY CAST(s.year_level AS UNSIGNED) ASC';
$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$stats['by_year_level'][] = ['level' => 'Year ' . $row['year_level'], 'count' => intval($row['count'])];
	}
}

// By admission type
$allAdmissionTypes = ['New Regular', 'Transferee', 'Old Student'];
$admissionTypeMap = [];
$sql = 'SELECT admission_type, COUNT(*) as count FROM applications GROUP BY admission_type';
$result = $db->query($sql);
if ($result) {
	while ($row = $result->fetch_assoc()) {
		$type = ($row['admission_type'] === 'Returnee') ? 'Old Student' : $row['admission_type'];
		if (!isset($admissionTypeMap[$type])) {
			$admissionTypeMap[$type] = 0;
		}
		$admissionTypeMap[$type] += intval($row['count']);
	}
}
foreach ($allAdmissionTypes as $type) {
	$stats['by_admission_type'][] = ['type' => $type, 'count' => $admissionTypeMap[$type] ?? 0];
}
?>

<section id="reports" class="page-section <?php echo ($section === 'reports' ? 'active' : ''); ?>">
	<div class="content-header">
		<h1><i class="fa-solid fa-chart-bar"></i> Summary Reports</h1>
		<p>Overview of all admission applications and statistics</p>
	</div>

	<div class="stats-header">
		<div class="stat-card highlight">
			<h3>Total Admissions</h3>
			<div class="number"><?php echo $stats['total_admissions']; ?></div>
		</div>
		<div class="stat-card"><h3>Pending</h3><div class="number"><?php echo $stats['total_pending']; ?></div></div>
		<div class="stat-card"><h3>Validated</h3><div class="number"><?php echo $stats['total_validated']; ?></div></div>
		<div class="stat-card"><h3>Enrolled</h3><div class="number"><?php echo $stats['total_enrolled']; ?></div></div>
		<div class="stat-card"><h3>Paid</h3><div class="number"><?php echo $stats['total_paid']; ?></div></div>
	</div>

	<div class="charts-grid">
		<div class="chart-container"><h3>Admissions by Status</h3><canvas id="statusChart"></canvas></div>
		<div class="chart-container"><h3>Admissions by Type</h3><canvas id="admissionChart"></canvas></div>
		<div class="chart-container"><h3>Admissions by Program</h3><canvas id="programChart"></canvas></div>
		<div class="chart-container"><h3>Admissions by Year Level</h3><canvas id="yearLevelChart"></canvas></div>
	</div>
</section>

<script>
	window.dashboardData = <?php echo json_encode($stats); ?>;
</script>

<link rel="stylesheet" href="../../assets/css/summary-report.css">
<script src="../../assets/js/summary-report.js?v=<?php echo time(); ?>"></script>
