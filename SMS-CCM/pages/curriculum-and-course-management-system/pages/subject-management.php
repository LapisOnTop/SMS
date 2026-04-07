<?php
session_start();

// Check if user is authenticated as admin for CMS
if (!isset($_SESSION['cms_role']) || $_SESSION['cms_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$host     = 'localhost';
$dbname   = 'sms_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('<p style="color:red;font-family:sans-serif;padding:20px;">
            Database connection failed: ' . htmlspecialchars($e->getMessage()) . '
         </p>');
}

/// ── Handle AJAX Actions (Add/Update/Delete) ──────────────────────────────────
// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        // Get data from the form (using the 'name' attributes from your HTML)
        $code = $_POST['subjectCode'] ?? '';
        $desc = $_POST['subjectDescription'] ?? '';
        $prog = $_POST['subjectProgram'] ?? '';
        $year = $_POST['subjectYear'] ?? '';
        $sem  = $_POST['subjectSemester'] ?? '';
        $unit = $_POST['subjectUnits'] ?? 0;
        $hour = $_POST['subjectHours'] ?? 0;
        $pre  = $_POST['subjectPrerequisite'] ?? 'None';
        $core = $_POST['subjectCorequisite'] ?? 'None';

        // Prepare the SQL Statement
        $sql = "INSERT INTO subjects (subject_code, subject_name, program, year, semester, units, hours, prerequisite, corequisite) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                subject_name = VALUES(subject_name),
                units = VALUES(units),
                hours = VALUES(hours)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$code, $desc, $prog, $year, $sem, $unit, $hour, $pre, $core]);

        echo json_encode(['status' => 'success', 'message' => 'Subject saved to database!']);
        exit;

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// ── Fetch subjects from DB ─────────────────────────────────────────────────────
// Updated to select actual columns instead of NULL defaults
$stmt = $pdo->query("
    SELECT
        subject_id   AS id,
        subject_code AS code,
        subject_name AS description,
        units,
        semester,
        hours,
        year,
        prerequisite,
        corequisite,
        program
    FROM subjects s
    ORDER BY s.subject_id DESC
");
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Normalise nullable defaults
foreach ($subjects as &$s) {
    $s['hours']        = $s['hours']        ?? '—';
    $s['year']         = $s['year']         ?? '—';
    $s['prerequisite'] = $s['prerequisite'] ?? 'None';
    $s['corequisite']  = $s['corequisite']  ?? 'None';
    $s['program']      = $s['program']      ?? '—';
    $s['semester']     = $s['semester']     ?? '—';
}
unset($s);

// ── Fetch distinct programs for filters ────────────────────────────────
$programsStmt = $pdo->query("SELECT program_name FROM programs ORDER BY program_name");
$programs     = $programsStmt->fetchAll(PDO::FETCH_COLUMN);

$years     = ['1st', '2nd', '3rd', '4th'];
$semesters = ['1st', '2nd'];
$totalSubjects = count($subjects);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Management - Curriculum Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/program-setup.css">
    <link rel="stylesheet" href="../assets/css/subject-management.css">
</head>
<body>
    <div class="container-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book"></i>
                <h2>CMS</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="program-setup.php" class="nav-item" data-section="program-setup">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Program Setup</span>
                </a>
                <a href="subject-management.php" class="nav-item active" data-section="subject-management">
                    <i class="fas fa-book-open"></i>
                    <span>Subject Management</span>
                </a>
                <a href="course-scheduling.php" class="nav-item" data-section="course-scheduling">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Course Scheduling</span>
                </a>
                <a href="revision-log-book.php" class="nav-item" data-section="revision-log">
                    <i class="fas fa-history"></i>
                    <span>Revision Log Book</span>
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="../login.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header Section -->
            <div class="page-header">
                <div class="header-left">
                    <h1>Subject Management</h1>
                    <p class="header-subtitle">All subjects are stored in and loaded from the <strong>subjects table in MySQL</strong></p>
                </div>
                <button class="btn-register-program" id="addSubjectBtn">
                    <i class="fas fa-plus"></i>
                    <span>Add Subject</span>
                </button>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-group">
                    <label for="programFilter">Program Filter</label>
                    <select id="programFilter" class="filter-select">
                        <option value="">All Programs</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo htmlspecialchars($program); ?>">
                                <?php echo htmlspecialchars($program); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="yearFilter">Year Filter</label>
                    <select id="yearFilter" class="filter-select">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo htmlspecialchars($year); ?>">
                                <?php echo htmlspecialchars($year); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="semesterFilter">Semester Filter</label>
                    <select id="semesterFilter" class="filter-select">
                        <option value="">All Semesters</option>
                        <?php foreach ($semesters as $semester): ?>
                            <option value="<?php echo htmlspecialchars($semester); ?>">
                                <?php echo htmlspecialchars($semester); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                  <button class="btn-reset-filter" id="resetFilterBtn">
                    <i class="fas fa-redo"></i>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Stats Section -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalSubjectsCount"><?php echo $totalSubjects; ?></h3>
                        <p>Total Subjects</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalUnitsCount">0</h3>
                        <p>Total Units</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalHoursCount">0</h3>
                        <p>Total Hours</p>
                    </div>
                </div>
            </div>

            <!-- Subjects Table Section -->
            <div class="table-container">
                <table class="subjects-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Program</th>
                            <th>Year / Sem</th>
                            <th>Units / Hours</th>
                            <th>Prerequisite</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="subjectsTableBody">
                        <?php if (empty($subjects)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:2rem;color:#888;">
                                No subjects found in the database.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($subjects as $subject): ?>
                       <tr class="subject-row" 
    data-subject-id="<?php echo $subject['id']; ?>"
    data-program="<?php echo htmlspecialchars($subject['program']); ?>"
    data-year="<?php echo htmlspecialchars($subject['year']); ?>"
    data-semester="<?php echo htmlspecialchars($subject['semester']); ?>"
    data-units="<?php echo htmlspecialchars($subject['units']); ?>"
    data-hours="<?php echo htmlspecialchars($subject['hours']); ?>"
    data-prerequisite="<?php echo htmlspecialchars($subject['prerequisite']); ?>"
    data-corequisite="<?php echo htmlspecialchars($subject['corequisite']); ?>">
    
    <td class="code-cell">
        <strong><?php echo htmlspecialchars($subject['code']); ?></strong>
    </td>
    <td class="description-cell">
        <div class="description-text"><?php echo htmlspecialchars($subject['description']); ?></div>
    </td>
    <td class="program-cell">
        <span class="program-badge"><?php echo htmlspecialchars($subject['program']); ?></span>
    </td>
    <td class="year-semester-cell">
        <span class="year-sem-badge">
            <?php echo htmlspecialchars($subject['year']); ?> / <?php echo htmlspecialchars($subject['semester']); ?>
        </span>
    </td>
    <td class="units-cell">
        <span class="units-badge">
            <?php echo htmlspecialchars((string)$subject['units']); ?> Units / <?php echo htmlspecialchars((string)$subject['hours']); ?> Hrs
        </span>
    </td>
    <td class="prerequisite-cell">
        <span class="prerequisite-text"><?php echo htmlspecialchars($subject['prerequisite']); ?></span>
    </td>
    <td class="action-cell">
        <div class="action-buttons">
            <button class="btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
        </div>
    </td>
</tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

        <!-- Add/Edit Subject Modal -->
    <div class="modal" id="subjectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Subject</h2>
                <button class="modal-close" id="closeModalBtn">&times;</button>
            </div>
            <form class="subject-form" id="subjectForm">
                <div class="form-group">
                    <label for="subjectCode">Subject Code</label>
                    <input type="text" id="subjectCode" name="subjectCode" placeholder="e.g., ITP101" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="subjectDescription">Subject Description</label>
                    <input type="text" id="subjectDescription" name="subjectDescription" placeholder="e.g., Introduction to Computing" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectProgram">Program</label>
                        <select id="subjectProgram" name="subjectProgram" required>
                            <option value="">Select Program</option>
                            <?php foreach ($programs as $program): ?>
                                <option value="<?php echo htmlspecialchars($program); ?>">
                                    <?php echo htmlspecialchars($program); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subjectYear">Year</label>
                        <select id="subjectYear" name="subjectYear" required>
                            <option value="">Select Year</option>
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>">
                                    <?php echo htmlspecialchars($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectSemester">Semester</label>
                        <select id="subjectSemester" name="subjectSemester" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?php echo htmlspecialchars($semester); ?>">
                                    <?php echo htmlspecialchars($semester); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subjectUnits">Units</label>
                        <input type="number" id="subjectUnits" name="subjectUnits" placeholder="3" min="1" max="12" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectHours">Hours</label>
                        <input type="number" id="subjectHours" name="subjectHours" placeholder="54" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="subjectPrerequisite">Prerequisite</label>
                        <input type="text" id="subjectPrerequisite" name="subjectPrerequisite" placeholder="Subject code or None">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="subjectCorequisite">Corequisite</label>
                        <input type="text" id="subjectCorequisite" name="subjectCorequisite" placeholder="Subject code or None">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelModalBtn">Cancel</button>
                    <button type="submit" class="btn-submit">Save Subject</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overlay -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- Message Toast -->
    <div class="message-toast" id="messageToast">
        <span id="messageText"></span>
    </div>

<script>

    (function() {
    'use strict';

    // DOM Elements
    const modal = document.getElementById('subjectModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const addSubjectBtn = document.getElementById('addSubjectBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const cancelModalBtn = document.getElementById('cancelModalBtn');
    const subjectForm = document.getElementById('subjectForm');
    const messageToast = document.getElementById('messageToast');
    const navItems = document.querySelectorAll('.nav-item');

    // Filters
    const programFilter = document.getElementById('programFilter');
    const yearFilter = document.getElementById('yearFilter');
    const semesterFilter = document.getElementById('semesterFilter');
    const resetFilterBtn = document.getElementById('resetFilterBtn');

    // Stats
    const totalSubjectsCount = document.getElementById('totalSubjectsCount');
    const totalUnitsCount = document.getElementById('totalUnitsCount');
    const totalHoursCount = document.getElementById('totalHoursCount');

    // Form fields
    const subjectCodeInput = document.getElementById('subjectCode');
    const subjectDescriptionInput = document.getElementById('subjectDescription');
    const subjectProgramSelect = document.getElementById('subjectProgram');
    const subjectYearSelect = document.getElementById('subjectYear');
    const subjectSemesterSelect = document.getElementById('subjectSemester');
    const subjectUnitsInput = document.getElementById('subjectUnits');
    const subjectHoursInput = document.getElementById('subjectHours');
    const subjectPrerequisiteInput = document.getElementById('subjectPrerequisite');
    const subjectCorequisiteInput = document.getElementById('subjectCorequisite');
    const modalTitle = document.getElementById('modalTitle');

    let editingSubjectId = null;

    function init() {
        addSubjectBtn.addEventListener('click', openModal);
        closeModalBtn.addEventListener('click', closeModal);
        cancelModalBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);

        // ✅ ONLY ONE SUBMIT HANDLER
        subjectForm.addEventListener('submit', handleFormSubmit);

        programFilter.addEventListener('change', updateTable);
        yearFilter.addEventListener('change', updateTable);
        semesterFilter.addEventListener('change', updateTable);
        resetFilterBtn.addEventListener('click', resetFilters);

        attachTableEventListeners();
        updateStatistics();
    }

    function openModal() {
        modal.classList.add('active');
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        subjectForm.reset();
        editingSubjectId = null;
        modalTitle.textContent = 'Add New Subject';
    }

    function closeModal() {
        modal.classList.remove('active');
        modalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
        subjectForm.reset();
    }

function handleFormSubmit(e) {
        e.preventDefault();

        const formData = new FormData(subjectForm);
        
        // Convert FormData to a plain object for consistency if you prefer JSON,
        // but standard FormData is fine as long as PHP reads $_POST.
        fetch('subject-management.php', {
            method: 'POST',
            body: formData // This sends as multipart/form-data
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Server Error');
            return data;
        })
        .then(data => {
            if (data.status === 'success') {
                showMessage('success', data.message);
                // Delay reload slightly so user sees the success message
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showMessage('error', data.message);
            }
        })
        .catch(err => {
            console.error('Fetch Error:', err);
            showMessage('error', err.message || 'Failed to save subject.');
        });
    }

    function attachTableEventListeners() {
        document.querySelectorAll('.subject-row').forEach(row => {
            const editBtn = row.querySelector('.btn-edit');
            const deleteBtn = row.querySelector('.btn-delete');

            if (editBtn) {
                editBtn.addEventListener('click', () => handleEditSubject(row));
            }

            if (deleteBtn) {
                deleteBtn.addEventListener('click', () => handleDeleteSubject(row));
            }
        });
    }

    function handleEditSubject(row) {
// Clear and set the ID we are editing
    editingSubjectId = row.dataset.subjectId;

    // Fill inputs using the data- attributes from the row
    subjectCodeInput.value = row.querySelector('.code-cell strong').textContent.trim();
    subjectDescriptionInput.value = row.querySelector('.description-text').textContent.trim();
    
    subjectProgramSelect.value = row.dataset.program;
    subjectYearSelect.value = row.dataset.year;
    subjectSemesterSelect.value = row.dataset.semester;
    
    subjectUnitsInput.value = row.dataset.units;
    subjectHoursInput.value = row.dataset.hours;
    subjectPrerequisiteInput.value = row.dataset.prerequisite;
    subjectCorequisiteInput.value = row.dataset.corequisite;

    modalTitle.textContent = 'Edit Subject';
    modal.classList.add('active');
    modalOverlay.classList.add('active');
    }

    function handleDeleteSubject(row) {
        const subjectId = row.dataset.subjectId;

        if (!confirm('Delete this subject?')) return;

        fetch('delete-subject.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `subject_id=${subjectId}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                row.remove();
                updateStatistics();
                showMessage('success', 'Deleted successfully');
            } else {
                showMessage('error', 'Delete failed');
            }
        });
    }

    function updateTable() {
        const pv = programFilter.value;
        const yv = yearFilter.value;
        const sv = semesterFilter.value;

        document.querySelectorAll('.subject-row').forEach(row => {
            const match =
                (!pv || row.dataset.program === pv) &&
                (!yv || row.dataset.year === yv) &&
                (!sv || row.dataset.semester === sv);

            row.style.display = match ? '' : 'none';
        });

        updateStatistics();
    }

    function resetFilters() {
        programFilter.value = '';
        yearFilter.value = '';
        semesterFilter.value = '';
        updateTable();
    }

    function updateStatistics() {
        let count = 0, units = 0, hours = 0;

        document.querySelectorAll('.subject-row').forEach(row => {
            if (row.style.display === 'none') return;

            count++;
            const parts = row.querySelector('.units-badge').textContent.split('/');
            units += parseInt(parts[0]) || 0;
            hours += parseInt(parts[1]) || 0;
        });

        totalSubjectsCount.textContent = count;
        totalUnitsCount.textContent = units;
        totalHoursCount.textContent = hours;
    }

    function showMessage(type, msg) {
        messageToast.textContent = msg;
        messageToast.className = `message-toast show ${type}`;
        setTimeout(() => messageToast.classList.remove('show'), 3000);
    }

    document.addEventListener('DOMContentLoaded', init);

    document.addEventListener('DOMContentLoaded', function() {
    const subjectForm = document.getElementById('subjectForm');

    if (subjectForm) {
        // This connects your "Save Subject" button to the function
        subjectForm.addEventListener('submit', handleFormSubmit);
    }
});

function handleFormSubmit(e) {
    e.preventDefault(); // This stops the page from refreshing immediately

    // 1. Gather the data from the form
    const formData = new FormData(e.target);

    // 2. Send the data to your PHP file
    fetch('subject-management.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json()) // We expect a JSON response from PHP
    .then(data => {
        if (data.status === 'success') {
            alert('Saved successfully!');
            window.location.reload(); // Refresh to show new data in table
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Could not connect to the server.');
    });
}
})();
</script>
</body>
</html>