<?php
/**
 * Search Functionality
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Search pages by query
 * 
 * @param string $query Search query
 * @param int $page Page number (1-based)
 * @param int $perPage Results per page
 * @return array Search results and pagination info
 */
function search($query, $page = 1, $perPage = null) {
    if ($perPage === null) {
        $perPage = SEARCH_RESULTS_PER_PAGE;
    }
    
    // Sanitize and prepare query
    $query = trim($query);
    if (empty($query)) {
        return [
            'results' => [],
            'pagination' => [
                'current_page' => 1,
                'total_pages' => 0,
                'total_results' => 0,
                'has_previous' => false,
                'has_next' => false,
            ]
        ];
    }
    
    // Calculate offset
    $offset = ($page - 1) * $perPage;
    
    // Prepare search terms for LIKE query
    $searchTerm = '%' . $query . '%';
    
    // Get total results count
    $countResult = dbQuerySingle(
        "SELECT COUNT(*) as count FROM pages 
         WHERE title LIKE ? OR content_md LIKE ?",
        [$searchTerm, $searchTerm]
    );
    $totalResults = $countResult['count'];
    
    // Get paginated results
    $results = dbQuery(
        "SELECT id, title, slug, 
         SUBSTRING(content_md, 1, 200) AS excerpt,
         updated_at
         FROM pages 
         WHERE title LIKE ? OR content_md LIKE ? 
         ORDER BY 
            CASE 
                WHEN title LIKE ? THEN 1
                ELSE 2
            END,
            title ASC
         LIMIT ? OFFSET ?",
        [$searchTerm, $searchTerm, $searchTerm, $perPage, $offset]
    );
    
    // Process results to highlight search terms and format excerpts
    foreach ($results as &$result) {
        // Create a more relevant excerpt by finding the first occurrence of the search term
        if (stripos($result['excerpt'], $query) !== false) {
            $position = stripos($result['excerpt'], $query);
            $start = max(0, $position - 50);
            $length = min(200, strlen($result['excerpt']) - $start);
            $result['excerpt'] = ($start > 0 ? '...' : '') . 
                                substr($result['excerpt'], $start, $length) . 
                                ($start + $length < strlen($result['excerpt']) ? '...' : '');
        }
        
        // Highlight search terms in title and excerpt
        $result['title_highlighted'] = highlightSearchTerms($result['title'], $query);
        $result['excerpt_highlighted'] = highlightSearchTerms($result['excerpt'], $query);
    }
    
    // Calculate pagination info
    $totalPages = ceil($totalResults / $perPage);
    
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
 * Highlight search terms in text
 * 
 * @param string $text Text to highlight
 * @param string $query Search query
 * @return string Text with highlighted search terms
 */
function highlightSearchTerms($text, $query) {
    // Simple word boundary highlighting
    $pattern = '/\b(' . preg_quote($query, '/') . ')\b/i';
    return preg_replace($pattern, '<mark>$1</mark>', $text);
}

/**
 * Get search suggestions based on partial query
 * 
 * @param string $partialQuery Partial search query
 * @param int $limit Maximum number of suggestions
 * @return array Search suggestions
 */
function getSearchSuggestions($partialQuery, $limit = 5) {
    if (empty($partialQuery)) {
        return [];
    }
    
    $searchTerm = $partialQuery . '%';
    
    // Get matching page titles
    $results = dbQuery(
        "SELECT title FROM pages 
         WHERE title LIKE ? 
         ORDER BY title ASC 
         LIMIT ?",
        [$searchTerm, $limit]
    );
    
    return array_column($results, 'title');
}
