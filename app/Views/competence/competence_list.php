<?php
// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
    header('Location: /dashboard');
    exit;
}

// Set up variables for the layout
$title = 'Gestión de Competencias - Asociación de Artistas';
$currentPath = 'competence/list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Competencias', 'url' => null],
];

// Start output buffering
ob_start();
?>

<style>
    /* Estilos para la tabla */
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
        border-collapse: collapse;
    }
    
    .table th,
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
    }
    
    .table tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    /* Estilos para los botones de acción */
    .btn-edit, .btn-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
        margin: 0 3px;
        border: none;
        color: white !important;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }
    
    .btn-edit {
        background-color: #28a745;
        border: 1px solid #28a745;
    }
    
    .btn-edit:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn-delete {
        background-color: #dc3545;
        border: 1px solid #dc3545;
    }
    
    .btn-delete:hover {
        background-color: #c82333;
        border-color: #bd2130;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn i {
        margin-right: 5px;
        font-size: 0.9em;
        color: white !important;
    }

    /* Estilo para la tabla */
    .table {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f1f1f1;
        font-weight: bold;
        text-align: center;
        border-bottom: 2px solid #ddd;
        color: #495057;
    }

    .table tbody tr {
        transition: background-color 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
        text-align: center;
        border-top: 1px solid #ddd;
    }

    /* Estilo para las alertas */
    .alert {
        font-size: 1rem;
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .alert i {
        margin-right: 10px;
    }

    /* Estilo de la tarjeta */
    .card {
        border-radius: 12px;
        border: 1px solid #ddd;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Estilo para el contenedor de la cabecera */
    .header-actions {
        margin-bottom: 20px;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="header-actions d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Lista de Competencias</h1>
            <a href="<?= u('competence/create') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Nueva Competencia
            </a>
        </div>

        <!-- Alertas de éxito y error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Contenedor principal de competencias -->
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($competences)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay competencias registradas.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Opción de Menú</th>
                                    <th style="width: 200px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competences as $competence): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-tag text-muted me-2"></i>
                                                <span><?= htmlspecialchars($competence['menuOption']) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="<?= u('competence/edit/' . $competence['idCompetence']) ?>" 
                                                   class="btn-edit" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= u('competence/delete/' . $competence['idCompetence']) ?>" 
                                                   class="btn-delete" 
                                                   onclick="return confirm('¿Estás seguro de que deseas eliminar esta competencia?')"
                                                   title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i> 
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>

<script>
    // Activar tooltips en la página
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
