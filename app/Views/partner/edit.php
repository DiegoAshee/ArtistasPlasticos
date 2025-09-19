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
                    <p>Puede actualizar las imágenes de la cédula de identidad (formato JPG o PNG, máximo 2MB cada una)</p>
                    
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
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('El archivo no puede ser mayor a 2MB');
            input.value = '';
            preview.style.display = 'none';
            fileNameElement.textContent = '';
            
            // Mostrar nuevamente la imagen actual o el mensaje de no disponible
            if (currentImage) currentImage.style.display = 'block';
            if (noImage) noImage.style.display = 'block';
            return;
        }
        
        // Mostrar vista previa para imágenes
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    } else {
        preview.style.display = 'none';
        fileNameElement.textContent = '';
        
        // Mostrar nuevamente la imagen actual o el mensaje de no disponible
        if (currentImage) currentImage.style.display = 'block';
        if (noImage) noImage.style.display = 'block';
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';