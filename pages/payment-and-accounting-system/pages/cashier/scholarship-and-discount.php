<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require_once '../../../../config/database.php';

// Verify logged-in cashier user
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$userRole = $_SESSION['user_role'] ?? null;

if (!$userId || $userRole !== 'cashier') {
	header('Location: ../../login.php');
	exit;
}

$loggedInUser = $username;
$navActive = 'scholarship-and-discount';

// Get database connection
$conn = sms_get_db_connection();
if (!$conn) {
	die('Database connection failed');
}

$terms = [];
$termQuery = "
	SELECT term_id, school_year, semester
	FROM terms
	ORDER BY school_year DESC, semester DESC
";
$termResult = $conn->query($termQuery);
if ($termResult instanceof mysqli_result) {
	while ($term = $termResult->fetch_assoc()) {
		$term['term_id'] = (int)$term['term_id'];
		$term['semester'] = (int)$term['semester'];
		$term['semester_label'] = ((int)$term['semester'] === 2) ? 'Second Semester' : 'First Semester';
		$term['label'] = $term['school_year'] . ' / ' . $term['semester_label'];
		$terms[] = $term;
	}
	$termResult->free();
}

if (isset($_GET['action']) && $_GET['action'] === 'search_student') {
	header('Content-Type: application/json; charset=utf-8');

	$studentNumber = trim($_GET['student_number'] ?? '');
	if ($studentNumber === '') {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Student number is required.'
		]);
		exit;
	}

	$sql = "
		SELECT
			s.student_id,
			s.student_number,
			TRIM(CONCAT(
				COALESCE(api.first_name, ''),
				CASE WHEN api.middle_name IS NOT NULL AND api.middle_name <> '' THEN CONCAT(' ', api.middle_name) ELSE '' END,
				CASE WHEN api.last_name IS NOT NULL AND api.last_name <> '' THEN CONCAT(' ', api.last_name) ELSE '' END,
				CASE WHEN api.suffix IS NOT NULL AND api.suffix <> '' THEN CONCAT(' ', api.suffix) ELSE '' END
			)) AS student_name,
			a.total_amount AS assessment_total,
			a.status AS assessment_status,
			sd.original_assessment,
			sd.discount_amount,
			sd.net_assessment
		FROM students s
		LEFT JOIN applicant_personal_info api ON api.application_id = s.application_id
		LEFT JOIN assessment a ON a.student_id = s.student_id
		LEFT JOIN student_discounts sd ON sd.id = (
			SELECT sd2.id
			FROM student_discounts sd2
			WHERE sd2.student_id = s.student_id
			ORDER BY sd2.id DESC
			LIMIT 1
		)
		WHERE s.student_number = ?
		LIMIT 1
	";

	$stmt = $conn->prepare($sql);
	if (!$stmt) {
		http_response_code(500);
		echo json_encode([
			'success' => false,
			'message' => 'Database prepare error.'
		]);
		exit;
	}

	$stmt->bind_param('s', $studentNumber);
	$stmt->execute();
	$result = $stmt->get_result();
	$student = $result ? $result->fetch_assoc() : null;
	$stmt->close();

	if (!$student) {
		http_response_code(404);
		echo json_encode([
			'success' => false,
			'message' => 'Student not found.'
		]);
		exit;
	}

	echo json_encode([
		'success' => true,
		'student' => [
			'student_id' => $student['student_id'],
			'student_number' => $student['student_number'],
			'student_name' => $student['student_name'] !== '' ? $student['student_name'] : 'Name not available',
			'assessment' => floatval($student['assessment_total'] ?? 0),
			'original_assessment' => floatval($student['original_assessment'] ?? $student['assessment_total'] ?? 0),
			'discount_amount' => floatval($student['discount_amount'] ?? 0),
			'net_assessment' => floatval($student['net_assessment'] ?? $student['assessment_total'] ?? 0),
			'assessment_status' => $student['assessment_status'] ?? null
		]
	]);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'apply_discount')) {
	header('Content-Type: application/json; charset=utf-8');

	$studentId = intval($_POST['student_id'] ?? 0);
	$discountType = trim($_POST['discount_type'] ?? '');
	$scholarshipName = trim($_POST['grant_name'] ?? '');
	$discountCategoryInput = trim($_POST['discount_category'] ?? '');
	$discountValue = floatval($_POST['discount_value'] ?? 0);
	$termId = intval($_POST['term_id'] ?? 0);
	$originalAssessment = floatval($_POST['original_assessment'] ?? 0);
	$discountAmount = floatval($_POST['discount_amount'] ?? 0);
	$netAssessment = floatval($_POST['net_assessment'] ?? 0);
	$hasPenalty = !empty($_POST['has_penalty']) ? 1 : 0;
	$penaltyAmount = floatval($_POST['penalty_amount'] ?? 500);

	if ($studentId <= 0) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Student is required.'
		]);
		exit;
	}

	if ($discountType === '') {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Discount type is required.'
		]);
		exit;
	}

	if ($discountValue < 0 || $originalAssessment < 0 || $discountAmount < 0 || $netAssessment < 0) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Invalid discount values supplied.'
		]);
		exit;
	}

	if ($termId <= 0) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Please select a valid term.'
		]);
		exit;
	}

	$discountCategory = $discountCategoryInput === 'fixed' ? 'Fixed Amount' : 'Percentage (%)';
	$penaltyAmount = $hasPenalty ? max($penaltyAmount, 0) : 500.00;
	$supportingDocument = null;

	if (isset($_FILES['support_docs']) && is_uploaded_file($_FILES['support_docs']['tmp_name'])) {
		$supportingDocument = file_get_contents($_FILES['support_docs']['tmp_name']);
	}

	$termStmt = $conn->prepare("
		SELECT term_id, school_year, semester
		FROM terms
		WHERE term_id = ?
		LIMIT 1
	");

	if (!$termStmt) {
		http_response_code(500);
		echo json_encode([
			'success' => false,
			'message' => 'Unable to prepare term lookup.'
		]);
		exit;
	}

	$termStmt->bind_param('i', $termId);
	$termStmt->execute();
	$termResult = $termStmt->get_result();
	$termRow = $termResult ? $termResult->fetch_assoc() : null;
	$termStmt->close();

	if (!$termRow) {
		http_response_code(400);
		echo json_encode([
			'success' => false,
			'message' => 'Selected term was not found.'
		]);
		exit;
	}

	$termId = intval($termRow['term_id']);

	$insertSql = "
		INSERT INTO student_discounts (
			student_id,
			term_id,
			discount_type,
			discount_category,
			discount_value,
			scholarship_name,
			original_assessment,
			discount_amount,
			net_assessment,
			supporting_documents,
			status,
			has_penalty,
			penalty_amount
		) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved', ?, ?)
	";

	$insertStmt = $conn->prepare($insertSql);
	if (!$insertStmt) {
		http_response_code(500);
		echo json_encode([
			'success' => false,
			'message' => 'Unable to prepare discount insert.'
		]);
		exit;
	}

	$null = null;
	$insertStmt->bind_param(
		'iissdsdddbid',
		$studentId,
		$termId,
		$discountType,
		$discountCategory,
		$discountValue,
		$scholarshipName,
		$originalAssessment,
		$discountAmount,
		$netAssessment,
		$null,
		$hasPenalty,
		$penaltyAmount
	);

	if ($supportingDocument !== null) {
		$insertStmt->send_long_data(9, $supportingDocument);
	}

	if (!$insertStmt->execute()) {
		http_response_code(500);
		echo json_encode([
			'success' => false,
			'message' => 'Failed to save discount record.'
		]);
		$insertStmt->close();
		exit;
	}

	$discountId = $insertStmt->insert_id;
	$insertStmt->close();

	echo json_encode([
		'success' => true,
		'message' => 'Discount applied and saved successfully.',
		'discount_id' => $discountId,
		'record' => [
			'student_id' => $studentId,
			'term_id' => $termId,
			'discount_type' => $discountType,
			'discount_category' => $discountCategory,
			'discount_value' => $discountValue,
			'scholarship_name' => $scholarshipName,
			'original_assessment' => $originalAssessment,
			'discount_amount' => $discountAmount,
			'net_assessment' => $netAssessment,
			'has_penalty' => $hasPenalty,
			'penalty_amount' => $penaltyAmount
		]
	]);
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Scholarships & Discounts - Payment and Accounting</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/cashier-management-new.css">
	<style>
		/* Allow page/content scrolling */
		html,
		body {
			min-height: 100%;
			overflow-y: auto;
		}

		/* Page Specific Styles */
		.form-card {
			background: white;
			border-radius: 12px;
			padding: 20px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			margin-bottom: 20px;
		}

		.form-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 18px;
			margin-bottom: 24px;
		}

		.form-group {
			display: flex;
			flex-direction: column;
		}

		.form-group label {
			font-size: 0.9rem;
			font-weight: 600;
			color: #333;
			margin-bottom: 8px;
		}

		.form-group input,
		.form-group select,
		.form-group textarea {
			padding: 10px;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			font-family: 'Poppins', sans-serif;
			font-size: 0.9rem;
			color: #333;
			background: #fafafa;
			transition: all 0.2s ease;
		}

		.form-group input:focus,
		.form-group select:focus,
		.form-group textarea:focus {
			outline: none;
			background: white;
			border-color: #3f69ff;
			box-shadow: 0 0 0 3px rgba(63, 105, 255, 0.1);
		}

		.search-section {
			display: flex;
			gap: 12px;
			margin-bottom: 24px;
			align-items: flex-end;
		}

		.search-section input {
			flex: 1;
			padding: 10px 14px;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			font-family: 'Poppins', sans-serif;
			height: 40px;
			box-sizing: border-box;
		}

		.search-section .btn-primary {
			flex: 0 0 auto;
			padding: 0 12px;
			white-space: nowrap;
			font-size: 0.85rem;
			min-width: auto;
			width: auto;
		}

		.search-btn {
			background: #3f69ff !important;
			color: white !important;
			padding: 0 !important;
			border: none !important;
			flex: 0 0 40px !important;
			width: 40px !important;
			height: 40px !important;
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			border-radius: 6px !important;
			cursor: pointer !important;
			transition: all 0.2s ease !important;
			font-size: 1rem !important;
		}

		.search-btn:hover {
			background: #2d5adb !important;
		}

		.btn {
			padding: 8px 16px;
			border-radius: 6px;
			font-weight: 600;
			cursor: pointer;
			border: none;
			font-family: 'Poppins', sans-serif;
			transition: all 0.2s ease;
			display: inline-flex;
			align-items: center;
			gap: 8px;
			font-size: 0.9rem;
		}

		.btn-primary {
			background: #3f69ff;
			color: white;
		}

		.btn-primary:hover {
			background: #2d5adb;
		}

		.btn-success {
			background: #10b981;
			color: white;
		}

		.btn-success:hover {
			background: #059669;
		}

		.btn-outline {
			background: white;
			color: #333;
			border: 2px solid #e0e0e0;
		}

		.btn-outline:hover {
			border-color: #3f69ff;
			color: #3f69ff;
		}

		.totals-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 12px;
			margin-bottom: 24px;
		}

		.total-card {
			background: white;
			border-radius: 12px;
			padding: 14px 16px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.total-card label {
			font-size: 0.8rem;
			color: #999;
			display: block;
			margin-bottom: 4px;
			font-weight: 500;
		}

		.total-card strong {
			font-size: 1.3rem;
			color: #333;
			font-weight: 700;
		}

		.penalty-card {
			background: white;
			border-radius: 12px;
			padding: 14px 16px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			display: flex;
			flex-direction: column;
			position: relative;
		}

		.penalty-card input[type="checkbox"] {
			position: absolute;
			top: 14px;
			right: 14px;
			width: 16px;
			height: 16px;
			cursor: pointer;
			accent-color: #3f69ff;
		}

		.penalty-card input[type="number"] {
			border: none;
			background: transparent;
			padding: 0;
			font-size: 1.3rem;
			color: #333;
			font-weight: 700;
			cursor: text;
			font-family: 'Poppins', sans-serif;
			flex: 1;
		}

		.penalty-card input[type="number"]:focus {
			outline: none;
		}

		.penalty-card input[type="number"]:disabled {
			color: #bbb;
		}

		/* Hide number input spinners */
		.penalty-card input[type="number"]::-webkit-outer-spin-button,
		.penalty-card input[type="number"]::-webkit-inner-spin-button {
			-webkit-appearance: none;
			margin: 0;
		}

		

		.action-row {
			display: flex;
			gap: 10px;
			flex-wrap: wrap;
			justify-content: flex-start;
		}

		.action-row .btn {
			flex: 0 1 auto;
			min-width: 140px;
		}

		.status-text {
			display: none;
		}

		.section-title {
			font-size: 1.1rem;
			color: #333;
			margin-bottom: 16px;
			font-weight: 600;
		}

		/* Penalty Toggle Styles */
		input[type="checkbox"] {
			accent-color: #3f69ff;
		}

		#penaltyAmountGroup {
			transition: all 0.2s ease;
		}
	</style>
</head>

<body>
	<!-- Header -->
	<header>
		<h1>
			<i class="fa-solid fa-graduation-cap"></i>
			Scholarships & Discounts
		</h1>
		<div class="header-actions">
			<div class="role-badge">
				<i class="fa-solid fa-user-shield"></i>
				<span><?php echo htmlspecialchars($loggedInUser); ?></span>
			</div>
			<a href="../../../../components/logout.php" class="logout-btn">
				<i class="fa-solid fa-sign-out-alt"></i>
				Logout
			</a>
		</div>
	</header>

	<div class="container">
		<!-- Sidebar Navigation -->
		<aside class="sidebar">
			<div class="sidebar-header">
				<h2>Cashier Menu</h2>
			</div>

			<nav class="sidebar-nav">
				<a href="analytics.php" class="nav-item <?php echo $navActive === 'analytics' ? 'active' : ''; ?>">
					<i class="fa-solid fa-chart-line"></i>
					<span>Analytics</span>
				</a>

				<a href="scholarship-and-discount.php" class="nav-item <?php echo $navActive === 'scholarship-and-discount' ? 'active' : ''; ?>">
					<i class="fa-solid fa-graduation-cap"></i>
					<span>Scholarships & Discounts</span>
				</a>

				<a href="cashier-management.php?section=payment-entry" class="nav-item">
					<i class="fa-solid fa-receipt"></i>
					<span>Payment Entry</span>
				</a>

				<a href="cashier-management.php?section=payment-history" class="nav-item">
					<i class="fa-solid fa-history"></i>
					<span>Payment History</span>
				</a>

				<a href="transaction-log.php" class="nav-item <?php echo $navActive === 'transaction-log' ? 'active' : ''; ?>">
					<i class="fa-solid fa-list-check"></i>
					<span>Transaction Log</span>
				</a>
			</nav>
		</aside>

		<!-- Main Content -->
		<main class="content-wrapper">
			<!-- Header Section -->
			<div style="padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #e0e0e0;">
				<h2 style="color: #333; margin: 0 0 8px 0; font-size: 1.8rem;">
					<i class="fa-solid fa-graduation-cap" style="color: #3f69ff; margin-right: 10px;"></i>
					Scholarship & Discount Management
				</h2>
				<p style="color: #999; margin: 0; font-size: 0.95rem;">
					Manage scholarships, discounts, and grants for students
				</p>
			</div>

			<!-- Search Section -->
			<div class="form-card">
				<h3 class="section-title">Find Student</h3>
				<div class="search-section">
					<input type="text" id="studentSearch" placeholder="Enter student ID or name">
				<button type="button" class="search-btn" id="searchButton">
					<i class="fa-solid fa-magnifying-glass"></i>
					</button>
				</div>
			</div>

			<!-- Main Form -->
			<div class="form-card">
				<h3 class="section-title">Student & Discount Information</h3>
				<div class="form-grid">
					<div class="form-group">
						<label for="studentId">Student ID</label>
						<input type="text" id="studentId" readonly>
					</div>
					<div class="form-group">
						<label for="studentName">Student Name</label>
						<input type="text" id="studentName" readonly>
					</div>
					<div class="form-group">
						<label for="discountType">Discount Type</label>
						<select id="discountType">
							<option value="">Select Discount Type</option>
							<option value="Academic Scholarship">Academic Scholarship</option>
							<option value="Athletic Scholarship">Athletic Scholarship</option>
							<option value="Sibling Discount">Sibling Discount</option>
							<option value="Loyalty Discount">Loyalty Discount</option>
						</select>
					</div>
					<div class="form-group">
						<label for="grantName">Scholarship/Grant Name</label>
						<input type="text" id="grantName" placeholder="Enter scholarship name">
					</div>
					<div class="form-group">
						<label for="discountCategory">Discount Category</label>
						<select id="discountCategory">
							<option value="percentage">Percentage (%)</option>
							<option value="fixed">Fixed Amount</option>
						</select>
					</div>
					<div class="form-group">
						<label for="discountValue">Discount Value</label>
						<input type="text" id="discountValue" placeholder="Enter discount value">
					</div>
					<div class="form-group">
						<label for="termId">Academic Term</label>
						<select id="termId">
							<option value="">Select Academic Term</option>
							<?php foreach ($terms as $term): ?>
								<option
									value="<?php echo htmlspecialchars((string)$term['term_id']); ?>"
									data-school-year="<?php echo htmlspecialchars($term['school_year']); ?>"
									data-semester-label="<?php echo htmlspecialchars($term['semester_label']); ?>"
									<?php echo count($terms) > 0 && $term === $terms[0] ? 'selected' : ''; ?>
								>
									<?php echo htmlspecialchars($term['label']); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group">
						<label for="semester">Semester</label>
						<input type="text" id="semester" readonly value="<?php echo htmlspecialchars($terms[0]['semester_label'] ?? ''); ?>">
					</div>
					<div class="form-group">
						<label for="validityPeriod">Validity Period</label>
						<input type="text" id="validityPeriod" readonly value="<?php echo htmlspecialchars($terms[0]['school_year'] ?? ''); ?>">
					</div>
					<div class="form-group">
						<label for="supportDocs">Supporting Documents</label>
						<input type="file" id="supportDocs" accept="image/*,.pdf">
					</div>
				</div>
			</div>

			<!-- Summary Cards -->
			<div class="totals-grid">
				<div class="total-card">
					<label>Original Assessment</label>
					<strong><span>₱</span><span id="originalAssessment">0.00</span></strong>
				</div>
				<div class="total-card">
					<label>Discount Amount</label>
					<strong><span>₱</span><span id="discountAmount">0.00</span></strong>
				</div>
				<div class="penalty-card">
					<input type="checkbox" id="hasPenalty" style="cursor: pointer;">
					<div>
						<label style="font-size: 0.8rem; color: #999; display: block; margin-bottom: 4px; font-weight: 500;">Total w/ Penalty</label>
						<div style="display: flex; align-items: baseline; gap: 2px;">
							<span style="font-size: 1.3rem; color: #333; font-weight: 700;">₱</span>
							<input type="number" id="totalWithPenalty" placeholder="500.00" value="500" min="0" step="0.01">
						</div>
					</div>
				</div>
				<div class="total-card">
					<label>Net Assessment</label>
					<strong><span>₱</span><span id="netAssessment">0.00</span></strong>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="form-card">
				<div class="action-row">
					<button type="button" class="btn btn-primary" id="applyButton">
						<i class="fa-solid fa-calculator"></i>
						Apply Discount
					</button>
					<button type="button" class="btn btn-outline" id="saveButton">
						<i class="fa-solid fa-floppy-disk"></i>
						Save Record
					</button>
				</div>
				<p class="status-text" id="statusText">Ready to process scholarship or discount.</p>
			</div>
		</main>
	</div>

	<script src="../../assets/js/scholarship-and-discount-db.js"></script>
</body>

</html>
