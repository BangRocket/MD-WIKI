<?php
/**
 * MD-WIKI Page Display
 */

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/markdown.php';
require_once __DIR__ . '/includes/search.php';

// Get page slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// If no slug provided, redirect to home
if (empty($slug)) {
    redirect('/index.php');
}

// Get page by slug
$page = getPageBySlug($slug);

// If page doesn't exist, show 404 page
if (!$page) {
    // Include header
    include __DIR__ . '/includes/header.php';
    
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-warning">';
    echo '<h1>Page Not Found</h1>';
    echo '<p>The page "' . h($slug) . '" does not exist.</p>';
    echo '<p><a href="index.php">Return to Home</a></p>';
    echo '</div>';
    echo '</div>';
    
    // Include footer
    include __DIR__ . '/includes/footer.php';
    exit;
}

// Get backlinks for this page
$backlinks = getBacklinks($page['id']);

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
                <h1><?= h($page['title']) ?></h1>
                
                <div class="text-muted mb-4">
                    <small>
                        Last updated: <?= formatDatetime($page['updated_at']) ?>
                    </small>
                </div>
                
                <div class="content">
                    <?= $page['content_html'] ?>
                </div>
                
                <?php if (!empty($backlinks)): ?>
                <div class="backlinks mt-5">
                    <h3>Backlinks</h3>
                    <ul>
                        <?php foreach ($backlinks as $link): ?>
                            <li>
                                <a href="page.php?slug=<?= h($link['slug']) ?>">
                                    <?= h($link['title']) ?>
                                </a>
                                <?php if ($link['link_text'] !== $link['title']): ?>
                                    <small class="text-muted">
                                        (as "<?= h($link['link_text']) ?>")
                                    </small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
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
                        <?php foreach ($recentPages as $recentPage): ?>
                            <li class="mb-2">
                                <a href="page.php?slug=<?= h($recentPage['slug']) ?>">
                                    <?= h($recentPage['title']) ?>
                                </a>
                                <small class="text-muted d-block">
                                    <?= formatDatetime($recentPage['updated_at'], 'M j, Y') ?>
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
                            <?php foreach ($allPages as $navPage): ?>
                                <li class="mb-1 <?= $navPage['slug'] === $slug ? 'fw-bold' : '' ?>">
                                    <a href="page.php?slug=<?= h($navPage['slug']) ?>">
                                        <?= h($navPage['title']) ?>
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
