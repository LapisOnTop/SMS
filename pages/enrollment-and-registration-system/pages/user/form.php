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
	<title>Online Admission - Enrollment Form</title>
	<link rel="stylesheet" href="../../assets/css/form.css?v=20260406a">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
	<header>
		<h1><i class="fa-solid fa-pen-to-square"></i><a href="landing.php" class="header-title-link"> Application Form</a></h1>
	</header>

	<main class="enrollment-page">
		<section class="enrollment-layout">
			<form id="enrollmentForm" class="form-panel" novalidate>
				<div class="steps-content">
					<section class="form-step is-active" data-step="0">
						<button type="button" class="accordion-header is-active" data-accordion-header>
							<span class="accordion-index">1</span>
							<span class="accordion-title">Basic Information</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4">
							<div class="field full">
								<label for="admissionType">Admission Type *</label>
								<select id="admissionType" name="admissionType" data-required="Admission Type">
									<option value="">Select Admission Type</option>
									<option value="New Regular">New Regular</option>
									<option value="Transferee">Transferee</option>
									<option value="Old Student">Old Student</option>
								</select>
								<p class="field-error"></p>
							</div>

							<div class="field full" id="studentNumberFieldWrap" hidden>
								<label for="studentNumber" id="studentNumberLabel">Student Number</label>
								<input id="studentNumber" name="studentNumber" type="text" maxlength="50" placeholder="Enter existing student number">
								<p class="field-error"></p>
							</div>

							<div class="field full checkbox-field">
								<label><input type="checkbox" id="workingStudent" name="workingStudent"> Are you a Working Student?</label>
							</div>

							<div class="field"><label for="lastName">Lastname *</label><input id="lastName" name="lastName" type="text" data-required="Lastname"><p class="field-error"></p></div>
							<div class="field"><label for="firstName">Firstname *</label><input id="firstName" name="firstName" type="text" data-required="Firstname"><p class="field-error"></p></div>
							<div class="field"><label for="middleName">Middlename</label><input id="middleName" name="middleName" type="text"></div>
							<div class="field"><label for="suffix">Suffix</label><input id="suffix" name="suffix" type="text"></div>

							<div class="field"><label for="sex">Sex *</label><select id="sex" name="sex" data-required="Sex"><option value="">Select Sex</option><option>Male</option><option>Female</option></select><p class="field-error"></p></div>
							<div class="field"><label for="civilStatus">Civil Status *</label><select id="civilStatus" name="civilStatus" data-required="Civil Status"><option value="">Select Civil Status</option><option>Single</option><option>Married</option><option>Widowed</option><option>Separated</option></select><p class="field-error"></p></div>
							<div class="field"><label for="religion">Religion</label><input id="religion" name="religion" type="text"></div>
							<div class="field"><label for="birthdate">Birthday *</label><input id="birthdate" name="birthdate" type="date" data-required="Birthday" data-validate="age18"><p class="field-error"></p></div>

							<div class="field"><label for="email">Email Address *</label><input id="email" name="email" type="email" data-required="Email Address" data-validate="email"><p class="field-error"></p></div>
							<div class="field"><label for="contact">Contact Number *</label><input id="contact" name="contact" type="text" inputmode="numeric" maxlength="11" data-required="Contact Number" data-validate="phone"><p class="field-error"></p></div>
							<div class="field full"><label for="fbName">Facebook / Messenger Name *</label><input id="fbName" name="fbName" type="text" data-required="Facebook / Messenger Name"><p class="field-error"></p></div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="1">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">2</span>
							<span class="accordion-title">Address</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4 address-grid">
							<div class="field full"><label for="address">Address # *</label><input id="address" name="address" type="text" data-required="Address"><p class="field-error"></p></div>
							<div class="field span-2 address-region-field" id="regionFieldWrap">
								<label for="region">Region *</label>
								<select id="region" name="region" data-required="Region">
									<option value="">Loading regions...</option>
								</select>
								<p class="field-error"></p>
							</div>
							<div class="field span-2 address-city-field" id="cityFieldWrap">
								<label for="city">Municipality / City *</label>
								<select id="city" name="city" data-required="Municipality / City" disabled>
									<option value="">Select a region first</option>
								</select>
								<p class="field-error"></p>
							</div>
							<div class="field full address-barangay-field" id="barangayFieldWrap">
								<label for="barangay">Barangay *</label>
								<select id="barangay" name="barangay" data-required="Barangay" disabled>
									<option value="">Select a city first</option>
								</select>
								<p class="field-error"></p>
							</div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="2">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">3</span>
							<span class="accordion-title">Parent's/Guardian's Information</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4">
							<div class="field full section-label"><label>Father's Information</label></div>
							<div class="field"><label for="fatherLast">Father's Lastname *</label><input id="fatherLast" name="fatherLast" type="text" data-required="Father's Lastname"><p class="field-error"></p></div>
							<div class="field"><label for="fatherFirst">Father's Firstname *</label><input id="fatherFirst" name="fatherFirst" type="text" data-required="Father's Firstname"><p class="field-error"></p></div>
							<div class="field"><label for="fatherMiddle">Father's Middlename</label><input id="fatherMiddle" name="fatherMiddle" type="text"></div>
							<div class="field"><label for="fatherSuffix">Father's Suffix</label><input id="fatherSuffix" name="fatherSuffix" type="text"></div>

							<div class="field full section-label"><label>Mother's Maiden Name</label></div>

							<div class="field"><label for="motherLast">Lastname *</label><input id="motherLast" name="motherLast" type="text" data-required="Mother's Lastname"><p class="field-error"></p></div>
							<div class="field"><label for="motherFirst">Firstname *</label><input id="motherFirst" name="motherFirst" type="text" data-required="Mother's Firstname"><p class="field-error"></p></div>
							<div class="field"><label for="motherMiddle">Middlename</label><input id="motherMiddle" name="motherMiddle" type="text"></div>
							<div class="field"><label for="motherSuffix">Suffix</label><input id="motherSuffix" name="motherSuffix" type="text"></div>

							<div class="field full section-label"><label>Parent/Guardian Information</label></div>
							<div class="field"><label for="guardianRelation">Relation *</label><select id="guardianRelation" name="guardianRelation" data-required="Relation"><option value="">Select Relation</option><option value="Father">Father</option><option value="Mother">Mother</option><option value="Guardian">Guardian</option></select><p class="field-error"></p></div>
							<div class="field"><label for="guardianLast">Parent/Guardian Lastname *</label><input id="guardianLast" name="guardianLast" type="text" data-required="Parent/Guardian Lastname"><p class="field-error"></p></div>
							<div class="field"><label for="guardianFirst">Parent/Guardian Firstname *</label><input id="guardianFirst" name="guardianFirst" type="text" data-required="Parent/Guardian Firstname"><p class="field-error"></p></div>
							<div class="field"><label for="guardianMiddle">Parent/Guardian Middlename</label><input id="guardianMiddle" name="guardianMiddle" type="text"></div>
							<div class="field"><label for="guardianSuffix">Parent/Guardian Suffix</label><input id="guardianSuffix" name="guardianSuffix" type="text"></div>
							<div class="field"><label for="guardianContact">Parent/Guardian Contact Number *</label><input id="guardianContact" name="guardianContact" type="text" inputmode="numeric" maxlength="11" data-required="Parent/Guardian Contact Number" data-validate="phone"><p class="field-error"></p></div>

							<div class="field"><label for="guardianOccupation">Parent/Guardian Occupation</label><input id="guardianOccupation" name="guardianOccupation" type="text"></div>
							<div class="field full checkbox-field"><label><input id="is4ps" name="is4ps" type="checkbox"> Parent / Guardian member of 4Ps?</label></div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="3">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">4</span>
							<span class="accordion-title">Enrollment Information</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4">
							<div class="field full">
								<label for="preferredBranch">Preferred Branch</label>
								<input id="preferredBranch" name="preferredBranch" type="text" value="Main Branch | #1071 Brgy. Kaligayahan, Quirino Highway Novaliches Quezon City" readonly>
								<input id="branchId" name="branchId" type="hidden" value="1">
							</div>
							<div class="field span-3">
								<label for="course">Course</label>
								<input id="course" name="course" type="text" value="Bachelor of Science in Information Technology" readonly>
							</div>
							<div class="field">
								<label for="yearLevel">Year Level *</label>
								<select id="yearLevel" name="yearLevel" data-required="Year Level"></select>
								<p class="field-error"></p>
							</div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="4">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">5</span>
							<span class="accordion-title">Educational Background</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4">
							<div class="field span-3"><label for="primarySchool">Primary *</label><input id="primarySchool" name="primarySchool" type="text" data-required="Primary"><p class="field-error"></p></div>
							<div class="field"><label for="primaryGrad">Year Graduated *</label><input id="primaryGrad" name="primaryGrad" type="month" data-required="Primary Year Graduated"><p class="field-error"></p></div>
							<div class="field span-3"><label for="secondarySchool">Secondary *</label><input id="secondarySchool" name="secondarySchool" type="text" data-required="Secondary"><p class="field-error"></p></div>
							<div class="field"><label for="secondaryGrad">Year Graduated *</label><input id="secondaryGrad" name="secondaryGrad" type="month" data-required="Secondary Year Graduated"><p class="field-error"></p></div>
							<div class="field span-3"><label for="lastSchool">Last School Attended *</label><input id="lastSchool" name="lastSchool" type="text" data-required="Last School Attended"><p class="field-error"></p></div>
							<div class="field"><label for="lastSchoolYear">Last School Year Attended *</label><input id="lastSchoolYear" name="lastSchoolYear" type="month" data-required="Last School Year Attended"><p class="field-error"></p></div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="5">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">6</span>
							<span class="accordion-title">How did you hear about our school?</span>
						</button>
						<div class="accordion-body">
						<div class="form-grid cols-4">
							<div class="field full">
								<label for="referral">Options *</label>
								<select id="referral" name="referral" data-required="Referral Option">
									<option value="">Select option</option>
									<option value="Social Media Account">Social Media Account</option>
									<option value="Adviser/Referral/Others">Adviser/Referral/Others</option>
									<option value="Walk-in/No Referral">Walk-in/No Referral</option>
								</select>
								<p class="field-error"></p>
							</div>
						</div>
						</div>
					</section>

					<section class="form-step" data-step="6">
						<button type="button" class="accordion-header" data-accordion-header>
							<span class="accordion-index">7</span>
							<span class="accordion-title">Summary</span>
						</button>
						<div class="accordion-body">
						<div id="summaryContent" class="summary-content"></div>
						</div>
					</section>

					<div class="form-actions">
						<button type="button" id="btnBack" class="btn-secondary is-hidden">Back</button>
						<button type="button" id="btnNext" class="btn-primary">Next</button>
						<button type="button" id="btnSubmit" class="btn-primary is-hidden">Submit</button>
					</div>
				</div>
			</form>

			<aside class="requirements-panel">
				<div class="requirements-icon">
					<i class="fa-solid fa-graduation-cap"></i>
				</div>
				<h3>College</h3>
				<h4>College Requirements</h4>
				<p>Original Copy of the following documents shall be submitted to your respective branch:</p>
				<div class="requirements-group">
					<strong>College New/Freshmen</strong>
					<ul>
						<li>Form 138 (Report Card)</li>
						<li>Form 137</li>
						<li>Certificate of Good Moral</li>
						<li>PSA Authenticated Birth Certificate</li>
						<li>Passport Size ID Picture (White Background, Formal Attire) - 2pcs.</li>
						<li>Barangay Clearance</li>
					</ul>
				</div>
				<div class="requirements-group">
					<strong>College Transferee</strong>
					<ul>
						<li>Transcript of Records from Previous School</li>
						<li>Honorable Dismissal</li>
						<li>Certificate of Good Moral</li>
						<li>PSA Authenticated Birth Certificate</li>
						<li>Passport Size ID Picture (White Background, Formal Attire) - 2pcs.</li>
						<li>Barangay Clearance</li>
					</ul>
				</div>
			</aside>
		</section>
	</main>

	<footer>
		<span>&copy; 2026 SMS System Administration. All rights reserved.</span>
	</footer>

	<?php include '../../components/status.php'; ?>
	
	<div id="toastContainer" class="toast-container"></div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="../../assets/js/form.js?v=20260406c"></script>
</body>
</html>
