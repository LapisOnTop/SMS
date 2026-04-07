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
$navActive = 'transaction-log';

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
	<title>Transaction Log - Payment and Accounting</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
	<link rel="stylesheet" href="../../assets/css/cashier-management-new.css">
	<style>
		/* Stats Grid */
		.stats-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
			gap: 18px;
			margin-bottom: 24px;
		}

		.stat-card {
			background: white;
			border-radius: 12px;
			padding: 20px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.stat-card label {
			font-size: 0.85rem;
			color: #999;
			display: block;
			margin-bottom: 8px;
			font-weight: 500;
		}

		.stat-card strong {
			font-size: 1.6rem;
			color: #333;
			font-weight: 700;
		}

		/* Filter Section */
		.filter-section {
			background: white;
			border-radius: 12px;
			padding: 20px;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
			margin-bottom: 24px;
		}

		.filter-row {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
			align-items: flex-end;
		}

		.form-group {
			display: flex;
			flex-direction: column;
			min-width: 160px;
			flex: 1;
		}

		.form-group label {
			font-size: 0.85rem;
			font-weight: 600;
			color: #333;
			margin-bottom: 6px;
		}

		.form-group input,
		.form-group select {
			padding: 10px;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			font-family: 'Poppins', sans-serif;
			font-size: 0.9rem;
			color: #333;
			background: #fafafa;
			transition: all 0.2s ease;
		}

		.form-group input:focus,
		.form-group select:focus {
			outline: none;
			background: white;
			border-color: #3f69ff;
			box-shadow: 0 0 0 3px rgba(63, 105, 255, 0.1);
		}

		.btn {
			padding: 10px 20px;
			border-radius: 6px;
			font-weight: 600;
			cursor: pointer;
			border: none;
			font-family: 'Poppins', sans-serif;
			transition: all 0.2s ease;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}

		.btn-primary {
			background: #3f69ff;
			color: white;
			height: fit-content;
		}

		.btn-primary:hover {
			background: #2d5adb;
		}

		.filter-btn {
			background: #3f69ff;
			color: white;
			border: none;
			padding: 0;
			width: 40px;
			height: 40px;
			display: inline-flex;
			align-items: center;
			justify-content: center;
			border-radius: 6px;
			cursor: pointer;
			transition: all 0.2s ease;
			flex-shrink: 0;
		}

		.filter-btn:hover {
			background: #2d5adb;
		}

		/* Table Section */
		.table-card {
			background: white;
			border-radius: 12px;
			overflow: hidden;
			box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
		}

		.table-wrapper {
			overflow-x: auto;
		}

		.table-card table {
			width: 100%;
			border-collapse: collapse;
		}

		.table-card th {
			background: #f9f9f9;
			padding: 14px;
			text-align: left;
			font-weight: 600;
			color: #666;
			border-bottom: 1px solid #e0e0e0;
			font-size: 0.9rem;
		}

		.table-card td {
			padding: 14px;
			border-bottom: 1px solid #f0f0f0;
			color: #333;
			font-size: 0.9rem;
		}

		.table-card tr:hover {
			background: #fafafa;
		}

		.status-badge {
			display: inline-block;
			padding: 5px 10px;
			border-radius: 6px;
			font-size: 0.8rem;
			font-weight: 600;
		}

		.status-badge.validated {
			background: rgba(16, 185, 129, 0.2);
			color: #10b981;
		}

		.status-badge.approved {
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

		.footer-text {
			padding: 16px 20px;
			font-size: 0.9rem;
			color: #999;
			border-top: 1px solid #e0e0e0;
		}
	</style>
</head>

<body>
	<!-- Header -->
	<header>
		<h1>
			<i class="fa-solid fa-list-check"></i>
			Transaction Log
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
					<i class="fa-solid fa-list-check" style="color: #3f69ff; margin-right: 10px;"></i>
					Financial Transactions Log
				</h2>
				<p style="color: #999; margin: 0; font-size: 0.95rem;">
					View and manage all payment and transaction records
				</p>
			</div>

			<!-- Statistics Cards -->
			<div class="stats-grid">
				<div class="stat-card">
					<label>Total Transactions</label>
					<strong id="totalTransactions">0</strong>
				</div>
				<div class="stat-card">
					<label>Total Payments</label>
					<strong id="totalPayments">₱0.00</strong>
				</div>
				<div class="stat-card">
					<label>Payment Count</label>
					<strong id="paymentCount">0</strong>
				</div>
				<div class="stat-card">
					<label>Partial Payments</label>
					<strong id="totalDiscounts">₱0.00</strong>
				</div>
			</div>

			<!-- Filter Section -->
			<div class="filter-section">
				<div class="filter-row">
					<div class="form-group" style="flex: 2;">
						<label for="searchTransactions">Search</label>
						<input type="text" id="searchTransactions" placeholder="Search student, OR number, amount...">
					</div>
					<div class="form-group">
						<label for="typeFilter">Type</label>
						<select id="typeFilter">
							<option value="All">All Types</option>
							<option value="Payment">Payment</option>
							<option value="Discount">Discount</option>
							<option value="Assessment">Assessment</option>
						</select>
					</div>
					<div class="form-group">
						<label for="statusFilter">Status</label>
						<select id="statusFilter">
							<option value="All">All Status</option>
							<option value="Full">Full</option>
							<option value="Partial">Partial</option>
						</select>
					</div>
					<div class="form-group">
						<label for="fromDate">From Date</label>
						<input type="date" id="fromDate">
					</div>
					<div class="form-group">
						<label for="toDate">To Date</label>
						<div style="display: flex; gap: 8px; align-items: flex-end;">
							<input type="date" id="toDate" style="flex: 1;">
							<button type="button" class="filter-btn" id="filterButton" title="Filter">
								<i class="fa-solid fa-filter"></i>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Transactions Table -->
			<div class="table-card">
				<div class="table-wrapper">
					<table>
						<thead>
							<tr>
								<th>Date</th>
								<th>Student ID</th>
								<th>Student Name</th>
								<th>Type</th>
								<th>Payment Method</th>
								<th>Amount</th>
								<th>OR Number</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody id="transactionBody">
							<tr>
								<td colspan="8" class="no-data">Loading transactions...</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="footer-text">
					Showing <span id="resultsText">0</span> records
				</div>
			</div>
		</main>
	</div>

	<script src="../../assets/js/transaction-log.js"></script>
</body>

</html>
