# MD-WIKI

A PHP-based wiki system that imports content from Obsidian markdown files.

## Features

- Import markdown files from an Obsidian vault
- Support for Obsidian-style wiki links (`[[Page Name]]`)
- Backlinks tracking
- Search functionality
- Admin interface for managing the wiki
- Responsive design with Bootstrap 5

## Requirements

- PHP 7.4 or higher
- MySQL/MariaDB database
- Apache web server with mod_rewrite enabled
- Obsidian vault with markdown files

## Installation

1. Clone or download this repository to your web server
2. Create a MySQL/MariaDB database for the wiki
3. Update the database configuration in `config/config.php`
4. Navigate to the setup page in your browser (e.g., `http://your-domain.com/admin/setup.php`)
5. Follow the setup instructions to create the database tables and admin user
6. Set the path to your Obsidian vault during setup
7. Log in to the admin interface and import your markdown files

## Configuration

The main configuration file is located at `config/config.php`. You can modify the following settings:

- Database connection details
- Application name and URL
- Obsidian vault path
- Debug mode

## Importing Markdown Files

1. Log in to the admin interface
2. Go to the Import page
3. Click the "Start Import" button
4. The system will scan your Obsidian vault and import all markdown files
5. Only files that have been modified since the last import will be updated

## File Structure

```
md-wiki/
├── admin/                  # Admin interface
│   ├── includes/           # Admin includes
│   ├── dashboard.php       # Admin dashboard
│   ├── import.php          # Import markdown files
│   ├── index.php           # Admin login
│   ├── logout.php          # Admin logout
│   ├── profile.php         # Admin profile
│   ├── settings.php        # Admin settings
│   └── setup.php           # Setup page
├── assets/                 # Assets
│   ├── css/                # CSS files
│   └── js/                 # JavaScript files
├── config/                 # Configuration
│   └── config.php          # Main configuration file
├── includes/               # Includes
│   ├── auth.php            # Authentication functions
│   ├── database.php        # Database functions
│   ├── footer.php          # Footer template
│   ├── functions.php       # Helper functions
│   ├── header.php          # Header template
│   ├── markdown.php        # Markdown parser
│   └── search.php          # Search functions
├── import/                 # Import scripts
│   ├── import.php          # Import script
│   └── setup_database.sql  # Database setup SQL
├── .htaccess               # Apache configuration
├── index.php               # Main entry point
├── page.php                # Page display
├── README.md               # This file
└── search.php              # Search page
```

## Customization

### Styling

The wiki uses Bootstrap 5 for styling. You can customize the appearance by modifying the CSS in `assets/css/style.css`.

### Templates

The main templates are located in the `includes` directory:

- `header.php`: Page header and navigation
- `footer.php`: Page footer

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- [Bootstrap](https://getbootstrap.com/) - Frontend framework
- [Bootstrap Icons](https://icons.getbootstrap.com/) - Icons
- [Prism.js](https://prismjs.com/) - Syntax highlighting
