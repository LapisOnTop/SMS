<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$db = ums_db();
ums_install_schema($db);
ums_require_admin();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    ums_json(['ok' => false, 'message' => 'Method not allowed'], 405);
}

$rows = [];
$res = $db->query(
    'SELECT created_at, actor_username AS actor, actor_role AS role, action, module, target
     FROM user_activity_logs
     ORDER BY id DESC
     LIMIT 300'
);
if ($res) {
    while ($r = $res->fetch_assoc()) $rows[] = $r;
}

ums_log($db, 'Viewed page: User Activity Logs', null);

ums_json(['ok' => true, 'logs' => $rows]);

