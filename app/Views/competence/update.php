<?php
// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
    header('Location: ' . u('dashboard'));
    exit;
}

// Set up variables for the layout
$title = 'Editar Competencia - Asociación de Artistas';
$currentPath = 'competence/update';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Competencias', 'url' => u('competence/competence_list')],
    ['label' => 'Editar Competencia', 'url' => null],
];

// Get the competence data
$competence = $competence ?? null;
$menuOptions = $menuOptions ?? [];

if (!$competence) {
    $_SESSION['error'] = 'No se encontró la competencia solicitada';
    header('Location: ' . u('competence/competence_list'));
    exit;
}

// Start output buffering
ob_start();
?>

<!-- Include Alpine.js for the alert component -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    .page-header {
        background: #fff;
        border-radius: 0.5rem;
        padding: 1.5rem 2rem;
        margin: 1rem 0 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
    }

    .page-header .page-title {
        color: #2d3748 !important;
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
    }

    .page-header .breadcrumb {
        background: transparent;
        padding: 0.75rem 0 0;
        margin: 0;
    }

    .page-header .breadcrumb-item {
        font-size: 0.875rem;
        display: flex;
        align-items: center;
    }

    .page-header .breadcrumb-item a {
        color: #4a5568;
        text-decoration: none;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .page-header .breadcrumb-item a:hover {
        color: #2b6cb0;
        transform: translateY(-1px);
    }

    .page-header .breadcrumb-item.active {
        color: #2d3748;
        font-weight: 500;
    }

    .page-header .breadcrumb-item + .breadcrumb-item::before {
        color: #a0aec0;
        content: '›';
        font-size: 1.2rem;
        line-height: 1;
        padding: 0 0.5rem;
    }

    .page-title {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .card {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        margin-bottom: 2rem;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.5rem 2rem;
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }

    .card-title {
        color: #2c3e50;
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }

    .card-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 500;
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        padding: 0.625rem 1rem;
        font-size: 0.9375rem;
        transition: all 0.2s ease;
        height: calc(2.75rem + 2px);
    }

    .form-control:focus {
        border-color: #e2e8f0;
        box-shadow: none;
    }

    .form-text {
        color: #718096;
        font-size: 0.8125rem;
        margin-top: 0.25rem;
    }

    .btn {
        font-weight: 500;
        padding: 0.625rem 1.5rem;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        font-size: 0.9375rem;
    }

    .btn-primary {
        background-color: #4299e1;
        border-color: #4299e1;
    }

    .btn-primary:hover {
        background-color: #3182ce;
        border-color: #2b6cb0;
        transform: translateY(-1px);
    }

    .btn-danger {
        background-color: #f56565;
        border-color: #f56565;
    }

    .btn-danger:hover {
        background-color: #e53e3e;
        border-color: #c53030;
        transform: translateY(-1px);
    }

    .alert {
        border-radius: 0.375rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid transparent;
    }

    .alert-danger {
        background-color: #fff5f5;
        border-color: #fed7d7;
        color: #e53e3e;
    }

    .alert-success {
        background-color: #f0fff4;
        border-color: #c6f6d5;
        color: #38a169;
    }

    /* Breadcrumb styles are now in the .page-header section */

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid #edf2f7;
        margin-top: 2rem;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>

<!-- Display alerts if any -->
<?php if (isset($_SESSION['success'])): ?>
    <?php 
    $alert = [
        'type' => 'success',
        'message' => $_SESSION['success']
    ];
    unset($_SESSION['success']);
    include __DIR__ . '/../components/alert.php';
    ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <?php 
    $alert = [
        'type' => 'error',
        'message' => $_SESSION['error']
    ];
    unset($_SESSION['error']);
    include __DIR__ . '/../components/alert.php';
    ?>
<?php endif; ?>

<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="page-title">
                    <i class="fas fa-edit me-2"></i>Editar Competencia
                </h1>
                <a href="<?= u('competence/competence_list') ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Volver al listado
                </a>
            </div>
            
            <nav aria-label="breadcrumb" class="mt-3">
                <ol class="breadcrumb mb-0">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <?php if ($index < count($breadcrumbs) - 1): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= $crumb['url'] ?>">
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-home"></i>
                                    <?php else: ?>
                                        <?= $crumb['label'] ?>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= $crumb['label'] ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>

        <!-- Alert Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>
                        <h6 class="alert-heading mb-1">¡Error!</h6>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Información de la Competencia</h2>
                <p class="text-muted mb-0">Modifique los campos que desee actualizar</p>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= u('competence/update/' . $competence['idCompetence']) ?>" class="needs-validation" novalidate>
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="mb-4">
                        <label for="menu_option" class="form-label">
                            Nombre de la Competencia <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-tag text-primary"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="menu_option" 
                                   name="menu_option" 
                                   value="<?= htmlspecialchars($competence['menuOption'] ?? '') ?>" 
                                   placeholder="Ej: Menú" 
                                   required>
                        </div>
                        <div class="form-text">Este será el nombre que aparecerá en el menú de navegación.</div>
                    </div>

                    <div class="mb-4">
                        <label for="url_option" class="form-label">
                            Ruta (urlOption) <small class="text-muted">(opcional)</small>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-link text-primary"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="url_option"
                                   name="url_option"
                                   value="<?= htmlspecialchars($competence['urlOption'] ?? '') ?>"
                                   placeholder="Ej: /competence/competence_list o competence/competence_list">
                        </div>
                        <div class="form-text">Si indicas una ruta, el menú enlazará a esta URL en lugar del comportamiento por defecto.</div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= u('competence/competence_list') ?>" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Validation Script -->
<script>
// Handle form validation
(function () {
    'use strict'
    
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')
    
    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Menu option selection is now handled by a simple text input
</script>

<?php
// Get the buffered content and assign it to $content
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/../layouts/app.php';
?>
