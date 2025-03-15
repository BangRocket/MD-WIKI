-- MD-WIKI Database Schema

-- Drop tables if they exist
DROP TABLE IF EXISTS page_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS links;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    last_login DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create pages table
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content_md TEXT NOT NULL,
    content_html TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    file_path VARCHAR(255) NULL,
    file_modified BIGINT NULL,
    INDEX idx_title (title),
    INDEX idx_slug (slug),
    FULLTEXT INDEX ft_content (title, content_md)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create links table
CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    source_page_id INT NOT NULL,
    target_page_id INT NULL,
    link_text VARCHAR(255) NOT NULL,
    page_name VARCHAR(255) NOT NULL,
    INDEX idx_source (source_page_id),
    INDEX idx_target (target_page_id),
    FOREIGN KEY (source_page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (target_page_id) REFERENCES pages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tags table
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create page_tags table
CREATE TABLE page_tags (
    page_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (page_id, tag_id),
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- Default credentials: admin@example.com / password
-- IMPORTANT: Change this password in production!
INSERT INTO users (email, password_hash, name, created_at)
VALUES ('admin@example.com', '$2y$10$8zTlsRlxAV5JwDxWBMoKWOaNYPbSW2yDJJYCZS5.H1xKQYOcvNJEu', 'Admin', NOW());

-- Insert home page
INSERT INTO pages (title, slug, content_md, content_html, created_at, updated_at)
VALUES (
    'Home',
    'home',
    '# Welcome to MD-WIKI\n\nThis is your wiki home page. You can edit this page by updating the corresponding markdown file in your Obsidian vault.\n\n## Getting Started\n\n1. Configure your Obsidian vault path in the config file\n2. Run the import script to import your markdown files\n3. Start creating and linking pages in your Obsidian vault\n\n## Features\n\n- Wiki-style navigation with [[Page Links]]\n- Automatic import from Obsidian vault\n- Markdown formatting\n- Search functionality\n- Backlinks tracking',
    '<h1>Welcome to MD-WIKI</h1>\n<p>This is your wiki home page. You can edit this page by updating the corresponding markdown file in your Obsidian vault.</p>\n<h2>Getting Started</h2>\n<ol>\n<li>Configure your Obsidian vault path in the config file</li>\n<li>Run the import script to import your markdown files</li>\n<li>Start creating and linking pages in your Obsidian vault</li>\n</ol>\n<h2>Features</h2>\n<ul>\n<li>Wiki-style navigation with <a href="/page.php?slug=page-links" class="wiki-link">Page Links</a></li>\n<li>Automatic import from Obsidian vault</li>\n<li>Markdown formatting</li>\n<li>Search functionality</li>\n<li>Backlinks tracking</li>\n</ul>',
    NOW(),
    NOW()
);
