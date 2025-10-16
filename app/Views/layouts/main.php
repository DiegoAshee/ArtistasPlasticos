<?php
/**
 * Main layout template
 * 
 * This is the base template that other views will extend.
 * It includes the basic HTML structure, header, and footer.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema de Gestión' ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .content-wrapper {
            padding: 20px;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
    
    <?php if (isset($styles)): ?>
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="<?= $style ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1><?= $title ?? 'Sistema de Gestión' ?></h1>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div>
                        <span class="me-3">Bienvenido, <?= htmlspecialchars($_SESSION['username'] ?? 'Usuario') ?></span>
                        <a href="<?= u('auth/logout') ?>" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Navigation -->
            <?php if (isset($menuOptions) && is_array($menuOptions)): ?>
                <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded mt-3">
                    <div class="container-fluid">
                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav">
                                <?php foreach ($menuOptions as $item): ?>
                                    <?php if (isset($item['submenu']) && is_array($item['submenu'])): ?>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                                <?= htmlspecialchars($item['label']) ?>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($item['submenu'] as $subItem): ?>
                                                    <li>
                                                        <a class="dropdown-item" href="<?= u($subItem['url']) ?>">
                                                            <?= htmlspecialchars($subItem['label']) ?>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php else: ?>
                                        <li class="nav-item">
                                            <a class="nav-link" href="<?= u($item['url']) ?>">
                                                <?= htmlspecialchars($item['label']) ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </nav>
            <?php endif; ?>
        </header>

        <!-- Main Content -->
        <main class="content-wrapper">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?= $content ?? '' ?>
        </main>

        <!-- Footer -->
        <footer class="mt-5 text-center text-muted">
            <p>&copy; <?= date('Y') ?> Sistema de Gestión. Todos los derechos reservados.</p>
        </footer>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Custom JS -->
    <?php if (isset($scripts)): ?>
        <?php foreach ($scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
