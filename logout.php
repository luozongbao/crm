<?php
define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ' . SITE_URL . '/login.php');
exit();
