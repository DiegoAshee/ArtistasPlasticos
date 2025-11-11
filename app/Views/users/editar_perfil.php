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


<script>
// Validaciones en tiempo real para formulario de editar perfil
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = document.getElementById('name');
    const ciInput = document.getElementById('ci');
    const phoneInput = document.getElementById('cellPhoneNumber');
    const addressInput = document.getElementById('address');
    const birthdayInput = document.getElementById('birthday');
    const emailInput = document.getElementById('email');

    // Establecer fecha máxima para el campo de nacimiento (hoy)
    if (birthdayInput) {
        const today = new Date().toISOString().split('T')[0];
        birthdayInput.setAttribute('max', today);
    }

    // Validar Nombre Completo
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            // Permitir solo letras, espacios, acentos y ñ
            const nameRegex = /^[a-záéíóúñA-ZÁÉÍÓÚÑ\s]+$/;
            
            if (this.value.length > 0 && !nameRegex.test(this.value)) {
                showError(this, 'El nombre solo puede contener letras y espacios');
            } else if (this.value.trim().length > 0 && this.value.trim().length < 3) {
                showError(this, 'El nombre debe tener al menos 3 caracteres');
            } else if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
                showError(this, 'El nombre no puede tener más de 100 caracteres');
            } else {
                clearError(this);
            }
        });

        nameInput.addEventListener('blur', function() {
            const trimmedValue = this.value.trim();
            this.value = trimmedValue;
            
            if (trimmedValue.length === 0) {
                showError(this, 'El nombre es requerido');
            } else if (trimmedValue.length < 3) {
                showError(this, 'El nombre debe tener al menos 3 caracteres');
            }
        });
    }

    // Validar Cédula de Identidad
    if (ciInput) {
        ciInput.addEventListener('input', function() {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            if (this.value.length > 0 && this.value.length < 6) {
                showError(this, 'La CI debe tener al menos 6 dígitos');
            } else if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
                showError(this, 'La CI no puede tener más de 10 dígitos');
            } else {
                clearError(this);
            }
        });

        ciInput.addEventListener('blur', function() {
            if (this.value.length > 0 && (this.value.length < 6 || this.value.length > 10)) {
                showError(this, 'La CI debe tener entre 6 y 10 dígitos');
            }
        });
    }

    // Validar Teléfono
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Permitir números, espacios, + y -
            this.value = this.value.replace(/[^0-9\s\+\-]/g, '');
            
            const cleanPhone = this.value.replace(/[\s\-]/g, '');
            const phoneRegex = /^(\+591\s?)?(6|7)[0-9]{7}$/;
            
            if (this.value.length > 0 && !phoneRegex.test(cleanPhone)) {
                showError(this, 'Ingrese un número celular boliviano válido (ej: 65734215 o +591 65734215)');
            } else {
                clearError(this);
            }
        });

        phoneInput.addEventListener('blur', function() {
            const trimmedValue = this.value.trim();
            this.value = trimmedValue;
            
            if (trimmedValue.length === 0) {
                showError(this, 'El teléfono es requerido');
            }
        });
    }

    // Validar Dirección
    if (addressInput) {
        addressInput.addEventListener('input', function() {
            if (this.value.trim().length > 0 && this.value.trim().length < 10) {
                showError(this, 'La dirección debe tener al menos 10 caracteres');
            } else if (this.value.length > 200) {
                this.value = this.value.substring(0, 200);
                showError(this, 'La dirección no puede tener más de 200 caracteres');
            } else {
                clearError(this);
            }
        });

        addressInput.addEventListener('blur', function() {
            const trimmedValue = this.value.trim();
            this.value = trimmedValue;
            
            if (trimmedValue.length > 0 && trimmedValue.length < 10) {
                showError(this, 'La dirección debe tener al menos 10 caracteres');
            }
        });
    }

    // Validar Fecha de Nacimiento
    if (birthdayInput) {
        birthdayInput.addEventListener('change', function() {
            if (!this.value) {
                clearError(this);
                return;
            }

            const birthday = new Date(this.value);
            const today = new Date();
            
            // Validar que no sea fecha futura
            if (birthday > today) {
                showError(this, 'La fecha de nacimiento no puede ser futura');
                this.value = '';
                return;
            }

            // Calcular edad exacta
            let age = today.getFullYear() - birthday.getFullYear();
            const monthDiff = today.getMonth() - birthday.getMonth();
            const dayDiff = today.getDate() - birthday.getDate();
            
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            // Validar edad mínima de 15 años
            if (age < 15) {
                showError(this, 'Debe tener al menos 15 años de edad');
                this.value = '';
            } else if (age > 120) {
                showError(this, 'La fecha de nacimiento no parece válida');
                this.value = '';
            } else {
                clearError(this);
            }
        });
    }

    // Validar Email
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (this.value.length > 0 && !emailRegex.test(this.value)) {
                showError(this, 'Ingrese un correo electrónico válido');
            } else if (this.value.length > 100) {
                this.value = this.value.substring(0, 100);
                showError(this, 'El correo no puede tener más de 100 caracteres');
            } else {
                clearError(this);
            }
        });

        emailInput.addEventListener('blur', function() {
            const trimmedValue = this.value.trim();
            this.value = trimmedValue;
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (trimmedValue.length === 0) {
                showError(this, 'El correo electrónico es requerido');
            } else if (!emailRegex.test(trimmedValue)) {
                showError(this, 'El formato del correo no es válido');
            }
        });
    }

    // Validación completa al enviar el formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];

        // Validar Nombre
        if (nameInput) {
            const nameValue = nameInput.value.trim();
            const nameRegex = /^[a-záéíóúñA-ZÁÉÍÓÚÑ\s]+$/;
            
            if (nameValue.length === 0) {
                errors.push('El nombre completo es requerido');
                isValid = false;
                showError(nameInput, 'Campo requerido');
            } else if (nameValue.length < 3) {
                errors.push('El nombre debe tener al menos 3 caracteres');
                isValid = false;
                showError(nameInput, 'Nombre muy corto');
            } else if (!nameRegex.test(nameValue)) {
                errors.push('El nombre solo puede contener letras y espacios');
                isValid = false;
                showError(nameInput, 'Caracteres no válidos');
            }
        }

        // Validar CI (opcional pero si se ingresa debe ser válido)
        if (ciInput && ciInput.value.length > 0) {
            const ciValue = ciInput.value;
            
            if (ciValue.length < 6 || ciValue.length > 10) {
                errors.push('La CI debe tener entre 6 y 10 dígitos');
                isValid = false;
                showError(ciInput, 'CI inválida');
            }
        }

        // Validar Teléfono
        if (phoneInput) {
            const phoneValue = phoneInput.value.replace(/[\s\-]/g, '');
            const phoneRegex = /^(\+591\s?)?(6|7)[0-9]{7}$/;
            
            if (phoneValue.length === 0) {
                errors.push('El número de teléfono es requerido');
                isValid = false;
                showError(phoneInput, 'Campo requerido');
            } else if (!phoneRegex.test(phoneValue)) {
                errors.push('El número de teléfono no es válido (debe ser celular boliviano)');
                isValid = false;
                showError(phoneInput, 'Teléfono inválido');
            }
        }

        // Validar Dirección (opcional pero si se ingresa debe ser válida)
        if (addressInput && addressInput.value.trim().length > 0) {
            const addressValue = addressInput.value.trim();
            
            if (addressValue.length < 10) {
                errors.push('La dirección debe tener al menos 10 caracteres');
                isValid = false;
                showError(addressInput, 'Dirección muy corta');
            }
        }

        // Validar Fecha de Nacimiento (opcional pero si se ingresa debe ser válida)
        if (birthdayInput && birthdayInput.value) {
            const birthday = new Date(birthdayInput.value);
            const today = new Date();
            
            let age = today.getFullYear() - birthday.getFullYear();
            const monthDiff = today.getMonth() - birthday.getMonth();
            const dayDiff = today.getDate() - birthday.getDate();
            
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            if (birthday > today) {
                errors.push('La fecha de nacimiento no puede ser futura');
                isValid = false;
                showError(birthdayInput, 'Fecha inválida');
            } else if (age < 15) {
                errors.push('Debe tener al menos 15 años de edad');
                isValid = false;
                showError(birthdayInput, 'Edad mínima 15 años');
            } else if (age > 120) {
                errors.push('La fecha de nacimiento no parece válida');
                isValid = false;
                showError(birthdayInput, 'Fecha no válida');
            }
        }

        // Validar Email
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
            }
        }

        if (!isValid) {
            e.preventDefault();
            
            // Crear mensaje de error general
            showErrorSummary(errors);
            
            // Scroll al primer campo con error
            const firstError = form.querySelector('.field-value-edit[style*="border-color: rgb(220, 38, 38)"]');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });

    // Prevenir espacios al inicio en campos de texto
    [nameInput, ciInput, phoneInput, emailInput].forEach(input => {
        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === ' ' && this.selectionStart === 0) {
                    e.preventDefault();
                }
            });
        }
    });

    // Confirmación antes de cancelar si hay cambios
    let formChanged = false;
    const inputs = form.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            formChanged = true;
        });
    });

    const cancelButton = document.querySelector('.btn-cancelar');
    if (cancelButton) {
        cancelButton.addEventListener('click', function(e) {
            if (formChanged) {
                if (!confirm('¿Está seguro de que desea cancelar? Los cambios no guardados se perderán.')) {
                    e.preventDefault();
                }
            }
        });
    }

    // No advertir si se envía el formulario
    form.addEventListener('submit', function() {
        formChanged = false;
    });
});

function showError(input, message) {
    // Remover error anterior si existe
    clearError(input);
    
    // Agregar estilo de error al input
    input.style.borderColor = '#dc2626';
    input.style.backgroundColor = '#fef2f2';
    
    // Ocultar mensajes info/warning existentes
    const existingMessages = input.parentNode.querySelectorAll('.field-message');
    existingMessages.forEach(msg => msg.style.display = 'none');
    
    // Crear mensaje de error
    const errorMsg = document.createElement('div');
    errorMsg.className = 'field-message';
    errorMsg.style.background = 'rgba(239, 68, 68, 0.08)';
    errorMsg.style.color = '#b91c1c';
    errorMsg.style.borderLeft = '4px solid #dc2626';
    errorMsg.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    
    // Insertar mensaje después del input
    input.parentNode.appendChild(errorMsg);
}

function clearError(input) {
    // Restaurar estilos del input
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    // Remover mensajes de error
    const errorMessages = input.parentNode.querySelectorAll('.field-message');
    errorMessages.forEach(msg => {
        if (msg.style.color === 'rgb(185, 28, 28)' || msg.textContent.includes('exclamation-circle')) {
            msg.remove();
        } else {
            msg.style.display = 'flex'; // Mostrar mensajes info/warning originales
        }
    });
}

function showErrorSummary(errors) {
    // Remover resumen anterior si existe
    const oldSummary = document.querySelector('.error-summary-box');
    if (oldSummary) {
        oldSummary.remove();
    }
    
    // Crear resumen de errores
    const errorBox = document.createElement('div');
    errorBox.className = 'error-summary-box';
    errorBox.style.cssText = `
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 2px solid #dc2626;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1);
        animation: slideIn 0.3s ease;
    `;
    
    errorBox.innerHTML = `
        <div style="display: flex; align-items: start; gap: 1rem;">
            <i class="fas fa-exclamation-triangle" style="color: #dc2626; font-size: 1.5rem; margin-top: 2px;"></i>
            <div style="flex: 1;">
                <h4 style="color: #b91c1c; margin: 0 0 0.75rem 0; font-weight: 700; font-size: 1.1rem;">
                    Por favor corrija los siguientes errores:
                </h4>
                <ul style="margin: 0; padding-left: 1.5rem; color: #991b1b;">
                    ${errors.map(err => `<li style="margin-bottom: 0.5rem;">${err}</li>`).join('')}
                </ul>
            </div>
        </div>
    `;
    
    // Insertar al inicio del formulario
    const form = document.querySelector('form');
    const firstSection = form.querySelector('.profile-section');
    form.insertBefore(errorBox, firstSection);
    
    // Scroll al resumen de errores
    errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>



<?php
// Obtener el contenido del buffer
$content = ob_get_clean();

// Incluir el archivo de layout
include __DIR__ . '/../layouts/app.php';
?>