<?php
require_once __DIR__ . '/../../../../../config/database.php';
require_once __DIR__ . '/../../includes/sim_bootstrap.php';
$appBoot = compute_app_boot(new StudentRepository(sim_db()));
if (!isset($_SESSION['student_id'])) {
    header('Location: ../../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <script>window.__API_BASE__ = '../../'; window.__APP_BOOT__ = <?php echo json_encode(
        $appBoot,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
    ); ?>;</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Personal Information</title>
    <link rel="icon" href="../../assets/img/bestlink.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-bg: #1e2532;
            --active-blue: #2563eb;
            --bg-light: #f3f4f6;
            --text-dark: #1f2937;
            --text-gray: #6b7280;
            --card-gold: #fbbf24;
            --success-green: #10b981;
            --danger-red: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            background-color: var(--bg-light);
            min-height: 100vh;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #9ca3af;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            flex-shrink: 0;
            height: 100vh;
            position: sticky;
            top: 0;
            overflow-y: auto;
            animation: fadeInPage 0.3s ease-out;
        }

        .brand {
            padding: 0 20px 30px;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .menu-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: 0.2s;
            font-size: 0.9rem;
            text-decoration: none;
            color: #9ca3af;
        }

        .menu-item:hover {
            color: white;
        }

        .menu-item.active {
            background-color: var(--active-blue);
            color: white;
            border-right: 4px solid var(--card-gold);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .support-btn {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: 0.2s;
            color: var(--text-dark);
            text-decoration: none;
        }

        .support-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
            animation: fadeInPage 0.3s ease-out;
        }

        @keyframes fadeInPage {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .breadcrumbs {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.85rem;
            width: 100%;
            color: var(--text-gray);
        }

        .user-profile {
            margin-left: auto;
            position: relative;
            cursor: pointer;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--active-blue);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-dropdown {
            position: absolute;
            top: 40px;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 150px;
            display: none;
            z-index: 100;
            overflow: hidden;
        }

        .profile-dropdown.show { display: block; }

        .dropdown-item {
            padding: 10px 15px;
            font-size: 0.9rem;
            color: var(--text-dark);
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }

        .dropdown-item:hover { background-color: #f9fafb; }
        .dropdown-item:last-child { border-bottom: none; }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .subtitle {
            color: var(--text-gray);
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        /* --- Section Titles --- */
        .section-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 20px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title i {
            color: var(--active-blue);
            font-size: 0.95rem;
        }

        /* --- Form Styles --- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-gray);
            font-weight: 500;
        }

        .form-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: white;
            font-size: 0.95rem;
            transition: 0.2s;
            color: var(--text-dark);
            font-family: 'Inter', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--active-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input[readonly],
        .form-input:disabled {
            background-color: #e5e7eb;
            color: #6b7280;
            cursor: not-allowed;
        }

        .read-only-field {
            background-color: #f3f4f6 !important;
            color: #6b7280 !important;
            cursor: not-allowed;
        }

        .grid-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        /* --- Profile & Signature Upload Card --- */
        .upload-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            margin-bottom: 30px;
        }

        .upload-card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .upload-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .upload-section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .upload-section-title i {
            color: var(--active-blue);
        }

        .photo-frame {
            width: 160px;
            height: 160px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            overflow: hidden;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 50% 20%;
        }

        .photo-frame .placeholder-icon {
            font-size: 3rem;
            color: #d1d5db;
        }

        /* --- Signature Canvas Container --- */
        .sig-canvas-wrap {
            width: 100%;
            max-width: 340px;
            height: 140px;
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            background: white;
            position: relative;
            overflow: hidden;
            cursor: crosshair;
            transition: border-color 0.2s;
        }

        .sig-canvas-wrap:hover {
            border-color: var(--active-blue);
        }

        .sig-canvas-wrap.signing {
            border-color: var(--active-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .sig-canvas-wrap canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .sig-canvas-wrap .sig-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #d1d5db;
            font-size: 0.85rem;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }

        .sig-canvas-wrap .sig-placeholder i {
            font-size: 1.5rem;
        }

        .sig-canvas-wrap.has-sig .sig-placeholder {
            opacity: 0;
        }

        /* Signature preview image */
        .sig-preview-img {
            width: 100%;
            max-width: 340px;
            height: 140px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            object-fit: contain;
            background: white;
        }

        /* Lock overlay */
        .locked-overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(243, 244, 246, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            border-radius: 10px;
            z-index: 10;
        }

        .locked-overlay i {
            font-size: 1.2rem;
            color: var(--success-green);
        }

        /* --- Upload Button --- */
        .upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            background: white;
            color: var(--text-dark);
            transition: 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .upload-btn:hover {
            background: #f9fafb;
            border-color: var(--active-blue);
            color: var(--active-blue);
        }

        .upload-btn.primary {
            background: var(--active-blue);
            color: white;
            border-color: var(--active-blue);
        }

        .upload-btn.primary:hover {
            background: #1d4ed8;
        }

        .upload-btn.danger-outline {
            color: var(--danger-red);
            border-color: #fca5a5;
        }

        .upload-btn.danger-outline:hover {
            background: #fef2f2;
        }

        .upload-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }

        .upload-hint {
            font-size: 0.75rem;
            color: #9ca3af;
            text-align: center;
            line-height: 1.4;
        }

        .upload-locked-msg {
            font-size: 0.75rem;
            color: var(--success-green);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: center;
        }

        /* --- Phone Input --- */
        .phone-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: white;
            transition: 0.2s;
            overflow: hidden;
        }

        .phone-input-wrapper:focus-within {
            border-color: var(--active-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .phone-input-prefix {
            display: flex;
            align-items: center;
            height: 100%;
            border-right: 1px solid #e5e7eb;
            background: transparent;
            padding: 0 5px;
        }

        .phone-country-trigger {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            cursor: default;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            white-space: nowrap;
            pointer-events: none;
        }

        .phone-country-code {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .phone-country-dial {
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .phone-input-wrapper input {
            flex: 1;
            padding: 10px 12px;
            border: none;
            background: transparent;
            font-size: 0.95rem;
            color: var(--text-dark);
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        /* --- Validation Errors --- */
        .phone-input-wrapper.error,
        .form-input.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }

        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .phone-input-wrapper.shake,
        .form-input.shake {
            animation: shakeError 0.4s ease-in-out;
        }

        /* --- Footer Buttons --- */
        .btn-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            font-size: 0.9rem;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary {
            background-color: var(--active-blue);
            color: white;
        }

        .btn-primary:hover { background-color: #1d4ed8; }

        .btn-secondary {
            background-color: #f3f4f6;
            color: var(--text-dark);
        }

        .btn-secondary:hover { background-color: #e5e7eb; }

        /* --- Responsive --- */
        @media (max-width: 900px) {
            .upload-card-grid {
                grid-template-columns: 1fr;
            }
            .grid-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                flex-direction: row;
                padding: 10px;
            }
            .main-content { padding: 20px; }
        }
    </style>
</head>

<body>

    <nav class="sidebar">
        <div class="brand">
            Student Information<br>Management
        </div>

        <a href="update-personal-info.php" class="menu-item active">
            <i class="fa-solid fa-edit"></i> Update Personal<br>Information
        </a>
        <a href="academic-records.php" class="menu-item ">
            <i class="fa-solid fa-file-alt"></i> Academic Records
        </a>
        <a href="student-id-generation.php" class="menu-item ">
            <i class="fa-solid fa-id-card"></i> Student ID Generation
        </a>

        <div class="sidebar-footer">
            <a href="../../api/logout.php" class="support-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </nav>


    <main class="main-content">
        <div class="breadcrumbs">
            Registration / Update Personal Information

            <div class="user-profile" id="userAvatarBtn">
                <div class="user-avatar">
                    <i class="fa-solid fa-bars"></i>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                    <div class="dropdown-item">Profile</div>
                    <div class="dropdown-item">Settings</div>
                </div>
            </div>
        </div>

        <h1 class="page-title">Update Personal Information: <span id="studentNameDisplay"
                style="color: var(--active-blue);">...</span></h1>
        <p class="subtitle">Search and modify student records efficiently.</p>

        <!-- ===== Photo & Signature Card ===== -->
        <div class="upload-card">
            <div class="upload-card-grid">
                <!-- Photo Section -->
                <div class="upload-section">
                    <div class="upload-section-title"><i class="fa-solid fa-camera"></i> Profile Picture</div>
                    <div class="photo-frame" id="photoFrame">
                        <i class="fa-solid fa-user placeholder-icon" id="photoPlaceholder"></i>
                        <img src="" id="photoPreview" style="display: none;">
                    </div>
                    <div class="btn-row" id="photoControls">
                        <button class="upload-btn primary" type="button" id="photoUploadBtn"
                            onclick="document.getElementById('photoInput').click()">
                            <i class="fa-solid fa-upload"></i> Upload Photo
                        </button>
                        <input type="file" id="photoInput" accept="image/webp,.webp,image/jpeg,image/png" style="display: none;">
                    </div>
                    <p class="upload-hint" id="photoHint">Upload a professional photo with white background. WebP format preferred.</p>
                    <div class="upload-locked-msg" id="photoLockedMsg" style="display: none;">
                        <i class="fa-solid fa-circle-check"></i> Photo saved — cannot be changed
                    </div>
                </div>

                <!-- Signature Section -->
                <div class="upload-section">
                    <div class="upload-section-title"><i class="fa-solid fa-signature"></i> Digital Signature</div>

                    <!-- Drawing Canvas (shown when no saved signature) -->
                    <div class="sig-canvas-wrap" id="sigCanvasWrap">
                        <canvas id="signaturePad"></canvas>
                        <div class="sig-placeholder" id="sigPlaceholder">
                            <i class="fa-solid fa-pen-nib"></i>
                            <span>Draw your signature here</span>
                        </div>
                    </div>

                    <!-- Preview Image (shown when signature is saved/locked) -->
                    <img id="signaturePreview" class="sig-preview-img" src="" style="display: none;">

                    <div class="btn-row" id="sigControls">
                        <button id="clearSignatureBtn" class="upload-btn danger-outline" type="button">
                            <i class="fa-solid fa-eraser"></i> Clear
                        </button>
                        <button class="upload-btn" type="button" onclick="document.getElementById('signatureInput').click()">
                            <i class="fa-solid fa-file-import"></i> Upload File
                        </button>
                        <input type="file" id="signatureInput" accept="image/png,image/jpeg" style="display: none;">
                    </div>
                    <p class="upload-hint" id="sigHint">Sign inside the box or upload a signature image.</p>
                    <div class="upload-locked-msg" id="signatureLockedMsg" style="display: none;">
                        <i class="fa-solid fa-circle-check"></i> Signature saved — cannot be changed
                    </div>
                </div>
            </div>
        </div>

        <form id="updateForm">
            <!-- Student Information Section -->
            <div class="section-title"><i class="fa-solid fa-user-pen"></i> Student Information</div>

            <div class="form-group">
                <label class="form-label">Student ID <span
                        style="font-size: 0.8rem; color: #9ca3af;">(Auto-generated)</span></label>
                <input type="text" class="form-input read-only-field" id="studentId" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Full Name <span style="font-size: 0.8rem; color: #9ca3af;">(Read-only)</span></label>
                <input type="text" class="form-input read-only-field" id="fullName" readonly style="background-color: #e5e7eb; color: #6b7280; pointer-events: none;">
            </div>

            <div class="form-group">
                <label class="form-label">Birthdate <span style="font-size: 0.8rem; color: #9ca3af;">(Read-only)</span></label>
                <input type="date" class="form-input read-only-field" id="birthdate" readonly style="background-color: #e5e7eb; color: #6b7280; pointer-events: none;">
            </div>

            <div class="grid-row">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Gender <span style="font-size: 0.8rem; color: #9ca3af;">(Read-only)</span></label>
                    <select class="form-input read-only-field" id="gender" disabled>
                        <option value="" disabled>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="grid-row" style="margin-top: 20px;">
                <div class="form-group">
                    <label class="form-label">Contact Number <span style="color: #ef4444;">*</span></label>
                    <div class="phone-input-wrapper">
                        <div class="phone-input-prefix">
                            <div class="phone-country-trigger">
                                <span class="phone-country-code">PH</span>
                                <span class="phone-country-dial">+63</span>
                            </div>
                        </div>
                        <input type="tel" id="contactNumber" placeholder="9XXXXXXXXX" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span style="color: #ef4444;">*</span></label>
                    <input type="email" class="form-input" id="email" required>
                </div>
            </div>

            <div class="section-title" style="margin-top: 15px;"><i class="fa-solid fa-location-dot"></i> Address Information</div>
            
            <!-- Hidden original address field for backend -->
            <input type="hidden" id="address">

            <div class="form-group">
                <label class="form-label">Street / Barangay <span style="color: #ef4444;">*</span></label>
                <input type="text" class="form-input split-addr" id="addrStreet" placeholder="e.g. 123 Main St, Brgy. San Jose" required>
            </div>

            <div class="grid-row">
                <div class="form-group">
                    <label class="form-label">Province <span style="color: #ef4444;">*</span></label>
                    <select class="form-input split-addr" id="addrProvince" required>
                        <option value="" disabled selected>Select Province</option>
                        <option value="Metro Manila">Metro Manila</option>
                        <option value="Bulacan">Bulacan</option>
                        <option value="Cavite">Cavite</option>
                        <option value="Laguna">Laguna</option>
                        <option value="Rizal">Rizal</option>
                        <option value="Batangas">Batangas</option>
                        <option value="Pampanga">Pampanga</option>
                        <option value="Nueva Ecija">Nueva Ecija</option>
                        <option value="Tarlac">Tarlac</option>
                        <option value="Quezon">Quezon</option>
                        <option value="Cebu">Cebu</option>
                        <option value="Davao del Sur">Davao del Sur</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">City / Municipality <span style="color: #ef4444;">*</span></label>
                    <input type="text" class="form-input split-addr" id="addrCity" placeholder="e.g. Quezon City" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Postal / Zip Code <span style="color: #ef4444;">*</span></label>
                <input type="text" class="form-input split-addr" id="addrPostal" placeholder="e.g. 1100" required>
            </div>

            <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;">

            <!-- Program Details Section -->
            <div class="section-title"><i class="fa-solid fa-graduation-cap"></i> Program Details</div>

            <div class="form-group">
                <label class="form-label">Program / Course <span style="color: #ef4444;">*</span></label>
                <select class="form-input" id="program">
                    <option value="BS Computer Science">BS Computer Science</option>
                    <option value="BS Information Technology">BS Information Technology</option>
                    <option value="BS Information Systems">BS Information Systems</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Year Level <span style="color: #ef4444;">*</span></label>
                <select class="form-input" id="yearLevel">
                    <option value="1st Year" selected>1st Year</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Admission Date</label>
                <input type="date" class="form-input" id="admissionDate">
            </div>

            <div class="btn-footer">
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset
                    Changes</button>
                <button type="submit" class="btn btn-primary">Save Changes <i class="fa-solid fa-floppy-disk"
                        style="font-size: 0.9rem;"></i></button>
            </div>

        </form>
    </main>

    <!-- Import our Simple Database -->
    <script src="../../assets/js/db-php.js"></script>
    <script src="../../assets/js/admin.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const DIAL_CODE = '+63';

            function digitsOnly(s) {
                return String(s || '').replace(/\D/g, '');
            }

            function getPhoneRules() {
                return {
                    min: 10,
                    max: 10,
                    placeholder: '9XXXXXXXXX',
                    normalizeNational: (nationalDigits) => {
                        if (nationalDigits.startsWith('0')) nationalDigits = nationalDigits.slice(1);
                        nationalDigits = nationalDigits.replace(/^[^9]+/, '');
                        return nationalDigits;
                    }
                };
            }

            function setupPhoneInput(numberInputId) {
                const numberEl = document.getElementById(numberInputId);
                if (!numberEl) return;

                const rules = getPhoneRules();

                numberEl.setAttribute('inputmode', 'numeric');
                numberEl.setAttribute('autocomplete', 'tel');
                numberEl.setAttribute('pattern', '[0-9]*');

                const applyRules = () => {
                    numberEl.placeholder = rules.placeholder;
                    numberEl.maxLength = rules.max;
                    const cleaned = digitsOnly(numberEl.value).slice(0, rules.max);
                    numberEl.value = cleaned;
                };

                const onInput = () => {
                    let rawInput = numberEl.value;
                    let cleaned = digitsOnly(rawInput);
                    cleaned = rules.normalizeNational(cleaned);

                    const wrapper = numberEl.closest('.phone-input-wrapper');

                    if (rawInput.length > 0 && !rawInput.startsWith('0') && !rawInput.startsWith('9') && !rawInput.startsWith('+')) {
                        wrapper.classList.add('error', 'shake');
                        setTimeout(() => wrapper.classList.remove('shake'), 400);
                    } else {
                        wrapper.classList.remove('error');
                    }

                    cleaned = cleaned.slice(0, rules.max);
                    numberEl.value = cleaned;
                };

                numberEl.addEventListener('input', onInput);
                numberEl.addEventListener('paste', () => setTimeout(onInput, 0));

                applyRules();
            }

            function buildStoredContactNumber(numberInputId, opts = {}) {
                const numberEl = document.getElementById(numberInputId);
                if (!numberEl) return '';

                const rules = getPhoneRules();
                let national = digitsOnly(numberEl.value);
                national = rules.normalizeNational(national);

                if (national.length < rules.min || national.length > rules.max || !national.startsWith('9')) {
                    const wrapper = numberEl.closest('.phone-input-wrapper');
                    if (wrapper) {
                        wrapper.classList.add('error', 'shake');
                        setTimeout(() => wrapper.classList.remove('shake'), 400);
                    }
                    if (!opts.silent) {
                        showErrorPopup(
                            'Invalid Contact Number',
                            'For Philippines (+63), enter 10 digits starting with 9. Example: 9XXXXXXXXX (do not type the starting 0).'
                        );
                    }
                    return null;
                }
                return `${DIAL_CODE} ${national}`;
            }

            setupPhoneInput('contactNumber');

            // Common-Sense Field Validation
            function validateField(inputEl, type) {
                if (!inputEl) return true;
                let isValid = true;
                const val = inputEl.value.trim();

                if (!val) {
                    isValid = false;
                } else {
                    if (type === 'name') {
                        isValid = val.split(/\s+/).length >= 2 && !/\d/.test(val);
                    } else if (type === 'email') {
                        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
                    } else if (type === 'date') {
                        const d = new Date(val);
                        const minDate = new Date('1900-01-01');
                        isValid = !isNaN(d.getTime()) && d >= minDate && d.getFullYear() < 2026;
                    }
                }

                if (!isValid) {
                    inputEl.classList.add('error');
                    inputEl.classList.remove('shake');
                    void inputEl.offsetWidth;
                    inputEl.classList.add('shake');
                } else {
                    inputEl.classList.remove('error', 'shake');
                }
                return isValid;
            }

            function setupFieldValidation() {
                const fields = [
                    { id: 'fullName', type: 'name' },
                    { id: 'birthdate', type: 'date' },
                    { id: 'email', type: 'email' },
                    { id: 'address', type: 'address' }
                ];
                fields.forEach(f => {
                    const el = document.getElementById(f.id);
                    if (el) {
                        el.addEventListener('blur', () => validateField(el, f.type));
                        el.addEventListener('input', () => {
                            if (el.classList.contains('error')) el.classList.remove('error', 'shake');
                        });
                    }
                });
            }

            setupFieldValidation();

            // 1. Initialize DB and get data
            window.StudentDB.init();

            if (window.StudentDB.isEmpty()) {
                alert('No students registered. Please register a student first.');
                console.log("Registration redirect prevented");
                return;
            }

            const currentStudent = window.StudentDB.getActive();

            if (!currentStudent) {
                alert('No student data found. Please register a student first.');
                console.log("Registration redirect prevented");
                return;
            }

            // Embed mode
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('embed') === 'true') {
                const sidebar = document.querySelector('.sidebar');
                const breadcrumbs = document.querySelector('.breadcrumbs');
                if (sidebar) sidebar.style.display = 'none';
                if (breadcrumbs) breadcrumbs.style.display = 'none';
                document.body.style.display = 'block';
                document.body.style.backgroundColor = 'white';
                document.body.style.padding = '20px';
                const formContainer = document.querySelector('.main-content');
                if (formContainer) {
                    formContainer.style.marginLeft = '0';
                    formContainer.style.width = '100%';
                    formContainer.style.padding = '0';
                }
            }

            // Date guards
            const birthInput = document.getElementById('birthdate');
            const admissionInput = document.getElementById('admissionDate');
            const todayStr = new Date().toISOString().split('T')[0];
            if (birthInput) {
                birthInput.max = '2025-12-31';
                birthInput.min = '1900-01-01';
            }
            if (admissionInput) {
                admissionInput.max = todayStr;
            }

            // 2. Populate Form
            document.getElementById('studentNameDisplay').textContent = currentStudent.fullName;
            // Show student number (e.g. 26000007) instead of internal numeric id.
            document.getElementById('studentId').value = currentStudent.studentNumber || currentStudent.student_number || currentStudent.id;
            document.getElementById('fullName').value = currentStudent.fullName;
            document.getElementById('birthdate').value = currentStudent.birthdate;

            // Address Parsing
            const rawAddress = currentStudent.address || '';
            const addrParts = rawAddress.split('|~|');
            if (addrParts.length >= 4) {
                document.getElementById('addrStreet').value = addrParts[0] || '';
                document.getElementById('addrCity').value = addrParts[1] || '';
                document.getElementById('addrProvince').value = addrParts[2] || '';
                document.getElementById('addrPostal').value = addrParts[3] || '';
                if(addrParts[4]) {
                    document.getElementById('gender').value = addrParts[4];
                }
            } else {
                document.getElementById('addrStreet').value = rawAddress;
            }

            // Contact number
            const contactContent = currentStudent.contactNumber || '';
            const spaceIndex = contactContent.indexOf(' ');
            const rulesForInitial = getPhoneRules();
            let nationalPart = spaceIndex !== -1 ? contactContent.substring(spaceIndex + 1) : contactContent;
            let existingNational = digitsOnly(nationalPart);
            existingNational = rulesForInitial.normalizeNational(existingNational);
            document.getElementById('contactNumber').value = existingNational.slice(0, rulesForInitial.max);

            document.getElementById('email').value = currentStudent.email;
            document.getElementById('program').value = currentStudent.program;
            document.getElementById('yearLevel').value = currentStudent.yearLevel;
            document.getElementById('admissionDate').value = currentStudent.admissionDate;
            
            // ========== PHOTO ONE-TIME LOGIC ==========
            const previewImg = document.getElementById('photoPreview');
            const photoPlaceholder = document.getElementById('photoPlaceholder');
            const photoLockedMsg = document.getElementById('photoLockedMsg');
            const photoControls = document.getElementById('photoControls');
            const photoHint = document.getElementById('photoHint');
            let base64Photo = currentStudent.photoUrl || '';
            const hasExistingPhoto = currentStudent.photoUrl && currentStudent.photoUrl.trim() !== '';

            if (hasExistingPhoto) {
                previewImg.src = currentStudent.photoUrl;
                previewImg.style.display = 'block';
                photoPlaceholder.style.display = 'none';
                // Lock it
                photoControls.style.display = 'none';
                photoHint.style.display = 'none';
                photoLockedMsg.style.display = 'flex';
            } else {
                previewImg.style.display = 'none';
                photoPlaceholder.style.display = 'block';
            }

            // ========== SIGNATURE PAD LOGIC ==========
            const sigCanvasWrap = document.getElementById('sigCanvasWrap');
            const canvas = document.getElementById('signaturePad');
            const sigPlaceholder = document.getElementById('sigPlaceholder');
            const sigPreview = document.getElementById('signaturePreview');
            const sigControls = document.getElementById('sigControls');
            const sigHint = document.getElementById('sigHint');
            const sigLockedMsg = document.getElementById('signatureLockedMsg');
            const clearBtn = document.getElementById('clearSignatureBtn');
            let base64Signature = currentStudent.signatureUrl || '';
            let signatureDrawn = false;
            const hasExistingSig = currentStudent.signatureUrl && currentStudent.signatureUrl.trim() !== '';

            // Properly size the canvas to its container
            function resizeCanvas() {
                const rect = sigCanvasWrap.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
            }

            if (hasExistingSig) {
                // Lock: show preview, hide canvas
                sigCanvasWrap.style.display = 'none';
                sigPreview.src = currentStudent.signatureUrl;
                sigPreview.style.display = 'block';
                sigControls.style.display = 'none';
                sigHint.style.display = 'none';
                sigLockedMsg.style.display = 'flex';
            } else {
                // Initialize drawing canvas
                resizeCanvas();
                window.addEventListener('resize', resizeCanvas);

                const ctx = canvas.getContext('2d');
                let painting = false;
                let lastX = 0, lastY = 0;

                function getPos(e) {
                    const rect = canvas.getBoundingClientRect();
                    let clientX, clientY;
                    if (e.touches && e.touches.length > 0) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    } else {
                        clientX = e.clientX;
                        clientY = e.clientY;
                    }
                    // Scale to actual canvas pixels
                    const scaleX = canvas.width / rect.width;
                    const scaleY = canvas.height / rect.height;
                    return {
                        x: (clientX - rect.left) * scaleX,
                        y: (clientY - rect.top) * scaleY
                    };
                }

                function startDraw(e) {
                    e.preventDefault();
                    painting = true;
                    const pos = getPos(e);
                    lastX = pos.x;
                    lastY = pos.y;
                    sigCanvasWrap.classList.add('signing');
                    sigCanvasWrap.classList.add('has-sig');
                    signatureDrawn = true;
                }

                function endDraw() {
                    painting = false;
                    ctx.beginPath();
                    sigCanvasWrap.classList.remove('signing');
                    checkChanges();
                }

                function draw(e) {
                    if (!painting) return;
                    e.preventDefault();
                    const pos = getPos(e);
                    
                    ctx.strokeStyle = '#1f2937';
                    ctx.lineWidth = 2.5;
                    ctx.lineCap = 'round';
                    ctx.lineJoin = 'round';
                    
                    ctx.beginPath();
                    ctx.moveTo(lastX, lastY);
                    ctx.lineTo(pos.x, pos.y);
                    ctx.stroke();
                    
                    lastX = pos.x;
                    lastY = pos.y;
                }

                canvas.addEventListener('mousedown', startDraw);
                canvas.addEventListener('mouseup', endDraw);
                canvas.addEventListener('mouseleave', endDraw);
                canvas.addEventListener('mousemove', draw);
                canvas.addEventListener('touchstart', startDraw, { passive: false });
                canvas.addEventListener('touchend', endDraw);
                canvas.addEventListener('touchmove', draw, { passive: false });

                clearBtn.addEventListener('click', () => {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    signatureDrawn = false;
                    sigCanvasWrap.classList.remove('has-sig');
                    base64Signature = '';
                    checkChanges();
                });
            }

            // Signature file upload
            const sigInput = document.getElementById('signatureInput');
            if (sigInput && !hasExistingSig) {
                sigInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(event) {
                            base64Signature = event.target.result;
                            sigCanvasWrap.style.display = 'none';
                            sigPreview.src = base64Signature;
                            sigPreview.style.display = 'block';
                            signatureDrawn = true;
                            checkChanges();
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Apply Registrar restrictions
            const isRegistrar = typeof checkRegistrarStatus === 'function' && checkRegistrarStatus();
            if (!isRegistrar) {
                const restrictedFields = ['fullName', 'birthdate', 'admissionDate', 'program', 'yearLevel'];
                restrictedFields.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        if (el.tagName === 'SELECT') {
                            el.disabled = true;
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = el.name || id;
                            hidden.id = id + '_hidden';
                            hidden.value = el.value;
                            el.parentNode.appendChild(hidden);
                        } else {
                            el.readOnly = true;
                        }
                        el.classList.add('read-only-field');
                    }
                });
            }

            // Track initial data for comparison
            const initialData = {
                fullName: currentStudent.fullName,
                birthdate: currentStudent.birthdate,
                contactNumber: currentStudent.contactNumber,
                email: currentStudent.email,
                address: currentStudent.address,
                program: currentStudent.program,
                yearLevel: currentStudent.yearLevel,
                admissionDate: currentStudent.admissionDate,
                photoUrl: currentStudent.photoUrl || ''
            };

            const saveBtn = document.querySelector('.btn-primary');
            saveBtn.disabled = true;
            saveBtn.style.opacity = '0.5';
            saveBtn.style.cursor = 'not-allowed';

            // Function to check for changes
            function checkChanges() {
                const storedContactNumber = buildStoredContactNumber('contactNumber', { silent: true });
                if (!storedContactNumber) {
                    saveBtn.disabled = true;
                    saveBtn.style.opacity = '0.5';
                    saveBtn.style.cursor = 'not-allowed';
                    return;
                }

                // Pack address for comparison
                const currentAddress = [
                    document.getElementById('addrStreet').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrCity').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrProvince').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrPostal').value.replace(/\|~\|/g, ''),
                    document.getElementById('gender').value.replace(/\|~\|/g, '')
                ].join('|~|');

                const currentData = {
                    fullName: document.getElementById('fullName').value,
                    birthdate: document.getElementById('birthdate').value,
                    contactNumber: storedContactNumber,
                    email: document.getElementById('email').value,
                    address: currentAddress,
                    program: document.getElementById('program').value,
                    yearLevel: document.getElementById('yearLevel').value,
                    admissionDate: document.getElementById('admissionDate').value,
                    photoUrl: base64Photo
                };

                // Also consider signature changes
                let hasChanges = JSON.stringify(initialData) !== JSON.stringify(currentData);
                
                // If signature was drawn/uploaded and there wasn't one before
                if (!hasExistingSig && signatureDrawn) {
                    hasChanges = true;
                }

                saveBtn.disabled = !hasChanges;
                saveBtn.style.opacity = hasChanges ? '1' : '0.5';
                saveBtn.style.cursor = hasChanges ? 'pointer' : 'not-allowed';
            }

            // Add listeners to all inputs
            const inputs = document.querySelectorAll('.form-input, .split-addr');
            inputs.forEach(input => {
                input.addEventListener('input', checkChanges);
                input.addEventListener('change', checkChanges);
            });

            // Photo Preview Logic
            const photoInput = document.getElementById('photoInput');

            photoInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                const fileName = (file.name || '').toLowerCase();
                const isWebpMime = file.type === 'image/webp';
                const isWebpExt = fileName.endsWith('.webp');

                if (!isWebpMime && !isWebpExt) {
                    showPhotoErrorPopup(
                        "Only WebP (.webp) images are allowed.",
                        () => document.getElementById('photoInput') && document.getElementById('photoInput').click()
                    );
                    photoInput.value = "";
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    const tempBase64 = event.target.result;

                    const img = new Image();
                    img.onload = function () {
                        const originalWidth = img.width;
                        const originalHeight = img.height;
                        const size = Math.min(originalWidth, originalHeight);
                        let sx = 0, sy = 0;

                        if (originalWidth > originalHeight) {
                            sx = (originalWidth - size) / 2;
                        } else if (originalHeight > originalWidth) {
                            const extra = originalHeight - size;
                            sy = extra * 0.2;
                            if (sy + size > originalHeight) sy = originalHeight - size;
                        }

                        const cropCanvas = document.createElement('canvas');
                        cropCanvas.width = size;
                        cropCanvas.height = size;
                        const ctx = cropCanvas.getContext('2d', { willReadFrequently: true });
                        ctx.drawImage(img, sx, sy, size, size, 0, 0, size, size);

                        // Check for white background
                        const pointsToCheck = [
                            { x: 0, y: 0 },
                            { x: cropCanvas.width - 1, y: 0 },
                            { x: 0, y: cropCanvas.height - 1 },
                            { x: cropCanvas.width - 1, y: cropCanvas.height - 1 },
                            { x: Math.floor(cropCanvas.width / 2), y: 0 },
                            { x: 0, y: Math.floor(cropCanvas.height / 2) }
                        ];

                        let whiteCount = 0, checked = 0;
                        for (let p of pointsToCheck) {
                            const data = ctx.getImageData(p.x, p.y, 1, 1).data;
                            if (data[3] > 50) {
                                checked++;
                                const brightness = (data[0] + data[1] + data[2]) / 3;
                                if (brightness > 185) whiteCount++;
                            }
                        }

                        const whiteRatio = checked > 0 ? whiteCount / checked : 0;
                        if (whiteRatio < 0.6) {
                            showPhotoErrorPopup(
                                "Please make sure the uploaded photo has a mostly white or light background.",
                                () => document.getElementById('photoInput') && document.getElementById('photoInput').click()
                            );
                            photoInput.value = "";
                            return;
                        }

                        const processedBase64 = cropCanvas.toDataURL('image/webp');
                        base64Photo = processedBase64;
                        previewImg.src = processedBase64;
                        previewImg.style.display = 'block';
                        photoPlaceholder.style.display = 'none';
                        checkChanges();
                    };
                    img.src = tempBase64;
                };
                reader.readAsDataURL(file);
            });

            // 3. Handle Form Submit
            document.getElementById('updateForm').addEventListener('submit', (e) => {
                e.preventDefault();

                let allValid = true;
                const fields = [
                    { id: 'email', type: 'email' }
                ];
                fields.forEach(f => {
                    const el = document.getElementById(f.id);
                    if (el && !validateField(el, f.type)) {
                        allValid = false;
                    }
                });

                if (!allValid) {
                    showErrorPopup("Invalid Input", "Please correct the highlighted fields before submitting.");
                    return;
                }

                const storedContactNumber = buildStoredContactNumber('contactNumber');
                if (!storedContactNumber) return;

                const birthEl = document.getElementById('birthdate');
                const admEl = document.getElementById('admissionDate');
                const today = new Date();
                const birthDate = birthEl.value ? new Date(birthEl.value) : null;
                const admissionDate = admEl.value ? new Date(admEl.value) : null;

                if (birthDate && birthDate > today) {
                    showErrorPopup("Invalid Profile", "Birthdate cannot be in the future.");
                    return;
                }
                if (admissionDate && admissionDate > today) {
                    showErrorPopup("Invalid Profile", "Admission date cannot be in the future.");
                    return;
                }
                if (birthDate && admissionDate && admissionDate <= birthDate) {
                    showErrorPopup("Invalid Profile", "Admission date must be after the birthdate.");
                    return;
                }

                // Get signature from canvas if drawn
                if (!hasExistingSig && signatureDrawn && !base64Signature) {
                    base64Signature = canvas.toDataURL('image/png');
                } else if (!hasExistingSig && signatureDrawn && sigPreview.style.display === 'none') {
                    // Canvas was used for drawing
                    base64Signature = canvas.toDataURL('image/png');
                }

                // Pack address
                const parts = [
                    document.getElementById('addrStreet').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrCity').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrProvince').value.replace(/\|~\|/g, ''),
                    document.getElementById('addrPostal').value.replace(/\|~\|/g, ''),
                    document.getElementById('gender').value.replace(/\|~\|/g, '')
                ];
                const packedAddress = parts.join('|~|');

                const updatedData = {
                    fullName: document.getElementById('fullName').value,
                    birthdate: document.getElementById('birthdate').value,
                    contactNumber: storedContactNumber,
                    email: document.getElementById('email').value,
                    address: packedAddress,
                    program: document.getElementById('program').value,
                    yearLevel: document.getElementById('yearLevel').value,
                    admissionDate: document.getElementById('admissionDate').value,
                    photoUrl: base64Photo,
                    signatureUrl: base64Signature,
                    idValidity: currentStudent.idValidity
                };

                window.StudentDB.update(currentStudent.id, updatedData).then(() => {
                    return window.StudentDB.setActive(currentStudent.id);
                }).then(() => {
                    showSuccessPopup("Student information updated successfully!", () => {
                        location.reload();
                    });
                }).catch(() => showErrorPopup('Update failed', 'Could not save changes on the server.'));
            });
        });
    </script>
</body>

</html>
