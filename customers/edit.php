<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Edit Customer';

// Get customer ID from URL
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$customer_id) {
    set_flash_message(['type' => 'danger', 'message' => 'Invalid customer ID.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

$conn = db_connect();

// Get customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    set_flash_message(['type' => 'danger', 'message' => 'Customer not found.']);
    header('Location: ' . SITE_URL . '/customers/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        // Delete customer
        $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->bind_param("i", $customer_id);
        
        if ($stmt->execute()) {
            set_flash_message(['type' => 'success', 'message' => 'Customer deleted successfully.']);
            header('Location: ' . SITE_URL . '/customers/index.php');
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error deleting customer.']);
        }
    } else {
        // Update customer
        $company_name = sanitize_input($_POST['company_name']);
        $status = sanitize_input($_POST['status']);
        
        if (empty($company_name) || empty($status)) {
            set_flash_message(['type' => 'danger', 'message' => 'Company name and status are required.']);
        } else {
            $address = sanitize_input($_POST['address']);
            $province = sanitize_input($_POST['province']);
            $country = sanitize_input($_POST['country']);
            $company_type = sanitize_input($_POST['company_type']);
            $phone = sanitize_input($_POST['phone']);
            $email = sanitize_input($_POST['email']);
            
            $stmt = $conn->prepare("UPDATE customers SET 
                                  company_name = ?, address = ?, province = ?, 
                                  country = ?, company_type = ?, phone = ?, 
                                  email = ?, status = ?
                                  WHERE id = ?");
            $stmt->bind_param("ssssssssi", 
                            $company_name, $address, $province, 
                            $country, $company_type, $phone, 
                            $email, $status, $customer_id);
            
            if ($stmt->execute()) {
                set_flash_message(['type' => 'success', 'message' => 'Customer updated successfully.']);
                header('Location: ' . SITE_URL . '/customers/view.php?id=' . $customer_id);
                exit();
            } else {
                set_flash_message(['type' => 'danger', 'message' => 'Error updating customer.']);
            }
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <h1 class="card-header">Edit Customer</h1>
    
    <form method="POST" action="" class="card-body">
        <div class="form-group">
            <label for="company_name">Company Name *</label>
            <input type="text" id="company_name" name="company_name" class="form-control" required
                   value="<?php echo htmlspecialchars($customer['company_name']); ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($customer['address']); ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" class="form-control"
                       value="<?php echo htmlspecialchars($customer['province']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" class="form-control"
                       value="<?php echo htmlspecialchars($customer['country']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="company_type">Company Type</label>
                <input type="text" id="company_type" name="company_type" class="form-control"
                       value="<?php echo htmlspecialchars($customer['company_type']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <?php
                    $statuses = ['Prospect', 'Qualified', 'Not Qualified', 'Active Customer', 
                                'Inactive Customer', 'Closed Won', 'Closed Lost'];
                    foreach ($statuses as $status):
                        $selected = $customer['status'] === $status ? 'selected' : '';
                    ?>
                        <option value="<?php echo $status; ?>" <?php echo $selected; ?>>
                            <?php echo $status; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" class="form-control"
                       value="<?php echo htmlspecialchars($customer['phone']); ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($customer['email']); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <button type="submit" name="delete" class="btn btn-danger" 
                    onclick="return confirm('Are you sure you want to delete this customer?');">
                Delete Customer
            </button>
            <a href="<?php echo SITE_URL; ?>/customers/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
