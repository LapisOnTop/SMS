<?php
$host = 'junction.proxy.rlwy.net';
$user = 'root';
$pass = 'vBSedJaGwGB1seq1xtbQWtzUoNutREke';
$port = 20543;
$db   = 'railway';

echo "Attempting to connect to Railway MySQL...\n";

$mysqli = mysqli_init();
if (!$mysqli) {
    die("mysqli_init failed\n");
}

// Set a long timeout
$mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30);

// Disable SSL just in case
$mysqli->options(MYSQLI_CLIENT_SSL, false);

$start = microtime(true);
if (@$mysqli->real_connect($host, $user, $pass, $db, $port)) {
    echo "SUCCESS! Connected in " . round(microtime(true) - $start, 2) . "s\n";
    echo "Server Info: " . $mysqli->server_info . "\n";
    $mysqli->close();
} else {
    echo "FAILURE after " . round(microtime(true) - $start, 2) . "s\n";
    echo "Connect Error (" . mysqli_connect_errno() . "): " . mysqli_connect_error() . "\n";
}
