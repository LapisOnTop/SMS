<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head><?php require_once 'includes/head.php'; ?></head>

<body>

    <div id="app-container" style="display:flex;width:100%;">
        <?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content" id="appMain">

            <?php require_once 'includes/topbar.php'; ?>
            <?php require_once 'pages/dashboard.php'; ?>
            <?php require_once 'pages/sections.php';  ?>
            <?php require_once 'pages/timetable.php'; ?>
            <?php require_once 'pages/rooms.php';     ?>
            <?php require_once 'pages/faculty.php';   ?>
            <?php require_once 'pages/conflicts.php'; ?>
            <?php require_once 'pages/terms.php';     ?>
        </main>
    </div>

    <?php require_once 'includes/modals.php'; ?>
    <?php require_once 'components/toast.php'; ?>

    <script src="assets/js/api.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/render.js"></script>
    <script src="assets/js/actions.js"></script>

</body>

</html>