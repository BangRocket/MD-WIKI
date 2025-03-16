<?php
/**
 * MD-WIKI Settings Migration
 * 
 * This script creates the settings table and adds default settings.
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

// Page title
$pageTitle = 'Settings Migration';

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <h1 class="mb-4">Settings Migration</h1>
                
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-database-gear me-2"></i>
                        Migration Results
                    </div>
                    <div class="card-body">
                        <?php
                        // Create settings table if it doesn't exist
                        $createTableQuery = "
                        CREATE TABLE IF NOT EXISTS settings (
                            setting_key VARCHAR(50) PRIMARY KEY,
                            setting_value TEXT NOT NULL,
                            setting_description VARCHAR(255) NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                        ";

                        $tableCreated = dbExecute($createTableQuery);
                        
                        if ($tableCreated !== false) {
                            echo '<div class="alert alert-success">Settings table created or already exists.</div>';
                            
                            // Insert default home page setting if it doesn't exist
                            $existingSetting = dbQuerySingle("SELECT * FROM settings WHERE setting_key = 'home_page_slug'");
                            if (!$existingSetting) {
                                $settingInserted = dbExecute(
                                    "INSERT INTO settings (setting_key, setting_value, setting_description) 
                                    VALUES ('home_page_slug', 'home', 'The slug of the page to use as the home page')"
                                );
                                
                                if ($settingInserted !== false) {
                                    echo '<div class="alert alert-success">Default home page setting added.</div>';
                                } else {
                                    echo '<div class="alert alert-danger">Error adding default home page setting.</div>';
                                }
                            } else {
                                echo '<div class="alert alert-info">Home page setting already exists.</div>';
                            }
                            
                            echo '<div class="alert alert-success mt-3">Settings migration completed successfully.</div>';
                        } else {
                            echo '<div class="alert alert-danger">Error creating settings table.</div>';
                        }
                        ?>
                        
                        <a href="settings.php" class="btn btn-primary mt-3">
                            <i class="bi bi-sliders me-2"></i>
                            Go to Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include __DIR__ . '/includes/footer.php';
?>
