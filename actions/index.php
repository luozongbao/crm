<?php
define('BASE_PATH', dirname(dirname(__FILE__)));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

check_login();

$page_title = 'All Activities';

// Default to last month
$default_start = date('Y-m-d', strtotime('-1 month'));
$default_end = date('Y-m-d');

// Get filter parameters
$start_date = isset($_GET['start_date']) ? sanitize_input($_GET['start_date']) : $default_start;
$end_date = isset($_GET['end_date']) ? sanitize_input($_GET['end_date']) : $default_end;
$company = isset($_GET['company']) ? sanitize_input($_GET['company']) : '';
$sort_order = isset($_GET['sort']) ? (strtolower($_GET['sort']) === 'desc' ? 'DESC' : 'ASC') : 'DESC';

// Build query
$conn = db_connect();
$where_conditions = [];
$params = [];
$types = '';

$where_conditions[] = "DATE(a.action_time) BETWEEN ? AND ?";
$params[] = $start_date;
$params[] = $end_date;
$types .= 'ss';

if ($company) {
    $where_conditions[] = "c.company_name LIKE ?";
    $company_param = "%$company%";
    $params[] = $company_param;
    $types .= 's';
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// Get activities
$query = "SELECT a.*, c.company_name, c.id as customer_id 
          FROM actions a 
          JOIN customers c ON a.customer_id = c.id 
          $where_clause 
          ORDER BY a.action_time $sort_order";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$activities = $stmt->get_result();

include BASE_PATH . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>All Activities</h1>
    <a href="<?php echo SITE_URL; ?>/actions/add.php" class="btn btn-primary">Add Activity</a>
</div>

<!-- Filter Form -->
<form method="GET" class="card mb-3">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="start_date">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" 
                       value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            
            <div class="form-group col-md-3">
                <label for="end_date">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" 
                       value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            
            <div class="form-group col-md-4">
                <label for="company">Company</label>
                <input type="text" id="company" name="company" class="form-control" 
                       value="<?php echo htmlspecialchars($company); ?>" 
                       placeholder="Filter by company name">
            </div>
            
            <div class="form-group col-md-2">
                <label for="sort">Sort Order</label>
                <select id="sort" name="sort" class="form-control">
                    <option value="desc" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>
                        Latest First
                    </option>
                    <option value="asc" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>
                        Oldest First
                    </option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <a href="<?php echo SITE_URL; ?>/actions/index.php" class="btn btn-secondary">Reset</a>
    </div>
</form>

<?php if ($activities->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Company</th>
                    <th>Action</th>
                    <th>Response</th>
                    <th>Next Step</th>
                    <th>Follow-up Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = $activities->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo format_date($activity['action_time']); ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/customers/view.php?id=<?php echo $activity['customer_id']; ?>">
                                <?php echo htmlspecialchars($activity['company_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($activity['action']); ?></td>
                        <td><?php echo htmlspecialchars($activity['response']); ?></td>
                        <td><?php echo htmlspecialchars($activity['next_step']); ?></td>
                        <td><?php echo $activity['followup_datetime'] ? format_date($activity['followup_datetime']) : '-'; ?></td>
                        <td>
                            <a href="<?php echo SITE_URL; ?>/actions/edit.php?id=<?php echo $activity['id']; ?>" 
                               class="btn btn-sm btn-primary">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">No activities found for the selected criteria.</div>
<?php endif; ?>

<?php include BASE_PATH . '/includes/footer.php'; ?>
