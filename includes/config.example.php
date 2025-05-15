<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'crm_db');

define('SITE_URL', 'http://localhost/crm');
define('BASE_PATH', dirname(__DIR__));

// Session lifetime in seconds (30 minutes)
define('SESSION_LIFETIME', 1800);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);