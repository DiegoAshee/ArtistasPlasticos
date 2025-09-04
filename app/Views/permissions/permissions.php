<?php
// Helper functions for URL generation

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
    header('Location: /dashboard');
    exit;
}

// Set up variables for the layout
$title = 'Gestión de Permisos - Asociación de Artistas';
$currentPath = 'permissions';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Permisos', 'url' => null],
];

// Start output buffering
ob_start();
?>

<style>
    :root {
        --soft-brown: #8B7355;
        --dark-brown: #5D4037;
        --light-cream: #FFF8E1;
        --lighter-brown: #A68A64;
        --hover-bg: rgba(139, 115, 85, 0.08);
    }

    .permissions-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 2rem;
        margin: 1rem auto;
        max-width: 1200px;
        width: calc(100% - 2rem);
        border: 1px solid #e2e8f0;
    }

    .permissions-title {
        color: var(--dark-brown);
        font-size: 1.75rem;
        font-weight: 600;
        margin: 0;
        padding: 0;
        line-height: 1.2;
    }

    .permissions-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--primary);
        border-radius: 2px;
    }

    .permissions-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1.5rem;
        font-size: 0.95rem;
        background: var(--bg-surface);
        border-radius: var(--radius);
        overflow: hidden;
        color: var(--soft-brown);
    }

    .permissions-table th {
        background-color: var(--soft-brown);
        color: var(--light-cream);
        padding: 1rem;
        text-align: left;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        border: none;
    }

    .permissions-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--soft-brown);
        vertical-align: middle;
        color: var(--soft-brown);
        background-color: var(--bg-surface);
        transition: var(--transition);
    }

    .permissions-table tr:last-child td {
        border-bottom: none;
    }

    .permissions-table tr:hover td {
        background-color: rgba(139, 115, 85, 0.1);
        color: var(--soft-brown);
    }

    .checkmark {
        color: var(--soft-brown);
        font-weight: bold;
        font-size: 1.2rem;
    }

    .no-permissions {
        text-align: center;
        padding: 2.5rem;
        color: var(--soft-brown);
        font-style: italic;
        font-size: 1.1rem;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: var(--radius);
        font-weight: 500;
        background: var(--soft-brown);
        border: 1px solid var(--soft-brown);
        color: var(--light-cream);
    }

    .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-color: rgba(16, 185, 129, 0.2);
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-color: rgba(239, 68, 68, 0.2);
    }

    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .btn-create {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.65rem 1.5rem;
        background-color: var(--soft-brown);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-create:hover {
        background-color: var(--dark-brown);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-create i {
        margin-right: 8px;
        font-size: 1rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .permissions-container {
            padding: 1.25rem;
            margin: 0.75rem;
            width: auto;
        }
        
        .permissions-table {
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }
</style>

<div class="content-wrapper">
    <div class="permissions-container">
        <div class="header-actions">
            <h1 class="permissions-title">Gestión de Permisos</h1>
            <a href=<?= u('permissions/create') ?> class="btn-create">
                <i class="fas fa-plus"></i> Nuevo Permiso
            </a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error'] ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="permissions-table">
                <thead>
                    <tr>
                        <th>Menú Opción</th>
                        <th class="text-center">Rol Socio</th>
                        <th class="text-center">Rol Administrador</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($permissions)): ?>
                        <?php foreach ($permissions as $permission): ?>
                            <tr>
                                <td><?= htmlspecialchars($permission['Menu Opcion'] ?? '') ?></td>
                                <td class="text-center checkmark"><?= $permission['Rol Socio'] ?? '' ?></td>
                                <td class="text-center checkmark"><?= $permission['Rol Admin'] ?? '' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="no-permissions">No se encontraron permisos configurados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';

// Cerrar la conexión a la base de datos si es necesario
if (isset($db)) {
    $db = null;
}
?>
