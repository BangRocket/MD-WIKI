<?php
/**
 * Markdown Import Script
 * 
 * This script scans the Obsidian vault directory, parses markdown files,
 * and imports them into the database.
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
require_once __DIR__ . '/../includes/markdown.php';

// Set execution time limit (0 = no limit)
set_time_limit(0);

/**
 * Resolve relative paths to absolute paths
 * 
 * @param string $path Path to resolve
 * @return string Resolved absolute path
 */
function resolveVaultPath($path) {
    // Get the website root directory (parent of import directory)
    $websiteRoot = dirname(__DIR__);
    
    // Handle relative paths by converting to absolute paths
    if (substr($path, 0, 2) === './') {
        // For paths starting with ./
        return $websiteRoot . substr($path, 1);
    } else if (substr($path, 0, 1) !== '/' && substr($path, 1, 1) !== ':') {
        // For other relative paths without leading slash or drive letter (Windows)
        return $websiteRoot . '/' . $path;
    }
    
    // Already an absolute path
    return $path;
}

/**
 * Import markdown files from Obsidian vault
 * 
 * @param string $vaultPath Path to Obsidian vault
 * @param bool $verbose Whether to output verbose information
 * @return array Import statistics
 */
function importMarkdownFiles($vaultPath = null, $verbose = true) {
    if ($vaultPath === null) {
        $vaultPath = VAULT_PATH;
    }
    
    // Resolve relative paths to absolute paths
    $absolutePath = resolveVaultPath($vaultPath);
    
    if ($verbose && $absolutePath !== $vaultPath) {
        echo "Resolved vault path: $vaultPath -> $absolutePath\n";
    }
    
    // Validate vault path
    if (!is_dir($absolutePath)) {
        die("Error: Vault path '$vaultPath' (resolved to '$absolutePath') is not a valid directory.");
    }
    
    // Use the absolute path for the import
    $vaultPath = $absolutePath;
    
    // Initialize statistics
    $stats = [
        'total_files' => 0,
        'new_files' => 0,
        'updated_files' => 0,
        'unchanged_files' => 0,
        'skipped_files' => 0,
        'errors' => 0,
        'start_time' => microtime(true),
        'end_time' => null,
        'duration' => null
    ];
    
    // Get all markdown files in the vault
    $markdownFiles = findMarkdownFiles($vaultPath);
    $stats['total_files'] = count($markdownFiles);
    
    if ($verbose) {
        echo "Found {$stats['total_files']} markdown files in vault.\n";
        echo "Starting import process...\n";
    }
    
    // Process each file
    foreach ($markdownFiles as $filePath) {
        try {
            $relativePath = str_replace($vaultPath . '/', '', $filePath);
            
            if ($verbose) {
                echo "Processing: $relativePath... ";
            }
            
            // Get file modification time
            $fileModified = filemtime($filePath);
            
            // Check if file exists in database
            $existingPage = dbQuerySingle(
                "SELECT id, file_modified FROM pages WHERE file_path = ?",
                [$relativePath]
            );
            
            // Skip if file hasn't been modified since last import
            if ($existingPage && $existingPage['file_modified'] >= $fileModified) {
                $stats['unchanged_files']++;
                if ($verbose) {
                    echo "SKIPPED (unchanged)\n";
                }
                continue;
            }
            
            // Read file content
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("Failed to read file");
            }
            
            // Get file name without extension as title
            $fileName = basename($filePath, '.md');
            $title = str_replace('_', ' ', $fileName);
            $slug = slugify($title);
            
            // Parse markdown
            $parsed = parseMarkdown($content);
            $html = $parsed['html'];
            
            // Extract tags
            $tags = extractTags($content);
            
            // Insert or update page in database
            if ($existingPage) {
                // Update existing page
                dbExecute(
                    "UPDATE pages SET 
                     title = ?, 
                     content_md = ?, 
                     content_html = ?, 
                     updated_at = NOW(), 
                     file_modified = ? 
                     WHERE id = ?",
                    [$title, $content, $html, $fileModified, $existingPage['id']]
                );
                
                $pageId = $existingPage['id'];
                $stats['updated_files']++;
                
                if ($verbose) {
                    echo "UPDATED\n";
                }
            } else {
                // Insert new page
                dbExecute(
                    "INSERT INTO pages 
                     (title, slug, content_md, content_html, created_at, updated_at, file_path, file_modified) 
                     VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?)",
                    [$title, $slug, $content, $html, $relativePath, $fileModified]
                );
                
                $pageId = dbLastInsertId();
                $stats['new_files']++;
                
                if ($verbose) {
                    echo "ADDED\n";
                }
            }
            
            // Process links
            processExtractedLinks($parsed['links'], $pageId);
            
            // Process tags
            processTags($pageId, $tags);
            
        } catch (Exception $e) {
            $stats['errors']++;
            if ($verbose) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Update statistics
    $stats['end_time'] = microtime(true);
    $stats['duration'] = $stats['end_time'] - $stats['start_time'];
    
    if ($verbose) {
        echo "\nImport completed in " . round($stats['duration'], 2) . " seconds.\n";
        echo "New files: {$stats['new_files']}\n";
        echo "Updated files: {$stats['updated_files']}\n";
        echo "Unchanged files: {$stats['unchanged_files']}\n";
        echo "Errors: {$stats['errors']}\n";
    }
    
    return $stats;
}

/**
 * Find all markdown files in a directory recursively
 * 
 * @param string $dir Directory to search
 * @param array $results Array to store results
 * @return array Paths to markdown files
 */
function findMarkdownFiles($dir, &$results = []) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        // Skip . and .. directories
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // Skip hidden files and directories
        if ($file[0] === '.') {
            continue;
        }
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            // Recursively search subdirectories
            findMarkdownFiles($path, $results);
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'md') {
            // Add markdown files to results
            $results[] = $path;
        }
    }
    
    return $results;
}

/**
 * Update broken links after import
 * 
 * This function updates links that were broken during import because
 * the target page didn't exist yet.
 * 
 * @param bool $verbose Whether to output verbose information
 * @return int Number of links updated
 */
function updateBrokenLinks($verbose = true) {
    if ($verbose) {
        echo "Updating broken links...\n";
    }
    
    // Get links with null target_page_id
    $brokenLinks = dbQuery(
        "SELECT l.id, l.source_page_id, l.page_name, p.slug 
         FROM links l 
         JOIN pages p ON l.page_name = p.title 
         WHERE l.target_page_id IS NULL"
    );
    
    $updatedCount = 0;
    
    foreach ($brokenLinks as $link) {
        // Get target page by slug
        $targetPage = dbQuerySingle(
            "SELECT id FROM pages WHERE slug = ?",
            [$link['slug']]
        );
        
        if ($targetPage) {
            // Update link with target page ID
            dbExecute(
                "UPDATE links SET target_page_id = ? WHERE id = ?",
                [$targetPage['id'], $link['id']]
            );
            
            $updatedCount++;
        }
    }
    
    if ($verbose) {
        echo "Updated $updatedCount broken links.\n";
    }
    
    return $updatedCount;
}

// Run import if script is executed directly (not included)
if (realpath($_SERVER['SCRIPT_FILENAME']) === realpath(__FILE__)) {
    // Check if this is being run from the command line
    $isCli = php_sapi_name() === 'cli';
    
    // Set output format based on environment
    if ($isCli) {
        // Command line output
        $verbose = true;
    } else {
        // Web output
        header('Content-Type: text/plain');
        $verbose = true;
    }
    
    // Run import
    $stats = importMarkdownFiles(VAULT_PATH, $verbose);
    
    // Update broken links
    updateBrokenLinks($verbose);
    
    if (!$isCli) {
        // Add a link back to admin dashboard if running in browser
        echo "\n\n";
        echo "Return to <a href=\"" . APP_URL . "/admin/dashboard.php\">Admin Dashboard</a>";
    }
}
