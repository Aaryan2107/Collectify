<?php
// admin/auth_check.php
// Include this at the top of every admin page (after config.php)

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php?role=admin');
    exit();
}

// Session timeout: 2 hours of inactivity
$timeout = 7200;
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header('Location: login.php?role=admin&msg=timeout');
    exit();
}
$_SESSION['admin_last_activity'] = time();

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
