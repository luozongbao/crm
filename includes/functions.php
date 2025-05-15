<?php
session_start();

function db_connect() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin';
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function get_pagination_limit() {
    $conn = db_connect();
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_name = 'pagination_limit'");
    $row = $result->fetch_assoc();
    return (int)$row['setting_value'];
}

function format_date($date) {
    return date('Y-m-d H:i:s', strtotime($date));
}

function get_customer_status_counts() {
    $conn = db_connect();
    $query = "SELECT status, COUNT(*) as count FROM customers GROUP BY status";
    $result = $conn->query($query);
    $counts = [];
    while ($row = $result->fetch_assoc()) {
        $counts[$row['status']] = $row['count'];
    }
    return $counts;
}

function get_upcoming_followups($limit = 10) {
    $conn = db_connect();
    $query = "SELECT a.*, c.company_name 
              FROM actions a 
              JOIN customers c ON a.customer_id = c.id 
              WHERE a.followup_datetime >= NOW() 
              ORDER BY a.followup_datetime ASC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function get_recent_activities($limit = 10) {
    $conn = db_connect();
    $query = "SELECT a.*, c.company_name 
              FROM actions a 
              JOIN customers c ON a.customer_id = c.id 
              ORDER BY a.action_time DESC 
              LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function create_default_contact($customer_id, $company_name) {
    $conn = db_connect();
    $query = "INSERT INTO contacts (customer_id, name, is_primary) VALUES (?, 'Company Main Contact', 1)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customer_id);
    return $stmt->execute();
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function set_flash_message($message) {
    $_SESSION['flash_message'] = $message;
}

function generate_pagination($total_records, $current_page, $records_per_page) {
    $total_pages = ceil($total_records / $records_per_page);
    $pagination = [];
    
    if ($total_pages > 1) {
        if ($current_page > 1) {
            $pagination['prev'] = $current_page - 1;
        }
        
        if ($current_page < $total_pages) {
            $pagination['next'] = $current_page + 1;
        }
        
        $pagination['current'] = $current_page;
        $pagination['total'] = $total_pages;
    }
    
    return $pagination;
}