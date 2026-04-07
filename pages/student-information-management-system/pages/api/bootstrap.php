<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/sim_bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$repo = new StudentRepository(db());
json_response(['ok' => true, 'boot' => compute_app_boot($repo)]);
