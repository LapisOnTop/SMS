<?php

declare(strict_types=1);

// Backward-compat redirect for mistakenly duplicated paths like:
// /pages/student-information-management-system/pages/student-information-management-system/role-selection.php
header('Location: ../role-selection.php', true, 302);
exit;

