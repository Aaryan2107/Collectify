<?php
require_once __DIR__ . '/config.php';

unset($_SESSION['admin_logged_in'], $_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_last_activity']);

header('Location: login.php?role=admin&msg=logout');
exit();
