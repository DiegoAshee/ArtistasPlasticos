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

// Preservar datos del formulario en caso de errores
$formData = $form_data ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Online de Socio</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= asset('assets/css/dashboard.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= asset('assets/images/favicon.ico') ?>">

    <style>
        :root {
            --cream-50:#f9f8f6; --cream-100:#f5f2ed; --cream-200:#e8e2d8; --cream-300:#d9d0c1;
            --cream-400:#cfc4b0; --cream-500:#b8ab94; --cream-600:#9c8f7a; --cream-700:#7a7164;
            --cream-800:#5a5343; --cream-900:#3a362e;
        }
        * { box-sizing: border-box; }
        body {
            min-height:100vh; margin:0; font-family:'Inter', Arial, sans-serif;
            background:url('<?= asset('img/579801593549840d8cf000e7cb35cfd6.jpg') ?>') no-repeat center center fixed;
            background-size:cover;
            display:flex; align-items:center; justify-content:center;
            padding:24px;
        }
        body::before {
            content:''; position:fixed; inset:0;
            background:rgba(255,255,255,0.35);
            z-index:0;
        }
        .register-container {
            position:relative; z-index:1;
            background:rgba(255,255,255,0.97);
            border-radius:28px;
            box-shadow:0 8px 40px #bca47844;
            max-width:760px; width:100%;
            padding:48px 44px 36px 44px;
            animation:fadeIn .6s ease-out;
            backdrop-filter: blur(3px);
        }
        @keyframes fadeIn { from{opacity:0; transform:translateY(20px)} to{opacity:1; transform:none} }
        .register-title {
            text-align:center; color:#bca478; font-size:2.2rem; margin:0 0 22px 0;
            font-family:'Playfair Display', Georgia, serif; letter-spacing:.5px;
        }
        .subtitle { text-align:center; color:#6b6b6b; margin-bottom:26px; font-size:.98rem; }
        .form-row { display:flex; gap:20px; }
        .form-group { margin-bottom:18px; flex:1; }
        label { display:block; margin:0 0 7px 0; color:#9c8f7a; font-weight:600; font-size:.98rem; }
        input[type="text"], input[type="date"], input[type="email"] {
            width:100%; padding:14px 15px; border:2px solid #e1e5e9; border-radius:14px;
            font-size:1rem; background:#f9f8f6; transition:border-color .2s, box-shadow .2s; outline:none;
        }
        input:focus { border-color:#bca478; box-shadow:0 0 0 2px #bca47833; }
        .hint { display:block; font-size:.85rem; color:#7a7164; margin-top:6px; }
        .register-btn {
            width:100%; padding:15px 0; border:none; border-radius:14px; cursor:pointer;
            background:linear-gradient(90deg, #bca478 60%, #f7f1ae 100%); color:#fff;
            font-size:1.1rem; font-weight:800; letter-spacing:.4px;
            box-shadow:0 2px 12px #e1e5e9; transition:transform .15s, filter .2s; margin-top:2px;
        }
        .register-btn:hover { transform:translateY(-2px); filter:saturate(1.05); }
        .error-messages {
            background:#ffecec; color:#b63838; border:1px solid #ffc9c9;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; font-weight:600;
        }
        .error-messages ul {
            margin: 0; padding-left: 20px;
        }
        .error-messages li {
            margin-bottom: 5px;
        }
        .error-message {
            background:#ffecec; color:#b63838; border:1px solid #ffc9c9;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; text-align:center; font-weight:600;
        }
        .success-inline {
            background:#e8f8ef; color:#18794e; border:1px solid #bfead2;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; text-align:center; font-weight:600;
        }
        .back-link {
            display:block; text-align:center; margin-top:16px; color:#bca478;
            text-decoration:none; font-weight:600;
        }
        .back-link:hover { color:#a48c6b; text-decoration:underline; }
        .is-hidden { display:none !important; }
        .modal-success { position:fixed; inset:0; background:rgba(44,44,44,.45);
            display:flex; align-items:center; justify-content:center; z-index:9999; }
        .modal-content {
            background:#fff; border-radius:18px; box-shadow:0 6px 32px #e1e5e9;
            padding:30px 26px 24px 26px; width:min(360px, 92vw); text-align:center; animation:fadeIn .4s ease-out;
        }
        .modal-content h2 { color:#10b981; margin:0 0 12px 0; font-size:1.4rem; }
        .modal-content p { color:#444; margin:0 0 18px 0; }
        .close-modal-btn {
            background:#cfc4b0; color:#fff; border:none; border-radius:10px;
            padding:10px 22px; font-size:1rem; font-weight:700; cursor:pointer; transition:background .2s;
        }
        .close-modal-btn:hover { background:#bca478; }
        
        /* Estilos para los inputs de archivo */
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 14px;
            background: #f9f8f6;
            font-size: 0.95rem;
            cursor: pointer;
            transition: border-color .2s, box-shadow .2s;
        }
        input[type="file"]:focus {
            border-color: #bca478;
            box-shadow: 0 0 0 2px #bca47833;
            outline: none;
        }
        
        .file-info {
            margin-top: 5px;
            font-size: 0.85rem;
            color: #2d5016;
            font-weight: 700;
            text-shadow: 0 1px 1px rgba(255,255,255,0.8);
        }
        
        @media (max-width: 860px) {
            .register-container { padding:28px 18px 22px 18px; }
            .form-row { flex-direction:column; gap:0; }
        }
    </style>
    
    <script>
        // Función para mostrar el nombre del archivo seleccionado
        function showFileName(inputElement, displayElementId) {
            const displayElement = document.getElementById(displayElementId);
            if (inputElement.files && inputElement.files.length > 0) {
                const fileName = inputElement.files[0].name;
                const fileSize = (inputElement.files[0].size / (1024 * 1024)).toFixed(2); // Tamaño en MB
                displayElement.textContent = `Archivo seleccionado: ${fileName} (${fileSize} MB)`;
                displayElement.style.color = '#2d5016'; // Verde más oscuro para mejor visibilidad
            } else {
                displayElement.textContent = '';
            }
        }
        
        // Validación de tamaño de archivo antes de envío
        function validateFileSize(inputElement, maxSizeMB = 2) {
            if (inputElement.files && inputElement.files.length > 0) {
                const fileSizeMB = inputElement.files[0].size / (1024 * 1024);
                if (fileSizeMB > maxSizeMB) {
                    alert(`El archivo excede el tamaño máximo permitido de ${maxSizeMB}MB. Tamaño actual: ${fileSizeMB.toFixed(2)}MB`);
                    inputElement.value = '';
                    document.getElementById(inputElement.id.replace('Image', 'ImageInfo')).textContent = '';
                    return false;
                }
            }
            return true;
        }
        
        // Validar formulario antes de envío
        function validateForm() {
            const frontImage = document.getElementById('frontImage');
            const backImage = document.getElementById('backImage');
            
            if (!validateFileSize(frontImage) || !validateFileSize(backImage)) {
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="register-container">
        <h1 class="register-title">Registro Online de Socio</h1>
        <p class="subtitle">Completa tus datos para enviar tu solicitud.</p>

        <?php if (isset($errors) && is_array($errors) && count($errors) > 0): ?>
            <div class="error-messages">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (isset($error) && $error): ?>
            <div class="error-message">
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($success)): ?>
            <form method="POST" action="<?= u('partner/register') ?>" enctype="multipart/form-data" autocomplete="off" novalidate onsubmit="return validateForm()">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre Completo</label>
                        <input type="text" name="name" id="name" placeholder="Ej: Juan Pérez" 
                               value="<?= htmlspecialchars($formData['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="ci">Cédula de Identidad (CI)</label>
                        <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" 
                               value="<?= htmlspecialchars($formData['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cellPhoneNumber">Celular</label>
                        <input type="text" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: 65734215" 
                               value="<?= htmlspecialchars($formData['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo</label>
                        <input type="email" name="email" id="email" placeholder="ejemplo@dominio.com" 
                               value="<?= htmlspecialchars($formData['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Dirección</label>
                        <input type="text" name="address" id="address" placeholder="Ej: Calle 12 #123" 
                               value="<?= htmlspecialchars($formData['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Fecha de Nacimiento</label>
                        <input type="date" name="birthday" id="birthday" required 
                               min="<?= $minBirthday ?>" max="<?= $maxBirthday ?>"
                               value="<?= htmlspecialchars($formData['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="frontImage">CI (Frontal)</label>
                        <input type="file" name="frontImage" id="frontImage" accept="image/jpeg,image/png" required
                               onchange="showFileName(this, 'frontImageInfo'); validateFileSize(this);">
                        <small class="hint">Máximo 2MB, solo JPG o PNG</small>
                        <div id="frontImageInfo" class="file-info"></div>
                    </div>
                    <div class="form-group">
                        <label for="backImage">CI (Posterior)</label>
                        <input type="file" name="backImage" id="backImage" accept="image/jpeg,image/png" required
                               onchange="showFileName(this, 'backImageInfo'); validateFileSize(this);">
                        <small class="hint">Máximo 2MB, solo JPG o PNG</small>
                        <div id="backImageInfo" class="file-info"></div>
                    </div>
                </div>

                <div class="form-group is-hidden">
                    <label for="dateRegistration">Fecha de Registro</label>
                    <input type="text" id="dateRegistration_view" value="<?= date('Y-m-d H:i') ?> (automática)" readonly>
                    <small class="hint">Se guardará automáticamente al enviar. No es editable.</small>
                </div>
                
                <div class="g-recaptcha" data-sitekey="6Lf4Pb0rAAAAANwvyOXxEqIguKcFGo3uLgewa41b"></div>
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
                
                <input type="hidden" name="dateRegistration" value="<?= date('Y-m-d H:i:s') ?>">

                <button type="submit" class="register-btn">Enviar Solicitud</button>
            </form>
        <?php else: ?>
            <div class="success-inline"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <a href="<?= u('login') ?>" class="back-link">← Volver al inicio de sesión</a>
    </div>

    <?php if (isset($success) && $success): ?>
        <div class="modal-success" id="modalSuccess">
            <div class="modal-content">
                <h2>¡Solicitud enviada!</h2>
                <p>Se ha enviado un enlace de verificación a tu correo. Por favor, verifica tu email para completar el registro.</p>
                <button class="close-modal-btn" onclick="window.location.href='<?= u('login') ?>'">Cerrar</button>
            </div>
        </div>
        <script>
            setTimeout(function(){ window.location.href = "<?= u('login') ?>"; }, 5000);
        </script>
    <?php endif; ?>
</body>
</html>