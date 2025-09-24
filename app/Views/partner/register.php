<?php
// app/Views/partner/register.php

// Helpers portables
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}

$maxBirthday = date('Y-m-d', strtotime('-18 years'));
$minBirthday = date('Y-m-d', strtotime('-120 years'));

// Preservar datos del formulario
$formData = $form_data ?? [];
$fieldErrors = $field_errors ?? [];
$generalErrors = $general_errors ?? [];
$uploadedFiles = $uploaded_files ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Online de Socio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('assets/css/dashboard.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= asset('assets/images/favicon.ico') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --cream-50: #f9f8f6;
            --cream-100: #f5f2ed;
            --cream-200: #e8e2d8;
            --cream-300: #d9d0c1;
            --cream-400: #cfc4b0;
            --cream-500: #b8ab94;
            --cream-600: #9c8f7a;
            --cream-700: #7a7164;
            --cream-800: #5a5343;
            --cream-900: #3a362e;
            --surface: rgba(255, 255, 255, 0.97);
            --border: #e1e5e9;
            --shadow-md: 0 8px 40px rgba(188, 164, 120, 0.27);
            --border-radius: 12px;
            --transition: all 0.2s ease;
            --error-color: #dc3545;
            --success-color: #28a745;
            --warning-color: #ffc107;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: url('<?= asset('img/579801593549840d8cf000e7cb35cfd6.jpg') ?>') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.35);
            z-index: 0;
        }

        .create-container {
            position: relative;
            z-index: 1;
            background: var(--surface);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 2.5rem 3rem;
            margin: 1rem auto;
            max-width: 1200px;
            width: calc(100% - 2rem);
            border: 1px solid var(--border);
            animation: fadeIn 0.6s ease-out;
            backdrop-filter: blur(3px);
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }

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

        /* ALERTAS GENERALES */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin: 0 0 2rem 0;
            font-size: 1rem;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .alert-error {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-left: 4px solid var(--error-color);
        }

        .alert-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-left: 4px solid var(--success-color);
        }

        .alert-icon {
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }

        .alert-content ul {
            margin: 0;
            padding-left: 1.5rem;
        }

        .alert-content li {
            margin-bottom: 0.5rem;
        }

        .alert-content li:last-child {
            margin-bottom: 0;
        }

        /* CAMPOS DE FORMULARIO */
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: var(--cream-800);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
        }

        .form-group.has-error label {
            color: var(--error-color);
        }

        .form-group input,
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

        .form-group.has-error input,
        .form-group.has-error textarea {
            border-color: var(--error-color);
            background-color: #fef2f2;
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--cream-600);
            box-shadow: 0 0 0 3px rgba(156, 143, 122, 0.1);
            background-color: var(--surface);
        }

        .form-group.has-error input:focus,
        .form-group.has-error textarea:focus {
            border-color: var(--error-color);
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
        }

        .field-error {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .field-error i {
            font-size: 0.875rem;
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
            position: relative;
        }

        .btn-submit:hover:not(:disabled) {
            background: var(--cream-700);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn-submit .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        .btn-submit.loading .loading-spinner {
            display: inline-block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: var(--cream-600);
            text-decoration: none;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }

        .back-link:hover {
            color: var(--cream-700);
            text-decoration: underline;
        }

        /* UPLOAD DE ARCHIVOS */
        .image-upload-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .image-upload-group {
            position: relative;
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
            min-height: 200px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .image-upload-box:hover {
            border-color: var(--cream-600);
            background-color: var(--cream-100);
        }

        .image-upload-group.has-error .image-upload-box {
            border-color: var(--error-color);
            background-color: #fef2f2;
        }

        .image-upload-box i {
            font-size: 2rem;
            color: var(--cream-600);
            margin-bottom: 0.75rem;
        }

        .image-upload-group.has-error .image-upload-box i {
            color: var(--error-color);
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
            /* width: auto;
            height: auto; */
            object-fit: contain;
            display: none;
            border-radius: 4px;
            /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); */
            /* background: #f8f9fa; */
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

        .file-status {
            display: none;
            margin-top: 0.75rem;
            padding: 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            text-align: center;
        }

        .file-status.success {
            background: #d1fae5;
            color: #065f46;
            display: block;
        }

        .modal-success {
            position: fixed;
            inset: 0;
            background: rgba(44, 44, 44, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .modal-content-success {
            background: var(--surface);
            border-radius: 18px;
            box-shadow: var(--shadow-md);
            padding: 30px 26px 24px 26px;
            width: min(400px, 92vw);
            text-align: center;
            animation: fadeIn 0.4s ease-out;
        }

        .modal-content-success h2 {
            color: var(--success-color);
            margin: 0 0 12px 0;
            font-size: 1.4rem;
        }

        .modal-content-success p {
            color: var(--cream-800);
            margin: 0 0 18px 0;
        }

        .close-modal-btn {
            background: var(--cream-600);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 22px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-modal-btn:hover {
            background: var(--cream-700);
        }

        /* VALIDATION TOOLTIP */
        .validation-tooltip {
            position: absolute;
            top: -0.5rem;
            right: 1rem;
            background: var(--error-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            pointer-events: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 10;
        }

        .validation-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border: 5px solid transparent;
            border-top-color: var(--error-color);
        }

        .form-group.has-error .validation-tooltip {
            opacity: 1;
            transform: translateY(0);
        }

        /* RESPONSIVE */
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

        @media (max-width: 480px) {
            .alert {
                padding: 1rem;
            }
            
            .modal-content-success {
                margin: 20px;
                width: calc(100% - 40px);
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="create-container">
        <h1 class="create-title">Solicitud de Registro Online de Socio</h1>

        <?php if (!empty($generalErrors)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle alert-icon"></i>
                <div class="alert-content">
                    <ul>
                        <?php foreach ($generalErrors as $error): ?>
                            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        // Mostrar alerta informativa si hay archivos que se perdieron
        $hasLostFiles = (isset($uploadedFiles['frontImage']) || isset($uploadedFiles['backImage']));
        if ($hasLostFiles && !isset($success)): 
        ?>
            <div class="alert" style="background: linear-gradient(135deg, #fff3cd, #ffeaa7); color: #856404; border: 1px solid #ffecb5; border-left: 4px solid #ffc107;">
                <i class="fas fa-info-circle alert-icon"></i>
                <div class="alert-content">
                    <strong>Archivos perdidos al recargar</strong><br>
                    Los archivos se pierden automáticamente cuando hay errores (por seguridad del navegador). 
                    Debes volver a seleccionar los archivos marcados en amarillo más abajo.
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle alert-icon"></i>
                <div class="alert-content">
                    <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!isset($success)): ?>
            <form method="POST" action="<?= u('partner/register') ?>" enctype="multipart/form-data" autocomplete="off" novalidate id="partnerForm">
                <div class="partner-fields">
                    <div class="form-section">
                        <h2 class="section-title">Información Personal</h2>
                        <div class="form-row">
                            <div class="form-group <?= isset($fieldErrors['name']) ? 'has-error' : '' ?>">
                                <label for="name">Nombre Completo *</label>
                                <input type="text" name="name" id="name" placeholder="Ej: Juan Pérez López" 
                                       value="<?= htmlspecialchars($formData['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                       maxlength="100" required>
                                <?php if (isset($fieldErrors['name'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?= isset($fieldErrors['ci']) ? 'has-error' : '' ?>">
                                <label for="ci">Cédula de Identidad *</label>
                                <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" 
                                       value="<?= htmlspecialchars($formData['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                       maxlength="12" pattern="[0-9]+" required>
                                <?php if (isset($fieldErrors['ci'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['ci'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group <?= isset($fieldErrors['cellPhoneNumber']) ? 'has-error' : '' ?>">
                                <label for="cellPhoneNumber">Celular *</label>
                                <input type="tel" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: 65734215" 
                                       value="<?= htmlspecialchars($formData['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                       maxlength="8" pattern="[67][0-9]{7}" required>
                                <?php if (isset($fieldErrors['cellPhoneNumber'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['cellPhoneNumber'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?= isset($fieldErrors['email']) ? 'has-error' : '' ?>">
                                <label for="email">Correo Electrónico *</label>
                                <input type="email" name="email" id="email" placeholder="ejemplo@dominio.com" 
                                       value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                                       maxlength="100" required>
                                <?php if (isset($fieldErrors['email'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['email'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group <?= isset($fieldErrors['address']) ? 'has-error' : '' ?>">
                                <label for="address">Dirección Completa *</label>
                                <textarea name="address" id="address" rows="3" placeholder="Ej: Av. América #1234 entre Calles 5 y 6, Villa Adela" 
                                          maxlength="255" required><?= htmlspecialchars($formData['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                <?php if (isset($fieldErrors['address'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['address'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-group <?= isset($fieldErrors['birthday']) ? 'has-error' : '' ?>">
                                <label for="birthday">Fecha de Nacimiento *</label>
                                <input type="date" name="birthday" id="birthday" required 
                                       min="<?= $minBirthday ?>" max="<?= $maxBirthday ?>"
                                       value="<?= htmlspecialchars($formData['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?php if (isset($fieldErrors['birthday'])): ?>
                                    <div class="field-error">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['birthday'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h2 class="section-title">Documentos de Identidad</h2>
                        <p style="margin-bottom: 1.5rem; color: var(--cream-700);">
                            <i class="fas fa-info-circle" style="color: var(--cream-600); margin-right: 0.5rem;"></i>
                            Suba imágenes claras de ambos lados de su cédula de identidad. Formatos aceptados: JPG, PNG o JPEG. Tamaño máximo: 2MB por archivo.
                        </p>
                        <div class="image-upload-container">
                            <div class="image-upload-group <?= isset($fieldErrors['frontImage']) ? 'has-error' : '' ?>">
                                <div class="image-upload-box" onclick="document.getElementById('frontImage').click()">
                                    <i class="fas fa-id-card"></i>
                                    <p><strong>Cédula - Frente</strong></p>
                                    <small>Haga clic para seleccionar la imagen</small>
                                    <input type="file" name="frontImage" id="frontImage" accept="image/*" style="display: none" 
                                           onchange="handleFileSelect(this, 'frontPreview', 'frontFileName', 'frontStatus')" required>
                                    <img id="frontPreview" class="image-preview" alt="Vista previa frente de cédula">
                                    <span id="frontFileName" class="file-name"></span>
                                    <div id="frontStatus" class="file-status"></div>
                                </div>
                                <?php if (isset($fieldErrors['frontImage'])): ?>
                                    <div class="field-error" style="margin-top: 0.75rem;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['frontImage'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php elseif (isset($uploadedFiles['frontImage'])): ?>
                                    <div class="file-lost-warning" style="background: #fff3cd; color: #856404; padding: 0.75rem; border-radius: 6px; margin-top: 0.75rem; border-left: 4px solid #ffc107;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Archivo anterior perdido</strong>
                                        </div>
                                        <div style="font-size: 0.85rem;">
                                            Tenías: <em><?= htmlspecialchars($uploadedFiles['frontImage']['name'], ENT_QUOTES, 'UTF-8') ?></em><br>
                                            <strong>Debes seleccionar este archivo nuevamente</strong>
                                            <br><small>Los archivos se pierden al recargar por seguridad del navegador</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="image-upload-group <?= isset($fieldErrors['backImage']) ? 'has-error' : '' ?>">
                                <div class="image-upload-box" onclick="document.getElementById('backImage').click()">
                                    <i class="fas fa-id-card"></i>
                                    <p><strong>Cédula - Dorso</strong></p>
                                    <small>Haga clic para seleccionar la imagen</small>
                                    <input type="file" name="backImage" id="backImage" accept="image/*" style="display: none" 
                                           onchange="handleFileSelect(this, 'backPreview', 'backFileName', 'backStatus')" required>
                                    <img id="backPreview" class="image-preview" alt="Vista previa dorso de cédula">
                                    <span id="backFileName" class="file-name"></span>
                                    <div id="backStatus" class="file-status"></div>
                                </div>
                                <?php if (isset($fieldErrors['backImage'])): ?>
                                    <div class="field-error" style="margin-top: 0.75rem;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <?= htmlspecialchars($fieldErrors['backImage'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                <?php elseif (isset($uploadedFiles['backImage'])): ?>
                                    <div class="file-lost-warning" style="background: #fff3cd; color: #856404; padding: 0.75rem; border-radius: 6px; margin-top: 0.75rem; border-left: 4px solid #ffc107;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Archivo anterior perdido</strong>
                                        </div>
                                        <div style="font-size: 0.85rem;">
                                            Tenías: <em><?= htmlspecialchars($uploadedFiles['backImage']['name'], ENT_QUOTES, 'UTF-8') ?></em><br>
                                            <strong>Debes seleccionar este archivo nuevamente</strong>
                                            <br><small>Los archivos se pierden al recargar por seguridad del navegador</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group is-hidden">
                        <input type="hidden" name="dateRegistration" value="<?= date('Y-m-d H:i:s') ?>">
                    </div>

                    <div class="g-recaptcha" data-sitekey="6Lf4Pb0rAAAAANwvyOXxEqIguKcFGo3uLgewa41b" style="margin-top: 2rem;"></div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="loading-spinner"></span>
                    <i class="fas fa-user-plus"></i> Enviar Solicitud
                </button>
            </form>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <?php else: ?>
            <div class="modal-success" id="modalSuccess">
                <div class="modal-content-success">
                    <i class="fas fa-envelope-open" style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;"></i>
                    <h2>¡Solicitud enviada correctamente!</h2>
                    <p>Se ha enviado un enlace de verificación a <strong><?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
                    <p>Por favor, revisa tu correo (incluyendo la carpeta de spam) y haz clic en el enlace para completar tu registro.</p>
                    <button class="close-modal-btn" onclick="window.location.href='<?= u('login') ?>'">Ir al Login</button>
                </div>
            </div>
            <script>
                setTimeout(function(){ 
                    window.location.href = "<?= u('login') ?>"; 
                }, 10000); // 10 segundos
            </script>
        <?php endif; ?>

        <a href="<?= u('login') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

    <script>
        // Manejo mejorado de archivos con UX clara
        function handleFileSelect(input, previewId, fileNameId, statusId) {
            const preview = document.getElementById(previewId);
            const fileNameElement = document.getElementById(fileNameId);
            const statusElement = document.getElementById(statusId);
            const file = input.files[0];
            const group = input.closest('.image-upload-group');

            // Limpiar estados previos
            group.classList.remove('has-error');
            statusElement.className = 'file-status';
            statusElement.style.display = 'none';

            if (file) {
                // Validar tamaño (2MB = 2,097,152 bytes exactos)
                if (file.size > 2 * 1024 * 1024) {
                    showError(group, statusElement, `Archivo muy grande: ${formatFileSize(file.size)}. Máximo: 2MB`);
                    clearFile(input, preview, fileNameElement);
                    return;
                }

                // Validar que no esté vacío
                if (file.size === 0) {
                    showError(group, statusElement, 'El archivo está vacío');
                    clearFile(input, preview, fileNameElement);
                    return;
                }

                // Validar tipo de archivo
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const allowedExtensions = ['.jpg', '.jpeg', '.png'];
                const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
                
                if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                    showError(group, statusElement, 'Formato no válido. Use JPG, PNG o JPEG');
                    clearFile(input, preview, fileNameElement);
                    return;
                }

                // Archivo válido - mostrar confirmación
                fileNameElement.textContent = file.name;
                statusElement.textContent = `✓ Archivo válido (${formatFileSize(file.size)})`;
                statusElement.className = 'file-status success';

                // Mostrar preview optimizado para imágenes
                if (file.type.startsWith('image/') || ['.jpg', '.jpeg', '.png'].includes(fileExtension)) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        
                        // Optimizar la imagen para mejor visualización
                        preview.onload = function() {
                            // La imagen se mostrará con object-fit: contain para mantener proporción
                            console.log('Preview cargado correctamente');
                        }
                    }
                    reader.readAsDataURL(file);
                } 
            } else {
                clearFile(input, preview, fileNameElement, statusElement);
            }
        }

        function showError(group, statusElement, message) {
            group.classList.add('has-error');
            statusElement.textContent = `⚠ ${message}`;
            statusElement.className = 'file-status error';
            statusElement.style.display = 'block';
            statusElement.style.background = '#fee2e2';
            statusElement.style.color = '#991b1b';
        }

        function clearFile(input, preview, fileNameElement, statusElement) {
            input.value = '';
            preview.style.display = 'none';
            preview.src = '';
            fileNameElement.textContent = '';
            if (statusElement) {
                statusElement.style.display = 'none';
                statusElement.textContent = '';
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        // Validación en tiempo real
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('partnerForm');
            const submitBtn = document.getElementById('submitBtn');

            // Validación en tiempo real para campos de texto
            const fields = {
                'name': {
                    validate: (value) => {
                        if (!value.trim()) return 'El nombre es obligatorio';
                        if (value.length < 3) return 'El nombre debe tener al menos 3 caracteres';
                        if (value.length > 100) return 'El nombre no puede exceder 100 caracteres';
                        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value)) return 'El nombre solo puede contener letras y espacios';
                        return null;
                    }
                },
                'ci': {
                    validate: (value) => {
                        if (!value.trim()) return 'La cédula es obligatoria';
                        if (!/^[0-9]+$/.test(value)) return 'La cédula solo puede contener números';
                        if (value.length < 6 || value.length > 12) return 'La cédula debe tener entre 6 y 12 dígitos';
                        return null;
                    }
                },
                'cellPhoneNumber': {
                    validate: (value) => {
                        if (!value.trim()) return 'El celular es obligatorio';
                        if (!/^[67][0-9]{7}$/.test(value)) return 'El celular debe tener 8 dígitos y comenzar con 6 o 7';
                        return null;
                    }
                },
                'email': {
                    validate: (value) => {
                        if (!value.trim()) return 'El correo es obligatorio';
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'Formato de correo inválido';
                        if (value.length > 100) return 'El correo no puede exceder 100 caracteres';
                        return null;
                    }
                },
                'address': {
                    validate: (value) => {
                        if (!value.trim()) return 'La dirección es obligatoria';
                        if (value.length < 10) return 'La dirección debe ser más específica';
                        if (value.length > 255) return 'La dirección no puede exceder 255 caracteres';
                        return null;
                    }
                }
            };

            Object.keys(fields).forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const group = field.closest('.form-group');
                
                field.addEventListener('blur', function() {
                    validateField(fieldName, field.value, group, fields[fieldName].validate);
                });

                field.addEventListener('input', function() {
                    // Limpiar error mientras escribe
                    if (group.classList.contains('has-error')) {
                        const error = group.querySelector('.field-error');
                        if (error) {
                            const result = fields[fieldName].validate(field.value);
                            if (!result) {
                                group.classList.remove('has-error');
                                error.remove();
                            }
                        }
                    }
                });
            });

            function validateField(fieldName, value, group, validator) {
                const existingError = group.querySelector('.field-error');
                const error = validator(value);
                
                if (error) {
                    group.classList.add('has-error');
                    if (!existingError) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error';
                        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error}`;
                        group.appendChild(errorDiv);
                    } else {
                        existingError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error}`;
                    }
                } else {
                    group.classList.remove('has-error');
                    if (existingError) {
                        existingError.remove();
                    }
                }
            }

            // Manejo de envío del formulario
            form.addEventListener('submit', function(e) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                
                // Si hay errores, cancelar el loading después de un momento
                setTimeout(() => {
                    if (document.querySelector('.has-error, .alert-error')) {
                        submitBtn.classList.remove('loading');
                        submitBtn.disabled = false;
                    }
                }, 1000);
            });

            // Auto-scroll a primer error si existe
            const firstError = document.querySelector('.has-error, .alert-error');
            if (firstError) {
                setTimeout(() => {
                    firstError.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }, 100);
            }
        });
    </script>
</body>
</html>