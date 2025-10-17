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

// Obtener datos del usuario desde la BD
$user = !empty($users) ? $users[0] : [];
$isAdmin = ($_SESSION['role'] ?? 1) == 1;

// Extraer valores de la base de datos usando los campos correctos de la tabla partner
$userName = $user['name'] ?? '';
$userCI = $user['ci'] ?? '';
$userPhone = $user['cellPhoneNumber'] ?? '';
$userAddress = $user['address'] ?? '';
$userBirthday = $user['birthday'] ?? '';
$userDateRegistration = $user['dateRegistration'] ?? '';
$userLogin = $user['login'] ?? $_SESSION['username'] ?? '';
$userEmail = $user['email'] ?? $_SESSION['email'] ?? '';

// Formatear fechas a dd-mm-aaaa
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') {
        return null;
    }
    return date('d-m-Y', strtotime($date));
}

$formattedBirthday = formatDate($userBirthday);
$formattedDateRegistration = formatDate($userDateRegistration);

// Verificar si hay un mensaje de éxito en la sesión
$showSuccessNotification = false;
$successMessageText = '';
if (isset($_SESSION['success'])) {
    $showSuccessNotification = true;
    $successMessageText = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Start output buffering for the content
ob_start();
?>

<style>
    :root {
        --primary: #4f46e5;
        --primary-dark: #4338ca;
        --primary-light: #e0e7ff;
        --danger: #ef4444;
        --warning: #f59e0b;
        --info: #3b82f6;
        --success: #10b981;
        --border: #e5e7eb;
        --secondary: #6b7280;
        --secondary-light: #f8fafc;
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .profile-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--success));
    }

    .profile-avatar-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        box-shadow: var(--shadow-lg);
    }

    .profile-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
    }

    .profile-actions .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .profile-actions .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-dark), #3730a3);
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .profile-header-info {
        text-align: left;
    }

    .profile-name {
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }

    .profile-role {
        font-size: 1.1rem;
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.5rem;
        background: var(--primary-light);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        display: inline-block;
    }

    .profile-email {
        font-size: 1.1rem;
        color: var(--secondary);
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .profile-email::before {
        content: '📧';
        font-size: 1.2rem;
    }

    .profile-content {
        max-width: 1000px;
        margin: 0 auto;
    }

    .profile-section {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .profile-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--success));
    }

    .profile-section:hover {
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
    }

    .section-header {
        border-bottom: 2px solid var(--primary-light);
        padding-bottom: 1rem;
        margin-bottom: 1.5rem;
    }

    .section-title {
        color: #1f2937;
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .section-title i {
        color: var(--primary);
        font-size: 1.75rem;
        background: var(--primary-light);
        padding: 0.75rem;
        border-radius: 12px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .profile-field {
        position: relative;
    }

    .field-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
        transition: color 0.3s ease;
    }

    .field-value {
        padding: 0.875rem 1rem;
        background: white;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 1rem;
        color: #1f2937;
        min-height: 50px;
        display: flex;
        align-items: center;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .text-muted {
        color: #9ca3af !important;
        font-style: italic;
    }

    .security-info {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .security-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: white;
        border: 2px solid var(--border);
        border-radius: 12px;
        transition: all 0.3s ease;
    }

    .security-item:hover {
        border-color: var(--primary);
        transform: translateY(-1px);
        box-shadow: var(--shadow);
    }

    .security-icon {
        width: 60px;
        height: 60px;
        background: var(--primary-light);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.5rem;
    }

    .security-details {
        flex: 1;
    }

    .security-details h4 {
        margin: 0 0 0.5rem 0;
        color: #1f2937;
        font-weight: 600;
    }

    .security-details p {
        margin: 0 0 1rem 0;
        color: var(--secondary);
    }

    .btn-outline {
        background: transparent;
        color: var(--primary);
        border: 2px solid var(--primary);
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-outline:hover {
        background: var(--primary);
        color: white;
        transform: translateY(-1px);
    }

    .alert {
        padding: 1rem 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        border-left: 4px solid;
        animation: slideIn 0.3s ease;
    }

    .alert-success {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
        border-left-color: var(--success);
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .profile-avatar-section {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-name {
            font-size: 1.5rem;
        }
        
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .profile-section {
            padding: 1.5rem;
        }
        
        .security-item {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php if ($showSuccessNotification): ?>
    <div class="alert alert-success">
        <strong>¡Éxito!</strong> <?= htmlspecialchars($successMessageText) ?>
    </div>
<?php endif; ?>

<?php if (!empty($successMessage) && !$showSuccessNotification): ?>
    <div class="alert alert-success">
        <strong>¡Éxito!</strong> <?= htmlspecialchars($successMessage) ?>
    </div>
<?php endif; ?>

<!-- Profile Header -->
<div class="profile-header">
    <div class="profile-avatar-section">
        <div class="profile-avatar-large">
            <?php 
            // Usar el nombre si existe, sino el username
            $displayName = !empty($userName) ? $userName : $userLogin;
            echo strtoupper(substr($displayName, 0, 2)); 
            ?>
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
        <h2 class="profile-name">
            <?php 
            // Mostrar el nombre completo si existe, sino el login
            echo htmlspecialchars(!empty($userName) ? $userName : $userLogin);
            ?>
        </h2>
        <p class="profile-role"><?= $isAdmin ? 'Administrador del Sistema' : 'Socio de la Asociación' ?></p>
        <p class="profile-email"><?= !empty($userEmail) ? htmlspecialchars($userEmail) : 'No disponible' ?></p>
    </div>
</div>

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
            <!-- Nombre Completo -->
            <div class="profile-field">
                <label class="field-label">Nombre Completo</label>
                <div class="field-value">
                    <?= !empty($userName) ? htmlspecialchars($userName) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Cédula de Identidad -->
            <div class="profile-field">
                <label class="field-label">Cédula de Identidad</label>
                <div class="field-value">
                    <?= !empty($userCI) ? htmlspecialchars($userCI) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Teléfono -->
            <div class="profile-field">
                <label class="field-label">Teléfono</label>
                <div class="field-value">
                    <?= !empty($userPhone) ? htmlspecialchars($userPhone) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Dirección -->
            <div class="profile-field">
                <label class="field-label">Dirección</label>
                <div class="field-value">
                    <?= !empty($userAddress) ? htmlspecialchars($userAddress) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Fecha de Nacimiento -->
            <div class="profile-field">
                <label class="field-label">Fecha de Nacimiento</label>
                <div class="field-value">
                    <?= !empty($formattedBirthday) ? htmlspecialchars($formattedBirthday) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Fecha de Registro -->
            <div class="profile-field">
                <label class="field-label">Fecha de Registro</label>
                <div class="field-value">
                    <?= !empty($formattedDateRegistration) ? htmlspecialchars($formattedDateRegistration) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Nombre de Usuario -->
            <div class="profile-field">
                <label class="field-label">Nombre de Usuario</label>
                <div class="field-value">
                    <?= !empty($userLogin) ? htmlspecialchars($userLogin) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
            
            <!-- Correo Electrónico -->
            <div class="profile-field">
                <label class="field-label">Correo Electrónico</label>
                <div class="field-value">
                    <?= !empty($userEmail) ? htmlspecialchars($userEmail) : '<span class="text-muted">No disponible</span>' ?>
                </div>
            </div>
        </div>
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

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>