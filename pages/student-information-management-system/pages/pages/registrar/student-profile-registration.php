<?php
session_start();

// Check if user is authenticated and is a registrar
if (!isset($_SESSION['registrar_id'])) {
    header('Location: ../../login.php');
    exit;
}

// Reference-number fetch will populate program automatically (no dropdown needed).
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile Registration - Student Information Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/student-profile.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="brand">
            Student Information<br>Management
        </div>

        <a href="student-profile-registration.php" class="menu-item active">
            <i class="fa-solid fa-id-card"></i> Student Profile<br>Registration
        </a>
        <a href="student-tracking.php" class="menu-item">
            <i class="fa-solid fa-chart-line"></i> Student Tracking
        </a>
        <a href="student-list.php" class="menu-item">
            <i class="fa-solid fa-list"></i> Student List
        </a>

        <div class="sidebar-footer" style="margin-top: auto; padding: 20px;">
            <a href="../../api/logout.php" class="support-btn" style="text-decoration:none; color:var(--text-dark);">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="breadcrumb">
                    <span>Registration</span>
                    <span class="separator">/</span>
                    <span>Student Profile Registration</span>
                </div>
                <div class="header-title">
                    <h1>Student Profile Registration</h1>
                    <p class="subtitle">Register a new student for the system</p>
                </div>
            </div>

            <!-- Form Container -->
            <div class="form-container">
                <!-- Welcome Box -->
                <div class="welcome-box">
                    <div class="welcome-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="welcome-content">
                        <h2>Welcome to Student Information Management!</h2>
                        <p>To get started with the system, please register your student profile below. Once registered, you'll have access to all features including academic records, status tracking, and ID generation.</p>
                    </div>
                </div>

                <!-- Registration Form -->
                <form id="studentRegistrationForm" class="student-form" novalidate>
                    <input type="hidden" id="applicationId" name="applicationId" value="">
                    <!-- Reference Fetch Section -->
                    <div class="form-section" style="background:#f9fafb; border: 1px solid #e5e7eb;">
                        <h2 class="section-title">
                            <i class="fas fa-search"></i> Find Student Record
                        </h2>
                        <div class="form-row" style="align-items: flex-end;">
                            <div class="form-group" style="flex: 2;">
                                <label for="referenceNumber" style="font-weight: 600;">Reference Number <span class="required">*</span></label>
                                <input type="text" id="referenceNumber" name="referenceNumber" class="form-control" placeholder="Enter reference number (e.g., SMS-XYZ1A234)" style="border: 1px solid #d1d5db;">
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <button type="button" id="btnFetchReference" class="btn btn-primary" style="width: 100%; padding: 12px; background: var(--active-blue); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display:flex; gap: 8px; justify-content: center; align-items:center;">
                                    <i class="fas fa-sync-alt"></i> Fetch Details
                                </button>
                            </div>
                        </div>
                        <div id="fetchMessage" style="margin-top: 10px; font-size: 14px; display: none;"></div>
                    </div>

                    <!-- Student Information Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-user"></i> Student Information
                        </h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="required">*</span></label>
                                <input type="text" id="firstName" name="firstName" class="form-control" placeholder="Enter first name" required readonly>
                            </div>
                            <div class="form-group">
                                <label for="middleName">Middle Name</label>
                                <input type="text" id="middleName" name="middleName" class="form-control" placeholder="Enter middle name" readonly>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="required">*</span></label>
                                <input type="text" id="lastName" name="lastName" class="form-control" placeholder="Enter last name" required readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="birthdate">Birthdate <span class="required">*</span></label>
                                <input type="date" id="birthdate" name="birthdate" class="form-control" required readonly>
                            </div>
                            <div class="form-group" style="flex: 1;">
                                <label for="gender">Gender</label>
                                <input type="text" id="gender" name="gender" class="form-control" placeholder="Gender will auto-fill" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="contactNumber">Contact Number <span class="required">*</span></label>
                                <div class="phone-input-group">
                                    <input type="text" id="countryCode" name="countryCode" class="country-code" value="+63" readonly style="width: 60px; text-align: center; background-color: #f9fafb; border: 1px solid #d1d5db; border-radius: 8px 0 0 8px;">
                                    <input type="tel" id="contactNumber" name="contactNumber" class="form-control" placeholder="9926367123" required readonly style="border-radius: 0 8px 8px 0;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="example@gmail.com" required readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address">Address <span class="required">*</span></label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="Enter your address" required readonly>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" class="form-control" placeholder="City" readonly>
                            </div>
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" id="province" name="province" class="form-control" placeholder="Province" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Program Details Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-graduation-cap"></i> Program Details
                        </h2>
                        <div class="form-group">
                            <label for="programId">Program / Course <span class="required">*</span></label>
                            <input type="hidden" id="programId" name="programId" value="">
                            <input type="text" id="programName" name="programName" class="form-control" placeholder="Program will auto-fill from reference number" readonly>
                        </div>

                        <div class="form-group">
                            <label for="admissionDate">Admission Date <span class="required">*</span></label>
                            <input type="date" id="admissionDate" name="admissionDate" class="form-control" value="<?php echo date('Y-m-d'); ?>" required readonly>
                        </div>
                    </div>

                    <!-- Student Media Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-images"></i> Student Photo
                        </h2>
                        <div class="photo-upload-area">
                            <div class="photo-preview" id="photoPreview">
                                <img id="photoImage" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23ccc' stroke-width='2'%3E%3Ccircle cx='12' cy='8' r='4'%3E%3C/circle%3E%3Cpath d='M6 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2'%3E%3C/path%3E%3C/svg%3E" alt="Student Photo">
                            </div>
                            <div class="photo-upload-info">
                                <label for="studentPhoto" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Click to upload photo</span>
                                    <small>PNG, JPG, GIF up to 10MB</small>
                                </label>
                                <input type="file" id="studentPhoto" name="studentPhoto" class="form-control" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <!-- Digital Signature Section -->
                    <div class="form-section">
                        <h2 class="section-title">
                            <i class="fas fa-signature"></i> Digital Signature
                        </h2>
                        <div class="photo-upload-area">
                            <div class="photo-preview" id="signaturePreview">
                                <img id="signatureImage" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%23ccc' stroke-width='2'%3E%3Cpath d='M12 20h9'%3E%3C/path%3E%3Cpath d='M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z'%3E%3C/path%3E%3C/svg%3E" alt="Signature" style="max-height: 100px; width:auto;">
                            </div>
                            <div class="photo-upload-info">
                                <label for="studentSignature" class="upload-label">
                                    <i class="fas fa-file-signature"></i>
                                    <span>Click to upload signature</span>
                                    <small>PNG, JPG, GIF up to 5MB</small>
                                </label>
                                <input type="file" id="studentSignature" name="studentSignature" class="form-control" accept="image/*" required>
                            </div>
                        </div>
                        <div class="signature-container" style="display:none;">
                            <canvas id="signatureCanvas" class="signature-canvas"></canvas>
                            <button type="button" id="clearSignatureBtn" class="btn-secondary">Clear</button>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="reset" class="btn-secondary">Clear Form</button>
                        <button type="submit" class="btn-primary">Register</button>
                    </div>
                </form>
            </div>
        </main>

    <!-- Message Toast -->
    <div class="message-toast" id="messageToast">
        <span id="messageText"></span>
    </div>

    <script src="../../assets/js/student-profile.js?v=<?= time() ?>"></script>
</body>
</html>
