<?php
/**
 * MD-WIKI Configuration File
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u890312866_wiki');
define('DB_USER', 'u890312866_wiki');
define('DB_PASS', 'CJR0cmz-efu-wfk0mgp');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'MD-WIKI');
define('APP_URL', 'http://jorsh.net/wikidewakidiewack');
define('ADMIN_EMAIL', 'jorsh@jorsh.net');

// Obsidian vault configuration
define('VAULT_PATH', '/assets/vault/spygame-vault'); // Update this with the actual path to your Obsidian vault

// Session configuration
define('SESSION_NAME', 'md_wiki_session');
define('SESSION_LIFETIME', 86400); // 24 hours

// Search configuration
define('SEARCH_RESULTS_PER_PAGE', 10);

// Debug mode (set to false in production)
define('DEBUG_MODE', true);
