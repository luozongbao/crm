<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Add Action';

// Get customer ID from URL
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
if (!$customer_id) {
    set_flash_message(['type' => 'danger', 'message' => 'Invalid customer ID.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

// Verify customer exists
$conn = db_connect();
$stmt = $conn->prepare("SELECT id, company_name FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    set_flash_message(['type' => 'danger', 'message' => 'Customer not found.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action']);
    $action_time = sanitize_input($_POST['action_time']);
    
    if (empty($action) || empty($action_time)) {
        set_flash_message(['type' => 'danger', 'message' => 'Action and time are required.']);
    } else {
        $response = sanitize_input($_POST['response']);
        $next_step = sanitize_input($_POST['next_step']);
        $followup_datetime = !empty($_POST['followup_datetime']) ? sanitize_input($_POST['followup_datetime']) : null;
        
        // Insert action
        $stmt = $conn->prepare("INSERT INTO actions (customer_id, action_time, action, response, 
                              next_step, followup_datetime) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $customer_id, $action_time, $action, $response, 
                         $next_step, $followup_datetime);
        
        if ($stmt->execute()) {
            // Update customer's last_contact
            $stmt = $conn->prepare("UPDATE customers SET last_contact = ? WHERE id = ?");
            $stmt->bind_param("si", $action_time, $customer_id);
            $stmt->execute();
            
            set_flash_message(['type' => 'success', 'message' => 'Action added successfully.']);
            header('Location: ' . SITE_URL . '/customers/view.php?id=' . $customer_id);
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error adding action.']);
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="mb-0">Add Action</h1>
        <p class="text-muted">for <?php echo htmlspecialchars($customer['company_name']); ?></p>
    </div>
    
    <form method="POST" action="" class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="action_time">Date & Time *</label>
                <input type="datetime-local" id="action_time" name="action_time" class="form-control" required
                       value="<?php echo isset($_POST['action_time']) ? htmlspecialchars($_POST['action_time']) : date('Y-m-d\TH:i'); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="action">Action *</label>
            <textarea id="action" name="action" class="form-control" rows="3" required><?php echo isset($_POST['action']) ? htmlspecialchars($_POST['action']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="response">Response</label>
            <textarea id="response" name="response" class="form-control" rows="3"><?php echo isset($_POST['response']) ? htmlspecialchars($_POST['response']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="next_step">Next Step</label>
            <textarea id="next_step" name="next_step" class="form-control" rows="3"><?php echo isset($_POST['next_step']) ? htmlspecialchars($_POST['next_step']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="followup_datetime">Follow-up Date & Time</label>
            <input type="datetime-local" id="followup_datetime" name="followup_datetime" class="form-control"
                   value="<?php echo isset($_POST['followup_datetime']) ? htmlspecialchars($_POST['followup_datetime']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Action</button>
            <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $customer_id; ?>" 
               class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
