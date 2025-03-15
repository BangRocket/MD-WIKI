<?php
/**
 * MD-WIKI Admin Dashboard
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

// Get statistics
$stats = [
    'total_pages' => dbQuerySingle("SELECT COUNT(*) as count FROM pages")['count'],
    'total_links' => dbQuerySingle("SELECT COUNT(*) as count FROM links")['count'],
    'total_tags' => dbQuerySingle("SELECT COUNT(*) as count FROM tags")['count'],
    'recent_pages' => getRecentPages(5)
];

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <h1 class="mb-4">Dashboard</h1>
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Welcome back, <?= h($currentUser['name']) ?>! Last login: 
                    <?= $currentUser['last_login'] ? formatDatetime($currentUser['last_login']) : 'First login' ?>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-file-earmark-text text-primary" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-3">Total Pages</h5>
                                <p class="card-text display-4"><?= $stats['total_pages'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-link-45deg text-success" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-3">Total Links</h5>
                                <p class="card-text display-4"><?= $stats['total_links'] ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <i class="bi bi-tags text-warning" style="font-size: 2.5rem;"></i>
                                <h5 class="card-title mt-3">Total Tags</h5>
                                <p class="card-text display-4"><?= $stats['total_tags'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-clock-history me-2"></i>
                                Recent Pages
                            </div>
                            <div class="card-body">
                                <?php if (empty($stats['recent_pages'])): ?>
                                    <p class="text-muted">No pages found.</p>
                                <?php else: ?>
                                    <div class="list-group">
                                        <?php foreach ($stats['recent_pages'] as $page): ?>
                                            <a href="<?= APP_URL ?>/page.php?slug=<?= h($page['slug']) ?>" class="list-group-item list-group-item-action">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?= h($page['title']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= formatDatetime($page['updated_at'], 'M j, Y') ?>
                                                    </small>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <i class="bi bi-gear me-2"></i>
                                Admin Actions
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="import.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100">
                                            <div class="me-3">
                                                <i class="bi bi-arrow-down-circle text-primary" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Import Markdown Files</h6>
                                                <small class="text-muted">
                                                    Import markdown files from your Obsidian vault
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <a href="settings.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100">
                                            <div class="me-3">
                                                <i class="bi bi-sliders text-warning" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Settings</h6>
                                                <small class="text-muted">
                                                    Configure wiki settings
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <a href="profile.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100">
                                            <div class="me-3">
                                                <i class="bi bi-person-circle text-info" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Profile</h6>
                                                <small class="text-muted">
                                                    Update your profile and password
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                    
                                    <a href="logout.php" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100">
                                            <div class="me-3">
                                                <i class="bi bi-box-arrow-right text-danger" style="font-size: 1.5rem;"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Logout</h6>
                                                <small class="text-muted">
                                                    End your current session
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
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
