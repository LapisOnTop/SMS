<?php
require_once __DIR__ . '/../config/app.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>SMS Dashboard</title>

  <!-- Fonts and Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="<?php echo htmlspecialchars(sms_url('assets/css/index.css'), ENT_QUOTES); ?>">
</head>

<body>

  <header>
    <h1><i class="fa-solid fa-chart-line"></i> SMS Dashboard</h1>
  </header>

  <div class="dashboard">

    <a href="<?php echo htmlspecialchars(sms_url('pages/student-information-management-system/pages/role-selection.php'), ENT_QUOTES); ?>" class="card" data-full-text="Student Information Management">
      <i class="fa-solid fa-graduation-cap"></i>
      <span>SIM</span>
    </a>

    <a href="<?php echo htmlspecialchars(sms_url('pages/enrollment-and-registration-system/role-selection.php'), ENT_QUOTES); ?>" class="card" data-full-text="Enrollment and Registration">
      <i class="fa-solid fa-user-plus"></i>
      <span>ER</span>
    </a>

    <a href="<?php echo htmlspecialchars(sms_url('pages/curriculum-and-course-management-system/login.php'), ENT_QUOTES); ?>" class="card" data-full-text="Curriculum and Course Management">
      <i class="fa-solid fa-book-open"></i>
      <span>CCM</span>
    </a>

    <a href="<?php echo htmlspecialchars(sms_url('pages/class-scheduling-and-section-management-system/index.php'), ENT_QUOTES); ?>" class="card" data-full-text="Class Scheduling and Section Management">
      <i class="fa-solid fa-calendar-days"></i>
      <span>CSSM</span>
    </a>

    <a href="<?php echo htmlspecialchars(sms_url('pages/grades-and-assessment-management-system/role-selection.php'), ENT_QUOTES); ?>" class="card" data-full-text="Grades and Assessment Management">
      <i class="fa-solid fa-chart-bar"></i>
      <span>GAM</span>
    </a>

    <a href="<?php echo htmlspecialchars(sms_url('pages/payment-and-accounting-system/login.php'), ENT_QUOTES); ?>" class="card" data-full-text="Payment and Accounting">
      <i class="fa-solid fa-money-bill-wave"></i>
      <span>PA</span>
    </a>

     <a href="<?php echo htmlspecialchars(sms_url('pages/document-and-credentials-management-system/login.php'), ENT_QUOTES); ?>" class="card" data-full-text="Document and Credentials Management">
      <i class="fa-solid fa-file-lines"></i>
      <span>DCM</span>
    </a>

     <a href="<?php echo htmlspecialchars(sms_url('pages/human-resources-management-system/pages/dashboard.php'), ENT_QUOTES); ?>" class="card" data-full-text="Human Resources Management System">
      <i class="fa-solid fa-users"></i>
      <span>HR</span>
    </a> 

    <a href="<?php echo htmlspecialchars(sms_url('pages/user-management-system/role-selection.php'), ENT_QUOTES); ?>" class="card" data-full-text="User Management">
      <i class="fa-solid fa-users-cog"></i>
      <span>UM</span>
    </a>

  </div>

  <footer>
    <span>&copy; 2026 SMS System Administration. All rights reserved.</span>
  </footer>

</body>
</html>