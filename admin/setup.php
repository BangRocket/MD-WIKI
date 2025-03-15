<?php
/**
 * MD-WIKI Setup Page
 * 
 * This page is shown when the wiki is first installed.
 * It creates the database tables and initial admin user.
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize variables
$message = '';
$messageType = '';
$setupComplete = false;

// Check if database tables exist
try {
    $pdo = getDbConnection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'pages'");
    $tablesExist = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    $tablesExist = false;
    $message = 'Error connecting to database: ' . $e->getMessage();
    $messageType = 'danger';
}

// Process setup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'setup') {
    try {
        // Get form data
        $adminEmail = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
        $adminPassword = isset($_POST['admin_password']) ? $_POST['admin_password'] : '';
        $adminName = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '';
        $vaultPath = isset($_POST['vault_path']) ? trim($_POST['vault_path']) : '';
        
        // Validate form data
        $errors = [];
        
        if (empty($adminEmail)) {
            $errors[] = 'Admin email is required.';
        } else if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Admin email is not a valid email address.';
        }
        
        if (empty($adminPassword)) {
            $errors[] = 'Admin password is required.';
        } else if (strlen($adminPassword) < 8) {
            $errors[] = 'Admin password must be at least 8 characters long.';
        }
        
        if (empty($adminName)) {
            $errors[] = 'Admin name is required.';
        }
        
        if (empty($vaultPath)) {
            $errors[] = 'Vault path is required.';
        } else if (!is_dir($vaultPath)) {
            $errors[] = 'Vault path is not a valid directory.';
        }
        
        // If no errors, set up the wiki
        if (empty($errors)) {
            // Get database connection
            $pdo = getDbConnection();
            
            // Read SQL file
            $sqlFile = __DIR__ . '/../import/setup_database.sql';
            $sql = file_get_contents($sqlFile);
            
            // Execute SQL
            $pdo->exec($sql);
            
            // Update admin user
            $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $pdo->exec("UPDATE users SET email = '$adminEmail', password_hash = '$passwordHash', name = '$adminName' WHERE id = 1");
            
            // Update vault path in config
            $configFile = __DIR__ . '/../config/config.php';
            $configContent = file_get_contents($configFile);
            $configContent = preg_replace(
                '/define\(\'VAULT_PATH\', \'.*?\'\);/',
                'define(\'VAULT_PATH\', \'' . addslashes($vaultPath) . '\');',
                $configContent
            );
            file_put_contents($configFile, $configContent);
            
            // Set success message
            $message = 'Setup completed successfully. You can now <a href="index.php">log in</a> with your admin account.';
            $messageType = 'success';
            $setupComplete = true;
        } else {
            $message = 'Please fix the following errors: ' . implode(' ', $errors);
            $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = 'Error during setup: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Page title
$pageTitle = 'Setup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h1 class="h4 mb-0"><?= APP_NAME ?> Setup</h1>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($tablesExist && !$setupComplete): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                The database tables already exist. If you continue, the existing data will be overwritten.
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?= $messageType ?>">
                                <?= $message ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$setupComplete): ?>
                            <p class="mb-4">
                                Welcome to the <?= APP_NAME ?> setup page. This will help you set up your wiki by creating the necessary database tables and admin user.
                            </p>
                            
                            <form method="post" action="">
                                <input type="hidden" name="action" value="setup">
                                
                                <h5 class="mb-3">Admin Account</h5>
                                
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= isset($adminEmail) ? h($adminEmail) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_password" class="form-label">Admin Password</label>
                                    <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                                    <div class="form-text">Password must be at least 8 characters long.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="admin_name" class="form-label">Admin Name</label>
                                    <input type="text" class="form-control" id="admin_name" name="admin_name" value="<?= isset($adminName) ? h($adminName) : '' ?>" required>
                                </div>
                                
                                <h5 class="mb-3 mt-4">Obsidian Vault</h5>
                                
                                <div class="mb-4">
                                    <label for="vault_path" class="form-label">Obsidian Vault Path</label>
                                    <input type="text" class="form-control" id="vault_path" name="vault_path" value="<?= isset($vaultPath) ? h($vaultPath) : '' ?>" required>
                                    <div class="form-text">The absolute path to your Obsidian vault directory.</div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Complete Setup
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                                <h4 class="mt-3">Setup Complete!</h4>
                                <p class="mb-4">
                                    Your wiki has been set up successfully. You can now log in with your admin account.
                                </p>
                                <a href="index.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Go to Login
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
