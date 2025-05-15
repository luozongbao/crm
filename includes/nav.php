<?php
if (!defined('BASE_PATH')) exit('No direct script access allowed');
?>
<nav class="navbar">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>" class="navbar-brand">Personal CRM</a>
        <ul class="nav-menu">
            <li><a href="<?php echo SITE_URL; ?>/dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="<?php echo SITE_URL; ?>/customers/index.php" class="nav-link">Customers</a></li>
            <li><a href="<?php echo SITE_URL; ?>/activities/index.php" class="nav-link">Activities</a></li>
            <li><a href="<?php echo SITE_URL; ?>/followups/index.php" class="nav-link">Follow-ups</a></li>
            <?php if (is_admin()): ?>
                <li><a href="<?php echo SITE_URL; ?>/settings/index.php" class="nav-link">Settings</a></li>
            <?php endif; ?>
            <li><a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>
