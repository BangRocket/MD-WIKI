<?php
/**
 * MD-WIKI Admin Page Editor
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
require_once __DIR__ . '/../includes/markdown.php';

// Require login
requireLogin();

// Get current user
$currentUser = getCurrentUser();

// Initialize variables
$message = '';
$messageType = '';
$page = null;
$pageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pageSlug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Get page data
if ($pageId > 0) {
    $page = getPageById($pageId);
} elseif (!empty($pageSlug)) {
    $page = getPageBySlug($pageSlug);
}

// If page doesn't exist, show error
if (!$page) {
    // Set error message
    $message = 'Page not found.';
    $messageType = 'danger';
} else {
    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_page') {
        // Get form data
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        
        // Validate form data
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Title is required.';
        }
        
        if (empty($content)) {
            $errors[] = 'Content is required.';
        }
        
        // If no errors, update page
        if (empty($errors)) {
            // Generate slug from title if it's changed
            $newSlug = $title !== $page['title'] ? slugify($title) : $page['slug'];
            
            // Check if slug already exists (except for current page)
            if ($newSlug !== $page['slug']) {
                $existingPage = getPageBySlug($newSlug);
                if ($existingPage && $existingPage['id'] !== $page['id']) {
                    $errors[] = 'A page with this title already exists. Please choose a different title.';
                }
            }
            
            if (empty($errors)) {
                // Convert markdown to HTML
                $contentHtml = parseMarkdown($content);
                
                // Update page in database
                $result = dbExecute(
                    "UPDATE pages SET 
                     title = ?, 
                     slug = ?, 
                     content_md = ?, 
                     content_html = ?, 
                     updated_at = NOW() 
                     WHERE id = ?",
                    [$title, $newSlug, $content, $contentHtml, $page['id']]
                );
                
                if ($result) {
                    // If page has a file path, update the file as well
                    if (!empty($page['file_path'])) {
                        $filePath = $page['file_path'];
                        
                        // Check if file exists and is writable
                        if (file_exists($filePath) && is_writable($filePath)) {
                            // Write content to file
                            file_put_contents($filePath, $content);
                        } else {
                            // Set warning message
                            $message = 'Page updated in database, but could not update the source file. Check file permissions.';
                            $messageType = 'warning';
                        }
                    }
                    
                    if (empty($message)) {
                        // Set success message
                        $message = 'Page updated successfully.';
                        $messageType = 'success';
                    }
                    
                    // Refresh page data
                    $page = getPageById($page['id']);
                } else {
                    // Set error message
                    $message = 'Error updating page.';
                    $messageType = 'danger';
                }
            } else {
                // Set error message
                $message = 'Please fix the following errors: ' . implode(' ', $errors);
                $messageType = 'danger';
            }
        } else {
            // Set error message
            $message = 'Please fix the following errors: ' . implode(' ', $errors);
            $messageType = 'danger';
        }
    }
}

// Page title
$pageTitle = $page ? 'Edit: ' . $page['title'] : 'Page Not Found';

// Include admin header
include __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="admin-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><?= h($pageTitle) ?></h1>
                    <?php if ($page): ?>
                        <a href="<?= APP_URL ?>/page.php?slug=<?= h($page['slug']) ?>" class="btn btn-outline-secondary" target="_blank">
                            <i class="bi bi-eye me-2"></i> View Page
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?>">
                        <?= h($message) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($page): ?>
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-pencil-square me-2"></i>
                            Edit Page
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <input type="hidden" name="action" value="save_page">
                                
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?= h($page['title']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content (Markdown)</label>
                                    <div class="editor-toolbar">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="**bold**">
                                            <i class="bi bi-type-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="*italic*">
                                            <i class="bi bi-type-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="# ">
                                            <i class="bi bi-type-h1"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="## ">
                                            <i class="bi bi-type-h2"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="- ">
                                            <i class="bi bi-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="1. ">
                                            <i class="bi bi-list-ol"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="[link text](url)">
                                            <i class="bi bi-link-45deg"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="![alt text](image-url)">
                                            <i class="bi bi-image"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="```\ncode\n```">
                                            <i class="bi bi-code-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="> ">
                                            <i class="bi bi-blockquote-left"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-markdown="[[Wiki Link]]">
                                            <i class="bi bi-journal-text"></i>
                                        </button>
                                    </div>
                                    <textarea class="form-control" id="content" name="content" rows="15" required><?= h($page['content_md']) ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_preview" name="show_preview">
                                        <label class="form-check-label" for="show_preview">
                                            Show Preview
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="preview-container" class="mb-3 d-none">
                                    <label class="form-label">Preview</label>
                                    <div id="preview" class="wiki-content p-3 border rounded"></div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>
                                        Save Changes
                                    </button>
                                    <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-2"></i>
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get elements
        const contentTextarea = document.getElementById('content');
        const showPreviewCheckbox = document.getElementById('show_preview');
        const previewContainer = document.getElementById('preview-container');
        const preview = document.getElementById('preview');
        
        // Markdown toolbar buttons
        const toolbarButtons = document.querySelectorAll('[data-markdown]');
        
        // Add event listeners to toolbar buttons
        toolbarButtons.forEach(button => {
            button.addEventListener('click', function() {
                const markdown = this.getAttribute('data-markdown');
                insertMarkdown(markdown);
            });
        });
        
        // Function to insert markdown at cursor position
        function insertMarkdown(markdown) {
            const start = contentTextarea.selectionStart;
            const end = contentTextarea.selectionEnd;
            const text = contentTextarea.value;
            const selectedText = text.substring(start, end);
            
            let insertText = '';
            
            // Handle different markdown patterns
            if (markdown === '**bold**' || markdown === '*italic*') {
                // For bold and italic, wrap the selected text
                const wrapStart = markdown.substring(0, markdown.indexOf(markdown.charAt(0), 1));
                const wrapEnd = markdown.substring(markdown.lastIndexOf(markdown.charAt(markdown.length - 1), markdown.length - 1));
                insertText = wrapStart + (selectedText || 'text') + wrapEnd;
            } else if (markdown === '# ' || markdown === '## ' || markdown === '- ' || markdown === '1. ' || markdown === '> ') {
                // For headings, lists, and blockquotes, prepend to the selected text or add a placeholder
                insertText = markdown + (selectedText || 'text');
            } else if (markdown === '[link text](url)') {
                // For links, use selected text as link text or add a placeholder
                insertText = '[' + (selectedText || 'link text') + '](url)';
            } else if (markdown === '![alt text](image-url)') {
                // For images, use selected text as alt text or add a placeholder
                insertText = '![' + (selectedText || 'alt text') + '](image-url)';
            } else if (markdown === '```\ncode\n```') {
                // For code blocks, wrap the selected text or add a placeholder
                insertText = '```\n' + (selectedText || 'code') + '\n```';
            } else if (markdown === '[[Wiki Link]]') {
                // For wiki links, wrap the selected text or add a placeholder
                insertText = '[[' + (selectedText || 'Wiki Link') + ']]';
            } else {
                // Default: just insert the markdown
                insertText = markdown;
            }
            
            // Insert the markdown
            contentTextarea.value = text.substring(0, start) + insertText + text.substring(end);
            
            // Set cursor position
            const newCursorPos = start + insertText.length;
            contentTextarea.setSelectionRange(newCursorPos, newCursorPos);
            
            // Focus the textarea
            contentTextarea.focus();
            
            // Update preview if visible
            if (!previewContainer.classList.contains('d-none')) {
                updatePreview();
            }
        }
        
        // Toggle preview
        showPreviewCheckbox.addEventListener('change', function() {
            if (this.checked) {
                previewContainer.classList.remove('d-none');
                updatePreview();
            } else {
                previewContainer.classList.add('d-none');
            }
        });
        
        // Update preview when content changes
        contentTextarea.addEventListener('input', function() {
            if (!previewContainer.classList.contains('d-none')) {
                updatePreview();
            }
        });
        
        // Function to update preview
        function updatePreview() {
            // Get content
            const content = contentTextarea.value;
            
            // Send AJAX request to get HTML preview
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= APP_URL ?>/admin/ajax/preview.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    preview.innerHTML = xhr.responseText;
                    
                    // Initialize Prism.js for syntax highlighting
                    if (typeof Prism !== 'undefined') {
                        Prism.highlightAllUnder(preview);
                    }
                }
            };
            xhr.send('content=' + encodeURIComponent(content));
        }
    });
</script>

<?php
// Include admin footer
include __DIR__ . '/includes/footer.php';
?>
