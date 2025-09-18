<?php
// Set up variables for the layout
$title = 'Crear Socio - Asociación de Artistas';
$currentPath = 'partner/create';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Socios', 'url' => u('partner/list')],
    ['label' => 'Crear Socio', 'url' => null],
];

// Start output buffering
ob_start();
?>
<style>
    .create-container {
        background: var(--surface);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        padding: 2.5rem 3rem;
        margin: 1rem auto;
        max-width: 1200px;
        width: calc(100% - 2rem);
        border: 1px solid var(--border);
    }

    .create-title {
        color: var(--cream-900);
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 2.5rem 0;
        text-align: center;
        position: relative;
        padding-bottom: 1.25rem;
        font-family: 'Playfair Display', serif;
    }

    .create-title:after {
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

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cream-600);
        box-shadow: 0 0 0 3px rgba(156, 143, 122, 0.1);
        background-color: var(--surface);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .form-section {
        margin-bottom: 2.5rem;
    }

    .section-title {
        font-size: 1.4rem;
        color: var(--cream-800);
        margin: 0 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--cream-200);
        font-family: 'Playfair Display', serif;
    }

    .partner-fields {
        background: var(--cream-50);
        border-radius: 12px;
        padding: 2rem 2.5rem;
        margin: 2.5rem 0;
        border: 1px solid var(--cream-200);
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
        transition: var(--transition);
        width: auto;
        min-width: 220px;
        margin: 2rem auto 0;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
    }

    .btn-submit:hover {
        background: var(--cream-700);
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

    @media (max-width: 1200px) {
        .create-container {
            padding: 2rem;
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    }

    @media (max-width: 768px) {
        .create-container {
            padding: 1.5rem;
            margin: 0.5rem;
            width: calc(100% - 1rem);
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .partner-fields {
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .create-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="create-container">
        <h1 class="create-title">Crear Socio o Usuario</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/partner/create">
            <div class="form-section">
                <h2 class="section-title">Datos de Acceso</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="idRole">Tipo de Usuario</label>
                        <select name="idRole" id="idRole" onchange="togglePartnerFields()" required>
                            <option value="1">Administrador</option>
                            <option value="2" selected>Socio</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="partner-fields">
                <div class="form-section">
                    <h2 class="section-title">Información Personal</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nombre Completo</label>
                            <input type="text" name="name" id="name" placeholder="Nombre y apellido" required>
                        </div>
                        <div class="form-group">
                            <label for="ci">Cédula de Identidad (será tu login)</label>
                            <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico</label>
                            <input type="email" name="email" id="email" placeholder="Ingrese su correo electrónico" required>
                        </div>
                        <div class="form-group">
                            <label for="cellPhoneNumber">Número de Celular</label>
                            <input type="tel" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: +591 65734215" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Dirección</label>
                            <input type="text" name="address" id="address" placeholder="Dirección completa" required>
                        </div>
                        <div class="form-group">
                            <label for="birthday">Fecha de Nacimiento</label>
                            <input type="date" name="birthday" id="birthday" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Crear Usuario
            </button>
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