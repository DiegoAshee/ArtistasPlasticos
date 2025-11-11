<?php
$title = 'Editar Socio - Asociación de Artistas';
$currentPath = 'partner/edit/' . ($partner['idPartner'] ?? '');
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Socios', 'url' => u('partner/list')],
    ['label' => 'Editar Socio', 'url' => null],
];

ob_start();
?>
<style>
    .edit-container {
        background: var(--surface);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        padding: 2.5rem 3rem;
        margin: 1rem auto;
        max-width: 1200px;
        width: calc(100% - 2rem);
        border: 1px solid var(--border);
    }

    .edit-title {
        color: var(--cream-900);
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 2.5rem 0;
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

    .image-upload-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .image-upload-box {
        border: 2px dashed var(--cream-400);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: var(--cream-50);
        position: relative;
    }

    .image-upload-box:hover {
        border-color: var(--cream-600);
        background-color: var(--cream-100);
    }

    .image-upload-box i {
        font-size: 2rem;
        color: var(--cream-600);
        margin-bottom: 0.75rem;
    }

    .image-upload-box p {
        margin: 0;
        color: var(--cream-700);
        font-weight: 500;
    }

    .image-upload-box small {
        display: block;
        margin-top: 0.5rem;
        color: var(--cream-500);
        font-size: 0.8rem;
    }

    .image-preview {
        margin-top: 1rem;
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .current-image {
        margin-top: 1rem;
        text-align: center;
    }

    .current-image img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 0.5rem;
    }

    .current-image small {
        display: block;
        color: var(--cream-600);
        font-style: italic;
    }

    .file-name {
        display: block;
        margin-top: 0.5rem;
        font-size: 0.8rem;
        color: var(--cream-600);
        word-break: break-all;
        text-align: center;
        font-style: italic;
    }

    .no-image {
        color: var(--cream-400);
        font-style: italic;
        text-align: center;
        margin: 1rem 0;
    }

    @media (max-width: 1200px) {
        .edit-container {
            padding: 2rem;
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    }

    @media (max-width: 768px) {
        .edit-container {
            padding: 1.5rem;
            margin: 0.5rem;
            width: calc(100% - 1rem);
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .image-upload-container {
            grid-template-columns: 1fr;
        }

        .partner-fields {
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .edit-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="edit-container">
        <h1 class="edit-title">Editar Socio</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= u('partner/edit/' . ($partner['idPartner'] ?? '')) ?>" enctype="multipart/form-data">
            <input type="hidden" name="idRole" value="2">
            
            <div class="partner-fields">
                <div class="form-section">
                    <h2 class="section-title">Información Personal</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" name="name" id="name" placeholder="Nombre y apellido" 
                                   value="<?= htmlspecialchars($partner['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="ci">Cédula de Identidad *</label>
                            <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" 
                                   value="<?= htmlspecialchars($partner['CI'] ?? $partner['ci'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" name="email" id="email" placeholder="Ingrese su correo electrónico" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="cellPhoneNumber">Número de Celular *</label>
                            <input type="tel" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: +591 65734215" 
                                   value="<?= htmlspecialchars($partner['cellPhoneNumber'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Dirección *</label>
                            <textarea name="address" id="address" rows="3" placeholder="Dirección completa" required><?= htmlspecialchars($partner['address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="birthday">Fecha de Nacimiento *</label>
                            <input type="date" name="birthday" id="birthday" 
                                   value="<?= htmlspecialchars($partner['birthday'] ?? '') ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dateRegistration">Fecha de Registro *</label>
                            <input type="date" name="dateRegistration" id="dateRegistration" 
                                   value="<?= htmlspecialchars($partner['dateRegistration'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="login">Login (CI) *</label>
                            <input type="text" name="login" id="login" placeholder="Cédula como login" 
                                   value="<?= htmlspecialchars($user['login'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Documentos de Identidad</h2>
                    <p style="color:black">Puede actualizar las imágenes de la cédula de identidad (formato JPG o PNG, máximo 2MB cada una)</p>
                    
                    <div class="image-upload-container">
                        <div class="image-upload-box" onclick="document.getElementById('frontImage').click()">
                            <i class="fas fa-id-card"></i>
                            <p>Cédula - Frente</p>
                            <small>Haga clic para seleccionar nueva imagen</small>
                            <input type="file" name="frontImage" id="frontImage" accept="image/*" style="display: none" onchange="previewImage(this, 'frontPreview', 'frontFileName')">
                            
                            <?php if (!empty($partner['frontImageURL'])): ?>
                                <div class="current-image">
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($partner['frontImageURL']) ?>" 
                                         alt="Imagen actual del frente de cédula">
                                    <small>Imagen actual</small>
                                </div>
                            <?php else: ?>
                                <div class="no-image">No hay imagen disponible</div>
                            <?php endif; ?>
                            
                            <img id="frontPreview" class="image-preview" style="display: none;" alt="Vista previa nueva imagen">
                            <span id="frontFileName" class="file-name"></span>
                        </div>
                        
                        <div class="image-upload-box" onclick="document.getElementById('backImage').click()">
                            <i class="fas fa-id-card"></i>
                            <p>Cédula - Dorso</p>
                            <small>Haga clic para seleccionar nueva imagen</small>
                            <input type="file" name="backImage" id="backImage" accept="image/*" style="display: none" onchange="previewImage(this, 'backPreview', 'backFileName')">
                            
                            <?php if (!empty($partner['backImageURL'])): ?>
                                <div class="current-image">
                                    <img src="<?= BASE_URL . '/' . htmlspecialchars($partner['backImageURL']) ?>" 
                                         alt="Imagen actual del dorso de cédula">
                                    <small>Imagen actual</small>
                                </div>
                            <?php else: ?>
                                <div class="no-image">No hay imagen disponible</div>
                            <?php endif; ?>
                            
                            <img id="backPreview" class="image-preview" style="display: none;" alt="Vista previa nueva imagen">
                            <span id="backFileName" class="file-name"></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <small>Nota: Si no selecciona nuevas imágenes, se mantendrán las actuales.</small>
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?= u('partner/list') ?>" class="btn-submit" style="background: var(--cream-400); text-decoration: none;">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Validación en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const nameInput = document.getElementById('name');
    const ciInput = document.getElementById('ci');
    const emailInput = document.getElementById('email');
    const cellPhoneInput = document.getElementById('cellPhoneNumber');
    const birthdayInput = document.getElementById('birthday');
    const dateRegistrationInput = document.getElementById('dateRegistration');
    const loginInput = document.getElementById('login');
    
    // Sincronizar CI con Login
    ciInput.addEventListener('input', function() {
        loginInput.value = this.value;
    });
    
    // Validar edad mínima de 15 años
    birthdayInput.addEventListener('change', function() {
        const birthday = new Date(this.value);
        const today = new Date();
        const age = today.getFullYear() - birthday.getFullYear();
        const monthDiff = today.getMonth() - birthday.getMonth();
        const dayDiff = today.getDate() - birthday.getDate();
        
        const exactAge = monthDiff < 0 || (monthDiff === 0 && dayDiff < 0) ? age - 1 : age;
        
        if (exactAge < 15) {
            showError(this, 'El socio debe tener al menos 15 años de edad');
            this.value = '';
        } else {
            clearError(this);
        }
    });
    
    // Validar que la fecha de nacimiento no sea futura
    birthdayInput.setAttribute('max', new Date().toISOString().split('T')[0]);
    
    // Validar fecha de registro
    dateRegistrationInput.addEventListener('change', function() {
        const regDate = new Date(this.value);
        const today = new Date();
        
        if (regDate > today) {
            showError(this, 'La fecha de registro no puede ser futura');
            this.value = '';
        } else {
            clearError(this);
        }
    });
    
    // Validar CI (solo números, mínimo 6 dígitos, máximo 10)
    ciInput.addEventListener('input', function() {
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
    
    // Validar nombre (solo letras y espacios, mínimo 3 caracteres)
    nameInput.addEventListener('input', function() {
        const nameRegex = /^[a-záéíóúñA-ZÁÉÍÓÚÑ\s]+$/;
        
        if (this.value.length > 0 && !nameRegex.test(this.value)) {
            showError(this, 'El nombre solo puede contener letras y espacios');
        } else if (this.value.length > 0 && this.value.length < 3) {
            showError(this, 'El nombre debe tener al menos 3 caracteres');
        } else {
            clearError(this);
        }
    });
    
    // Validar email
    emailInput.addEventListener('input', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (this.value.length > 0 && !emailRegex.test(this.value)) {
            showError(this, 'Ingrese un correo electrónico válido');
        } else {
            clearError(this);
        }
    });
    
    // Validar teléfono (formato boliviano)
    cellPhoneInput.addEventListener('input', function() {
        // Permitir solo números, espacios, + y -
        this.value = this.value.replace(/[^0-9\s\+\-]/g, '');
        
        const phoneRegex = /^(\+591\s?)?(6|7)[0-9]{7}$/;
        const cleanPhone = this.value.replace(/[\s\-]/g, '');
        
        if (this.value.length > 0 && !phoneRegex.test(cleanPhone)) {
            showError(this, 'Ingrese un número de celular boliviano válido (ej: +591 65734215 o 65734215)');
        } else {
            clearError(this);
        }
    });
    
    // Validación al enviar el formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const errors = [];
        
        // Validar nombre
        if (nameInput.value.trim().length < 3) {
            errors.push('El nombre debe tener al menos 3 caracteres');
            isValid = false;
            showError(nameInput, 'Campo requerido');
        }
        
        // Validar CI
        if (ciInput.value.length < 6 || ciInput.value.length > 10) {
            errors.push('La CI debe tener entre 6 y 10 dígitos');
            isValid = false;
            showError(ciInput, 'CI inválida');
        }
        
        // Validar email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(emailInput.value)) {
            errors.push('El correo electrónico no es válido');
            isValid = false;
            showError(emailInput, 'Email inválido');
        }
        
        // Validar teléfono
        const phoneRegex = /^(\+591\s?)?(6|7)[0-9]{7}$/;
        const cleanPhone = cellPhoneInput.value.replace(/[\s\-]/g, '');
        if (!phoneRegex.test(cleanPhone)) {
            errors.push('El número de celular no es válido');
            isValid = false;
            showError(cellPhoneInput, 'Teléfono inválido');
        }
        
        // Validar edad
        const birthday = new Date(birthdayInput.value);
        const today = new Date();
        const age = today.getFullYear() - birthday.getFullYear();
        const monthDiff = today.getMonth() - birthday.getMonth();
        const dayDiff = today.getDate() - birthday.getDate();
        const exactAge = monthDiff < 0 || (monthDiff === 0 && dayDiff < 0) ? age - 1 : age;
        
        if (exactAge < 15) {
            errors.push('El socio debe tener al menos 15 años de edad');
            isValid = false;
            showError(birthdayInput, 'Edad mínima 15 años');
        }
        
        // Validar fecha de registro
        const regDate = new Date(dateRegistrationInput.value);
        if (regDate > today) {
            errors.push('La fecha de registro no puede ser futura');
            isValid = false;
            showError(dateRegistrationInput, 'Fecha inválida');
        }
        
        // Validar dirección
        const addressInput = document.getElementById('address');
        if (addressInput.value.trim().length < 10) {
            errors.push('La dirección debe tener al menos 10 caracteres');
            isValid = false;
            showError(addressInput, 'Dirección muy corta');
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Mostrar resumen de errores
            const errorSummary = document.createElement('div');
            errorSummary.className = 'error-message';
            errorSummary.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Por favor corrija los siguientes errores:</strong>
                    <ul style="margin: 0.5rem 0 0 1.5rem;">
                        ${errors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                </div>
            `;
            
            // Eliminar mensajes de error anteriores
            const oldError = document.querySelector('.error-message');
            if (oldError) {
                oldError.remove();
            }
            
            // Insertar el nuevo mensaje de error
            const editTitle = document.querySelector('.edit-title');
            editTitle.parentNode.insertBefore(errorSummary, editTitle.nextSibling);
            
            // Scroll al primer error
            errorSummary.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

function showError(input, message) {
    // Remover error anterior si existe
    clearError(input);
    
    // Agregar clase de error al input
    input.style.borderColor = '#dc2626';
    input.style.backgroundColor = '#fef2f2';
    
    // Crear mensaje de error
    const errorMsg = document.createElement('small');
    errorMsg.className = 'field-error';
    errorMsg.style.color = '#dc2626';
    errorMsg.style.fontSize = '0.875rem';
    errorMsg.style.marginTop = '0.25rem';
    errorMsg.style.display = 'block';
    errorMsg.textContent = message;
    
    // Insertar mensaje después del input
    input.parentNode.appendChild(errorMsg);
}

function clearError(input) {
    // Restaurar estilos del input
    input.style.borderColor = '';
    input.style.backgroundColor = '';
    
    // Remover mensaje de error si existe
    const errorMsg = input.parentNode.querySelector('.field-error');
    if (errorMsg) {
        errorMsg.remove();
    }
}

function previewImage(input, previewId, fileNameId) {
    const preview = document.getElementById(previewId);
    const fileNameElement = document.getElementById(fileNameId);
    const file = input.files[0];
    
    // Ocultar imagen actual si se selecciona una nueva
    const currentImage = input.parentElement.querySelector('.current-image');
    const noImage = input.parentElement.querySelector('.no-image');
    
    if (currentImage) currentImage.style.display = 'none';
    if (noImage) noImage.style.display = 'none';
    
    if (file) {
        // Mostrar el nombre del archivo
        fileNameElement.textContent = file.name;
        
        // Verificar el tipo de archivo
        if (!file.type.startsWith('image/')) {
            alert('Por favor seleccione un archivo de imagen válido (JPG, PNG)');
            input.value = '';
            preview.style.display = 'none';
            fileNameElement.textContent = '';
            if (currentImage) currentImage.style.display = 'block';
            if (noImage) noImage.style.display = 'block';
            return;
        }
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('El archivo no puede ser mayor a 2MB');
            input.value = '';
            preview.style.display = 'none';
            fileNameElement.textContent = '';
            if (currentImage) currentImage.style.display = 'block';
            if (noImage) noImage.style.display = 'block';
            return;
        }
        
        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        fileNameElement.textContent = '';
        if (currentImage) currentImage.style.display = 'block';
        if (noImage) noImage.style.display = 'block';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';