<?php
$currentPath = 'permissions';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Permisos', 'url' => u('permissions')],
    ['label' => 'Nuevo Permiso', 'url' => null],
];

ob_start();
?>

<div class="content-wrapper">
    <!-- Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0">Nuevo Permiso</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <ol class="breadcrumb mb-0">
                        <?php foreach ($breadcrumbs as $item): ?>
                            <li class="breadcrumb-item <?= empty($item['url']) ? 'active' : '' ?>">
                                <?php if (!empty($item['url'])): ?>
                                    <a href="<?= $item['url'] ?>"><?= $item['label'] ?></a>
                                <?php else: ?>
                                    <?= $item['label'] ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <section class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Error Alert -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div><?= $_SESSION['error'] ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <!-- Card Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-plus-circle me-2"></i> Nuevo Permiso
                            </h3>
                        </div>

                        <form action="<?= u('permissions/store') ?>" method="POST" id="permissionForm" class="needs-validation" novalidate>
                            <div class="card-body">
                                <!-- Nombre del Permiso -->
                                <div class="mb-4">
                                    <label for="name" class="form-label">Nombre del Permiso <span>*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        <input type="text" class="form-control" id="name" name="name"
                                               placeholder="Ej: Gestionar Usuarios"
                                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                               required>
                                    </div>
                                    <div class="form-text">Nombre descriptivo del permiso mostrado en la interfaz.</div>
                                </div>

                                <!-- Clave del Permiso -->
                                <div class="mb-4">
                                    <label for="key" class="form-label">Clave del Permiso <span>*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="text" class="form-control" id="key" name="key"
                                               placeholder="Ej: manage_users"
                                               value="<?= htmlspecialchars($_POST['key'] ?? '') ?>"
                                               pattern="[a-z0-9_]+" required>
                                    </div>
                                    <div class="form-text">Usar solo letras minúsculas, números y guiones bajos (_).</div>
                                </div>

                                <!-- Roles -->
                                <div class="mb-4">
                                    <label class="form-label d-block mb-3">Roles con acceso <span>*</span></label>
                                    <div class="role-selection">
                                        <?php foreach ($roles as $role): ?>
                                            <label class="role-option <?= (isset($_POST['roles']) && in_array($role['idRol'], $_POST['roles'])) ? 'checked' : '' ?>">
                                                <input type="checkbox" name="roles[]" value="<?= $role['idRol'] ?>"
                                                    <?= (isset($_POST['roles']) && in_array($role['idRol'], $_POST['roles'])) ? 'checked' : '' ?>>
                                                <div class="ms-2">
                                                    <div class="fw-medium"><i class="fas fa-user-shield me-2"></i><?= htmlspecialchars($role['rol']) ?></div>
                                                    <div class="small text-muted"><?= htmlspecialchars($role['descripcion'] ?? 'Sin descripción') ?></div>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="invalid-feedback d-block mt-2" id="rolesError">Por favor selecciona al menos un rol.</div>
                                </div>

                                <!-- Descripción -->
                                <div class="mb-0">
                                    <label for="description" class="form-label">Descripción</label>
                                    <div class="input-group">
                                        <span class="input-group-text align-items-start"><i class="fas fa-align-left mt-1"></i></span>
                                        <textarea class="form-control" id="description" name="description"
                                                  rows="3" placeholder="Descripción detallada del permiso"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                    </div>
                                    <div class="form-text">Opcional. Detalles sobre cuándo y por qué se usa este permiso.</div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="card-footer">
                                <a href="<?= u('permissions') ?>" class="btn btn-cancel"><i class="fas fa-times me-2"></i> Cancelar</a>
                                <button type="submit" class="btn btn-submit"><i class="fas fa-save me-2"></i> Guardar Permiso</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
:root {
    --primary: #8B7355;      /* Darker, richer brown */
    --primary-light: #8B7355;
    --primary-lighter: #D8C3A5;
    --secondary: #3A56D4;   /* Slightly darker blue for better contrast */
    --secondary-hover: #2E45A8;
    --light: #F5F0EA;      /* Warmer beige background */
    --lighter: #FFFCF9;    /* Cleaner off-white */
    --border: #C4B5A2;     /* Slightly darker border */
    --text: #3A3A3A;       /* Darker text for better readability */
    --text-light: #6B6B6B;  /* Better contrast for secondary text */
    --shadow: 0 4px 20px rgba(0,0,0,0.08);
    --border-radius: 12px;
    --transition: all 0.3s ease;
}

/* Header */
.content-header {
    background: linear-gradient(135deg, #8B7355 0%, #8B7355 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-bottom: 1px solid var(--border);
}

.content-header h1 {
    color: white !important;
    font-weight: 600;
    letter-spacing: -0.5px;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

/* Card */
.card {
    border: 1px solid var(--border);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow: hidden;
    transition: var(--transition);
    background: white;
    margin-bottom: 2rem;
}

.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

/* Card header */
.card-header {
    background: linear-gradient(to right, var(--primary), var(--primary-light));
    color: white;
    border: none;
    padding: 1.25rem 1.5rem;
}

.card-header .card-title {
    color: white !important;
    font-weight: 600;
    margin: 0;
}

/* Form elements */
.form-label {
    color: var(--primary);
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
}

.form-control, .form-select, .input-group-text {
    border-radius: 8px;
    border: 1px solid var(--border);
    transition: var(--transition);
    font-size: 0.95rem;
    padding: 0.75rem 1rem;
}

.form-control, .form-select {
    background-color: var(--lighter);
    color: black;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(107, 79, 61, 0.15);
}

.input-group-text {
    background-color: var(--light);
    color: var(--primary);
    border-color: var(--border);
}

/* Role selection */
.role-selection {
    display: grid;
    gap: 0.75rem;
}

.role-option {
    background: var(--lighter);
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 1rem 1.25rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
}

.role-option:hover {
    border-color: var(--primary);
    background: white;
}

.role-option.checked {
    border-color: var(--primary);
    background: rgba(139, 115, 85, 0.05);
    box-shadow: 0 2px 8px rgba(107, 79, 61, 0.1);
}

.role-option input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    cursor: pointer;
    accent-color: var(--primary);
}

/* Buttons */
.btn {
    padding: 0.75rem 1.75rem;
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    font-size: 0.95rem;
}

.btn i {
    margin-right: 0.5rem;
    font-size: 0.9em;
}

.btn-submit {
    background: var(--secondary);
    color: white;
}

.btn-submit:hover {
    background: var(--secondary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-cancel {
    background: var(--primary);
    color: white;
}

.btn-cancel:hover {
    background: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Card footer */
.card-footer {
    background: var(--lighter);
    border-top: 1px solid var(--border);
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    gap: 1rem;
}

/* Form text */
.form-text {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-top: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .card-footer {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .btn:last-child {
        margin-bottom: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const keyInput = document.getElementById('key');
    if (keyInput) {
        keyInput.addEventListener('input', function(e) {
            this.value = this.value.toLowerCase().replace(/[^a-z0-9_]/g,'_').replace(/_+/g,'_').replace(/^_|_$/g,'');
        });
    }

    const form = document.getElementById('permissionForm');
    const roleOptions = document.querySelectorAll('.role-option');
    if (form) {
        form.addEventListener('submit', function(e) {
            const checked = Array.from(roleOptions).some(opt => opt.querySelector('input').checked);
            const errorDiv = document.getElementById('rolesError');
            if (!checked) { e.preventDefault(); errorDiv.style.display='block'; }
            else { errorDiv.style.display='none'; }
        });
    }

    roleOptions.forEach(option => {
        const checkbox = option.querySelector('input');
        option.addEventListener('click', function(e) {
            if (e.target !== checkbox) checkbox.checked = !checkbox.checked;
            option.classList.toggle('checked', checkbox.checked);
        });
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
