<?php
session_start();

// Check if user is authenticated as admin for CMS
if (!isset($_SESSION['cms_role']) || $_SESSION['cms_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../config/database.php';

$db = sms_get_db_connection();
$section = $_GET['section'] ?? 'dashboard';

// Fetch dashboard statistics
$program_count = $db->query("SELECT COUNT(*) as count FROM cms_programs WHERE status != 'archived'")->fetch_assoc()['count'] ?? 0;
$subject_count = $db->query("SELECT COUNT(*) as count FROM cms_subjects WHERE status != 'archived'")->fetch_assoc()['count'] ?? 0;
$active_curricula = $db->query("SELECT COUNT(*) as count FROM cms_curricula WHERE status = 'active'")->fetch_assoc()['count'] ?? 0;
$recent_logs = $db->query("SELECT * FROM cms_revision_logs ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curriculum Management System - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/index.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
        }

        .container-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 30px 30px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
        }

        .sidebar-header h2 {
            font-size: 24px;
        }

        .sidebar-nav {
            margin-top: 20px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 30px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background-color: rgba(255,255,255,0.15);
            border-left-color: #fff;
            color: white;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 30px;
            width: 100%;
            padding: 0 30px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            width: 100%;
            justify-content: center;
        }

        .logout-btn:hover {
            background-color: rgba(255,255,255,0.3);
        }

        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 40px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #333;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5568d3;
        }

        .btn-secondary {
            background-color: #e0e7ff;
            color: #667eea;
        }

        .btn-secondary:hover {
            background-color: #cfd9fc;
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-danger {
            background-color: #ef4444;
            color: white;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-top: 4px solid #667eea;
        }

        .stat-card h3 {
            color: #999;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .value {
            font-size: 32px;
            font-weight: 700;
            color: #333;
        }

        .form-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        thead {
            background-color: #f3f4f6;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }

        td {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-info {
            background-color: #dbeafe;
            color: #0c2340;
            border: 1px solid #bfdbfe;
        }

        .hidden {
            display: none;
        }

        .section-content {
            display: none;
        }

        .section-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-book"></i>
                <h2>CMS Admin</h2>
            </div>
            <nav class="sidebar-nav">
                <a href="?section=dashboard" class="nav-item <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?section=programs" class="nav-item <?php echo $section === 'programs' ? 'active' : ''; ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span>Programs</span>
                </a>
                <a href="?section=subjects" class="nav-item <?php echo $section === 'subjects' ? 'active' : ''; ?>">
                    <i class="fas fa-book-open"></i>
                    <span>Subjects</span>
                </a>
                <a href="?section=curriculum" class="nav-item <?php echo $section === 'curriculum' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Curriculum</span>
                </a>
                <a href="?section=revision" class="nav-item <?php echo $section === 'revision' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Revision Logs</span>
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
            <div class="page-header">
                <h1><?php echo ucfirst(str_replace('-', ' ', $section)); ?></h1>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="section-content <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <h3>Active Programs</h3>
                        <div class="value"><?php echo $program_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Subjects</h3>
                        <div class="value"><?php echo $subject_count; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Active Curricula</h3>
                        <div class="value"><?php echo $active_curricula; ?></div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Recent Activity</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_logs as $log): ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                                    <td><span class="badge"><?php echo $log['action']; ?></span></td>
                                    <td><?php echo $log['target_type']; ?></td>
                                    <td><?php echo $log['details']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Programs Section -->
            <div id="programs" class="section-content <?php echo $section === 'programs' ? 'active' : ''; ?>">
                <button class="btn btn-primary" onclick="openModal('programModal')">
                    <i class="fas fa-plus"></i> Add Program
                </button>

                <div class="form-section" style="margin-top: 20px;">
                    <div id="programsList"></div>
                </div>
            </div>

            <!-- Subjects Section -->
            <div id="subjects" class="section-content <?php echo $section === 'subjects' ? 'active' : ''; ?>">
                <button class="btn btn-primary" onclick="openModal('subjectModal')">
                    <i class="fas fa-plus"></i> Add Subject
                </button>

                <div class="form-section" style="margin-top: 20px;">
                    <div id="subjectsList"></div>
                </div>
            </div>

            <!-- Curriculum Section -->
            <div id="curriculum" class="section-content <?php echo $section === 'curriculum' ? 'active' : ''; ?>">
                <button class="btn btn-primary" onclick="openModal('curriculumModal')">
                    <i class="fas fa-plus"></i> Create Curriculum
                </button>

                <div class="form-section" style="margin-top: 20px;">
                    <div id="curriculumList"></div>
                </div>
            </div>

            <!-- Revision Logs Section -->
            <div id="revision" class="section-content <?php echo $section === 'revision' ? 'active' : ''; ?>">
                <div class="form-section">
                    <div id="revisionLogsList"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Program Modal -->
    <div id="programModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Program</h2>
                <button class="close-modal" onclick="closeModal('programModal')">&times;</button>
            </div>
            <form id="programForm">
                <div class="form-group">
                    <label>Program Code *</label>
                    <input type="text" name="code" required />
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <input type="text" name="description" required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total Units</label>
                        <input type="number" name="total_units" min="0" />
                    </div>
                    <div class="form-group">
                        <label>Total Hours</label>
                        <input type="number" name="total_hours" min="0" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Year Created</label>
                        <input type="number" name="year_created" value="<?php echo date('Y'); ?>" />
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Program</button>
            </form>
        </div>
    </div>

    <!-- Subject Modal -->
    <div id="subjectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Subject</h2>
                <button class="close-modal" onclick="closeModal('subjectModal')">&times;</button>
            </div>
            <form id="subjectForm">
                <div class="form-group">
                    <label>Subject Code *</label>
                    <input type="text" name="code" required />
                </div>
                <div class="form-group">
                    <label>Description *</label>
                    <input type="text" name="description" required />
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Units *</label>
                        <input type="number" name="units" min="1" required />
                    </div>
                    <div class="form-group">
                        <label>Hours</label>
                        <input type="number" name="hours" min="0" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Subject Type</label>
                        <select name="subject_type">
                            <option value="major">Major</option>
                            <option value="general_education">General Education</option>
                            <option value="elective">Elective</option>
                            <option value="practicum">Practicum</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Subject</button>
            </form>
        </div>
    </div>

    <!-- Curriculum Modal -->
    <div id="curriculumModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Create Curriculum</h2>
                <button class="close-modal" onclick="closeModal('curriculumModal')">&times;</button>
            </div>
            <form id="curriculumForm">
                <div class="form-group">
                    <label>Program *</label>
                    <select name="program_id" required id="currProgram">
                        <option>Select Program</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Year Level *</label>
                        <select name="year_level" required>
                            <option>Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Semester *</label>
                        <select name="semester" required>
                            <option>Select Semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Academic Year</label>
                    <input type="number" name="academic_year" value="<?php echo date('Y'); ?>" />
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Curriculum</button>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
    <script>
        const API_BASE = '../api/';

        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Load programs
        async function loadPrograms() {
            try {
                const response = await axios.get(API_BASE + 'programs.php?action=list');
                const programs = response.data.data;
                
                let html = '<table><thead><tr><th>Code</th><th>Description</th><th>Units</th><th>Year</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                programs.forEach(p => {
                    html += `<tr>
                        <td>${p.code}</td>
                        <td>${p.description}</td>
                        <td>${p.total_units}</td>
                        <td>${p.year_created}</td>
                        <td>${p.status}</td>
                        <td><button class="btn btn-sm btn-secondary" onclick="editProgram(${p.id})">Edit</button></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('programsList').innerHTML = html;

                // Update program dropdown in curriculum modal
                let optionsHtml = '<option>Select Program</option>';
                programs.forEach(p => {
                    optionsHtml += `<option value="${p.id}">${p.code}</option>`;
                });
                document.getElementById('currProgram').innerHTML = optionsHtml;
            } catch (error) {
                console.error('Error loading programs:', error);
            }
        }

        // Load subjects
        async function loadSubjects() {
            try {
                const response = await axios.get(API_BASE + 'subjects.php?action=list');
                const subjects = response.data.data;
                
                let html = '<table><thead><tr><th>Code</th><th>Description</th><th>Units</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                subjects.forEach(s => {
                    html += `<tr>
                        <td>${s.code}</td>
                        <td>${s.description}</td>
                        <td>${s.units}</td>
                        <td>${s.subject_type}</td>
                        <td>${s.status}</td>
                        <td><button class="btn btn-sm btn-secondary" onclick="editSubject(${s.id})">Edit</button></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('subjectsList').innerHTML = html;
            } catch (error) {
                console.error('Error loading subjects:', error);
            }
        }

        // Load curricula
        async function loadCurricula() {
            try {
                const response = await axios.get(API_BASE + 'curricula.php?action=list');
                const curricula = response.data.data;
                
                let html = '<table><thead><tr><th>Program</th><th>Year</th><th>Semester</th><th>Academic Year</th><th>Subjects</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                curricula.forEach(c => {
                    const subjectCount = c.subjects ? c.subjects.length : 0;
                    html += `<tr>
                        <td>${c.program.code}</td>
                        <td>Year ${c.year_level}</td>
                        <td>Sem ${c.semester}</td>
                        <td>${c.academic_year}</td>
                        <td>${subjectCount}</td>
                        <td>${c.status}</td>
                        <td><button class="btn btn-sm btn-secondary" onclick="editCurriculum(${c.id})">Manage</button></td>
                    </tr>`;
                });
                html += '</tbody></table>';
                document.getElementById('curriculumList').innerHTML = html;
            } catch (error) {
                console.error('Error loading curricula:', error);
            }
        }

        // Load revision logs
        async function loadRevisionLogs() {
            try {
                const response = await axios.get(API_BASE + '_helpers.php');
                // Note: Create a separate revisionlogs.php endpoint
                const html = '<p>Revision logs will be displayed here...</p>';
                document.getElementById('revisionLogsList').innerHTML = html;
            } catch (error) {
                console.error('Error loading revision logs:', error);
            }
        }

        // Handle program form submission
        document.getElementById('programForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await axios.post(API_BASE + 'programs.php?action=create', data);
                alert('Program created successfully!');
                closeModal('programModal');
                loadPrograms();
                e.target.reset();
            } catch (error) {
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        });

        // Handle subject form submission
        document.getElementById('subjectForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await axios.post(API_BASE + 'subjects.php?action=create', data);
                alert('Subject created successfully!');
                closeModal('subjectModal');
                loadSubjects();
                e.target.reset();
            } catch (error) {
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        });

        // Handle curriculum form submission
        document.getElementById('curriculumForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await axios.post(API_BASE + 'curricula.php?action=create', data);
                alert('Curriculum created successfully!');
                closeModal('curriculumModal');
                loadCurricula();
                e.target.reset();
            } catch (error) {
                alert('Error: ' + (error.response?.data?.message || error.message));
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadPrograms();
            loadSubjects();
            loadCurricula();
        });
    </script>
</body>
</html>
