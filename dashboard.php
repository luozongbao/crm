<?php
define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

// Check if user is logged in
check_login();

$page_title = 'Dashboard';

// Handle export requests
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 week'));
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $type . '_' . date('Y-m-d') . '.csv"');
    
    $conn = db_connect();
    $fp = fopen('php://output', 'w');
    
    if ($type === 'activities') {
        // Export activities
        fputcsv($fp, ['Date', 'Company', 'Action', 'Response', 'Next Step']);
        
        $stmt = $conn->prepare("SELECT a.action_time, c.company_name, a.action, a.response, a.next_step 
                              FROM actions a 
                              JOIN customers c ON a.customer_id = c.id 
                              WHERE DATE(a.action_time) BETWEEN ? AND ? 
                              ORDER BY a.action_time DESC");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($fp, [
                format_date($row['action_time']),
                $row['company_name'],
                $row['action'],
                $row['response'],
                $row['next_step']
            ]);
        }
    } else if ($type === 'followups') {
        // Export followups
        fputcsv($fp, ['Follow-up Date', 'Company', 'Action', 'Next Step']);
        
        $stmt = $conn->prepare("SELECT a.followup_datetime, c.company_name, a.action, a.next_step 
                              FROM actions a 
                              JOIN customers c ON a.customer_id = c.id 
                              WHERE DATE(a.followup_datetime) BETWEEN ? AND ? 
                              ORDER BY a.followup_datetime");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($fp, [
                format_date($row['followup_datetime']),
                $row['company_name'],
                $row['action'],
                $row['next_step']
            ]);
        }
    }
    
    fclose($fp);
    exit();
}

// Get customer statistics
$customer_stats = get_customer_status_counts();
$total_customers = array_sum($customer_stats);

// Get recent activities
$recent_activities = get_recent_activities(10);

// Get upcoming followups
$upcoming_followups = get_upcoming_followups(10);

// Include header
include BASE_PATH . '/includes/header.php';
?>

<h1 class="mb-3">Dashboard</h1>

<!-- Customer Statistics -->
<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Customers</h3>
        <p class="stat-number"><?php echo $total_customers; ?></p>
    </div>
    <?php foreach ($customer_stats as $status => $count): ?>
        <div class="stat-card">
            <h3><?php echo $status; ?></h3>
            <p class="stat-number"><?php echo $count; ?></p>
            <p class="stat-percentage"><?php echo $total_customers > 0 ? round(($count / $total_customers) * 100, 1) : 0; ?>%</p>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <!-- Export Forms -->
    <div class="col-md-12">
        <div class="row mb-4">
            <!-- Export Activities -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Export Activities</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="form-inline">
                            <input type="hidden" name="export" value="activities">
                            <div class="form-group mr-2">
                                <label for="activities_start_date" class="mr-2">From:</label>
                                <input type="date" id="activities_start_date" name="start_date" 
                                       class="form-control" value="<?php echo date('Y-m-d', strtotime('-1 week')); ?>">
                            </div>
                            <div class="form-group mr-2">
                                <label for="activities_end_date" class="mr-2">To:</label>
                                <input type="date" id="activities_end_date" name="end_date" 
                                       class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Export CSV</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Export Followups -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Export Followups</h3>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="form-inline">
                            <input type="hidden" name="export" value="followups">
                            <div class="form-group mr-2">
                                <label for="followups_start_date" class="mr-2">From:</label>
                                <input type="date" id="followups_start_date" name="start_date" 
                                       class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group mr-2">
                                <label for="followups_end_date" class="mr-2">To:</label>
                                <input type="date" id="followups_end_date" name="end_date" 
                                       class="form-control" value="<?php echo date('Y-m-d', strtotime('+1 week')); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Export CSV</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="col-md-6">
        <div class="card mb-3">
            <div class="card-header">
                <h2 class="mb-0">Recent Activities</h2>
            </div>
            <div class="card-body">
                <div class="actions-list">
            <?php if ($recent_activities->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Action</th>
                            <th>Next Step</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo format_date($activity['action_time']); ?></td>
                                <td><?php echo htmlspecialchars($activity['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($activity['action']); ?></td>
                                <td><?php echo htmlspecialchars($activity['next_step']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent activities</p>
            <?php endif; ?>
            <div class="text-right">
                <a href="<?php echo SITE_URL; ?>/activities/index.php" class="btn btn-primary">View All Activities</a>
            </div>
        </div>
    </div>

    <!-- Upcoming Follow-ups -->
    <div class="card">
        <h2>Upcoming Follow-ups</h2>
        <div class="followups-list">
            <?php if ($upcoming_followups->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Action</th>
                            <th>Next Step</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($followup = $upcoming_followups->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo format_date($followup['followup_datetime']); ?></td>
                                <td><?php echo htmlspecialchars($followup['company_name']); ?></td>
                                <td><?php echo htmlspecialchars($followup['action']); ?></td>
                                <td><?php echo htmlspecialchars($followup['next_step']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No upcoming follow-ups</p>
            <?php endif; ?>
            <div class="text-right">
                <a href="<?php echo SITE_URL; ?>/followups/index.php" class="btn btn-primary">View All Follow-ups</a>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
