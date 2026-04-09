<?php
require_once __DIR__ . '/config.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

header('Location: exp1.php');
exit;
