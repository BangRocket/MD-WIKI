<?php
/**
 * Helper Functions
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/database.php';

/**
 * Sanitize output to prevent XSS
 * 
 * @param string $str String to sanitize
 * @return string Sanitized string
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a URL-friendly slug from a string
 * 
 * @param string $str String to convert to slug
 * @return string Slug
 */
function slugify($str) {
    // Replace non-alphanumeric characters with hyphens
    $str = preg_replace('/[^a-z0-9]+/i', '-', strtolower(trim($str)));
    // Remove leading/trailing hyphens
    return trim($str, '-');
}

/**
 * Get page by slug
 * 
 * @param string $slug Page slug
 * @return array|null Page data or null if not found
 */
function getPageBySlug($slug) {
    return dbQuerySingle("SELECT * FROM pages WHERE slug = ?", [$slug]);
}

/**
 * Get page by ID
 * 
 * @param int $id Page ID
 * @return array|null Page data or null if not found
 */
function getPageById($id) {
    return dbQuerySingle("SELECT * FROM pages WHERE id = ?", [$id]);
}

/**
 * Get all pages
 * 
 * @return array All pages
 */
function getAllPages() {
    return dbQuery("SELECT id, title, slug FROM pages ORDER BY title");
}

/**
 * Get backlinks for a page
 * 
 * @param int $pageId Page ID
 * @return array Backlinks
 */
function getBacklinks($pageId) {
    return dbQuery(
        "SELECT p.id, p.title, p.slug, l.link_text 
         FROM links l 
         JOIN pages p ON l.source_page_id = p.id 
         WHERE l.target_page_id = ? 
         ORDER BY p.title",
        [$pageId]
    );
}

/**
 * Search pages
 * 
 * @param string $query Search query
 * @param int $page Page number (1-based)
 * @return array Search results and pagination info
 */
function searchPages($query, $page = 1) {
    $query = '%' . $query . '%';
    $limit = SEARCH_RESULTS_PER_PAGE;
    $offset = ($page - 1) * $limit;
    
    // Get total results count
    $countResult = dbQuerySingle(
        "SELECT COUNT(*) as count FROM pages 
         WHERE title LIKE ? OR content_md LIKE ?",
        [$query, $query]
    );
    $totalResults = $countResult['count'];
    
    // Get paginated results
    $results = dbQuery(
        "SELECT id, title, slug, 
         SUBSTRING(content_md, 1, 200) AS excerpt 
         FROM pages 
         WHERE title LIKE ? OR content_md LIKE ? 
         ORDER BY title 
         LIMIT ? OFFSET ?",
        [$query, $query, $limit, $offset]
    );
    
    // Calculate pagination info
    $totalPages = ceil($totalResults / $limit);
    
    return [
        'results' => $results,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_results' => $totalResults,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
        ]
    ];
}

/**
 * Get a list of recently updated pages
 * 
 * @param int $limit Number of pages to return
 * @return array Recently updated pages
 */
function getRecentPages($limit = 5) {
    return dbQuery(
        "SELECT id, title, slug, updated_at 
         FROM pages 
         ORDER BY updated_at DESC 
         LIMIT ?",
        [$limit]
    );
}

/**
 * Format a datetime string
 * 
 * @param string $datetime Datetime string
 * @param string $format PHP date format
 * @return string Formatted datetime
 */
function formatDatetime($datetime, $format = 'F j, Y, g:i a') {
    return date($format, strtotime($datetime));
}

/**
 * Redirect to a URL
 * 
 * @param string $url URL to redirect to
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get current URL
 * 
 * @return string Current URL
 */
function getCurrentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if a string starts with a specific substring
 * 
 * @param string $haystack String to check
 * @param string $needle Substring to check for
 * @return bool True if string starts with substring
 */
function startsWith($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

/**
 * Check if a string ends with a specific substring
 * 
 * @param string $haystack String to check
 * @param string $needle Substring to check for
 * @return bool True if string ends with substring
 */
function endsWith($haystack, $needle) {
    return substr($haystack, -strlen($needle)) === $needle;
}
