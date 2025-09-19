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
        display: none;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

        .image-upload-container {
            grid-template-columns: 1fr;
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
        <h1 class="create-title">Crear Nuevo Socio</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/partner/create" enctype="multipart/form-data">
            <input type="hidden" name="idRole" value="2">
            
            <div class="partner-fields">
                <div class="form-section">
                    <h2 class="section-title">Información Personal</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nombre Completo *</label>
                            <input type="text" name="name" id="name" placeholder="Nombre y apellido" required>
                        </div>
                        <div class="form-group">
                            <label for="ci">Cédula de Identidad *</label>
                            <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Correo Electrónico *</label>
                            <input type="email" name="email" id="email" placeholder="Ingrese su correo electrónico" required>
                        </div>
                        <div class="form-group">
                            <label for="cellPhoneNumber">Número de Celular *</label>
                            <input type="tel" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: +591 65734215" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="address">Dirección *</label>
                            <textarea name="address" id="address" rows="3" placeholder="Dirección completa" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="birthday">Fecha de Nacimiento *</label>
                            <input type="date" name="birthday" id="birthday" required>
                        </div>
                    </div>
                    
                    <!-- Se eliminó el campo de fecha de registro -->
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Documentos de Identidad</h2>
                    <p>Suba imágenes claras de la cédula de identidad (formato JPG, PNG o PDF, máximo 2MB cada una)</p>
                    
                    <div class="image-upload-container">
                        <div class="image-upload-box" onclick="document.getElementById('frontImage').click()">
                            <i class="fas fa-id-card"></i>
                            <p>Cédula - Frente</p>
                            <small>Haga clic para seleccionar la imagen</small>
                            <input type="file" name="frontImage" id="frontImage" accept="image/*,.pdf" style="display: none" onchange="previewImage(this, 'frontPreview', 'frontFileName')">
                            <img id="frontPreview" class="image-preview" alt="Vista previa frente de cédula">
                            <span id="frontFileName" class="file-name"></span>
                        </div>
                        
                        <div class="image-upload-box" onclick="document.getElementById('backImage').click()">
                            <i class="fas fa-id-card"></i>
                            <p>Cédula - Dorso</p>
                            <small>Haga clic para seleccionar la imagen</small>
                            <input type="file" name="backImage" id="backImage" accept="image/*,.pdf" style="display: none" onchange="previewImage(this, 'backPreview', 'backFileName')">
                            <img id="backPreview" class="image-preview" alt="Vista previa dorso de cédula">
                            <span id="backFileName" class="file-name"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i> Crear Socio
            </button>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId, fileNameId) {
    const preview = document.getElementById(previewId);
    const fileNameElement = document.getElementById(fileNameId);
    const file = input.files[0];
    
    if (file) {
        // Mostrar el nombre del archivo
        fileNameElement.textContent = file.name;
        
        // Verificar el tamaño del archivo (máximo 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('El archivo no puede ser mayor a 2MB');
            input.value = '';
            preview.style.display = 'none';
            fileNameElement.textContent = '';
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
        } else if (file.type === 'application/pdf') {
            // Para PDFs, mostrar un icono en lugar de la vista previa
            preview.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2NCIgaGVpZ2h0PSI2NCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiM5ODg0NmEiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMTQgMlYxMGEyIDIgMCAwIDAgMiAyaDhhMiAyIDAgMCAwIDItMlY4LjgyMmEyIDIgMCAwIDAtLjU4Ni0xLjQxNGwtNi44MjgtNi44MjhhMiAyIDAgMCAwLTEuNDE0LS41ODZINmEyIDIgMCAwIDAtMiAydjEzYTIgMiAwIDAgMCAyIDJoNCI+PC9wYXRoPjxwb2x5bGluZSBwb2ludHM9IjE0IDIgMTQgMTAgMjIgMTAiPjwvcG9seWxpbmU+PHBhdGggZD0iTTUgMTQuNWEyLjUgMi41IDAgMCAwIDUgMHYtNWEyLjUgMi41IDAgMCAwLTUgMHY1eiI+PC9wYXRoPjxwYXRoIGQ9Ik04IDEyaDUiPjwvcGF0aD48L3N2Zz4=';
            preview.style.display = 'block';
        }
    } else {
        preview.style.display = 'none';
        fileNameElement.textContent = '';
    }
}
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>