<?php
session_start();
define('BASE_PATH', dirname(dirname(__FILE__)));

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// If config.php exists, installation is already done
if (file_exists(BASE_PATH . '/includes/config.php') && $step !== 2) {
    header('Location: /index.php');
    exit();
}

// Process database configuration
// Function to get site URL from current URL
function getSiteUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    // Remove everything from /admin/install.php onwards
    $baseUri = preg_replace('~/admin/install\.php.*~', '', $uri);
    return $protocol . $host . $baseUri;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {
    $db_host = trim($_POST['db_host']);
    $db_name = trim($_POST['db_name']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_password']);
    $site_url = getSiteUrl();

    // Validate inputs
    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $error = "All fields are required.";
    } else {
        // Test database connection
        try {
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create config file
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
            $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
            $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
            $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
            $config_content .= "define('SITE_URL', '" . addslashes($site_url) . "');\n";
            $config_content .= "session_start();\n";
            $config_content .= "\$conn = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);\n";
            $config_content .= "\$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";

            // Write config file
            if (file_put_contents(BASE_PATH . '/includes/config.php', $config_content)) {
                try {
                    // Import database structure
                    $sql = file_get_contents(BASE_PATH . '/database/database.sql');
                    if ($sql === false) {
                        $error = "Could not read database.sql file. Please check if the file exists and is readable.";
                    } else {
                        $conn->exec($sql);
                        header('Location: /admin/install.php?step=2');
                        exit();
                    }
                } catch (PDOException $e) {
                    // Delete the config file if database import fails
                    unlink(BASE_PATH . '/includes/config.php');
                    $error = "Database import failed: " . $e->getMessage();
                }
            } else {
                $error = "Could not write config file. Please check permissions.";
            }
        } catch(PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    }
}

// Process admin account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 2) {
    require_once BASE_PATH . '/includes/config.php';
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($password) || empty($email)) {
        $error = "All fields are required.";
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, 'Admin')");
            if ($stmt->execute([$username, $password_hash, $email])) {
                $success = "Installation complete! <a href='/login.php'>Click here to login</a>";
            } else {
                $error = "Could not create admin account.";
            }
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>CRM Installation</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container install">
        <h1>CRM Installation</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <h2>Step 1: Database Configuration</h2>
            <form method="post" action="/admin/install.php?step=1">
                <div class="form-group">
                    <label>Database Host:</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Database Name:</label>
                    <input type="text" name="db_name" required>
                </div>
                <div class="form-group">
                    <label>Database User:</label>
                    <input type="text" name="db_user" required>
                </div>
                <div class="form-group">
                    <label>Database Password:</label>
                    <input type="password" name="db_password" required>
                </div>
                <button type="submit">Continue</button>
            </form>
        <?php elseif ($step === 2): ?>
            <h2>Step 2: Create Admin Account</h2>
            <form method="post" action="/admin/install.php?step=2">
                <div class="form-group">
                    <label>Username:</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Complete Installation</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
