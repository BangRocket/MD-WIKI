<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page) && isset($page['title']) ? h($page['title']) . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= APP_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Prism.js for syntax highlighting -->
    <link href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="<?= APP_URL ?>/index.php"><?= APP_NAME ?></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= APP_URL ?>/index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= APP_URL ?>/search.php">Search</a>
                        </li>
                    </ul>
                    <form class="d-flex" action="<?= APP_URL ?>/search.php" method="get">
                        <input class="form-control me-2" type="search" name="q" placeholder="Search..." aria-label="Search">
                        <button class="btn btn-light" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </nav>
    </header>
    <main>
