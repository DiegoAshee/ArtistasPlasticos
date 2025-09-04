<?php
// Helpers por si no existen
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
$title = 'Mi Perfil';
$currentPath = 'users/profile';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Mi Perfil', 'url' => null],
];

// Obtener datos del usuario
$user = !empty($users) ? $users[0] : [];
$isAdmin = ($_SESSION['role'] ?? 1) == 1;

// Verificar si hay solicitudes pendientes
/*
$pendingChanges = [];
if (!$isAdmin && isset($user['idPartner'])) {
    require_once __DIR__ . '/../Models/UserChangeRequest.php';
    //$changeRequestModel = new UserChangeRequest();
    $pendingChanges = $changeRequestModel->getPendingByPartner($user['idPartner']);
}*/

// Start output buffering for the content
ob_start();
?>
<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar-section">
        <div class="profile-avatar-large">
            <?= strtoupper(substr($_SESSION['username'] ?? 'AU', 0, 2)) ?>
        </div>
        <div class="profile-actions">
            <button class="btn btn-primary" onclick="editProfile()">
                <i class="fas fa-edit"></i>
                Editar Perfil
            </button>
        </div>
    </div>
    <div class="profile-header-info">
        <h2 class="profile-name"><?= htmlspecialchars($user['name'] ?? $_SESSION['username'] ?? 'Admin Usuario') ?></h2>
        <p class="profile-role"><?= $isAdmin ? 'Administrador del Sistema' : 'Socio de la Asociación' ?></p>
        <p class="profile-email"><?= htmlspecialchars($user['email'] ?? $_SESSION['email'] ?? 'admin@asociacion.com') ?></p>
    </div>
</div>

<!-- Pending Changes Notification -->
<?php if (!empty($pendingChanges) && !$isAdmin): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    Tienes <strong><?= count($pendingChanges) ?></strong> solicitud(es) de cambio pendientes de aprobación por el administrador.
</div>
<?php endif; ?>

<!-- Profile Content -->
<div class="profile-content">
    <!-- Personal Information -->
    <div class="profile-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Información Personal
            </h3>
        </div>
        <div class="profile-grid">
            <?php if ($isAdmin): ?>
                <div class="profile-field">
                    <label class="field-label">Nombre de Usuario</label>
                    <div class="field-value"><?= htmlspecialchars($user['login'] ?? $_SESSION['username'] ?? 'admin') ?></div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Correo Electrónico</label>
                    <div class="field-value"><?= htmlspecialchars($user['email'] ?? $_SESSION['email'] ?? 'admin@asociacion.com') ?></div>
                </div>
                <div class="profile-field">
                    <label class="field-label">ID de Usuario</label>
                    <div class="field-value"><?= htmlspecialchars($user['idUser'] ?? $_SESSION['user_id'] ?? 'N/A') ?></div>
                </div>
            <?php else: ?>
                <div class="profile-field">
                    <label class="field-label">Nombre Completo</label>
                    <div class="field-value" id="field-name"><?= htmlspecialchars($user['name'] ?? 'No disponible') ?></div>
                    <div class="field-edit" style="display: none;">
                        <input type="text" id="edit-name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
                    </div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Cédula de Identidad</label>
                    <div class="field-value" id="field-ci"><?= htmlspecialchars($user['CI'] ?? 'No disponible') ?></div>
                    <div class="field-edit" style="display: none;">
                        <input type="text" id="edit-ci" class="form-control" value="<?= htmlspecialchars($user['CI'] ?? '') ?>">
                    </div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Teléfono</label>
                    <div class="field-value" id="field-phone"><?= htmlspecialchars($user['cellPhoneNumber'] ?? 'No disponible') ?></div>
                    <div class="field-edit" style="display: none;">
                        <input type="text" id="edit-phone" class="form-control" value="<?= htmlspecialchars($user['cellPhoneNumber'] ?? '') ?>">
                    </div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Dirección</label>
                    <div class="field-value" id="field-address"><?= htmlspecialchars($user['address'] ?? 'No disponible') ?></div>
                    <div class="field-edit" style="display: none;">
                        <textarea id="edit-address" class="form-control"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Fecha de Nacimiento</label>
                    <div class="field-value" id="field-birthday"><?= htmlspecialchars($user['birthday'] ?? 'No disponible') ?></div>
                    <div class="field-edit" style="display: none;">
                        <input type="date" id="edit-birthday" class="form-control" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
                    </div>
                </div>
                <div class="profile-field">
                    <label class="field-label">Fecha de Registro</label>
                    <div class="field-value"><?= htmlspecialchars($user['dateRegistration'] ?? 'No disponible') ?></div>
                </div>
            <?php endif; ?>
            <div class="profile-field">
                <label class="field-label">Nombre de Usuario</label>
                <div class="field-value"><?= htmlspecialchars($user['login'] ?? $_SESSION['username'] ?? 'admin') ?></div>
            </div>
            <div class="profile-field">
                <label class="field-label">Correo Electrónico</label>
                <div class="field-value"><?= htmlspecialchars($user['email'] ?? $_SESSION['email'] ?? 'admin@asociacion.com') ?></div>
            </div>
        </div>
        
        <!-- Edit Actions (solo para socios) -->
        <?php if (!$isAdmin): ?>
        <div class="edit-actions" style="display: none; margin-top: 20px;">
            <button class="btn btn-success" onclick="saveChanges()">
                <i class="fas fa-paper-plane"></i>
                Enviar solicitud de cambios
            </button>
            <button class="btn btn-secondary" onclick="cancelEdit()">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Security Information -->
    <div class="profile-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-shield-alt"></i>
                Información de Seguridad
            </h3>
        </div>
        <div class="security-info">
            <div class="security-item">
                <div class="security-icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="security-details">
                    <h4>Contraseña</h4>
                    <p>Última actualización: <?= date('d/m/Y') ?></p>
                    <button class="btn btn-outline" onclick="changePassword()">
                        <i class="fas fa-edit"></i>
                        Cambiar Contraseña
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($users)): ?>
    <div class="profile-section">
        <div class="section-header">
            <h3 class="section-title">
                <i class="fas fa-exclamation-triangle"></i>
                Información no disponible
            </h3>
        </div>
        <p>No se encontraron datos del usuario. Por favor, contacte al administrador.</p>
    </div>
    <?php endif; ?>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>