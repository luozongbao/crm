<?php
if (!defined('BASE_PATH')) exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Personal CRM</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<?php include BASE_PATH . '/includes/nav.php'; ?>
<div class="container">
    <?php
    $flash_message = get_flash_message();
    if ($flash_message): ?>
        <div class="alert alert-<?php echo $flash_message['type']; ?>">
            <?php echo $flash_message['message']; ?>
        </div>
    <?php endif; ?>
