<?php
/**
 * MD-WIKI Admin Profile
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Require login
requireLogin();

// Get current user
$currentUser = getCurrentUser();

// Initialize variables
$message = '';
$messageType = '';
$profileUpdated = false;
$passwordUpdated = false;

// Process profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        // Get form data
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        
        // Validate form data
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not a valid email address.';
        }
        
        // Check if email is already in use by another user
        if (!empty($email) && $email !== $currentUser['email']) {
            $existingUser = dbQuerySingle("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $currentUser['id']]);
            if ($existingUser) {
                $errors[] = 'Email is already in use by another user.';
            }
        }
        
        // If no errors, update profile
        if (empty($errors)) {
            // Update user in database
            dbExecute(
                "UPDATE users SET name = ?, email = ? WHERE id = ?",
                [$name, $email, $currentUser['id']]
            );
            
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Set success message
            $message = 'Profile updated successfully.';
            $messageType = 'success';
            $profileUpdated = true;
            
            // Refresh current user data
            $currentUser = getCurrentUser();
        } else {
            $message = 'Please fix the following errors: ' . implode(' ', $errors);
            $messageType = 'danger';
        }
    } else if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        // Get form data
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validate form data
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required.';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required.';
        } else if (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New password and confirm password do not match.';
        }
        
        // If no errors, change password
        if (empty($errors)) {
            // Change password
            if (changePassword($currentUser['id'], $currentPassword, $newPassword)) {
                // Set success message
                $message = 'Password changed successfully.';
                $messageType = 'success';
                $passwordUpdated = true;
            } else {
                $message = 'Current password is incorrect.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Please fix the following errors: ' . implode(' ', $errors);
            $messageType = 'danger';
        }
    }
}

// Page title
$pageTitle = 'Profile';

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <h1 class="mb-4">Profile</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-person-circle me-2"></i>
                                Update Profile
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?= h($currentUser['name']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?= h($currentUser['email']) ?>" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>
                                        Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-lock me-2"></i>
                                Change Password
                            </div>
                            <div class="card-body">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <div class="form-text">Password must be at least 8 characters long.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-lock me-2"></i>
                                        Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include __DIR__ . '/includes/footer.php';
?>
