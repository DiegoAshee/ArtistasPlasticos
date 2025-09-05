<?php
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

// Get the permissions and roles from the controller
$permissions = $permissions ?? [];
$roles = $roles ?? [];

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
        margin: 0 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--soft-brown);
        position: relative;
    }

    .permissions-title:after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 80px;
        height: 3px;
        background: var(--dark-brown);
    }

    .permissions-table {
        width: 100%;
        border-collapse: collapse;
        margin: 1.5rem 0;
        font-size: 0.95rem;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .permissions-table th {
        background-color: var(--soft-brown);
        color: white;
        padding: 1rem;
        text-align: left;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .permissions-table th:first-child {
        border-top-left-radius: 8px;
    }

    .permissions-table th:last-child {
        border-top-right-radius: 8px;
    }

    .permissions-table td {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
        color: #555;
        background-color: #fff;
        transition: all 0.2s ease;
    }

    .permissions-table tr:last-child td {
        border-bottom: none;
    }

    .permissions-table tr:hover td {
        background-color: #f9f9f9;
    }

    .permission-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .btn-save {
        background-color: var(--dark-brown);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-save:hover {
        background-color: var(--soft-brown);
        transform: translateY(-1px);
    }

    .btn-save:active {
        transform: translateY(0);
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .alert {
        padding: 1rem;
        margin-bottom: 1.5rem;
        border-radius: 6px;
        font-size: 0.95rem;
    }

    .alert-success {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
    }

    .alert-error {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    .no-permissions {
        padding: 2rem;
        text-align: center;
        color: #777;
        font-style: italic;
    }
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
                        <?php foreach ($roles as $role): ?>
                            <th class="text-center"><?= htmlspecialchars($role['rol']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($permissions)): ?>
                        <tr>
                            <td colspan="<?= count($roles) + 1 ?>" class="no-permissions">No hay opciones de menú configuradas.</td>
                        </tr>
                    <?php else: ?>
                        <form action="<?= u('permissions/update') ?>" method="post">
                            <?php foreach ($permissions as $permission): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($permission['name']) ?>
                                        <input type="hidden" name="permissions[<?= $permission['id'] ?>][id]" value="<?= $permission['id'] ?>">
                                    </td>
                                    
                                    <?php foreach ($roles as $role): ?>
                                        <td class="text-center">
                                            <input type="checkbox" 
                                                   class="permission-checkbox" 
                                                   name="permissions[<?= $permission['id'] ?>][roles][<?= $role['idRol'] ?>]" 
                                                   value="1"
                                                   <?= !empty($permission['roles'][$role['idRol']]) ? 'checked' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="<?= count($roles) + 1 ?>" class="text-right">
                                    <button type="submit" class="btn-save">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </td>
                            </tr>
                        </form>
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
