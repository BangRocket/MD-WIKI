<?php
/**
 * MD-WIKI Markdown Preview AJAX Handler
 */

// Include required files
require_once __DIR__ . '/../../config/config.php';

// Configure error reporting based on debug mode
if (DEBUG_MODE) {
    // Show all errors in debug mode
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    // Hide errors in production
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/markdown.php';

// Require login
requireLogin();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// Get content from POST
$content = isset($_POST['content']) ? $_POST['content'] : '';

// Parse markdown to HTML
$html = parseMarkdown($content);

// Output HTML
echo $html;
