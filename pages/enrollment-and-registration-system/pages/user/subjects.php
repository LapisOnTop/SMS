<?php
	// Session validation
	session_start();
	if (!isset($_SESSION['enrollment_role']) || $_SESSION['enrollment_role'] !== 'user') {
		header('Location: ../../role-selection.php');
		exit;
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Online Admission - Subject Selection</title>
	<link rel="stylesheet" href="../../assets/css/selection.css">
	<link rel="stylesheet" href="../../assets/css/subjects.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
	<main class="selection-page subjects-page">
		<section class="selection-card subjects-card">
			<div class="selection-icon">
				<i class="fa-solid fa-book"></i>
			</div>

			<h1>Subject Selection</h1>
			<p class="selection-description">Select the subjects you want to enroll in for this academic year</p>

			<!-- Year & Semester Navigation -->
			<div class="navigation-tabs">
				<div class="year-selector">
					<label for="yearSelect">Year Level:</label>
					<select id="yearSelect">
						<option value="Year 1">Year 1</option>
						<option value="Year 2">Year 2</option>
						<option value="Year 3">Year 3</option>
						<option value="Year 4">Year 4</option>
					</select>
				</div>

				<div class="semester-selector">
					<label for="semesterSelect">Semester:</label>
					<select id="semesterSelect">
						<option value="1st Semester">1st Semester</option>
						<option value="2nd Semester">2nd Semester</option>
					</select>
				</div>

				<div class="credit-display">
					<span class="credit-label">Total Credits:</span>
					<span class="credit-value" id="totalCredits">0</span>
					<span class="credit-label">units</span>
				</div>
			</div>

			<!-- Subjects List & Selection Summary Container -->
			<div class="subjects-and-summary-wrapper">
				<!-- Subjects List -->
				<div class="subjects-container">
					<div id="subjectsListContainer" class="subjects-list-wrapper">
						<!-- Subjects will be loaded here by JavaScript -->
					</div>
				</div>

				<!-- Selection Summary -->
				<div class="selection-summary" id="selectionSummary" style="display: none;">
					<h3>Selected Subjects</h3>
					<div class="summary-table-wrapper">
						<table class="summary-table">
							<thead>
								<tr>
									<th>Code</th>
									<th>Units</th>
									<th></th>
								</tr>
							</thead>
							<tbody id="summaryTableBody">
								<!-- Selected subjects will appear here -->
							</tbody>
							<tfoot>
								<tr class="total-row">
									<td><strong>Total</strong></td>
									<td id="summaryTotalUnits">0</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
			</div>

			<!-- Warnings & Messages -->
			<div id="warningContainer"></div>

			<!-- Action Buttons -->
			<div class="action-buttons">
				<a href="selection.php?step=program" class="selection-btn secondary">Back</a>
				<button id="proceedBtn" class="selection-btn" disabled>Proceed to Application Form</button>
			</div>
		</section>
	</main>

	<!-- Store API endpoint for JavaScript -->
	<script>
		window.subjectsApiUrl = '../../api/get-subjects.php';
	</script>

	<script src="../../assets/js/subject-selection.js"></script>
</body>
</html>
