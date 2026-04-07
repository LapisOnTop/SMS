<?php
/**
 * Payment System Sidebar Component
 * Static sidebar with default values
 */

// Set defaults for variables if not provided
$userRole = $userRole ?? 'cashier';
$navActive = $navActive ?? 'assessment-fee';
$systemName = 'Payment & Accounting System';
$systemInitials = 'SMS';
?>
<aside class="sidebar">
	<nav class="nav-menu">
		<?php if ($userRole === 'cashier') { ?>
			<div class="nav-group-label">Cashier Workspace</div>
			<a href="../cashier/assessment-fee.php" class="<?php echo ($navActive === 'assessment-fee' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-file-invoice-dollar"></i>Assessment Fees
			</a>
			<a href="../cashier/billing-statement.php" class="<?php echo ($navActive === 'billing-statement' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-receipt"></i>Billing Statement
			</a>
			<a href="../cashier/payment-posting.php" class="<?php echo ($navActive === 'payment-posting' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-credit-card"></i>Payment Posting
			</a>
		<?php } elseif ($userRole === 'admin') { ?>
			<div class="nav-group-label">Admin Workspace</div>
			<a href="../admin/scholarship-and-discount.php" class="<?php echo ($navActive === 'scholarship-and-discount' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-graduation-cap"></i>Scholarships &amp; Discounts
			</a>
			<a href="../admin/transaction-log.php" class="<?php echo ($navActive === 'transaction-log' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-list-check"></i>Transactions Log
			</a>
			<a href="../admin/analytics.php" class="<?php echo ($navActive === 'analytics' ? 'nav-link active' : 'nav-link'); ?>">
				<i class="fa-solid fa-chart-line"></i>Analytics
			</a>
		<?php } ?>
		<a href="../../components/logout.php" class="nav-link danger">
			<i class="fa-solid fa-sign-out-alt"></i>Logout
		</a>
	</nav>
</aside>
