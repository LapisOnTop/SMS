<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Online Admission - Selection</title>
	<link rel="stylesheet" href="../../assets/css/selection.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
	<?php
		// Session validation
		session_start();
		if (!isset($_SESSION['enrollment_role']) || $_SESSION['enrollment_role'] !== 'user') {
			header('Location: ../../role-selection.php');
			exit;
		}

		$allowedSteps = ['branch', 'program'];
		$step = isset($_GET['step']) ? strtolower(trim($_GET['step'])) : 'branch';
		if (!in_array($step, $allowedSteps, true)) {
			$step = 'branch';
		}

		$stepConfig = [
			'branch' => [
				'icon' => 'fa-solid fa-location-dot',
				'title' => 'Main Branch',
				'description' => '#1071 Brgy. Kaligayahan, Quirino Highway Novaliches, Quezon City',
				'details' => [],
				'nextHref' => 'selection.php?step=program',
				'buttonText' => 'Proceed to Program Selection'
			],
			'program' => [
				'icon' => 'fa-solid fa-laptop-code',
				'title' => 'BSIT',
				'description' => 'Bachelor of Science in Information Technology',
				'details' => [],
				'nextHref' => 'subjects.php',
				'buttonText' => 'Proceed to Subject Selection'
			]
		];

		$active = $stepConfig[$step];
	?>

	<main class="selection-page">
		<section class="selection-card">
			<div class="selection-icon">
				<i class="<?php echo htmlspecialchars($active['icon']); ?>"></i>
			</div>

			<h1><?php echo htmlspecialchars($active['title']); ?></h1>
			<p class="selection-description"><?php echo htmlspecialchars($active['description']); ?></p>

			<?php if (!empty($active['details'])): ?>
				<ul class="selection-list">
					<?php foreach ($active['details'] as $item): ?>
						<?php if ($item === ''): ?>
							<li class="selection-space" aria-hidden="true"></li>
						<?php elseif (strpos($item, 'College ') === 0): ?>
							<li class="selection-heading"><?php echo htmlspecialchars($item); ?></li>
						<?php else: ?>
							<li>
								<i class="fa-solid fa-check"></i>
								<span><?php echo htmlspecialchars($item); ?></span>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<a href="<?php echo htmlspecialchars($active['nextHref']); ?>" class="selection-btn">
				<?php echo htmlspecialchars($active['buttonText']); ?>
			</a>
		</section>
	</main>
</body>
</html>
