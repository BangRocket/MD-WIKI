<?php
/**
 * MD-WIKI Configuration File
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'md_wiki');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'MD-WIKI');
define('APP_URL', 'http://localhost/md-wiki');
define('ADMIN_EMAIL', 'admin@example.com');

// Obsidian vault configuration
define('VAULT_PATH', '/path/to/obsidian/vault'); // Update this with the actual path to your Obsidian vault

// Session configuration
define('SESSION_NAME', 'md_wiki_session');
define('SESSION_LIFETIME', 86400); // 24 hours

// Search configuration
define('SEARCH_RESULTS_PER_PAGE', 10);

// Debug mode (set to false in production)
define('DEBUG_MODE', true);
