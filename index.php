<?php
define('BASE_PATH', dirname(__FILE__));

// Check if config file exists
if (!file_exists(BASE_PATH . '/includes/config.php')) {
    header('Location: /admin/install.php');
    exit();
}

require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

// Redirect to dashboard if logged in, otherwise to login page
if (isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/dashboard.php');
} else {
    header('Location: ' . SITE_URL . '/login.php');
}
exit();
