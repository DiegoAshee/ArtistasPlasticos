<?php
// app/Views/partner/register.php

// Helpers portables (si ya existen en tu config, puedes quitarlos aquí)
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}
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
        :root{
            --cream-50:#f9f8f6; --cream-100:#f5f2ed; --cream-200:#e8e2d8; --cream-300:#d9d0c1;
            --cream-400:#cfc4b0; --cream-500:#b8ab94; --cream-600:#9c8f7a; --cream-700:#7a7164;
            --cream-800:#5a5343; --cream-900:#3a362e;
        }
        *{ box-sizing: border-box; }
        body{
            min-height:100vh; margin:0; font-family:'Inter', Arial, sans-serif;
            background:url('<?= asset('img/579801593549840d8cf000e7cb35cfd6.jpg') ?>') no-repeat center center fixed;
            background-size:cover;
            display:flex; align-items:center; justify-content:center;
            padding:24px;
        }
        body::before{
            content:''; position:fixed; inset:0;
            background:rgba(255,255,255,0.35);
            z-index:0;
        }
        .register-container{
            position:relative; z-index:1;
            background:rgba(255,255,255,0.97);
            border-radius:28px;
            box-shadow:0 8px 40px #bca47844;
            max-width:760px; width:100%;
            padding:48px 44px 36px 44px;
            animation:fadeIn .6s ease-out;
            backdrop-filter: blur(3px);
        }
        @keyframes fadeIn{ from{opacity:0; transform:translateY(20px)} to{opacity:1; transform:none} }
        .register-title{
            text-align:center; color:#bca478; font-size:2.2rem; margin:0 0 22px 0;
            font-family:'Playfair Display', Georgia, serif; letter-spacing:.5px;
        }
        .subtitle{ text-align:center; color:#6b6b6b; margin-bottom:26px; font-size:.98rem; }
        .form-row{ display:flex; gap:20px; }
        .form-group{ margin-bottom:18px; flex:1; }
        label{ display:block; margin:0 0 7px 0; color:#9c8f7a; font-weight:600; font-size:.98rem; }
        input[type="text"], input[type="date"], input[type="email"]{
            width:100%; padding:14px 15px; border:2px solid #e1e5e9; border-radius:14px;
            font-size:1rem; background:#f9f8f6; transition:border-color .2s, box-shadow .2s; outline:none;
        }
        input:focus{ border-color:#bca478; box-shadow:0 0 0 2px #bca47833; }
        .hint{ display:block; font-size:.85rem; color:#7a7164; margin-top:6px; }
        .register-btn{
            width:100%; padding:15px 0; border:none; border-radius:14px; cursor:pointer;
            background:linear-gradient(90deg, #bca478 60%, #f7f1ae 100%); color:#fff;
            font-size:1.1rem; font-weight:800; letter-spacing:.4px;
            box-shadow:0 2px 12px #e1e5e9; transition:transform .15s, filter .2s; margin-top:2px;
        }
        .register-btn:hover{ transform:translateY(-2px); filter:saturate(1.05); }
        .error-message{
            background:#ffecec; color:#b63838; border:1px solid #ffc9c9;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; text-align:center; font-weight:600;
        }
        .success-inline{
            background:#e8f8ef; color:#18794e; border:1px solid #bfead2;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; text-align:center; font-weight:600;
        }
        .back-link{
            display:block; text-align:center; margin-top:16px; color:#bca478;
            text-decoration:none; font-weight:600;
        }
        .back-link:hover{ color:#a48c6b; text-decoration:underline; }

        /* Ocultar visualmente pero mantener en el DOM */
        .is-hidden{ display:none !important; }

        /* Modal éxito */
        .modal-success{ position:fixed; inset:0; background:rgba(44,44,44,.45);
            display:flex; align-items:center; justify-content:center; z-index:9999; }
        .modal-content{
            background:#fff; border-radius:18px; box-shadow:0 6px 32px #e1e5e9;
            padding:30px 26px 24px 26px; width:min(360px, 92vw); text-align:center; animation:fadeIn .4s ease-out;
        }
        .modal-content h2{ color:#10b981; margin:0 0 12px 0; font-size:1.4rem; }
        .modal-content p{ color:#444; margin:0 0 18px 0; }
        .close-modal-btn{
            background:#cfc4b0; color:#fff; border:none; border-radius:10px;
            padding:10px 22px; font-size:1rem; font-weight:700; cursor:pointer; transition:background .2s;
        }
        .close-modal-btn:hover{ background:#bca478; }

        @media (max-width: 860px){
            .register-container{ padding:28px 18px 22px 18px; }
            .form-row{ flex-direction:column; gap:0; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h1 class="register-title">Registro Online de Socio</h1>
        <p class="subtitle">Completa tus datos para enviar tu solicitud.</p>

        <?php if (isset($error) && $error): ?>
            <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!isset($success)): ?>
            <form method="POST" action="<?= u('partner/register') ?>" autocomplete="off" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Nombre Completo</label>
                        <input type="text" name="name" id="name" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label for="ci">Cédula de Identidad (CI)</label>
                        <input type="text" name="ci" id="ci" placeholder="Ej: 8845325" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="cellPhoneNumber">Celular</label>
                        <input type="text" name="cellPhoneNumber" id="cellPhoneNumber" placeholder="Ej: 65734215" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo</label>
                        <input type="email" name="email" id="email" placeholder="ejemplo@dominio.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="address">Dirección</label>
                        <input type="text" name="address" id="address" placeholder="Ej: Calle 12 #123" required>
                    </div>
                    <div class="form-group">
                        <label for="birthday">Fecha de Nacimiento</label>
                        <input type="date" name="birthday" id="birthday" required>
                    </div>
                </div>

                <!-- Fecha de registro: se envía y se mantiene en el DOM pero OCULTA -->
                <div class="form-group is-hidden">
                    <label for="dateRegistration">Fecha de Registro</label>
                    <input type="text" id="dateRegistration_view" value="<?= date('Y-m-d H:i') ?> (automática)" readonly>
                    <small class="hint">Se guardará automáticamente al enviar. No es editable.</small>
                </div>
                <!-- Valor real que viaja en el POST -->
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
                <h2>¡Registro exitoso!</h2>
                <p>Su solicitud fue enviada correctamente. Pronto nos pondremos en contacto con usted.</p>
                <button class="close-modal-btn" onclick="window.location.href='<?= u('login') ?>'">Cerrar</button>
            </div>
        </div>
        <script>
            setTimeout(function(){ window.location.href = "<?= u('login') ?>"; }, 3000);
        </script>
    <?php endif; ?>
</body>
</html>
