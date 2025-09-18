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
$title = 'Editar ' . (($user['idRol'] ?? 2) == 2 ? 'Socio' : 'Usuario');
$currentPath = 'partner/edit';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Socios', 'url' => u('partner/list')],
    ['label' => 'Editar ' . (($user['idRol'] ?? 2) == 2 ? 'Socio' : 'Usuario'), 'url' => null],
];

// Start output buffering for the content
ob_start();
?>

<style>
    .edit-container {
        background: var(--surface);
        border-radius: var(--border-radius-lg);
        box-shadow: var(--shadow-md);
        padding: 2.5rem 3rem;
        margin: 1.5rem auto;
        max-width: 1000px;
        width: calc(100% - 3rem);
        border: 1px solid var(--border);
    }

    .edit-title {
        color: var(--cream-900);
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 2rem 0;
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
        margin-bottom: 1.75rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--cream-800);
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 1rem 1.25rem;
        border: 1px solid var(--cream-300);
        border-radius: 8px;
        font-size: 1rem;
        transition: var(--transition);
        background-color: var(--cream-50);
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cream-600);
        box-shadow: 0 0 0 3px rgba(156, 143, 122, 0.15);
        background-color: var(--surface);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 1.5rem;
    }

    .form-section {
        margin-bottom: 3rem;
    }

    .section-title {
        font-size: 1.4rem;
        color: var(--cream-800);
        margin: 0 0 1.75rem 0;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--cream-200);
        font-family: 'Playfair Display', serif;
        position: relative;
    }

    .section-title:after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 60px;
        height: 2px;
        background: var(--cream-600);
    }

    .partner-fields {
        background: var(--cream-50);
        border-radius: 12px;
        padding: 2.5rem;
        margin: 3rem 0;
        border: 1px solid var(--cream-200);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .btn-submit {
        background: var(--cream-600);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 200px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        height: 48px;
    }

    .btn-cancel {
        background: #fef2f2;
        color: #7f1d1d;
        border: 1px solid #dc2626;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        min-width: 200px;
        height: 48px;
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
    }

    .btn-cancel:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    }

    .btn-submit:hover {
        background: var(--cream-700);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .button-group {
        display: flex;
        gap: 1.25rem;
        margin-top: 2.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .error-message {
        background: #fef2f2;
        color: #b91c1c;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin: 0 0 2.5rem 0;
        border-left: 4px solid #ef4444;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.05rem;
    }

    @media (max-width: 1200px) {
        .edit-container {
            padding: 2.25rem;
            margin: 1.25rem auto;
            width: calc(100% - 2.5rem);
        }
    }

    @media (max-width: 992px) {
        .edit-container {
            padding: 2rem;
            margin: 1rem auto;
            width: calc(100% - 2rem);
        }
    }

    @media (max-width: 768px) {
        .edit-container {
            padding: 1.75rem 1.5rem;
            margin: 1rem 0.75rem;
            width: calc(100% - 1.5rem);
            border-radius: 12px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .partner-fields {
            padding: 1.75rem 1.5rem;
            margin: 2.5rem 0;
        }

        .edit-title {
            font-size: 1.75rem;
            margin-bottom: 1.75rem;
        }

        .section-title {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .button-group {
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn-submit,
        .btn-cancel {
            width: 100%;
            margin: 0;
        }
    }
</style>

<div class="content-wrapper">
    <div class="edit-container">
        <h1 class="edit-title">Editar <?= ($user['idRol'] ?? 2) == 2 ? 'Socio' : 'Usuario' ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= rtrim(BASE_URL, '/') ?>/partner/edit/<?= htmlspecialchars($partner['idPartner'] ?? $user['idUser']) ?>">
            <input type="hidden" name="id" value="<?= htmlspecialchars($partner['idPartner'] ?? $user['idUser']) ?>">
            
            <div class="form-section">
                <h2 class="section-title">Datos de Acceso</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="idRole">Tipo de Usuario</label>
                        <select name="idRole" id="idRole" onchange="togglePartnerFields()" required>
                            <option value="1" <?= ($user['idRol'] ?? 2) == 1 ? 'selected' : '' ?>>Administrador</option>
                            <option value="2" <?= ($user['idRol'] ?? 2) == 2 ? 'selected' : '' ?>>Socio</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="login">Login (C.I.)</label>
                        <input type="text" name="login" id="login" value="<?= htmlspecialchars($user['login'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="partner-fields">
                <div class="form-section">
                    <h2 class="section-title">Información del Socio</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nombre Completo</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($partner['name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ci">Cédula de Identidad</label>
                            <input type="text" name="ci" id="ci" value="<?= htmlspecialchars($partner['ci'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cellPhoneNumber">Número de Celular</label>
                            <input type="tel" name="cellPhoneNumber" id="cellPhoneNumber" value="<?= htmlspecialchars($partner['cellPhoneNumber'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <input type="text" name="address" id="address" value="<?= htmlspecialchars($partner['address'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthday">Fecha de Nacimiento</label>
                            <input type="date" name="birthday" id="birthday" value="<?= htmlspecialchars($partner['birthday'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="dateRegistration">Fecha de Registro</label>
                            <input type="date" name="dateRegistration" id="dateRegistration" value="<?= htmlspecialchars($partner['dateRegistration'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?= u('partner/list') ?>" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePartnerFields() {
    const roleSelect = document.getElementById('idRole');
    const partnerFields = document.querySelector('.partner-fields');
    const partnerInputs = partnerFields.querySelectorAll('input');
    
    if (roleSelect.value === '2') { // Socio
        partnerFields.style.display = 'block';
        partnerInputs.forEach(input => input.required = true);
    } else {
        partnerFields.style.display = 'none';
        partnerInputs.forEach(input => input.required = false);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    togglePartnerFields();
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>