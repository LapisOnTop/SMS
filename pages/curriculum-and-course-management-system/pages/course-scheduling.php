<?php
session_start();

// Check if user is authenticated as admin for CMS
if (!isset($_SESSION['cms_role']) || $_SESSION['cms_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ─── DB CONNECTION ────────────────────────────────────────────────────────────
$host = 'localhost';
$db   = 'pamana';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ─── HANDLE AJAX: MOVE SUBJECT TO DIFFERENT SEMESTER ─────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'move_subject') {
    header('Content-Type: application/json');

    $subject_id     = (int) ($_POST['subject_id'] ?? 0);
    $new_semester   = trim($_POST['new_semester'] ?? '');

    if (!$subject_id || !$new_semester) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE subjects SET semester = :semester WHERE subject_id = :id");
        $stmt->execute([':semester' => $new_semester, ':id' => $subject_id]);
        echo json_encode(['success' => true, 'message' => 'Subject moved successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
    }
    exit;
}

// ─── FETCH PROGRAMS FROM DB ───────────────────────────────────────────────────
$programsStmt = $pdo->query("SELECT program_id, program_name FROM programs ORDER BY program_name");
$programs = $programsStmt->fetchAll();

$selectedProgram = isset($_GET['program_id']) ? (int) $_GET['program_id'] : ($programs[0]['program_id'] ?? 1);

// ─── FETCH SUBJECTS GROUPED BY SEMESTER ──────────────────────────────────────
// subjects table has: subject_id, subject_code, subject_name, units, semester
$subjectsStmt = $pdo->prepare("
    SELECT subject_id, subject_code, subject_name, units, semester
    FROM subjects
    ORDER BY semester, subject_code
");
$subjectsStmt->execute();
$allSubjects = $subjectsStmt->fetchAll();

// Group subjects by semester label
$courseScheduling = [];
foreach ($allSubjects as $subject) {
    $sem = $subject['semester'] ?? 'Unassigned';
    $courseScheduling[$sem][] = $subject;
}

// All possible year/semester combinations
$yearSemesters = [
    'Year 1 - Semester 1',
    'Year 1 - Semester 2',
    'Year 2 - Semester 1',
    'Year 2 - Semester 2',
    'Year 3 - Semester 1',
    'Year 3 - Semester 2',
    'Year 4 - Semester 1',
    'Year 4 - Semester 2',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Scheduling - Curriculum Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/program-setup.css">
    <link rel="stylesheet" href="../assets/css/course-scheduling.css">
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
                <a href="subject-management.php" class="nav-item" data-section="subject-management">
                    <i class="fas fa-book-open"></i>
                    <span>Subject Management</span>
                </a>
                <a href="course-scheduling.php" class="nav-item active" data-section="course-scheduling">
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
                    <h1>Course Offering Planner</h1>
                    <p class="header-subtitle">Drag subjects between year levels and semesters — changes saved to database</p>
                </div>
                <div class="program-selector">
                    <label for="programSelect">Program:</label>
                    <select id="programSelect" class="program-dropdown" onchange="window.location.href='?program_id='+this.value">
                        <?php foreach ($programs as $program): ?>
                            <option value="<?php echo $program['program_id']; ?>"
                                <?php echo $selectedProgram === (int)$program['program_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($program['program_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Course Scheduling Container -->
            <div class="scheduling-container" id="schedulingContainer">
                <?php foreach ($yearSemesters as $yearSemester): ?>
                    <?php $subjects = $courseScheduling[$yearSemester] ?? []; ?>
                    <div class="semester-section" data-year-semester="<?php echo htmlspecialchars($yearSemester); ?>">
                        <div class="semester-header">
                            <h2><?php echo htmlspecialchars($yearSemester); ?></h2>
                            <div class="semester-info">
                                <span class="units-badge" id="units-<?php echo str_replace([' ', '-'], '-', $yearSemester); ?>">
                                    Total: <span class="total-units"><?php echo array_sum(array_column($subjects, 'units')); ?></span> units
                                </span>
                            </div>
                        </div>
                        <div class="subjects-list" data-year-semester="<?php echo htmlspecialchars($yearSemester); ?>">
                            <div class="list-header">
                                <div class="col-subject">SUBJECT</div>
                                <div class="col-description">DESCRIPTION</div>
                                <div class="col-units">UNITS</div>
                                <div class="col-action">MOVE TO</div>
                            </div>
                            <?php if (!empty($subjects)): ?>
                                <?php foreach ($subjects as $subject): ?>
                                    <div class="subject-item" draggable="true"
                                         data-subject-id="<?php echo $subject['subject_id']; ?>"
                                         data-code="<?php echo htmlspecialchars($subject['subject_code']); ?>"
                                         data-units="<?php echo $subject['units']; ?>">
                                        <div class="col-subject"><?php echo htmlspecialchars($subject['subject_code']); ?></div>
                                        <div class="col-description"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
                                        <div class="col-units"><?php echo $subject['units']; ?></div>
                                        <div class="col-action">
                                            <select class="move-dropdown" data-from="<?php echo htmlspecialchars($yearSemester); ?>">
                                                <option value="">-- Move --</option>
                                                <?php foreach ($yearSemesters as $target): ?>
                                                    <?php if ($target !== $yearSemester): ?>
                                                        <option value="<?php echo htmlspecialchars($target); ?>">
                                                            <?php echo htmlspecialchars($target); ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>No subjects assigned</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Message Toast -->
    <div class="message-toast" id="messageToast">
        <span id="messageText"></span>
    </div>

    <script>
    // ─── MOVE SUBJECT VIA DROPDOWN (saves to DB via AJAX) ──────────────────────
    document.querySelectorAll('.move-dropdown').forEach(select => {
        select.addEventListener('change', function () {
            const newSemester = this.value;
            if (!newSemester) return;

            const subjectItem = this.closest('.subject-item');
            const subjectId   = subjectItem.dataset.subjectId;
            const fromSemester = this.dataset.from;
            const targetList  = document.querySelector(`.subjects-list[data-year-semester="${newSemester}"]`);

            if (!targetList) return;

            // Optimistic UI move
            const emptyState = targetList.querySelector('.empty-state');
            if (emptyState) emptyState.remove();
            targetList.appendChild(subjectItem);

            // Update the "from" attribute and rebuild dropdown options
            this.dataset.from = newSemester;
            rebuildDropdown(this, newSemester);

            // Update unit counts for both affected semester sections
            updateUnitCount(fromSemester);
            updateUnitCount(newSemester);

            // Check if old list is now empty
            const oldList = document.querySelector(`.subjects-list[data-year-semester="${fromSemester}"]`);
            if (oldList && oldList.querySelectorAll('.subject-item').length === 0) {
                oldList.insertAdjacentHTML('beforeend', `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No subjects assigned</p>
                    </div>
                `);
            }

            // Persist to DB
            const formData = new FormData();
            formData.append('action', 'move_subject');
            formData.append('subject_id', subjectId);
            formData.append('new_semester', newSemester);

            fetch('course-scheduling.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    showToast(data.message, data.success ? 'success' : 'error');
                })
                .catch(() => showToast('Network error. Please try again.', 'error'));
        });
    });

    function rebuildDropdown(select, currentSemester) {
        const allSemesters = <?php echo json_encode($yearSemesters); ?>;
        select.innerHTML = '<option value="">-- Move --</option>';
        allSemesters.forEach(sem => {
            if (sem !== currentSemester) {
                const opt = document.createElement('option');
                opt.value = sem;
                opt.textContent = sem;
                select.appendChild(opt);
            }
        });
    }

    function updateUnitCount(semesterLabel) {
        const list  = document.querySelector(`.subjects-list[data-year-semester="${semesterLabel}"]`);
        if (!list) return;
        const items = list.querySelectorAll('.subject-item');
        let total = 0;
        items.forEach(item => { total += parseInt(item.dataset.units || 0); });
        const badge = document.getElementById('units-' + semesterLabel.replace(/[\s]+/g, '-'));
        if (badge) badge.querySelector('.total-units').textContent = total;
    }

    function showToast(message, type = 'success') {
        const toast = document.getElementById('messageToast');
        const text  = document.getElementById('messageText');
        text.textContent = message;
        toast.className = 'message-toast show ' + type;
        setTimeout(() => { toast.className = 'message-toast'; }, 3000);
    }
    </script>

    <script src="../assets/js/course-scheduling.js"></script>
</body>
</html>