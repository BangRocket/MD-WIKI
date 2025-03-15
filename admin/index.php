<?php
/**
 * MD-WIKI Admin Login
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize variables
$error = '';
$email = '';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validate form data
    if (empty($email)) {
        $error = 'Email is required.';
    } else if (empty($password)) {
        $error = 'Password is required.';
    } else {
        // Attempt to log in
        if (login($email, $password)) {
            // Redirect to dashboard
            redirect('dashboard.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Page title
$pageTitle = 'Admin Login';

// Include header (without navigation)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center py-3">
                        <h1 class="h4 mb-0"><?= APP_NAME ?> Admin</h1>
                    </div>
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Login</h2>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?= h($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= h($email) ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Login
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center py-3">
                        <a href="<?= APP_URL ?>/index.php" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Back to Wiki
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
