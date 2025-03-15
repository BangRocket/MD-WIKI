<?php
/**
 * Markdown Parser
 * 
 * Handles converting Obsidian markdown to HTML, including support for
 * wiki-links and other Obsidian-specific features.
 */

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Parse Obsidian markdown to HTML
 * 
 * @param string $markdown Markdown content
 * @param int $sourcePageId ID of the page containing the markdown (for link tracking)
 * @return array Parsed HTML and extracted links
 */
function parseMarkdown($markdown, $sourcePageId = null) {
    // Extract wiki-links before parsing
    $links = [];
    $markdown = extractWikiLinks($markdown, $links);
    
    // Basic markdown parsing
    $html = parseBasicMarkdown($markdown);
    
    // Process extracted links
    if ($sourcePageId !== null) {
        processExtractedLinks($links, $sourcePageId);
    }
    
    return [
        'html' => $html,
        'links' => $links
    ];
}

/**
 * Extract wiki-links from markdown and replace with HTML links
 * 
 * @param string $markdown Markdown content
 * @param array &$links Array to store extracted links
 * @return string Markdown with wiki-links replaced
 */
function extractWikiLinks($markdown, &$links) {
    // Match [[Page Name]] or [[Page Name|Display Text]]
    $pattern = '/\[\[(.*?)(?:\|(.*?))?\]\]/';
    
    return preg_replace_callback($pattern, function($matches) use (&$links) {
        $pageName = trim($matches[1]);
        $displayText = isset($matches[2]) ? trim($matches[2]) : $pageName;
        
        // Add to links array
        $links[] = [
            'page_name' => $pageName,
            'display_text' => $displayText,
            'slug' => slugify($pageName)
        ];
        
        // Replace with HTML link
        return '<a href="' . APP_URL . '/page.php?slug=' . slugify($pageName) . '" class="wiki-link">' . $displayText . '</a>';
    }, $markdown);
}

/**
 * Process extracted links for database storage
 * 
 * @param array $links Extracted links
 * @param int $sourcePageId ID of the page containing the links
 * @return void
 */
function processExtractedLinks($links, $sourcePageId) {
    // First, delete existing links for this page
    dbExecute("DELETE FROM links WHERE source_page_id = ?", [$sourcePageId]);
    
    // Process each link
    foreach ($links as $link) {
        // Check if target page exists
        $targetPage = dbQuerySingle(
            "SELECT id FROM pages WHERE slug = ?", 
            [$link['slug']]
        );
        
        $targetPageId = $targetPage ? $targetPage['id'] : null;
        
        // Store link in database
        dbExecute(
            "INSERT INTO links (source_page_id, target_page_id, link_text, page_name) 
             VALUES (?, ?, ?, ?)",
            [
                $sourcePageId,
                $targetPageId,
                $link['display_text'],
                $link['page_name']
            ]
        );
    }
}

/**
 * Parse basic markdown to HTML
 * 
 * @param string $markdown Markdown content
 * @return string HTML content
 */
function parseBasicMarkdown($markdown) {
    // This is a simple implementation. In a real application, you would use
    // a proper markdown library like Parsedown or league/commonmark.
    
    // Process headers
    $markdown = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $markdown);
    $markdown = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $markdown);
    $markdown = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $markdown);
    $markdown = preg_replace('/^#### (.*?)$/m', '<h4>$1</h4>', $markdown);
    $markdown = preg_replace('/^##### (.*?)$/m', '<h5>$1</h5>', $markdown);
    $markdown = preg_replace('/^###### (.*?)$/m', '<h6>$1</h6>', $markdown);
    
    // Process bold and italic
    $markdown = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $markdown);
    $markdown = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $markdown);
    
    // Process lists
    $markdown = preg_replace_callback('/^- (.*?)$/m', function($matches) {
        return '<ul><li>' . $matches[1] . '</li></ul>';
    }, $markdown);
    $markdown = preg_replace_callback('/^[0-9]+\. (.*?)$/m', function($matches) {
        return '<ol><li>' . $matches[1] . '</li></ol>';
    }, $markdown);
    
    // Combine consecutive list items
    $markdown = preg_replace('/<\/ul>\s*<ul>/s', '', $markdown);
    $markdown = preg_replace('/<\/ol>\s*<ol>/s', '', $markdown);
    
    // Process code blocks
    $markdown = preg_replace_callback('/```(.*?)\n(.*?)```/s', function($matches) {
        $language = trim($matches[1]);
        $code = trim($matches[2]);
        return '<pre><code class="language-' . $language . '">' . htmlspecialchars($code) . '</code></pre>';
    }, $markdown);
    
    // Process inline code
    $markdown = preg_replace('/`(.*?)`/s', '<code>$1</code>', $markdown);
    
    // Process blockquotes
    $markdown = preg_replace_callback('/^> (.*?)$/m', function($matches) {
        return '<blockquote>' . $matches[1] . '</blockquote>';
    }, $markdown);
    $markdown = preg_replace('/<\/blockquote>\s*<blockquote>/s', '<br>', $markdown);
    
    // Process horizontal rules
    $markdown = preg_replace('/^---$/m', '<hr>', $markdown);
    
    // Process paragraphs
    $markdown = preg_replace_callback('/^(?!<h|<ul|<ol|<blockquote|<hr|<pre)(.*?)$/m', function($matches) {
        $line = trim($matches[1]);
        return !empty($line) ? '<p>' . $line . '</p>' : '';
    }, $markdown);
    
    return $markdown;
}

/**
 * Extract tags from markdown content
 * 
 * @param string $markdown Markdown content
 * @return array Extracted tags
 */
function extractTags($markdown) {
    $tags = [];
    
    // Match #tag format (not within code blocks)
    preg_match_all('/#([a-zA-Z0-9_-]+)/', $markdown, $matches);
    
    if (isset($matches[1]) && !empty($matches[1])) {
        $tags = array_unique($matches[1]);
    }
    
    return $tags;
}

/**
 * Process tags for a page
 * 
 * @param int $pageId Page ID
 * @param array $tags Tags to process
 * @return void
 */
function processTags($pageId, $tags) {
    // First, delete existing tags for this page
    dbExecute("DELETE FROM page_tags WHERE page_id = ?", [$pageId]);
    
    // Process each tag
    foreach ($tags as $tagName) {
        // Check if tag exists
        $tag = dbQuerySingle("SELECT id FROM tags WHERE name = ?", [$tagName]);
        
        if (!$tag) {
            // Create new tag
            dbExecute("INSERT INTO tags (name) VALUES (?)", [$tagName]);
            $tagId = dbLastInsertId();
        } else {
            $tagId = $tag['id'];
        }
        
        // Associate tag with page
        dbExecute(
            "INSERT INTO page_tags (page_id, tag_id) VALUES (?, ?)",
            [$pageId, $tagId]
        );
    }
}
