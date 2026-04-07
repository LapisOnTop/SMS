<?php
session_start();

if (!isset($_SESSION['cms_role']) || $_SESSION['cms_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ─── DB CONNECTION (PDO — consistent with rest of project) ────────────────────
$host    = 'localhost';
$dbname  = 'sms_db';
$dbuser  = 'root';
$dbpass  = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// ----------------------------------------------------------------
// AJAX handler
// ----------------------------------------------------------------
$action = $_GET['action'] ?? '';

if ($action) {
    header('Content-Type: application/json');

    function respond(bool $ok, string $msg, array $extra = []): void {
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    switch ($action) {

        // LIST all programs with subject count
        // Bug fix: removed the broken cross-join on schedules; subjects has no program_id yet
        // so we count 0 subjects per program until that FK exists.
        case 'list':
            $stmt = $db->query("
                SELECT
                    p.program_id   AS id,
                    p.program_name AS name,
                    COUNT(DISTINCT s.subject_id) AS totalSubjects
                FROM programs p
                LEFT JOIN subjects s ON 1 = 0
                GROUP BY p.program_id, p.program_name
                ORDER BY p.program_name ASC
            ");
            respond(true, 'OK', ['data' => $stmt->fetchAll()]);

        // GET single program
        case 'get':
            $id   = (int)($_GET['id'] ?? 0);
            $stmt = $db->prepare("SELECT program_id AS id, program_name AS name FROM programs WHERE program_id = ?");
            $stmt->execute([$id]);
            $row  = $stmt->fetch();
            $row ? respond(true, 'OK', ['data' => $row]) : respond(false, 'Program not found.');

        // CREATE
        case 'create':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $name  = trim($input['program_name'] ?? '');

            if (!$name) respond(false, 'Program name is required.');

            $chk = $db->prepare("SELECT program_id FROM programs WHERE program_name = ?");
            $chk->execute([$name]);
            if ($chk->fetch()) respond(false, "Program '$name' already exists.");

            $stmt = $db->prepare("INSERT INTO programs (program_name) VALUES (?)");
            $stmt->execute([$name]);
            $newId = (int)$db->lastInsertId();

            respond(true, 'Program registered successfully.', ['id' => $newId]);

        // UPDATE
        case 'update':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id    = (int)($input['id'] ?? 0);
            $name  = trim($input['program_name'] ?? '');

            if (!$id || !$name) respond(false, 'ID and program name are required.');

            $chk = $db->prepare("SELECT program_id FROM programs WHERE program_name = ? AND program_id != ?");
            $chk->execute([$name, $id]);
            if ($chk->fetch()) respond(false, 'Another program with this name already exists.');

            $stmt = $db->prepare("UPDATE programs SET program_name = ? WHERE program_id = ?");
            $stmt->execute([$name, $id]);

            respond(true, 'Program updated successfully.');

        // DELETE
        case 'delete':
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $id    = (int)($input['id'] ?? 0);
            if (!$id) respond(false, 'Missing program ID.');

            $chk = $db->prepare("
                SELECT COUNT(*) AS cnt FROM (
                    SELECT program_id FROM admissions   WHERE program_id = ?
                    UNION ALL
                    SELECT program_id FROM applications WHERE program_id = ?
                    UNION ALL
                    SELECT program_id FROM enrollments  WHERE program_id = ?
                ) AS refs
            ");
            $chk->execute([$id, $id, $id]);
            if ((int)$chk->fetchColumn() > 0) {
                respond(false, 'Cannot delete — this program has existing admissions or enrollment records.');
            }

            $stmt = $db->prepare("DELETE FROM programs WHERE program_id = ?");
            $stmt->execute([$id]);

            respond(true, 'Program deleted successfully.');

        default:
            http_response_code(400);
            respond(false, 'Unknown action.');
    }
}

// ----------------------------------------------------------------
// PAGE DATA
// ----------------------------------------------------------------
$programs = $db->query("
    SELECT
        p.program_id   AS id,
        p.program_name AS name,
        0              AS totalSubjects
    FROM programs p
    ORDER BY p.program_name ASC
")->fetchAll();

$totalSubjects = (int)$db->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$academicYear  = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Setup - Curriculum Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/program-setup.css">
</head>
<body>
<div class="container-wrapper">

    <aside class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-book"></i>
            <h2>CMS</h2>
        </div>
        <nav class="sidebar-nav">
            <a href="program-setup.php" class="nav-item active">
                <i class="fas fa-graduation-cap"></i><span>Program Setup</span>
            </a>
            <a href="subject-management.php" class="nav-item">
                <i class="fas fa-book-open"></i><span>Subject Management</span>
            </a>
            <a href="course-scheduling.php" class="nav-item">
                <i class="fas fa-calendar-alt"></i><span>Course Scheduling</span>
            </a>
            <a href="revision-log-book.php" class="nav-item">
                <i class="fas fa-history"></i><span>Revision Log Book</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../login.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="header-left">
                <h1>Degree Programs</h1>
                <p class="header-subtitle">Registered academic programs for AY <?php echo $academicYear; ?>-<?php echo $academicYear + 1; ?></p>
            </div>
            <button class="btn-register-program" id="registerProgramBtn">
                <i class="fas fa-plus"></i><span>Register Program</span>
            </button>
        </div>

        <div id="alertBox" style="display:none;" class="alert"></div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-content">
                    <h3><?php echo $totalSubjects; ?></h3>
                    <p>Total Subjects</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="stat-content">
                    <h3><?php echo count($programs); ?></h3>
                    <p>Total Programs</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-content">
                    <h3><?php echo $academicYear; ?></h3>
                    <p>Academic Year</p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="programs-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Program Name</th>
                        <th>Subjects</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="programsTableBody">
                    <?php if (empty($programs)): ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:30px;color:#999;">
                            No programs found. Click <strong>Register Program</strong> to add one.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($programs as $i => $p): ?>
                    <tr class="program-row" data-id="<?php echo $p['id']; ?>">
                        <td><?php echo $i + 1; ?></td>
                        <td class="description-cell"><div class="description-text"><?php echo htmlspecialchars($p['name']); ?></div></td>
                        <td class="subjects-cell"><span class="subjects-badge"><?php echo $p['totalSubjects']; ?></span></td>
                        <td class="action-cell">
                            <div class="action-buttons">
                                <button class="btn-edit"   title="Edit"   data-id="<?php echo $p['id']; ?>"><i class="fas fa-edit"></i></button>
                                <button class="btn-view"   title="View"   data-id="<?php echo $p['id']; ?>"><i class="fas fa-eye"></i></button>
                                <button class="btn-delete" title="Delete" data-id="<?php echo $p['id']; ?>"><i class="fas fa-trash"></i></button>
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

<!-- Modal -->
<div class="modal" id="programModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Register New Program</h2>
            <button class="modal-close" id="closeModalBtn">&times;</button>
        </div>
        <form class="program-form" id="programForm">
            <input type="hidden" id="programId" value="">
            <div class="form-group">
                <label for="programName">Program Name</label>
                <input type="text" id="programName" placeholder="e.g., Bachelor of Science in Information Technology" required>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" id="cancelModalBtn">Cancel</button>
                <button type="submit" class="btn-submit" id="submitBtn">Register Program</button>
            </div>
        </form>
    </div>
</div>
<div class="modal-overlay" id="modalOverlay"></div>

<script>
    const API = 'program-setup.php';

    function showAlert(msg, type) {
        const box = document.getElementById('alertBox');
        box.textContent = msg;
        box.className = 'alert alert-' + (type || 'success');
        box.style.display = 'block';
        setTimeout(() => { box.style.display = 'none'; }, 4000);
    }

    async function api(action, body) {
        const opts = { headers: { 'Content-Type': 'application/json' } };
        if (body) { opts.method = 'POST'; opts.body = JSON.stringify(body); }
        const res = await fetch(API + '?action=' + action, opts);
        return res.json();
    }

function openModal(title) {
    document.getElementById('modalTitle').textContent = title || 'Register New Program';
    document.getElementById('programModal').classList.add('active');
    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
}

   function closeModal() {
    document.getElementById('programModal').classList.remove('active');
    document.getElementById('modalOverlay').classList.remove('active');
    document.getElementById('programForm').reset();
    document.getElementById('programId').value = '';
    document.getElementById('submitBtn').textContent = 'Register Program';
    document.body.style.overflow = 'auto';
}

    function buildRow(p, index) {
        return '<tr class="program-row" data-id="' + p.id + '">'
            + '<td>' + (index + 1) + '</td>'
            + '<td class="description-cell"><div class="description-text">' + p.name + '</div></td>'
            + '<td class="subjects-cell"><span class="subjects-badge">' + p.totalSubjects + '</span></td>'
            + '<td class="action-cell"><div class="action-buttons">'
            + '<button class="btn-edit"   title="Edit"   data-id="' + p.id + '"><i class="fas fa-edit"></i></button>'
            + '<button class="btn-view"   title="View"   data-id="' + p.id + '"><i class="fas fa-eye"></i></button>'
            + '<button class="btn-delete" title="Delete" data-id="' + p.id + '"><i class="fas fa-trash"></i></button>'
            + '</div></td></tr>';
    }

    async function refreshTable() {
        const res   = await api('list');
        const tbody = document.getElementById('programsTableBody');
        if (!res.success || !res.data.length) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:30px;color:#999;">No programs found. Click <strong>Register Program</strong> to add one.</td></tr>';
            return;
        }
        tbody.innerHTML = res.data.map((p, i) => buildRow(p, i)).join('');
    }

    document.getElementById('programForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id   = document.getElementById('programId').value;
        const body = {
            id:           id ? parseInt(id) : undefined,
            program_name: document.getElementById('programName').value.trim()
        };
        const res = await api(id ? 'update' : 'create', body);
        if (res.success) { showAlert(res.message, 'success'); closeModal(); refreshTable(); }
        else showAlert(res.message, 'error');
    });

    document.getElementById('programsTableBody').addEventListener('click', async function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = parseInt(btn.dataset.id);

        if (btn.classList.contains('btn-edit')) {
            const res = await api('get&id=' + id);
            if (!res.success) return showAlert(res.message, 'error');
            document.getElementById('programId').value    = res.data.id;
            document.getElementById('programName').value  = res.data.name;
            document.getElementById('submitBtn').textContent = 'Update Program';
            openModal('Edit Program');
        }

        if (btn.classList.contains('btn-delete')) {
            if (!confirm('Delete this program? This cannot be undone.')) return;
            const res = await api('delete', { id: id });
            showAlert(res.message, res.success ? 'success' : 'error');
            if (res.success) refreshTable();
        }

        if (btn.classList.contains('btn-view')) {
            window.location.href = 'course-scheduling.php?program_id=' + id;
        }
    });

    document.getElementById('registerProgramBtn').addEventListener('click', function() { openModal(); });
    document.getElementById('closeModalBtn').addEventListener('click', closeModal);
    document.getElementById('cancelModalBtn').addEventListener('click', closeModal);
    document.getElementById('modalOverlay').addEventListener('click', closeModal);
</script>
</body>
</html>