<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Edit Action';

// Get action ID from URL
$action_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$action_id) {
    set_flash_message(['type' => 'danger', 'message' => 'Invalid action ID.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

$conn = db_connect();

// Get action details with customer info
$stmt = $conn->prepare("SELECT a.*, c.company_name 
                       FROM actions a 
                       JOIN customers c ON a.customer_id = c.id 
                       WHERE a.id = ?");
$stmt->bind_param("i", $action_id);
$stmt->execute();
$action = $stmt->get_result()->fetch_assoc();

if (!$action) {
    set_flash_message(['type' => 'danger', 'message' => 'Action not found.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete action
        $stmt = $conn->prepare("DELETE FROM actions WHERE id = ?");
        $stmt->bind_param("i", $action_id);
        
        if ($stmt->execute()) {
            set_flash_message(['type' => 'success', 'message' => 'Action deleted successfully.']);
            header('Location: ' . SITE_URL . '/customers/view.php?id=' . $action['customer_id']);
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error deleting action.']);
        }
    } else {
        // Update action
        $action_text = sanitize_input($_POST['action']);
        $action_time = sanitize_input($_POST['action_time']);
        
        if (empty($action_text) || empty($action_time)) {
            set_flash_message(['type' => 'danger', 'message' => 'Action and time are required.']);
        } else {
            $response = sanitize_input($_POST['response']);
            $next_step = sanitize_input($_POST['next_step']);
            $followup_datetime = !empty($_POST['followup_datetime']) ? sanitize_input($_POST['followup_datetime']) : null;
            
            // Update action
            $stmt = $conn->prepare("UPDATE actions SET action_time = ?, action = ?, response = ?, 
                                  next_step = ?, followup_datetime = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $action_time, $action_text, $response, 
                            $next_step, $followup_datetime, $action_id);
            
            if ($stmt->execute()) {
                // Update customer's last_contact if this is the most recent action
                $stmt = $conn->prepare("UPDATE customers SET last_contact = ? 
                                      WHERE id = ? AND NOT EXISTS (
                                          SELECT 1 FROM actions 
                                          WHERE customer_id = ? AND action_time > ?
                                      )");
                $stmt->bind_param("siis", $action_time, $action['customer_id'], 
                                $action['customer_id'], $action_time);
                $stmt->execute();
                
                set_flash_message(['type' => 'success', 'message' => 'Action updated successfully.']);
                header('Location: ' . SITE_URL . '/customers/view.php?id=' . $action['customer_id']);
                exit();
            } else {
                set_flash_message(['type' => 'danger', 'message' => 'Error updating action.']);
            }
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="mb-0">Edit Action</h1>
        <p class="text-muted">for <?php echo htmlspecialchars($action['company_name']); ?></p>
    </div>
    
    <form method="POST" action="" class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="action_time">Date & Time *</label>
                <input type="datetime-local" id="action_time" name="action_time" class="form-control" required
                       value="<?php echo date('Y-m-d\TH:i', strtotime($action['action_time'])); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="action">Action *</label>
            <textarea id="action" name="action" class="form-control" rows="3" required><?php echo htmlspecialchars($action['action']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="response">Response</label>
            <textarea id="response" name="response" class="form-control" rows="3"><?php echo htmlspecialchars($action['response']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="next_step">Next Step</label>
            <textarea id="next_step" name="next_step" class="form-control" rows="3"><?php echo htmlspecialchars($action['next_step']); ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="followup_datetime">Follow-up Date & Time</label>
            <input type="datetime-local" id="followup_datetime" name="followup_datetime" class="form-control"
                   value="<?php echo $action['followup_datetime'] ? date('Y-m-d\TH:i', strtotime($action['followup_datetime'])) : ''; ?>">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="submit" name="delete" class="btn btn-danger" 
                    onclick="return confirm('Are you sure you want to delete this action?');">
                Delete Action
            </button>
            <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $action['customer_id']; ?>" 
               class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
