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

// Variables para el layout
$title = 'Cambiar contraseña';
$currentPath = 'users/change-password';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Mi Perfil', 'url' => u('users/profile')],
    ['label' => 'Cambiar contraseña', 'url' => null],
];

// Estilos directos para asegurar que se apliquen
$customStyles = "
<style>
.change-password-page {
    max-width: 600px;
    margin: 40px auto;
    padding: 30px;
    background-color: #a49884;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
.change-password-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    padding: 30px;
}
.change-password-header {
    text-align: center;
    margin-bottom: 30px;
}
.change-password-header i {
    font-size: 2.5rem;
    color: #a49884;
    margin-bottom: 15px;
}
.change-password-header h3 {
    color: #5a5c69;
    font-size: 1.5rem;
    margin-bottom: 10px;
}
.change-password-header p {
    color: #6c757d;
    margin-bottom: 0;
}
.password-rules {
    background-color: #f8f9fc;
    border-left: 4px solid #a49884;
    padding: 15px;
    margin: 20px 0;
    border-radius: 0 4px 4px 0;
}
.password-rules h6 {
    color: #a49884;
    margin-top: 0;
    font-weight: 600;
}
.password-rules ul {
    padding-left: 20px;
    margin-bottom: 0;
}
.password-rules li {
    margin-bottom: 5px;
    color: #5a5c69;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    display: block;
}
.form-control {
    border-radius: 0.35rem;
    padding: 0.75rem 1rem 0.75rem 1rem;
    border: 1px solid #d1d3e2;
    width: 100%;
    transition: all 0.3s ease;
    padding-right: 40px; /* Space for the eye icon */
}

.input-group {
    position: relative;
    width: 100%;
}

.input-group-append {
    position: absolute;
    right: 0;
    top: 0;
    height: 100%;
    display: flex;
    align-items: center;
    padding-right: 10px;
    pointer-events: none; /* Allow clicks to pass through to the button */
}

.toggle-password {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    pointer-events: auto; /* Re-enable pointer events for the button */
}

.toggle-password i {
    color: #6c757d;
    font-size: 1rem;
    transition: color 0.2s;
}

.toggle-password:hover i {
    color: #5a5c69;
}
.form-control:focus {
    border-color: #d1c9bd;
    box-shadow: 0 0 0 0.2rem rgba(164, 152, 132, 0.3);
}
.text-muted {
    color: #858796 !important;
    font-size: 0.85rem;
    margin-top: 0.25rem;
    display: block;
}
.btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
    line-height: 1.5;
    border-radius: 0.35rem;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
    text-decoration: none;
}
.btn-primary {
    color: #fff;
    background-color: #a49884;
    border-color: #a49884;
}
.btn-primary:hover {
    background-color: #8c7d6b;
    border-color: #8c7d6b;
}
.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}
.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}
.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.35rem;
}
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}
.d-none {
    display: none !important;
}
.d-flex {
    display: flex !important;
}
.flex-wrap {
    flex-wrap: wrap !important;
}
.justify-content-between {
    justify-content: space-between !important;
}
.mb-4 {
    margin-bottom: 1.5rem !important;
}
.gap-3 {
    gap: 1rem !important;
}
.w-100 {
    width: 100% !important;
}
@media (max-width: 768px) {
    .change-password-page {
        margin: 20px 15px;
        padding: 20px 15px;
    }
    .change-password-card {
        padding: 20px 15px;
    }
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    .d-flex {
        flex-direction: column;
    }
    .form-control {
        padding-right: 40px; /* Keep space for the eye icon on mobile */
    }
}
</style>
";

ob_start();
?>

<?= $customStyles ?>

<div class="change-password-page">
    <div class="change-password-card">
        <div class="change-password-header">
            <i class="fas fa-shield-alt"></i>
            <h3>Cambiar contraseña</h3>
            <p>Por favor ingrese su contraseña actual y la nueva contraseña</p>
        </div>

        <div id="pw-error" class="alert alert-danger d-none"></div>
        <div id="pw-success" class="alert alert-success d-none"></div>

        <form id="changePasswordForm" onsubmit="event.preventDefault(); submitPasswordChangePage();">
            <div class="form-group">
                <label for="pw_current">Contraseña actual</label>
                <div class="input-group">
                    <input type="password" id="pw_current" class="form-control" required 
                           autocomplete="current-password" placeholder="Ingrese su contraseña actual" />
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#pw_current">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="pw_new">Nueva contraseña</label>
                <div class="input-group">
                    <input type="password" id="pw_new" class="form-control" required 
                           autocomplete="new-password" placeholder="Ingrese su nueva contraseña" />
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#pw_new">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <small class="text-muted">8-12 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y 1 símbolo.</small>
            </div>

            <div class="form-group">
                <label for="pw_confirm">Confirmar nueva contraseña</label>
                <div class="input-group">
                    <input type="password" id="pw_confirm" class="form-control" required 
                           autocomplete="new-password" placeholder="Confirme su nueva contraseña" />
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#pw_confirm">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="password-rules">
                <h6>Requisitos de la contraseña:</h6>
                <ul>
                    <li>Entre 8 y 12 caracteres</li>
                    <li>Al menos una letra mayúscula (A-Z)</li>
                    <li>Al menos una letra minúscula (a-z)</li>
                    <li>Al menos un número (0-9)</li>
                    <li>Al menos un símbolo especial (ej. !@#$%^&*)</li>
                </ul>
            </div>

            <div class="d-flex flex-wrap justify-content-between gap-3">
                <a href="<?= u('users/profile') ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al perfil
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Función para alternar visibilidad de contraseña
function togglePasswordVisibility(button) {
    const targetId = button.getAttribute('data-target');
    const input = document.querySelector(targetId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Agregar event listeners a los botones de mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips de Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover focus'
        });
    });

    // Manejar el clic en el botón de mostrar/ocultar contraseña
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            togglePasswordVisibility(this);
        });
        
        // Agregar tooltip
        button.setAttribute('data-bs-toggle', 'tooltip');
        button.setAttribute('title', 'Mostrar/ocultar contraseña');
    });
    
    // Actualizar tooltips después de cargar el DOM
    tooltipList.forEach(tooltip => tooltip._maybeEnable());
});

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

function submitPasswordChangePage() {
  hidePwAlerts();

  const current = document.getElementById('pw_current').value.trim();
  const pw = document.getElementById('pw_new').value.trim();
  const cf = document.getElementById('pw_confirm').value.trim();

  // Validación rápida en cliente (igual que el modal)
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
      window.location.href = '<?= u('users/profile') ?>';
    }, 1200);
  })
  .catch(() => {
    showPwAlert('pw-error', 'Error de red al intentar actualizar la contraseña');
  });
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
