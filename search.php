<?php
/**
 * MD-WIKI Search Page
 */

// Include required files
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/markdown.php';
require_once __DIR__ . '/includes/search.php';

// Get search query from URL
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Get page number from URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Perform search
$searchResults = search($query, $page);

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
            <h1>Search Results</h1>
            
            <div class="search-form mb-4">
                <form action="search.php" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="q" value="<?= h($query) ?>" placeholder="Search...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
            
            <?php if (empty($query)): ?>
                <div class="alert alert-info">
                    Please enter a search term.
                </div>
            <?php elseif (empty($searchResults['results'])): ?>
                <div class="alert alert-warning">
                    No results found for "<?= h($query) ?>".
                </div>
            <?php else: ?>
                <div class="search-results">
                    <p class="text-muted">
                        Found <?= $searchResults['pagination']['total_results'] ?> result(s) for "<?= h($query) ?>".
                    </p>
                    
                    <div class="list-group">
                        <?php foreach ($searchResults['results'] as $result): ?>
                            <a href="page.php?slug=<?= h($result['slug']) ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?= $result['title_highlighted'] ?></h5>
                                    <small class="text-muted">
                                        <?= formatDatetime($result['updated_at'], 'M j, Y') ?>
                                    </small>
                                </div>
                                <p class="mb-1"><?= $result['excerpt_highlighted'] ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($searchResults['pagination']['total_pages'] > 1): ?>
                        <nav aria-label="Search results pagination" class="mt-4">
                            <ul class="pagination">
                                <?php if ($searchResults['pagination']['has_previous']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="search.php?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>">
                                            Previous
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $searchResults['pagination']['total_pages']; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="search.php?q=<?= urlencode($query) ?>&page=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($searchResults['pagination']['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="search.php?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>">
                                            Next
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Sidebar -->
        <div class="col-md-3">
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
                                <li class="mb-1">
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
