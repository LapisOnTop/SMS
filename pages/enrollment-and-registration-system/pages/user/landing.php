<?php
	// Start session
	session_start();

	// Get and validate role parameter
	$selectedRole = isset($_GET['role']) ? strtolower(trim($_GET['role'])) : null;
	
	// If no valid role is provided, redirect back to role selection
	if (!$selectedRole || $selectedRole !== 'user') {
		header('Location: ../../role-selection.php');
		exit;
	}

	// Store role in session for use throughout enrollment flow
	$_SESSION['enrollment_role'] = $selectedRole;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Admission - Landing</title>
  <link rel="stylesheet" href="../../assets/css/landing.css">
  <link rel="stylesheet" href="../../assets/css/terms-and-condition.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <!-- HEADER -->
  <header>
    <h1><i class="fa-solid fa-user-plus"></i> <a href="../../../index.php" class="header-title-link">Online Application</a></h1>
  </header>

  <!-- MAIN CONTENT -->
  <main>
    <section class="landing-content">
      <!-- WELCOME SECTION -->
      <div class="content-block">
        <h2>Welcome to Our Online Admission Process</h2>
        <p>The admission's online application process is quick, easy and secure. You can start filling in your application now, save your work, and complete it later. You will receive a unique applicant number, which allows you to access your personal application, change it and add documents and submit or start new applications any time you want.</p>

        <h3>Basic Requirements</h3>
        <p>Before you start filling in your application, please keep the following basic requirements in mind:</p>
        <ul>
          <li>You are interested in enrolling in a college program.</li>
          <li>You want to study and develop yourself in a new environment.</li>
          <li>You are able to write and communicate in the language the studies are conducted in.</li>
          <li>You have completed the basic admission requirements for college.</li>
        </ul>
      </div>

      <!-- TRACK AND COURSE SECTION -->
      <div class="content-block">
        <h2>Find a College Course of Your Interest</h2>
        <p>The first thing you have to do to get started with the application process is to find a college course that interests you. You can use the course finder to sort through all the different opportunities. Next to the course listing, you will find buttons to start the application.</p>
      </div>

      <!-- REQUIRED DOCUMENTS SECTION -->
      <div class="content-block">
        <h2>Required Documents for Application</h2>
        <p>In the online application form, you have to submit personal information, academic results and a reference(s) from your teacher(s).</p>

        <h3>Documents required for the application:</h3>
        <p style="font-weight: 600; margin-bottom: 20px;">Original copy of the following:</p>

        <div class="requirements-grid">
          <div class="requirement-card">
            <h4><i class="fas fa-book"></i> College New/Freshmen Requirements:</h4>
            <ul>
              <li>Form 138 (Report Card)</li>
              <li>Form 137</li>
              <li>Certificate of Good Moral</li>
              <li>PSA Authenticated Birth Certificates</li>
              <li>Passport Size ID Picture (White Background, Format Attire) - 2pcs.</li>
              <li>Barangay Clearance</li>
            </ul>
          </div>

          <div class="requirement-card">
            <h4><i class="fas fa-exchange-alt"></i> College Transferee/Old Student Requirements:</h4>
            <ul>
              <li>Transcript of Records from Previous School</li>
              <li>Honorable Dismissal</li>
              <li>Certificate of Good Moral</li>
              <li>PSA Authenticated Birth Certificate</li>
              <li>Passport Size ID Picture (White Background, Format Attire) - 2pcs.</li>
              <li>Barangay Clearance</li>
            </ul>
          </div>
        </div>

        <h3 style="margin-top: 30px;">Important Notes:</h3>
        <ul>
          <li>During your application process, please provide a valid personal email address. This email will be used by the Admissions and Registrar's Office to communicate with you. It is crucial that you regularly check your email for important updates, including instructions, acceptance notifications, the application link, and login credentials for your student account.</li>
          <li>Our personnel are not allowed to correct any mistake in your application. After the application is accepted, it is forwarded directly to the proper authorities. Please make sure that all information presented in the application form are correct and error-free.</li>
          <li>If there are still significant mistakes in the application, the Admissions Office will not accept the application. In this case the application is sent back to the applicant with directions so the applicant could correct the mistakes. If the mistakes are corrected, the application must be resubmitted by the applicant.</li>
          <li>Your privacy is important to us. The information that you entered in your application form will remain strictly confidential and we will only use them to contact you regarding your application.</li>
          <li>Your online readiness is also important to us. With the challenge that we are in right now, we would like to understand if you are ready for any of our learning modalities. Do not worry if you are not ready of this time. We will give you enough time to prepare for your most convenient modality based on what we are offering.</li>
        </ul>
      </div>

      <!-- ACCEPTANCE SECTION -->
      <div class="content-block">
        <h2>Letter of Acceptance</h2>
        <p>After successfully completing the earlier parts of the enrolment process, you will receive an official e-mail of acceptance from us.</p>
        <p>The letter of acceptance can be conditional or unconditional.</p>
      </div>

      <!-- ACTION SECTION -->
      <div class="content-block action-block">
        <h3>Ready to Apply?</h3>
        <p style="margin-bottom: 30px;">Proceed with your college online application.</p>
        <button type="button" class="btn-proceed" data-open-modal="true">
          <i></i> Proceed to Online Application
        </button>
      </div>
    </section>
  </main>

  <!-- FOOTER -->
  <footer>
    <span>&copy; 2026 SMS System Administration. All rights reserved.</span>
    <span> | <a href="../../../website.php">Visit Website</a></span>
  </footer>

  <?php include '../../components/terms-and-condition.php'; ?>
  <script src="../../assets/js/landing.js"></script>
</body>
</html>
