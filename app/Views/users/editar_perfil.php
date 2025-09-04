<?php
// Helpers
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}

// Set up variables for the layout
$title = 'Editar Perfil';
$currentPath = 'users/profile/edit';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Mi Perfil', 'url' => u('users/profile')],
    ['label' => 'Editar Perfil', 'url' => null],
];

// Obtener datos del usuario
$user = !empty($users) ? $users[0] : [];
$isAdmin = ($_SESSION['role'] ?? 1) == 1;

// Start output buffering for the content
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </h4>
                    <div class="card-tools">
                        <a href="<?= u('users/profile') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Perfil
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form id="profileEditForm" onsubmit="return saveProfile(event)">
                        <div class="profile-content">
                            <!-- Información Personal -->
                            <div class="profile-section bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="section-header border-b border-gray-100 pb-3 mb-4">
                                    <h3 class="section-title text-xl font-semibold text-gray-800">
                                        <i class="fas fa-user-edit text-indigo-500 mr-2"></i>
                                        Editar Información Personal
                                    </h3>
                                </div>
                                <div class="profile-grid gap-6">
                                    <?php if (!$isAdmin): ?>
                                    <div class="profile-field">
                                        <label for="editName" class="field-label required">Nombre Completo</label>
                                        <input type="text" class="field-value-edit" id="editName" name="name" required
                                               value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                               placeholder="Ingresa tu nombre completo">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="editCI" class="field-label required">Cédula de Identidad</label>
                                        <input type="text" class="field-value-edit" id="editCI" name="CI" required
                                               value="<?= htmlspecialchars($user['CI'] ?? '') ?>"
                                               placeholder="Ej: 12345678">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="editPhone" class="field-label required">Teléfono</label>
                                        <input type="tel" class="field-value-edit" id="editPhone" name="cellPhoneNumber" required
                                               value="<?= htmlspecialchars($user['cellPhoneNumber'] ?? '') ?>"
                                               placeholder="Ej: 04121234567">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="editAddress" class="field-label">Dirección</label>
                                        <textarea class="field-value-edit" id="editAddress" name="address" rows="3"
                                                  placeholder="Ingresa tu dirección completa"><?= 
                                            htmlspecialchars($user['address'] ?? '') 
                                        ?></textarea>
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="editBirthday" class="field-label">Fecha de Nacimiento</label>
                                        <input type="date" class="field-value-edit" id="editBirthday" name="birthday" 
                                               value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="profile-field">
                                        <label for="editEmail" class="field-label required">Correo Electrónico</label>
                                        <input type="email" class="field-value-edit" id="editEmail" name="email" required
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                               placeholder="tucorreo@ejemplo.com">
                                    </div>
                                    
                                    <!-- Contraseña (opcional) -->
                                    <div class="profile-field">
                                        <label for="editPassword" class="field-label">Nueva Contraseña</label>
                                        <input type="password" class="field-value-edit" id="editPassword" name="password"
                                               placeholder="Dejar en blanco para no cambiar">
                                        <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
                                    </div>
                                    
                                    <div class="profile-field mt-6 pt-4 border-t border-gray-100">
                                        <div class="profile-actions flex justify-end space-x-3">
                                            <a href="<?= u('users/profile') ?>" class="btn btn-secondary hover:bg-gray-100">
                                                <i class="fas fa-times mr-2"></i> Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-primary hover:bg-indigo-600 transition-colors">
                                                <i class="fas fa-save mr-2"></i> Guardar Cambios
                                            </button>
                                        </div>
                                    </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para guardar los cambios del perfil
function saveProfile(event) {
    event.preventDefault();
    
    // Mostrar indicador de carga
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    
    // Simular envío del formulario
    setTimeout(() => {
        // Mostrar mensaje de éxito
        showAlert('¡Perfil actualizado correctamente!', 'success');
        
        // Redirigir al perfil después de 1.5 segundos
        setTimeout(() => {
            window.location.href = '<?= u('users/profile') ?>';
        }, 1500);
    }, 1000);
    
    return false;
}

// Función auxiliar para mostrar alertas
function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Insertar la alerta al principio del contenido
    document.querySelector('.content').insertAdjacentHTML('afterbegin', alertHtml);
    
    // Eliminar la alerta después de 3 segundos
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 3000);
}
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>
