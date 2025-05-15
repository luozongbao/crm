<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

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

// Get contacts
$stmt = $conn->prepare("SELECT * FROM contacts WHERE customer_id = ? ORDER BY is_primary DESC, name");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$contacts = $stmt->get_result();

// Get action history
$stmt = $conn->prepare("SELECT * FROM actions WHERE customer_id = ? ORDER BY action_time DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$actions = $stmt->get_result();

$page_title = htmlspecialchars($customer['company_name']);
include BASE_PATH . '/includes/header.php';
?>

<!-- Customer Card -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h1 class="mb-0"><?php echo htmlspecialchars($customer['company_name']); ?></h1>
        <a href="<?php echo SITE_URL; ?>/customers/edit.php?id=<?php echo $customer_id; ?>" 
           class="btn btn-primary">Edit Customer</a>
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Status:</strong> 
                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $customer['status'])); ?>">
                        <?php echo $customer['status']; ?>
                    </span>
                </p>
                <p><strong>Company Type:</strong> <?php echo htmlspecialchars($customer['company_type']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($customer['address'])); ?></p>
                <p><strong>Province:</strong> <?php echo htmlspecialchars($customer['province']); ?></p>
                <p><strong>Country:</strong> <?php echo htmlspecialchars($customer['country']); ?></p>
                <p><strong>Last Contact:</strong> 
                    <?php echo $customer['last_contact'] ? format_date($customer['last_contact']) : 'Never'; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Contact Persons -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Contact Persons</h2>
        <a href="<?php echo SITE_URL; ?>/contacts/add.php?customer_id=<?php echo $customer_id; ?>" 
           class="btn btn-primary">Add Contact</a>
    </div>
    
    <div class="card-body">
        <?php if ($contacts->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Role</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($contact = $contacts->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo htmlspecialchars($contact['name']); ?>
                                <?php if ($contact['is_primary']): ?>
                                    <span class="badge badge-primary">Primary</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($contact['title']); ?></td>
                            <td><?php echo htmlspecialchars($contact['role']); ?></td>
                            <td><?php echo htmlspecialchars($contact['contact_number']); ?></td>
                            <td><?php echo htmlspecialchars($contact['email']); ?></td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/contacts/edit.php?id=<?php echo $contact['id']; ?>" 
                                   class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No contacts found.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Action History -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Action History</h2>
        <a href="<?php echo SITE_URL; ?>/actions/add.php?customer_id=<?php echo $customer_id; ?>" 
           class="btn btn-primary">Add Action</a>
    </div>
    
    <div class="card-body">
        <?php if ($actions->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Action</th>
                        <th>Response</th>
                        <th>Next Step</th>
                        <th>Follow-up</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($action = $actions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo format_date($action['action_time']); ?></td>
                            <td><?php echo htmlspecialchars($action['action']); ?></td>
                            <td><?php echo htmlspecialchars($action['response']); ?></td>
                            <td><?php echo htmlspecialchars($action['next_step']); ?></td>
                            <td>
                                <?php echo $action['followup_datetime'] ? format_date($action['followup_datetime']) : ''; ?>
                            </td>
                            <td>
                                <a href="<?php echo SITE_URL; ?>/actions/edit.php?id=<?php echo $action['id']; ?>" 
                                   class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No action history found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
