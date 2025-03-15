<?php
/**
 * Authentication System
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Set secure session parameters
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in
 * 
 * @param string $redirect URL to redirect to if not logged in
 * @return void
 */
function requireLogin($redirect = '/admin/index.php') {
    if (!isLoggedIn()) {
        redirect($redirect);
    }
}

/**
 * Attempt to log in a user
 * 
 * @param string $email User email
 * @param string $password User password
 * @return bool True if login successful
 */
function login($email, $password) {
    // Get user by email
    $user = dbQuerySingle("SELECT * FROM users WHERE email = ?", [$email]);
    
    // Check if user exists and password is correct
    if ($user && password_verify($password, $user['password_hash'])) {
        // Start session
        startSession();
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        
        // Update last login time
        dbExecute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        return true;
    }
    
    return false;
}

/**
 * Log out the current user
 * 
 * @return void
 */
function logout() {
    startSession();
    
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Create a new user
 * 
 * @param string $email User email
 * @param string $password User password
 * @param string $name User name
 * @return int|bool User ID if successful, false if email already exists
 */
function createUser($email, $password, $name) {
    // Check if email already exists
    $existingUser = dbQuerySingle("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existingUser) {
        return false;
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    dbExecute(
        "INSERT INTO users (email, password_hash, name, created_at) 
         VALUES (?, ?, ?, NOW())",
        [$email, $passwordHash, $name]
    );
    
    return dbLastInsertId();
}

/**
 * Change user password
 * 
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return bool True if password changed successfully
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Get user
    $user = dbQuerySingle("SELECT * FROM users WHERE id = ?", [$userId]);
    
    // Check if user exists and current password is correct
    if ($user && password_verify($currentPassword, $user['password_hash'])) {
        // Hash new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        dbExecute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $userId]
        );
        
        return true;
    }
    
    return false;
}

/**
 * Get current user data
 * 
 * @return array|null User data or null if not logged in
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return dbQuerySingle("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
}
