<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Edit Contact Person';

// Get contact ID from URL
$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$contact_id) {
    set_flash_message(['type' => 'danger', 'message' => 'Invalid contact ID.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

$conn = db_connect();

// Get contact details with customer info
$stmt = $conn->prepare("SELECT c.*, cu.company_name 
                       FROM contacts c 
                       JOIN customers cu ON c.customer_id = cu.id 
                       WHERE c.id = ?");
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$contact = $stmt->get_result()->fetch_assoc();

if (!$contact) {
    set_flash_message(['type' => 'danger', 'message' => 'Contact not found.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete contact
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->bind_param("i", $contact_id);
        
        if ($stmt->execute()) {
            set_flash_message(['type' => 'success', 'message' => 'Contact deleted successfully.']);
            header('Location: ' . SITE_URL . '/customers/view.php?id=' . $contact['customer_id']);
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error deleting contact.']);
        }
    } else {
        // Update contact
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
            if ($is_primary && !$contact['is_primary']) {
                $stmt = $conn->prepare("UPDATE contacts SET is_primary = 0 WHERE customer_id = ?");
                $stmt->bind_param("i", $contact['customer_id']);
                $stmt->execute();
            }
            
            // Update contact
            $stmt = $conn->prepare("UPDATE contacts SET name = ?, title = ?, role = ?, 
                                  contact_number = ?, email = ?, is_primary = ? 
                                  WHERE id = ?");
            $stmt->bind_param("sssssii", $name, $title, $role, $contact_number, 
                            $email, $is_primary, $contact_id);
            
            if ($stmt->execute()) {
                set_flash_message(['type' => 'success', 'message' => 'Contact updated successfully.']);
                header('Location: ' . SITE_URL . '/customers/view.php?id=' . $contact['customer_id']);
                exit();
            } else {
                set_flash_message(['type' => 'danger', 'message' => 'Error updating contact.']);
            }
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h1 class="mb-0">Edit Contact Person</h1>
        <p class="text-muted">for <?php echo htmlspecialchars($contact['company_name']); ?></p>
    </div>
    
    <form method="POST" action="" class="card-body">
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="name">Name *</label>
                <input type="text" id="name" name="name" class="form-control" required
                       value="<?php echo htmlspecialchars($contact['name']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control"
                       value="<?php echo htmlspecialchars($contact['title']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="role">Role</label>
            <input type="text" id="role" name="role" class="form-control"
                   value="<?php echo htmlspecialchars($contact['role']); ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="contact_number">Contact Number</label>
                <input type="tel" id="contact_number" name="contact_number" class="form-control"
                       value="<?php echo htmlspecialchars($contact['contact_number']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($contact['email']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="is_primary" name="is_primary"
                       <?php echo $contact['is_primary'] ? 'checked' : ''; ?>>
                <label class="custom-control-label" for="is_primary">Set as primary contact</label>
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="submit" name="delete" class="btn btn-danger" 
                    onclick="return confirm('Are you sure you want to delete this contact?');">
                Delete Contact
            </button>
            <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $contact['customer_id']; ?>" 
               class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
