<?php
define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'All Customers';

// Get search parameters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = get_pagination_limit();
$offset = ($page - 1) * $limit;

// Build query
$conn = db_connect();
$where_conditions = [];
$params = [];
$types = '';

if ($search) {
    $where_conditions[] = "(company_name LIKE ? OR phone LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= 'ss';
}

if ($status) {
    $where_conditions[] = "status = ?";
    $params[] = &$status;
    $types .= 's';
}

if ($location) {
    $where_conditions[] = "(province LIKE ? OR country LIKE ?)";
    $location_param = "%$location%";
    $params[] = &$location_param;
    $params[] = &$location_param;
    $types .= 'ss';
}

$where_clause = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total records
$count_query = "SELECT COUNT(*) as total FROM customers $where_clause";
$stmt = $conn->prepare($count_query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];

// Get customers
$query = "SELECT id, company_name, status, last_contact, phone FROM customers 
          $where_clause ORDER BY company_name 
          LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = &$limit;
$params[] = &$offset;

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$customers = $stmt->get_result();

// Generate pagination
$pagination = generate_pagination($total_records, $page, $limit);

include BASE_PATH . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>All Customers</h1>
    <a href="<?php echo SITE_URL; ?>/customers/add.php" class="btn btn-primary">Add Customer</a>
</div>

<!-- Search Form -->
<form method="GET" class="card mb-3">
    <div class="form-group">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" class="form-control" 
               value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search by company name or phone">
    </div>
    
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="status">Status</label>
            <select id="status" name="status" class="form-control">
                <option value="">All Status</option>
                <?php
                $statuses = ['Prospect', 'Qualified', 'Not Qualified', 'Active Customer', 
                            'Inactive Customer', 'Closed Won', 'Closed Lost'];
                foreach ($statuses as $s):
                ?>
                    <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group col-md-6">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" class="form-control" 
                   value="<?php echo htmlspecialchars($location); ?>" 
                   placeholder="Search by province or country">
        </div>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="<?php echo SITE_URL; ?>/customers/index.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<!-- Customers List -->
<?php if ($customers->num_rows > 0): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Status</th>
                <th>Last Contact</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($customer = $customers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['company_name']); ?></td>
                    <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $customer['status'])); ?>">
                        <?php echo $customer['status']; ?>
                    </span></td>
                    <td><?php echo $customer['last_contact'] ? format_date($customer['last_contact']) : 'Never'; ?></td>
                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $customer['id']; ?>" 
                           class="btn btn-sm btn-info">View</a>
                        <a href="<?php echo SITE_URL; ?>/customers/edit.php?id=<?php echo $customer['id']; ?>" 
                           class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if (!empty($pagination)): ?>
        <div class="pagination">
            <?php if (isset($pagination['prev'])): ?>
                <a href="?page=<?php echo $pagination['prev']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&location=<?php echo urlencode($location); ?>" 
                   class="btn btn-secondary">&laquo; Previous</a>
            <?php endif; ?>
            
            <span class="pagination-info">
                Page <?php echo $pagination['current']; ?> of <?php echo $pagination['total']; ?>
            </span>
            
            <?php if (isset($pagination['next'])): ?>
                <a href="?page=<?php echo $pagination['next']; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&location=<?php echo urlencode($location); ?>" 
                   class="btn btn-secondary">Next &raquo;</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <div class="alert alert-info">No customers found.</div>
<?php endif; ?>

<?php include BASE_PATH . '/includes/footer.php'; ?>
