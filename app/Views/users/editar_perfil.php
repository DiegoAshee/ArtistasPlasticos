<?php
// app/Views/users/editar_perfil.php

// Variables del layout
$title = 'Editar Perfil';
$currentPath = 'users/profile/edit';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Mi Perfil', 'url' => u('users/profile')],
    ['label' => 'Editar Perfil', 'url' => null],
];

// Obtener datos del usuario desde la BD
$user = !empty($users) ? $users[0] : [];

// Extraer valores de la base de datos (sin valores por defecto)
$fullName = $user['name'] ?? '';
$userCI = $user['ci'] ?? '';
$userPhone = $user['cellPhoneNumber'] ?? '';
$userAddress = $user['address'] ?? '';
$userBirthday = $user['birthday'] ?? '';
$userEmail = $user['email'] ?? '';

// Iniciar el buffer de salida para el contenido
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

    .profile-content {
        max-width: 800px;
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

    .field-label.required::after {
        content: '*';
        color: var(--danger);
        margin-left: 4px;
    }

    .field-value-edit {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }

    .field-value-edit:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        transform: translateY(-1px);
    }

    .field-value-edit:hover {
        border-color: #9ca3af;
    }

    textarea.field-value-edit {
        resize: vertical;
        min-height: 100px;
        line-height: 1.5;
    }

    .field-message {
        display: flex;
        align-items: center;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        gap: 0.5rem;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .field-message.info {
        background: rgba(16, 185, 129, 0.08);
        color: #065f46;
        border-left: 4px solid var(--success);
    }

    .field-message.warning {
        background: rgba(245, 158, 11, 0.08);
        color: #92400e;
        border-left: 4px solid var(--warning);
    }

    .field-message i {
        font-size: 1rem;
    }

    .profile-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        align-items: center;
        padding-top: 1.5rem;
        border-top: 2px solid var(--primary-light);
        margin-top: 1rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: var(--shadow);
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-secondary {
        background: white;
        color: #6b7280;
        border: 2px solid var(--border);
    }

    .btn-secondary:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #9ca3af;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--primary-dark), #3730a3);
        color: white;
    }

    .card {
        background: transparent;
        border: none;
        box-shadow: none;
    }

    .card-header {
        background: transparent;
        border-bottom: none;
        padding: 1.5rem 0;
        margin-bottom: 1rem;
    }

    .card-title {
        color: #1f2937;
        font-weight: 800;
        font-size: 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .card-title i {
        color: var(--primary);
        background: var(--primary-light);
        padding: 1rem;
        border-radius: 16px;
    }

    .card-tools .btn {
        background: var(--secondary-light);
        color: var(--secondary);
        border: 2px solid var(--border);
    }

    .card-tools .btn:hover {
        background: white;
        color: var(--primary);
        border-color: var(--primary);
    }

    /* Placeholder styling */
    .field-value-edit::placeholder {
        color: #9ca3af;
        font-style: italic;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .profile-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .profile-section {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            font-size: 1.25rem;
        }
        
        .profile-actions {
            flex-direction: column-reverse;
            gap: 0.75rem;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .card-title {
            font-size: 1.5rem;
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
    }

    /* Loading state for inputs */
    .field-value-edit:disabled {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }

    /* Success state animation */
    .field-value-edit:valid {
        border-color: rgba(16, 185, 129, 0.3);
    }

    /* Error state */
    .field-value-edit:invalid:not(:focus):not(:placeholder-shown) {
        border-color: rgba(239, 68, 68, 0.3);
    }

    /* Asegurar que los botones sean visibles */
    .btn-cancelar {
        background: linear-gradient(135deg, #6b7280, #4b5563) !important;
        color: white !important;
        border: 2px solid #6b7280 !important;
    }

    .btn-cancelar:hover {
        background: linear-gradient(135deg, #4b5563, #374151) !important;
        color: white !important;
        border-color: #4b5563 !important;
    }
</style>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-user-edit"></i> Solicitar Cambio de Datos
                            </h4>
                        </div>
                        <div class="card-tools">
                            <a href="<?= u('users/profile') ?>" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Volver al Perfil
                            </a>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2">Actualiza tu información personal y envía una solicitud de modificación</p>
                </div>
                <div class="card-body p-0">
                    <form method="POST" action="<?= u('partnerOnline/createRequest') ?>" class="p-4">
                        <div class="profile-content">
                            <!-- Información Personal -->
                            <div class="profile-section">
                                <div class="section-header">
                                    <h3 class="section-title">
                                        <i class="fas fa-user-edit"></i>
                                        Solicitar Modificación de Datos
                                    </h3>
                                    <p class="text-muted mb-0">Completa los campos que deseas modificar</p>
                                </div>
                                <div class="profile-grid">
                                    
                                    <!-- Nombre Completo -->
                                    <div class="profile-field">
                                        <label for="name" class="field-label required">Nombre Completo</label>
                                        <input type="text" 
                                               class="field-value-edit" 
                                               id="name" 
                                               name="name" 
                                               required
                                               value="<?= htmlspecialchars($fullName) ?>"
                                               placeholder="<?= empty($fullName) ? '👤 Ingresa tu nombre completo' : 'Actualiza tu nombre completo' ?>">
                                        <?php if (!empty($fullName)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> Nombre registrado en el sistema
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay nombre registrado
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Cédula de Identidad -->
                                    <div class="profile-field">
                                        <label for="ci" class="field-label">Cédula de Identidad</label>
                                        <input type="text" 
                                               class="field-value-edit" 
                                               id="ci" 
                                               name="ci"
                                               value="<?= htmlspecialchars($userCI) ?>"
                                               placeholder="<?= empty($userCI) ? '🆔 Ingresa tu cédula de identidad' : 'Actualiza tu cédula de identidad' ?>">
                                        <?php if (!empty($userCI)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> CI registrado en el sistema
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay CI registrado en el sistema
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Teléfono -->
                                    <div class="profile-field">
                                        <label for="cellPhoneNumber" class="field-label required">Teléfono</label>
                                        <input type="tel" 
                                               class="field-value-edit" 
                                               id="cellPhoneNumber" 
                                               name="cellPhoneNumber" 
                                               required
                                               value="<?= htmlspecialchars($userPhone) ?>"
                                               placeholder="<?= empty($userPhone) ? '📞 Ingresa tu número de teléfono' : 'Actualiza tu número de teléfono' ?>">
                                        <?php if (!empty($userPhone)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> Teléfono registrado en el sistema
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay teléfono registrado
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Dirección -->
                                    <div class="profile-field">
                                        <label for="address" class="field-label">Dirección</label>
                                        <textarea class="field-value-edit" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3"
                                                  placeholder="<?= empty($userAddress) ? '🏠 Ingresa tu dirección completa' : 'Actualiza tu dirección completa' ?>"><?= htmlspecialchars($userAddress) ?></textarea>
                                        <?php if (!empty($userAddress)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> Dirección registrada en el sistema
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay dirección registrada
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Fecha de Nacimiento -->
                                    <div class="profile-field">
                                        <label for="birthday" class="field-label">Fecha de Nacimiento</label>
                                        <input type="date" 
                                               class="field-value-edit" 
                                               id="birthday" 
                                               name="birthday" 
                                               value="<?= htmlspecialchars($userBirthday) ?>">
                                        <?php if (!empty($userBirthday)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> Fecha de nacimiento registrada
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay fecha de nacimiento registrada
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Correo Electrónico -->
                                    <div class="profile-field">
                                        <label for="email" class="field-label required">Correo Electrónico</label>
                                        <input type="email" 
                                               class="field-value-edit" 
                                               id="email" 
                                               name="email" 
                                               required
                                               value="<?= htmlspecialchars($userEmail) ?>"
                                               placeholder="<?= empty($userEmail) ? '📧 Ingresa tu correo electrónico' : 'Actualiza tu correo electrónico' ?>">
                                        <?php if (!empty($userEmail)): ?>
                                        <div class="field-message info">
                                            <i class="fas fa-check-circle"></i> Correo electrónico registrado
                                        </div>
                                        <?php else: ?>
                                        <div class="field-message warning">
                                            <i class="fas fa-exclamation-triangle"></i> No hay correo registrado
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>

                            <!-- Botones de acción - CORREGIDOS -->
                            <div class="profile-actions">
                                <a href="<?= u('users/profile') ?>" class="btn btn-cancelar">
                                    <i class="fas fa-times mr-2"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitud
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