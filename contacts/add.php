<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Add Contact Person';

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
    $name = sanitize_input($_POST['name']);
    
    if (empty($name)) {
        set_flash_message(['type' => 'danger', 'message' => 'Contact name is required.']);
    } else {
        $title = sanitize_input($_POST['title']);
        $role = sanitize_input($_POST['role']);
        $contact_number = sanitize_input($_POST['contact_number']);
        $email = sanitize_input($_POST['email']);
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;
        
        // If making this contact primary, unset other primary contacts
        if ($is_primary) {
            $stmt = $conn->prepare("UPDATE contacts SET is_primary = 0 WHERE customer_id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
        }
        
        // Insert new contact
        $stmt = $conn->prepare("INSERT INTO contacts (customer_id, name, title, role, contact_number, 
                              email, is_primary) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssi", $customer_id, $name, $title, $role, $contact_number, 
                         $email, $is_primary);
        
        if ($stmt->execute()) {
            set_flash_message(['type' => 'success', 'message' => 'Contact added successfully.']);
            header('Location: ' . SITE_URL . '/customers/view.php?id=' . $customer_id);
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error adding contact.']);
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="mb-0">Add Contact Person</h1>
        <p class="text-muted">for <?php echo htmlspecialchars($customer['company_name']); ?></p>
    </div>
    
    <form method="POST" action="" class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <input type="text" id="role" name="role" class="form-control"
                   value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : ''; ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" class="form-control"
                       value="<?php echo isset($_POST['contact_number']) ? htmlspecialchars($_POST['contact_number']) : ''; ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="is_primary" name="is_primary"
                       <?php echo isset($_POST['is_primary']) ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="is_primary">Set as primary contact</label>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Contact</button>
            <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $customer_id; ?>" 
               class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
