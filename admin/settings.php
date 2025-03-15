<?php
/**
 * MD-WIKI Admin Settings
 */

// Include required files
require_once __DIR__ . '/../config/config.php';

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
$configFile = __DIR__ . '/../config/config.php';
$configContent = file_get_contents($configFile);

// Process settings form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    // Get form data
    $appName = isset($_POST['app_name']) ? trim($_POST['app_name']) : '';
    $appUrl = isset($_POST['app_url']) ? trim($_POST['app_url']) : '';
    $vaultPath = isset($_POST['vault_path']) ? trim($_POST['vault_path']) : '';
    $adminEmail = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
    $debugMode = isset($_POST['debug_mode']) ? (bool)$_POST['debug_mode'] : false;
    
    // Validate form data
    $errors = [];
    
    if (empty($appName)) {
        $errors[] = 'Application name is required.';
    }
    
    if (empty($appUrl)) {
        $errors[] = 'Application URL is required.';
    }
    
    if (empty($vaultPath)) {
        $errors[] = 'Vault path is required.';
    } else {
        // Get the website root directory (parent of admin directory)
        $websiteRoot = dirname(__DIR__);
        
        // Handle relative paths by converting to absolute paths
        if (substr($vaultPath, 0, 2) === './') {
            // For paths starting with ./
            $absolutePath = $websiteRoot . substr($vaultPath, 1);
        } else if (substr($vaultPath, 0, 1) !== '/' && substr($vaultPath, 1, 1) !== ':') {
            // For other relative paths without leading slash or drive letter (Windows)
            $absolutePath = $websiteRoot . '/' . $vaultPath;
        } else {
            // Already an absolute path
            $absolutePath = $vaultPath;
        }
        
        // Check if directory exists
        if (!is_dir($absolutePath)) {
            $errors[] = 'Vault path is not a valid directory. Resolved path: ' . $absolutePath;
            
            // Add debug info if in debug mode
            if (DEBUG_MODE) {
                $errors[] = 'Debug info: Website root = ' . $websiteRoot . 
                            ', Original path = ' . $vaultPath . 
                            ', Current script path = ' . __DIR__;
            }
        } else {
            // Use the absolute path for storage
            $vaultPath = $absolutePath;
        }
    }
    
    if (empty($adminEmail)) {
        $errors[] = 'Admin email is required.';
    } else if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Admin email is not a valid email address.';
    }
    
    // If no errors, update config file
    if (empty($errors)) {
        // Update config values
        $configContent = preg_replace(
            '/define\(\'APP_NAME\', \'.*?\'\);/',
            'define(\'APP_NAME\', \'' . addslashes($appName) . '\');',
            $configContent
        );
        
        $configContent = preg_replace(
            '/define\(\'APP_URL\', \'.*?\'\);/',
            'define(\'APP_URL\', \'' . addslashes($appUrl) . '\');',
            $configContent
        );
        
        $configContent = preg_replace(
            '/define\(\'VAULT_PATH\', \'.*?\'\);/',
            'define(\'VAULT_PATH\', \'' . addslashes($vaultPath) . '\');',
            $configContent
        );
        
        $configContent = preg_replace(
            '/define\(\'ADMIN_EMAIL\', \'.*?\'\);/',
            'define(\'ADMIN_EMAIL\', \'' . addslashes($adminEmail) . '\');',
            $configContent
        );
        
        $configContent = preg_replace(
            '/define\(\'DEBUG_MODE\', (?:true|false)\);/',
            'define(\'DEBUG_MODE\', ' . ($debugMode ? 'true' : 'false') . ');',
            $configContent
        );
        
        // Write updated config to file
        if (file_put_contents($configFile, $configContent)) {
            $message = 'Settings saved successfully.';
            $messageType = 'success';
        } else {
            $message = 'Error saving settings. Check file permissions.';
            $messageType = 'danger';
        }
    } else {
        $message = 'Please fix the following errors: ' . implode(' ', $errors);
        $messageType = 'danger';
    }
}

// Extract current settings from config file
preg_match('/define\(\'APP_NAME\', \'(.*?)\'\);/', $configContent, $appNameMatch);
preg_match('/define\(\'APP_URL\', \'(.*?)\'\);/', $configContent, $appUrlMatch);
preg_match('/define\(\'VAULT_PATH\', \'(.*?)\'\);/', $configContent, $vaultPathMatch);
preg_match('/define\(\'ADMIN_EMAIL\', \'(.*?)\'\);/', $configContent, $adminEmailMatch);
preg_match('/define\(\'DEBUG_MODE\', (true|false)\);/', $configContent, $debugModeMatch);

$appName = isset($appNameMatch[1]) ? $appNameMatch[1] : APP_NAME;
$appUrl = isset($appUrlMatch[1]) ? $appUrlMatch[1] : APP_URL;
$vaultPath = isset($vaultPathMatch[1]) ? $vaultPathMatch[1] : VAULT_PATH;
$adminEmail = isset($adminEmailMatch[1]) ? $adminEmailMatch[1] : ADMIN_EMAIL;
$debugMode = isset($debugModeMatch[1]) ? ($debugModeMatch[1] === 'true') : DEBUG_MODE;

// Page title
$pageTitle = 'Settings';

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <h1 class="mb-4">Settings</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-sliders me-2"></i>
                        Application Settings
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="save_settings">
                            
                            <div class="mb-3">
                                <label for="app_name" class="form-label">Application Name</label>
                                <input type="text" class="form-control" id="app_name" name="app_name" value="<?= h($appName) ?>" required>
                                <div class="form-text">The name of your wiki.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="app_url" class="form-label">Application URL</label>
                                <input type="text" class="form-control" id="app_url" name="app_url" value="<?= h($appUrl) ?>" required>
                                <div class="form-text">The base URL of your wiki (e.g., http://localhost/md-wiki).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="vault_path" class="form-label">Obsidian Vault Path</label>
                                <input type="text" class="form-control" id="vault_path" name="vault_path" value="<?= h($vaultPath) ?>" required>
                                <div class="form-text">Path to your Obsidian vault directory. You can use absolute paths (e.g., /home/user/vault) or relative paths (e.g., ./vaults).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Admin Email</label>
                                <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= h($adminEmail) ?>" required>
                                <div class="form-text">The email address of the admin user.</div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="debug_mode" name="debug_mode" value="1" <?= $debugMode ? 'checked' : '' ?>>
                                <label class="form-check-label" for="debug_mode">Debug Mode</label>
                                <div class="form-text">Enable debug mode to show detailed error messages. Disable in production.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Theme Preference</label>
                                <div class="form-text mb-2">Choose your preferred theme for the wiki. This can also be changed using the theme toggle button in the navigation bar.</div>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_preference" id="theme_light" value="light" checked>
                                        <label class="form-check-label" for="theme_light">
                                            <i class="bi bi-sun-fill me-1"></i> Light
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_preference" id="theme_dark" value="dark">
                                        <label class="form-check-label" for="theme_dark">
                                            <i class="bi bi-moon-fill me-1"></i> Dark
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="theme_preference" id="theme_system" value="system">
                                        <label class="form-check-label" for="theme_system">
                                            <i class="bi bi-display me-1"></i> System Preference
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>
                                Save Settings
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get theme preference radio buttons
        const themeRadios = document.querySelectorAll('input[name="theme_preference"]');
        
        // Get saved theme preference
        const savedTheme = localStorage.getItem('md-wiki-theme');
        const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Set the appropriate radio button based on saved preference
        if (savedTheme === 'dark') {
            document.getElementById('theme_dark').checked = true;
        } else if (savedTheme === 'light') {
            document.getElementById('theme_light').checked = true;
        } else {
            document.getElementById('theme_system').checked = true;
        }
        
        // Add event listeners to radio buttons
        themeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const selectedTheme = this.value;
                
                if (selectedTheme === 'dark') {
                    // Enable dark mode
                    document.documentElement.classList.add('dark-mode');
                    localStorage.setItem('md-wiki-theme', 'dark');
                } else if (selectedTheme === 'light') {
                    // Enable light mode
                    document.documentElement.classList.remove('dark-mode');
                    localStorage.setItem('md-wiki-theme', 'light');
                } else if (selectedTheme === 'system') {
                    // Use system preference
                    localStorage.removeItem('md-wiki-theme');
                    
                    if (prefersDarkMode) {
                        document.documentElement.classList.add('dark-mode');
                    } else {
                        document.documentElement.classList.remove('dark-mode');
                    }
                }
            });
        });
    });
</script>

<?php
// Include admin footer
include __DIR__ . '/includes/footer.php';
?>
