<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'Add Customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = db_connect();
    
    // Validate required fields
    $company_name = sanitize_input($_POST['company_name']);
    $status = sanitize_input($_POST['status']);
    
    if (empty($company_name) || empty($status)) {
        set_flash_message(['type' => 'danger', 'message' => 'Company name and status are required.']);
    } else {
        // Prepare other fields
        $address = sanitize_input($_POST['address']);
        $province = sanitize_input($_POST['province']);
        $country = sanitize_input($_POST['country']);
        $company_type = sanitize_input($_POST['company_type']);
        $phone = sanitize_input($_POST['phone']);
        $email = sanitize_input($_POST['email']);
        
        // Insert customer
        $stmt = $conn->prepare("INSERT INTO customers (company_name, address, province, country, 
                              company_type, phone, email, status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $company_name, $address, $province, $country, 
                         $company_type, $phone, $email, $status);
        
        if ($stmt->execute()) {
            $customer_id = $conn->insert_id;
            
            // Create default contact
            create_default_contact($customer_id, $company_name);
            
            set_flash_message(['type' => 'success', 'message' => 'Customer added successfully.']);
            header('Location: ' . SITE_URL . '/customers/view.php?id=' . $customer_id);
            exit();
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error adding customer.']);
        }
    }
}

include BASE_PATH . '/includes/header.php';
?>

<div class="card">
    <h1 class="card-header">Add New Customer</h1>
    
    <form method="POST" action="" class="card-body">
        <div class="form-group">
            <label for="company_name">Company Name *</label>
            <input type="text" id="company_name" name="company_name" class="form-control" required
                   value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>">
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-12">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="province">Province</label>
                <input type="text" id="province" name="province" class="form-control"
                       value="<?php echo isset($_POST['province']) ? htmlspecialchars($_POST['province']) : ''; ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" class="form-control"
                       value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="company_type">Company Type</label>
                <input type="text" id="company_type" name="company_type" class="form-control"
                       value="<?php echo isset($_POST['company_type']) ? htmlspecialchars($_POST['company_type']) : ''; ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="">Select Status</option>
                    <?php
                    $statuses = ['Prospect', 'Qualified', 'Not Qualified', 'Active Customer', 
                                'Inactive Customer', 'Closed Won', 'Closed Lost'];
                    foreach ($statuses as $status):
                        $selected = isset($_POST['status']) && $_POST['status'] === $status ? 'selected' : '';
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
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Add Customer</button>
            <a href="<?php echo SITE_URL; ?>/customers/index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
