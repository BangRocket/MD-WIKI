<?php
/**
 * MD-WIKI Main Entry Point
 */

// Include required files
require_once __DIR__ . '/config/config.php';

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
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/markdown.php';
require_once __DIR__ . '/includes/search.php';

// Get home page slug from settings (default to 'home' if not set)
$homePageSlug = getSetting('home_page_slug', 'home');

// For debugging
if (DEBUG_MODE) {
    error_log("Home page slug from settings: " . $homePageSlug);
}

// Get home page
$homePage = getPageBySlug($homePageSlug);

// For debugging
if (DEBUG_MODE && !$homePage) {
    error_log("Home page not found for slug: " . $homePageSlug);
}

// If home page doesn't exist, redirect to admin setup
if (!$homePage) {
    redirect(APP_URL . '/admin/setup.php');
}

// If home page slug is not 'home', redirect to that page
if ($homePageSlug !== 'home') {
    redirect(APP_URL . '/page.php?slug=' . $homePageSlug);
}

// Get recent pages for sidebar
$recentPages = getRecentPages(5);

// Get all pages for navigation
$allPages = getAllPages();

// Include header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Main content -->
        <div class="col-md-9">
            <article class="wiki-content">
                <h1><?= h($homePage['title']) ?></h1>
                <div class="content">
                    <?= $homePage['content_html'] ?>
                </div>
            </article>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-search"></i> Search
                </div>
                <div class="card-body">
                    <form action="search.php" method="get">
                        <div class="input-group">
                            <input type="text" class="form-control" name="q" placeholder="Search...">
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-clock-history"></i> Recent Pages
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <?php foreach ($recentPages as $page): ?>
                            <li class="mb-2">
                                <a href="page.php?slug=<?= h($page['slug']) ?>">
                                    <?= h($page['title']) ?>
                                </a>
                                <small class="text-muted d-block">
                                    <?= formatDatetime($page['updated_at'], 'M j, Y') ?>
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-book"></i> All Pages
                </div>
                <div class="card-body">
                    <div class="all-pages-list" style="max-height: 300px; overflow-y: auto;">
                        <ul class="list-unstyled">
                            <?php foreach ($allPages as $page): ?>
                                <li class="mb-1">
                                    <a href="page.php?slug=<?= h($page['slug']) ?>">
                                        <?= h($page['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>
