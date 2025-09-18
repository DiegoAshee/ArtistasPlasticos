<?php
// Set up variables for the layout
$title = 'Editar Usuario - Administración';
$currentPath = 'users/edit';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Usuarios', 'url' => u('users/list')],
    ['label' => 'Editar Usuario', 'url' => null],
];

// Helper function for URL
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

// Start output buffering
ob_start();
?>

<style>
    .edit-container {
        background: var(--surface);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        padding: 2.5rem 3rem;
        margin: 1rem auto;
        max-width: 800px;
        width: calc(100% - 2rem);
        border: 1px solid var(--border);
    }

    .edit-title {
        color: var(--cream-900);
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 2.5rem 0;
        text-align: center;
        position: relative;
        padding-bottom: 1.25rem;
        font-family: 'Playfair Display', serif;
    }

    .edit-title:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: var(--cream-600);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.6rem;
        font-weight: 600;
        color: var(--cream-800);
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
    }

    .form-group input {
        width: 100%;
        padding: 0.85rem 1.25rem;
        border: 1px solid var(--cream-300);
        border-radius: 8px;
        font-size: 1rem;
        transition: var(--transition);
        background-color: var(--cream-50);
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--cream-600);
        box-shadow: 0 0 0 3px rgba(156, 143, 122, 0.1);
        background-color: var(--surface);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2.5rem;
        flex-wrap: wrap;
    }

    .btn {
        padding: 0.85rem 2rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 140px;
        justify-content: center;
        font-family: 'Inter', sans-serif;
    }

    .btn-primary {
        background: var(--cream-600);
        color: white;
    }

    .btn-primary:hover {
        background: var(--cream-700);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin: 0 0 2.5rem 0;
        border-left: 4px solid #dc2626;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.05rem;
    }

    .password-help {
        font-size: 0.875rem;
        color: #666;
        margin-top: 0.5rem;
        font-style: italic;
    }

    .user-info {
        background: var(--cream-100);
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border-left: 4px solid var(--cream-600);
    }

    .user-info h3 {
        margin: 0 0 1rem 0;
        color: var(--cream-800);
        font-size: 1.1rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding: 0.25rem 0;
    }

    .info-label {
        font-weight: 600;
        color: var(--cream-700);
    }

    .info-value {
        color: var(--cream-900);
    }

    @media (max-width: 768px) {
        .edit-container {
            padding: 1.5rem;
            margin: 0.5rem;
            width: calc(100% - 1rem);
        }
        
        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn {
            width: 100%;
        }

        .edit-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="edit-container">
        <h1 class="edit-title">Editar Usuario Administrador</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Información actual del usuario -->
        <div class="user-info">
            <h3><i class="fas fa-info-circle"></i> Información Actual</h3>
            <!-- <div class="info-item">
                <span class="info-label">ID:</span>
                <span class="info-value"><?= htmlspecialchars($user['idUser'] ?? '') ?></span>
            </div> -->
            <div class="info-item">
                <span class="info-label">Rol:</span>
                <span class="info-value">
                    <?= (int)($user['idRol'] ?? 0) === 1 ? 'Administrador' : 'Usuario' ?>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Estado:</span>
                <span class="info-value">
                    <?= (int)($user['status'] ?? 0) === 1 ? 'Activo' : 'Inactivo' ?>
                </span>
            </div>
        </div>
        
        <form method="POST" action="<?= u('users/edit/' . urlencode((string)($user['idUser'] ?? ''))) ?>">
            <div class="form-group">
                <label for="login">Login / Cédula de Identidad</label>
                <input type="text" 
                       name="login" 
                       id="login" 
                       value="<?= htmlspecialchars($user['login'] ?? '') ?>"
                       placeholder="Ingrese el login o cédula" 
                       required>
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                       placeholder="Ingrese el correo electrónico" 
                       required>
            </div>

            <!-- <div class="form-group">
                <label for="password">Nueva Contraseña (Opcional)</label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       placeholder="Dejar vacío para mantener la actual">
                <div class="password-help">
                    Solo complete este campo si desea cambiar la contraseña del usuario
                </div>
            </div> -->
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Usuario
                </button>
                <a href="<?= u('users/list') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Validación básica del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const loginInput = document.getElementById('login');
    const emailInput = document.getElementById('email');

    form.addEventListener('submit', function(e) {
        let errors = [];

        // Validar login
        if (loginInput.value.trim().length < 3) {
            errors.push('El login debe tener al menos 3 caracteres');
        }

        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value.trim())) {
            errors.push('El email debe ser válido');
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert('Errores:\n- ' + errors.join('\n- '));
        }
    });
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>