<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

require_once '../../../../config/database.php';

// Verify logged-in cashier user
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? null;
$userRole = $_SESSION['user_role'] ?? null;

if (!$userId || $userRole !== 'cashier') {
	header('Location: ../../login.php');
	exit;
}

$loggedInUser = $username;
$navActive = 'analytics';

// Get database connection
$conn = sms_get_db_connection();
if (!$conn) {
	die('Database connection failed');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Analytics Dashboard - Payment and Accounting</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/cashier-management-new.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
	<style>
		/* Charts Grid */
		.charts-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
			gap: 24px;
			margin-bottom: 24px;
		}

		.chart-card {
			background: white;
			border-radius: 12px;
			padding: 24px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.chart-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 20px;
		}

		.chart-header h3 {
			margin: 0;
			font-size: 1.1rem;
			color: #333;
			font-weight: 600;
		}

		.chart-controls {
			display: flex;
			gap: 8px;
		}

		.chart-btn {
			background: rgba(63, 105, 255, 0.1);
			color: #3f69ff;
			border: 1px solid #3f69ff;
			padding: 6px 12px;
			border-radius: 6px;
			cursor: pointer;
			font-size: 0.85rem;
			font-weight: 500;
			transition: all 0.2s ease;
		}

		.chart-btn:hover,
		.chart-btn.active {
			background: #3f69ff;
			color: white;
		}

		.chart-wrapper {
			position: relative;
			height: 300px;
		}

		.metrics-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
			gap: 18px;
			margin-bottom: 24px;
		}

		.metric-card {
			background: white;
			border-radius: 12px;
			padding: 20px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.metric-label {
			font-size: 0.85rem;
			color: #999;
			margin-bottom: 8px;
			font-weight: 500;
		}

		.metric-value {
			font-size: 1.8rem;
			font-weight: 700;
			color: #333;
			margin-bottom: 8px;
		}

		.metric-sub {
			font-size: 0.8rem;
			color: #10b981;
		}

		.transactions-table {
			background: white;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.table-header {
			padding: 20px;
			border-bottom: 1px solid #e0e0e0;
		}

		.table-header h3 {
			margin: 0;
			font-size: 1.1rem;
			color: #333;
			font-weight: 600;
		}

		.table-body {
			overflow-x: auto;
		}

		.transactions-table table {
			width: 100%;
			border-collapse: collapse;
		}

		.transactions-table th {
			background: #f9f9f9;
			padding: 14px;
			text-align: left;
			font-weight: 600;
			color: #666;
			border-bottom: 1px solid #e0e0e0;
			font-size: 0.9rem;
		}

		.transactions-table td {
			padding: 14px;
			border-bottom: 1px solid #f0f0f0;
			color: #333;
			font-size: 0.9rem;
		}

		.transactions-table tr:hover {
			background: #fafafa;
		}

		.status-badge {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 6px;
			font-size: 0.8rem;
			font-weight: 600;
		}

		.status-badge.paid {
			background: rgba(16, 185, 129, 0.2);
			color: #10b981;
		}

		.status-badge.partial {
			background: rgba(59, 130, 246, 0.2);
			color: #3b82f6;
		}

		.status-badge.pending {
			background: rgba(245, 158, 11, 0.2);
			color: #f59e0b;
		}

		.no-data {
			text-align: center;
			padding: 40px !important;
			color: #999;
		}

		.section-title {
			font-size: 1.3rem;
			color: #333;
			margin-bottom: 20px;
			font-weight: 600;
		}
	</style>
</head>

<body>
	<!-- Header -->
	<header>
		<h1>
			<i class="fa-solid fa-chart-bar"></i>
			Analytics Dashboard
		</h1>
		<div class="header-actions">
			<div class="role-badge">
				<i class="fa-solid fa-user-shield"></i>
				<span><?php echo htmlspecialchars($loggedInUser); ?></span>
			</div>
			<a href="../../../../components/logout.php" class="logout-btn">
				<i class="fa-solid fa-sign-out-alt"></i>
				Logout
			</a>
		</div>
	</header>

	<div class="container">
		<!-- Sidebar Navigation -->
		<aside class="sidebar">
			<div class="sidebar-header">
				<h2>Cashier Menu</h2>
			</div>

			<nav class="sidebar-nav">
				<a href="analytics.php" class="nav-item <?php echo $navActive === 'analytics' ? 'active' : ''; ?>">
					<i class="fa-solid fa-chart-line"></i>
					<span>Analytics</span>
				</a>

				<a href="scholarship-and-discount.php" class="nav-item <?php echo $navActive === 'scholarship-and-discount' ? 'active' : ''; ?>">
					<i class="fa-solid fa-graduation-cap"></i>
					<span>Scholarships & Discounts</span>
				</a>

				<a href="cashier-management.php?section=payment-entry" class="nav-item">
					<i class="fa-solid fa-receipt"></i>
					<span>Payment Entry</span>
				</a>

				<a href="cashier-management.php?section=payment-history" class="nav-item">
					<i class="fa-solid fa-history"></i>
					<span>Payment History</span>
				</a>

				<a href="transaction-log.php" class="nav-item <?php echo $navActive === 'transaction-log' ? 'active' : ''; ?>">
					<i class="fa-solid fa-list-check"></i>
					<span>Transaction Log</span>
				</a>
			</nav>
		</aside>

		<!-- Main Content -->
		<main class="content-wrapper">
			<!-- Header Section -->
			<div style="padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #e0e0e0;">
				<h2 style="color: #333; margin: 0 0 8px 0; font-size: 1.8rem;">
					<i class="fa-solid fa-chart-line" style="color: #3f69ff; margin-right: 10px;"></i>
					Analytics Overview
				</h2>
				<p style="color: #999; margin: 0; font-size: 0.95rem;">
					Monitor payment collection and transaction performance
				</p>
			</div>

			<!-- Metrics Grid -->
			<div class="metrics-grid">
				<div class="metric-card">
					<div class="metric-label">Today's Collection</div>
					<div class="metric-value" id="todayCollection">₱0.00</div>
					<div class="metric-sub" id="todayTrend">↑ No data yet</div>
				</div>

				<div class="metric-card">
					<div class="metric-label">Monthly Total</div>
					<div class="metric-value" id="monthlyTotal">₱0.00</div>
					<div class="metric-sub">Month to date</div>
				</div>

				<div class="metric-card">
					<div class="metric-label">Total Transactions</div>
					<div class="metric-value" id="totalTransactions">0</div>
					<div class="metric-sub">This month</div>
				</div>

				<div class="metric-card">
					<div class="metric-label">Pending Payments</div>
					<div class="metric-value" id="pendingPayments">0</div>
					<div class="metric-sub">Awaiting completion</div>
				</div>
			</div>

			<!-- Charts Section -->
			<div class="charts-grid">
				<div class="chart-card">
					<div class="chart-header">
						<h3>Daily Collections</h3>
						<div class="chart-controls">
							<button type="button" class="chart-btn active" data-period="week">Week</button>
							<button type="button" class="chart-btn" data-period="month">Month</button>
							<button type="button" class="chart-btn" data-period="year">Year</button>
						</div>
					</div>
					<div class="chart-wrapper">
						<canvas id="collectionsChart"></canvas>
					</div>
				</div>

				<div class="chart-card">
					<div class="chart-header">
						<h3>Payment Status</h3>
					</div>
					<div class="chart-wrapper">
						<canvas id="paymentStatusChart"></canvas>
					</div>
				</div>

</div>

			<!-- Recent Transactions -->
			<div class="transactions-table">
				<div class="table-header">
					<h3>Recent Transactions</h3>
				</div>
				<div class="table-body">
					<table>
						<thead>
							<tr>
								<th>Receipt #</th>
								<th>Student Name</th>
								<th>Amount</th>
								<th>Status</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody id="recentTransactionsBody">
							<tr>
								<td colspan="5" class="no-data">Loading transactions...</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</main>
	</div>

	<script src="../../assets/js/analytics.js"></script>
</body>

</html>
