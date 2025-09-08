<?php
// app/Views/partner/verify.php
require_once __DIR__ . '/../../Config/helpers.php';

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
    <title>Verificación de Correo</title>
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
        .verify-container {
            position:relative; z-index:1;
            background:rgba(255,255,255,0.97);
            border-radius:28px;
            box-shadow:0 8px 40px #bca47844;
            max-width:760px; width:100%;
            padding:48px 44px 36px 44px;
            animation:fadeIn .6s ease-out;
            backdrop-filter: blur(3px);
            text-align:center;
        }
        @keyframes fadeIn { from{opacity:0; transform:translateY(20px)} to{opacity:1; transform:none} }
        .verify-title {
            color:#bca478; font-size:2.2rem; margin:0 0 22px 0;
            font-family:'Playfair Display', Georgia, serif; letter-spacing:.5px;
        }
        .error-message {
            background:#ffecec; color:#b63838; border:1px solid #ffc9c9;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; font-weight:600;
        }
        .success-message {
            background:#e8f8ef; color:#18794e; border:1px solid #bfead2;
            padding:12px 14px; border-radius:10px; margin-bottom:16px; font-weight:600;
        }
        .back-link {
            display:block; text-align:center; margin-top:16px; color:#bca478;
            text-decoration:none; font-weight:600;
        }
        .back-link:hover { color:#a48c6b; text-decoration:underline; }
    </style>
</head>
<body>
    <div class="verify-container">
        <h1 class="verify-title">Verificación de Correo</h1>

        <?php if (isset($error) && $error): ?>
            <div class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php elseif (isset($success) && $success): ?>
            <div class="success-message"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <a href="<?= u('login') ?>" class="back-link">← Volver al inicio de sesión</a>
    </div>
</body>
</html>