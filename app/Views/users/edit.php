<?php
// Set up variables for the layout
$title = 'Editar Usuario';
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

     .form-group input,
    .form-group select,
    .form-group textarea {
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
            <!-- <div class="info-item">
                <span class="info-label">Rol:</span>
                <span class="info-value">
                    <?= (int)($user['idRol'] ?? 0) === 1 ? 'Administrador' : 'Usuario' ?>
                </span>
            </div> -->
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
            <div class="form-group">
                <label for="idRol">Rol</label>
                <select name="idRol" id="idRol" required>
                    <option value="">Seleccione un rol</option>
                    <?php foreach ($roles ?? [] as $rol): ?>
                        <option value="<?= htmlspecialchars((string)$rol['idRol']) ?>" 
                                <?= ((int)$rol['idRol'] === (int)($user['idRol'] ?? 0)) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rol['rol']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
// Validación en tiempo real para formulario de editar usuario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const loginInput = document.getElementById('login');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('idRol');
    const passwordInput = document.getElementById('password');

    // Validar login en tiempo real
    if (loginInput) {
        loginInput.addEventListener('input', function() {
            // Permitir solo números y letras (sin espacios)
            this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');
            
            if (this.value.length > 0 && this.value.length < 3) {
                showError(this, 'El login debe tener al menos 3 caracteres');
            } else if (this.value.length > 20) {
                this.value = this.value.substring(0, 20);
                showError(this, 'El login no puede tener más de 20 caracteres');
            } else {
                clearError(this);
            }
        });

        loginInput.addEventListener('blur', function() {
            if (this.value.trim().length === 0) {
                showError(this, 'El login es requerido');
            }
        });
    }

    // Validar email en tiempo real
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (this.value.length > 0 && !emailRegex.test(this.value)) {
                showError(this, 'Ingrese un correo electrónico válido');
            } else {
                clearError(this);
            }
        });

        emailInput.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (this.value.trim().length === 0) {
                showError(this, 'El correo electrónico es requerido');
            } else if (!emailRegex.test(this.value)) {
                showError(this, 'El formato del correo no es válido');
            }
        });
    }

    // Validar rol
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            if (this.value === '' || this.value === '0') {
                showError(this, 'Debe seleccionar un rol');
            } else {
                clearError(this);
            }
        });
    }

    // Validar contraseña (si está habilitada)
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            // Solo validar si se está ingresando una contraseña
            if (this.value.length > 0) {
                if (this.value.length < 6) {
                    showError(this, 'La contraseña debe tener al menos 6 caracteres');
                } else if (this.value.length > 50) {
                    this.value = this.value.substring(0, 50);
                    showError(this, 'La contraseña no puede tener más de 50 caracteres');
                } else if (!/[A-Z]/.test(this.value)) {
                    showError(this, 'La contraseña debe contener al menos una mayúscula');
                } else if (!/[a-z]/.test(this.value)) {
                    showError(this, 'La contraseña debe contener al menos una minúscula');
                } else if (!/[0-9]/.test(this.value)) {
                    showError(this, 'La contraseña debe contener al menos un número');
                } else {
                    clearError(this);
                }
            } else {
                clearError(this);
            }
        });
    }

    // Validación al enviar el formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];

        // Validar login
        if (loginInput) {
            const loginValue = loginInput.value.trim();
            
            if (loginValue.length === 0) {
                errors.push('El login es requerido');
                isValid = false;
                showError(loginInput, 'Campo requerido');
            } else if (loginValue.length < 3) {
                errors.push('El login debe tener al menos 3 caracteres');
                isValid = false;
                showError(loginInput, 'Login muy corto');
            } else if (loginValue.length > 20) {
                errors.push('El login no puede tener más de 20 caracteres');
                isValid = false;
                showError(loginInput, 'Login muy largo');
            } else if (!/^[a-zA-Z0-9]+$/.test(loginValue)) {
                errors.push('El login solo puede contener letras y números');
                isValid = false;
                showError(loginInput, 'Caracteres no válidos');
            }
        }

        // Validar email
        if (emailInput) {
            const emailValue = emailInput.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (emailValue.length === 0) {
                errors.push('El correo electrónico es requerido');
                isValid = false;
                showError(emailInput, 'Campo requerido');
            } else if (!emailRegex.test(emailValue)) {
                errors.push('El correo electrónico no es válido');
                isValid = false;
                showError(emailInput, 'Email inválido');
            } else if (emailValue.length > 100) {
                errors.push('El correo electrónico es demasiado largo');
                isValid = false;
                showError(emailInput, 'Email muy largo');
            }
        }

        // Validar rol
        if (roleSelect) {
            const roleValue = roleSelect.value;
            
            if (roleValue === '' || roleValue === '0') {
                errors.push('Debe seleccionar un rol');
                isValid = false;
                showError(roleSelect, 'Seleccione un rol');
            }
        }

        // Validar contraseña (solo si se está cambiando)
        if (passwordInput && passwordInput.value.length > 0) {
            const passwordValue = passwordInput.value;
            
            if (passwordValue.length < 6) {
                errors.push('La contraseña debe tener al menos 6 caracteres');
                isValid = false;
                showError(passwordInput, 'Contraseña muy corta');
            } else if (passwordValue.length > 50) {
                errors.push('La contraseña no puede tener más de 50 caracteres');
                isValid = false;
                showError(passwordInput, 'Contraseña muy larga');
            } else if (!/[A-Z]/.test(passwordValue)) {
                errors.push('La contraseña debe contener al menos una mayúscula');
                isValid = false;
                showError(passwordInput, 'Falta mayúscula');
            } else if (!/[a-z]/.test(passwordValue)) {
                errors.push('La contraseña debe contener al menos una minúscula');
                isValid = false;
                showError(passwordInput, 'Falta minúscula');
            } else if (!/[0-9]/.test(passwordValue)) {
                errors.push('La contraseña debe contener al menos un número');
                isValid = false;
                showError(passwordInput, 'Falta número');
            }
        }

        if (!isValid) {
            e.preventDefault();
            
            // Mostrar resumen de errores
            const errorSummary = document.createElement('div');
            errorSummary.className = 'error-message';
            errorSummary.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Por favor corrija los siguientes errores:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        ${errors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                </div>
            `;
            
            // Eliminar mensajes de error anteriores
            const oldError = document.querySelector('.error-message');
            if (oldError) {
                oldError.remove();
            }
            
            // Insertar el nuevo mensaje de error
            const editTitle = document.querySelector('.edit-title');
            editTitle.parentNode.insertBefore(errorSummary, editTitle.nextSibling);
            
            // Scroll al primer error
            errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // Prevenir espacios en el campo de login
    if (loginInput) {
        loginInput.addEventListener('keydown', function(e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
    }

    // Trimear email al perder el foco
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            this.value = this.value.trim();
        });
    }
});

function showError(input, message) {
    // Remover error anterior si existe
    clearError(input);
    
    // Agregar clase de error al input
    input.style.borderColor = '#dc2626';
    input.style.backgroundColor = '#fef2f2';
    
    // Crear mensaje de error
    const errorMsg = document.createElement('small');
    errorMsg.className = 'field-error';
    errorMsg.style.color = '#dc2626';
    errorMsg.style.fontSize = '0.875rem';
    errorMsg.style.marginTop = '0.25rem';
    errorMsg.style.display = 'block';
    errorMsg.style.fontWeight = '500';
    errorMsg.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
    
    // Insertar mensaje después del input
    input.parentNode.appendChild(errorMsg);
}

function clearError(input) {
    // Restaurar estilos del input
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    // Remover mensaje de error si existe
    const errorMsg = input.parentNode.querySelector('.field-error');
    if (errorMsg) {
        errorMsg.remove();
    }
}

// Confirmar antes de salir si hay cambios sin guardar
let formChanged = false;
const form = document.querySelector('form');
const inputs = form.querySelectorAll('input, select');

inputs.forEach(input => {
    input.addEventListener('change', function() {
        formChanged = true;
    });
});

// Advertir al usuario si intenta salir sin guardar
window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

// No advertir si se envía el formulario
form.addEventListener('submit', function() {
    formChanged = false;
});

// No advertir si se hace clic en el botón de volver
const backButton = document.querySelector('.btn-secondary');
if (backButton) {
    backButton.addEventListener('click', function(e) {
        if (formChanged) {
            if (!confirm('¿Está seguro de que desea salir? Los cambios no guardados se perderán.')) {
                e.preventDefault();
            }
        }
    });
}
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>