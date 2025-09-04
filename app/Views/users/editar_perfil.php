<?php
// Variables del layout
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

// Iniciar el buffer de salida para el contenido
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-user-edit"></i> Solicitar Cambio de Datos
                    </h4>
                    <div class="card-tools">
                        <a href="<?= u('users/profile') ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Perfil
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= u('partnerOnline/createRequest') ?>">
                        <div class="profile-content">
                            <!-- Información Personal -->
                            <div class="profile-section bg-white rounded-lg shadow-sm border border-gray-200">
                                <div class="section-header border-b border-gray-100 pb-3 mb-4">
                                    <h3 class="section-title text-xl font-semibold text-gray-800">
                                        <i class="fas fa-user-edit text-indigo-500 mr-2"></i>
                                        Solicitar Modificación de Datos
                                    </h3>
                                </div>
                                <div class="profile-grid gap-6">
                                    <!-- Campos editables para el usuario -->
                                    <div class="profile-field">
                                        <label for="name" class="field-label required">Nombre Completo</label>
                                        <input type="text" class="field-value-edit" id="name" name="name" required
                                               value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                                               placeholder="Ingresa tu nombre completo">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="ci" class="field-label required">Cédula de Identidad</label>
                                        <input type="text" class="field-value-edit" id="ci" name="ci" required
                                               value="<?= htmlspecialchars($user['CI'] ?? '') ?>"
                                               placeholder="Ej: 12345678">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="cellPhoneNumber" class="field-label required">Teléfono</label>
                                        <input type="tel" class="field-value-edit" id="cellPhoneNumber" name="cellPhoneNumber" required
                                               value="<?= htmlspecialchars($user['cellPhoneNumber'] ?? '') ?>"
                                               placeholder="Ej: 04121234567">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="address" class="field-label">Dirección</label>
                                        <textarea class="field-value-edit" id="address" name="address" rows="3"
                                                  placeholder="Ingresa tu dirección completa"><?= 
                                            htmlspecialchars($user['address'] ?? '') 
                                        ?></textarea>
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="birthday" class="field-label">Fecha de Nacimiento</label>
                                        <input type="date" class="field-value-edit" id="birthday" name="birthday" 
                                               value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="profile-field">
                                        <label for="email" class="field-label required">Correo Electrónico</label>
                                        <input type="email" class="field-value-edit" id="email" name="email" required
                                               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                               placeholder="tucorreo@ejemplo.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="profile-field mt-6 pt-4 border-t border-gray-100">
                                <div class="profile-actions flex justify-end space-x-3">
                                    <a href="<?= u('users/profile') ?>" class="btn btn-secondary hover:bg-gray-100">
                                        <i class="fas fa-times mr-2"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary hover:bg-indigo-600 transition-colors">
                                        <i class="fas fa-save mr-2"></i> Enviar Solicitud
                                    </button>
                                </div>
                            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Obtener el contenido del buffer
$content = ob_get_clean();

// Incluir el archivo de layout
include __DIR__ . '/../layouts/app.php';
?>
