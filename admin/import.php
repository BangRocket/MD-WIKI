<?php
/**
 * MD-WIKI Admin Import
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../import/import.php';

// Require login
requireLogin();

// Get current user
$currentUser = getCurrentUser();

// Initialize variables
$message = '';
$messageType = '';
$importStats = null;

// Process import form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    try {
        // Start output buffering to capture import script output
        ob_start();
        
        // Run import
        $importStats = importMarkdownFiles(VAULT_PATH, true);
        
        // Update broken links
        updateBrokenLinks(true);
        
        // Get output
        $output = ob_get_clean();
        
        // Set success message
        $message = "Import completed successfully. Processed {$importStats['total_files']} files.";
        $messageType = 'success';
    } catch (Exception $e) {
        // Get output
        $output = ob_get_clean();
        
        // Set error message
        $message = "Error during import: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Page title
$pageTitle = 'Import Markdown Files';

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <h1 class="mb-4">Import Markdown Files</h1>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i>
                        Import Information
                    </div>
                    <div class="card-body">
                        <p>
                            This tool imports markdown files from your Obsidian vault into the wiki.
                            It will scan the vault directory, parse markdown files, and import them into the database.
                        </p>
                        <p>
                            <strong>Current Vault Path:</strong> <?= h(VAULT_PATH) ?>
                        </p>
                        <p>
                            <strong>Note:</strong> Only files that have been modified since the last import will be updated.
                        </p>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-arrow-down-circle me-2"></i>
                        Import Markdown Files
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="import">
                            <p>Click the button below to start the import process.</p>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-down-circle me-2"></i>
                                Start Import
                            </button>
                        </form>
                    </div>
                </div>
                
                <?php if ($importStats): ?>
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-list-check me-2"></i>
                        Import Results
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Statistics</h5>
                                <table class="table">
                                    <tr>
                                        <td>Total Files Processed:</td>
                                        <td><?= $importStats['total_files'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>New Files:</td>
                                        <td><?= $importStats['new_files'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Updated Files:</td>
                                        <td><?= $importStats['updated_files'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Unchanged Files:</td>
                                        <td><?= $importStats['unchanged_files'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Errors:</td>
                                        <td><?= $importStats['errors'] ?></td>
                                    </tr>
                                    <tr>
                                        <td>Duration:</td>
                                        <td><?= round($importStats['duration'], 2) ?> seconds</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Import Log</h5>
                                <div class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                                    <pre class="mb-0" style="white-space: pre-wrap;"><?= h($output ?? '') ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
include __DIR__ . '/includes/footer.php';
?>
