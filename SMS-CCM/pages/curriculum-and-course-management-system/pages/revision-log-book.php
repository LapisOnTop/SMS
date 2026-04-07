<?php
session_start();

// Check if user is authenticated as admin for CMS
if (!isset($_SESSION['cms_role']) || $_SESSION['cms_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// ── Database connection ────────────────────────────────────────────────────────
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

// ── Fetch revision logs ────────────────────────────────────────────────────────
// NOTE: The `revision_logs` table does not yet exist in the pamana schema.
// The query below will return an empty result set gracefully until the table
// is created. Expected columns: id, timestamp, action, details.
$revisionLogs = [];

try {
    $stmt = $pdo->query("
        SELECT id, timestamp, action, details
        FROM revision_logs
        ORDER BY timestamp DESC
    ");
    $revisionLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table doesn't exist yet — show empty state instead of crashing
    $revisionLogs = [];
}

$totalLogs = count($revisionLogs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revision Log Book - Curriculum Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/program-setup.css">
    <link rel="stylesheet" href="../assets/css/revision-log-book.css">
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
                <a href="course-scheduling.php" class="nav-item" data-section="course-scheduling">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Course Scheduling</span>
                </a>
                <a href="revision-log-book.php" class="nav-item active" data-section="revision-log">
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
                    <h1>Revision Log Book</h1>
                    <p class="header-subtitle">All actions logged to <strong>revision_logs</strong> table in MySQL</p>
                </div>
                <div class="header-actions">
                    <button class="btn-export-csv" id="exportCsvBtn">
                        <i class="fas fa-download"></i>
                        <span>Export CSV</span>
                    </button>
                    <button class="btn-reset" id="resetBtn">
                        <i class="fas fa-redo"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-content">
                        <h3 id="totalLogsCount"><?php echo $totalLogs; ?></h3>
                        <p>Total Logs</p>
                    </div>
                </div>
            </div>

            <!-- Logs Table Section -->
            <div class="table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>TIMESTAMP</th>
                            <th>ACTION</th>
                            <th>DETAILS</th>
                        </tr>
                    </thead>
                    <tbody id="logsTableBody">
                        <?php if (empty($revisionLogs)): ?>
                        <tr>
                            <td colspan="3" style="text-align:center;padding:2rem;color:#888;">
                                No revision logs found. The <code>revision_logs</code> table may not exist yet.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($revisionLogs as $log): ?>
                        <tr class="log-row"
                            data-log-id="<?php echo $log['id']; ?>"
                            data-action="<?php echo htmlspecialchars($log['action']); ?>">
                            <td class="timestamp-cell">
                                <div class="timestamp-wrapper">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo htmlspecialchars($log['timestamp']); ?></span>
                                </div>
                            </td>
                            <td class="action-cell">
                                <span class="action-badge action-<?php echo strtolower(htmlspecialchars($log['action'])); ?>">
                                    <?php echo htmlspecialchars($log['action']); ?>
                                </span>
                            </td>
                            <td class="details-cell">
                                <span class="details-text"><?php echo htmlspecialchars($log['details']); ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Message Toast -->
    <div class="message-toast" id="messageToast">
        <span id="messageText"></span>
    </div>

    <script src="../assets/js/revision-log-book.js"></script>
</body>
</html>