<?php
/**
 * MD-WIKI Admin Logout
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Log out the user
logout();

// Redirect to login page
redirect('index.php');
