<?php
define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

// Check if user is admin
if (!is_admin()) {
    set_flash_message(['type' => 'danger', 'message' => 'Access denied. Admin privileges required.']);
    header('Location: ' . SITE_URL . '/dashboard.php');
    exit();
}

$page_title = 'Settings';
$conn = db_connect();

// Handle pagination limit update
if (isset($_POST['update_pagination'])) {
    $limit = (int)$_POST['pagination_limit'];
    if ($limit > 0) {
        $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'pagination_limit'");
        $stmt->bind_param("s", $limit);
        
        if ($stmt->execute()) {
            set_flash_message(['type' => 'success', 'message' => 'Pagination limit updated successfully.']);
        } else {
            set_flash_message(['type' => 'danger', 'message' => 'Error updating pagination limit.']);
        }
    }
}

// Handle user management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = sanitize_input($_POST['username']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];
        $role = sanitize_input($_POST['role']);
        
        if (empty($username) || empty($email) || empty($password)) {
            set_flash_message(['type' => 'danger', 'message' => 'All fields are required.']);
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
            
            if ($stmt->execute()) {
                set_flash_message(['type' => 'success', 'message' => 'User added successfully.']);
            } else {
                set_flash_message(['type' => 'danger', 'message' => 'Error adding user.']);
            }
        }
    } else if (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        
        // Prevent deleting self
        if ($user_id === $_SESSION['user_id']) {
            set_flash_message(['type' => 'danger', 'message' => 'Cannot delete your own account.']);
        } else {
            // Check if this is the last admin
            $stmt = $conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'Admin'");
            $stmt->execute();
            $result = $stmt->get_result();
            $admin_count = $result->fetch_assoc()['admin_count'];
            
            // Get the user's role
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_role = $result->fetch_assoc()['role'];
            
            if ($admin_count <= 1 && $user_role === 'Admin') {
                set_flash_message(['type' => 'danger', 'message' => 'Cannot delete the last admin user.']);
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                
                if ($stmt->execute()) {
                    set_flash_message(['type' => 'success', 'message' => 'User deleted successfully.']);
                } else {
                    set_flash_message(['type' => 'danger', 'message' => 'Error deleting user.']);
                }
            }
        }
    }
}

// Get current pagination limit
$current_limit = get_pagination_limit();

// Get all users
$users = $conn->query("SELECT * FROM users ORDER BY username");

include BASE_PATH . '/includes/header.php';
?>

<h1 class="mb-4">Settings</h1>

<div class="row">
    <!-- Pagination Settings -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Pagination Settings</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="pagination_limit">Items per page</label>
                        <input type="number" id="pagination_limit" name="pagination_limit" 
                               class="form-control" value="<?php echo $current_limit; ?>" 
                               min="1" required>
                    </div>
                    <button type="submit" name="update_pagination" class="btn btn-primary">
                        Update Pagination
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- User Management -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Add New User</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- User List -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">User List</h2>
            </div>
            <div class="card-body">
                <?php if ($users->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                                        <td><?php echo format_date($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" 
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this user?');">
                                                        Delete
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
