<?php
$pageTitle = $pageTitle ?? 'Payment and Accounting System';
$userRole = $userRole ?? 'admin';
$navActive = $navActive ?? 'dashboard';
$workspaceLabel = $workspaceLabel ?? 'Operations Hub';
$heroTitle = $heroTitle ?? 'Payment and Accounting System';
$heroCopy = $heroCopy ?? 'A stable workspace for cashier and admin operations.';
$sectionTitle = $sectionTitle ?? 'Workspace Overview';
$sectionCopy = $sectionCopy ?? 'Use the navigation to move between the cashier and admin workflows.';
$primaryActionLabel = $primaryActionLabel ?? 'Open Assessment Fees';
$primaryActionHref = $primaryActionHref ?? '../cashier/assessment-fee.php';
$secondaryActionLabel = $secondaryActionLabel ?? 'Back to Dashboard';
$secondaryActionHref = $secondaryActionHref ?? '../dashboard.php';
$dashboardHref = $dashboardHref ?? '../dashboard.php';
$cashierAssessmentHref = $cashierAssessmentHref ?? '../cashier/assessment-fee.php';
$cashierPostingHref = $cashierPostingHref ?? '../cashier/payment-posting.php';
$cashierBillingHref = $cashierBillingHref ?? '../cashier/billing-statement.php';
$adminLogHref = $adminLogHref ?? '../admin/transaction-log.php';
$adminScholarshipHref = $adminScholarshipHref ?? '../admin/scholarship-and-discount.php';
$adminAnalyticsHref = $adminAnalyticsHref ?? '../admin/analytics.php';
$logoutHref = $logoutHref ?? '../components/logout.php';

function payment_nav_class($current, $target)
{
	return 'nav-link' . ($current === $target ? ' active' : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($pageTitle); ?></title>
	<link rel="stylesheet" href="../../assets/css/assessment-fee.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
	<div class="app-shell">
		<aside class="sidebar">
			<div class="brand-block">
				<div class="brand-mark">SMS</div>
				<div>
					<h1>Payment &amp; Accounting</h1>
					<p>System</p>
				</div>
			</div>

			<nav class="nav-menu">
				<?php if ($userRole === 'cashier') { ?>
					<div class="nav-group-label">Cashier</div>
					<a href="<?php echo htmlspecialchars($cashierAssessmentHref); ?>" class="<?php echo payment_nav_class($navActive, 'assessment-fee'); ?>"><i class="fa-solid fa-file-invoice-dollar"></i>Assessment Fees</a>
					<a href="<?php echo htmlspecialchars($cashierBillingHref); ?>" class="<?php echo payment_nav_class($navActive, 'billing-statement'); ?>"><i class="fa-solid fa-receipt"></i>Billing Statement</a>
					<a href="<?php echo htmlspecialchars($cashierPostingHref); ?>" class="<?php echo payment_nav_class($navActive, 'payment-posting'); ?>"><i class="fa-solid fa-credit-card"></i>Payment Posting</a>
				<?php } else { ?>
					<div class="nav-group-label">Admin</div>
					<a href="<?php echo htmlspecialchars($adminScholarshipHref); ?>" class="<?php echo payment_nav_class($navActive, 'scholarship-and-discount'); ?>"><i class="fa-solid fa-tags"></i>Scholarship &amp; Discount</a>
					<a href="<?php echo htmlspecialchars($adminLogHref); ?>" class="<?php echo payment_nav_class($navActive, 'transaction-log'); ?>"><i class="fa-solid fa-list-check"></i>Transaction Log</a>
					<a href="<?php echo htmlspecialchars($adminAnalyticsHref); ?>" class="<?php echo payment_nav_class($navActive, 'analytics'); ?>"><i class="fa-solid fa-chart-line"></i>Analytics</a>
				<?php } ?>
				<a href="<?php echo htmlspecialchars($logoutHref); ?>" class="nav-link danger"><i class="fa-solid fa-right-from-bracket"></i>Logout</a>
			</nav>
		</aside>

		<main class="content">
			<section class="hero-panel">
				<div>
					<p class="eyebrow"><?php echo htmlspecialchars($workspaceLabel); ?></p>
					<h2><?php echo htmlspecialchars($heroTitle); ?></h2>
					<p class="hero-copy"><?php echo htmlspecialchars($heroCopy); ?></p>
				</div>
				<div class="hero-chip">
					<i class="fa-solid fa-circle-info"></i>
					<span>Stable navigation shell</span>
				</div>
			</section>

			<section class="card info-card">
				<div class="section-title">
					<i class="fa-solid fa-screwdriver-wrench"></i>
					<h3><?php echo htmlspecialchars($sectionTitle); ?></h3>
				</div>
				<p class="hero-copy" style="color:#5b6480;"><?php echo htmlspecialchars($sectionCopy); ?></p>
				<div class="action-row" style="margin-top: 18px;">
					<a href="<?php echo htmlspecialchars($primaryActionHref); ?>" class="btn-primary"><i class="fa-solid fa-arrow-right"></i> <?php echo htmlspecialchars($primaryActionLabel); ?></a>
					<a href="<?php echo htmlspecialchars($secondaryActionHref); ?>" class="btn-secondary"><i class="fa-solid fa-house"></i> <?php echo htmlspecialchars($secondaryActionLabel); ?></a>
				</div>
			</section>
		</main>
	</div>
</body>
</html>