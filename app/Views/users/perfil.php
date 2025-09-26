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
    $changeRequestModel = new UserChangeRequest();
    $pendingChanges = $changeRequestModel->getPendingByPartner($user['idPartner']);
}
*/
// Verificar si hay un mensaje de éxito en la sesión (para la notificación de solicitud enviada)
$showSuccessNotification = false;
$successMessageText = '';
if (isset($_SESSION['success'])) {
    $showSuccessNotification = true;
    $successMessageText = $_SESSION['success'];
    unset($_SESSION['success']); // Limpiar el mensaje después de mostrarlo
}

// Start output buffering for the content
ob_start();
?>

<?php if ($showSuccessNotification): ?>
    <div class="alert alert-success">
        <strong>Éxito!</strong> <?= htmlspecialchars($successMessageText) ?>
    </div>
<?php endif; ?>

<?php if (!empty($successMessage) && !$showSuccessNotification): ?>
    <div class="alert alert-success">
        <strong>Éxito!</strong> <?= htmlspecialchars($successMessage) ?>
    </div>
<?php endif; ?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar-section">
        <div class="profile-avatar-large">
            <?= strtoupper(substr($_SESSION['username'] ?? 'AU', 0, 2)) ?>
        </div>
        <div class="profile-actions">
            <?php if (!$isAdmin): ?>
            <a href="<?= u('users/profile/edit') ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Editar Perfil
            </a>
            <?php endif; ?>
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
                <!-- Vista para administradores (sin cambios) -->
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
                <!-- Vista para socios (con capacidad de edición) -->
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
                    <a href="<?= u('users/change-password') ?>" class="btn btn-outline">
  <i class="fas fa-edit"></i>
  Cambiar Contraseña
</a>
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

<!-- Modal para cambio de contraseña
<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changePasswordModalLabel">Cambiar contraseña</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="pw-error" class="alert alert-danger d-none"></div>
        <div id="pw-success" class="alert alert-success d-none"></div>

        <div class="form-group">
          <label>Contraseña actual</label>
          <input type="password" id="pw_current" class="form-control" autocomplete="current-password" />
        </div>

        <div class="form-group">
          <label>Nueva contraseña</label>
          <input type="password" id="pw_new" class="form-control" autocomplete="new-password" />
          <small class="form-text text-muted">8-12 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y 1 símbolo.</small>
        </div>

        <div class="form-group">
          <label>Confirmar nueva contraseña</label>
          <input type="password" id="pw_confirm" class="form-control" autocomplete="new-password" />
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" onclick="submitPasswordChange()">Guardar</button>
      </div>
    </div>
  </div>
</div> -->

<script>
// Variables para almacenar los cambios
let changes = {};

function editProfile() {
    // Mostrar campos de edición
    document.querySelectorAll('.field-value').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll('.field-edit').forEach(el => {
        el.style.display = 'block';
    });
    document.querySelector('.edit-actions').style.display = 'block';
}

function cancelEdit() {
    // Ocultar campos de edición
    document.querySelectorAll('.field-value').forEach(el => {
        el.style.display = 'block';
    });
    document.querySelectorAll('.field-edit').forEach(el => {
        el.style.display = 'none';
    });
    document.querySelector('.edit-actions').style.display = 'none';
    changes = {};
}

function collectChanges() {
    changes = {};
    
    // Verificar cada campo editable
    const fields = [
        {id: 'name', field: 'name', value: document.getElementById('edit-name').value},
        {id: 'ci', field: 'CI', value: document.getElementById('edit-ci').value},
        {id: 'phone', field: 'cellPhoneNumber', value: document.getElementById('edit-phone').value},
        {id: 'address', field: 'address', value: document.getElementById('edit-address').value},
        {id: 'birthday', field: 'birthday', value: document.getElementById('edit-birthday').value}
    ];
    
    fields.forEach(item => {
        const currentValue = document.getElementById(`field-${item.id}`).textContent.trim();
        if (item.value !== currentValue) {
            changes[item.field] = {
                old: currentValue,
                new: item.value
            };
        }
    });
    
    return changes;
}

function saveChanges() {
    const changes = collectChanges();
    
    if (Object.keys(changes).length === 0) {
        alert('No has realizado ningún cambio.');
        return;
    }
    
    // Mostrar resumen de cambios en el modal
    let summaryHtml = '<ul>';
    for (const field in changes) {
        const fieldName = getFieldDisplayName(field);
        summaryHtml += `<li><strong>${fieldName}:</strong> "${changes[field].old}" → "${changes[field].new}"</li>`;
    }
    summaryHtml += '</ul>';
    
    document.getElementById('changes-summary').innerHTML = summaryHtml;
    $('#confirmChangesModal').modal('show');
}

function getFieldDisplayName(field) {
    const fieldNames = {
        'name': 'Nombre completo',
        'CI': 'Cédula de identidad',
        'cellPhoneNumber': 'Teléfono',
        'address': 'Dirección',
        'birthday': 'Fecha de nacimiento'
    };
    
    return fieldNames[field] || field;
}

function submitChanges() {
    $('#confirmChangesModal').modal('hide');
    
    // Enviar cambios al servidor
    fetch('<?= u('users/request-changes') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            changes: changes
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Solicitud de cambios enviada correctamente. Espera la aprobación del administrador.');
            location.reload();
        } else {
            alert('Error al enviar la solicitud: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al enviar la solicitud.');
    });
}

function changePassword() {
  // Abrir modal de cambio de contraseña
  $('#changePasswordModal').modal('show');
}

function showPwAlert(id, message) {
  const el = document.getElementById(id);
  el.textContent = message;
  el.classList.remove('d-none');
}

function hidePwAlerts() {
  ['pw-error', 'pw-success'].forEach(id => {
    const el = document.getElementById(id);
    el.classList.add('d-none');
    el.textContent = '';
  });
}

function submitPasswordChange() {
  hidePwAlerts();

  const current = document.getElementById('pw_current').value.trim();
  const pw = document.getElementById('pw_new').value.trim();
  const cf = document.getElementById('pw_confirm').value.trim();

  // Validación rápida en cliente
  const reLen = /^.{8,12}$/; const reUp=/[A-Z]/; const reLo=/[a-z]/; const reNum=/[0-9]/; const reSym=/[^A-Za-z0-9]/;
  if (!current) { showPwAlert('pw-error', 'La contraseña actual es obligatoria'); return; }
  if (!reLen.test(pw) || !reUp.test(pw) || !reLo.test(pw) || !reNum.test(pw) || !reSym.test(pw)) {
    showPwAlert('pw-error', 'La nueva contraseña no cumple con los requisitos');
    return;
  }
  if (pw !== cf) { showPwAlert('pw-error', 'Las contraseñas no coinciden'); return; }

  fetch('<?= u('users/change-password-profile') ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ current_password: current, new_password: pw, confirm_password: cf })
  })
  .then(async (res) => {
    const data = await res.json().catch(() => ({ success: false, message: 'Error inesperado' }));
    if (!res.ok || !data.success) {
      showPwAlert('pw-error', data.message || 'No se pudo actualizar la contraseña');
      return;
    }
    showPwAlert('pw-success', data.message || 'Contraseña actualizada correctamente');
    setTimeout(() => {
      $('#changePasswordModal').modal('hide');
      document.getElementById('pw_current').value = '';
      document.getElementById('pw_new').value = '';
      document.getElementById('pw_confirm').value = '';
      hidePwAlerts();
    }, 1500);
  })
  .catch(() => {
    showPwAlert('pw-error', 'Error de red al intentar actualizar la contraseña');
  });
}
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>