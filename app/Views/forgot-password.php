<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
 
// Redirigir si ya está logueado
if (isset($_SESSION['user_id'])) {
    header('Location: ' . u('dashboard'));
    exit;
}
 
// Helpers para URLs (mismos que usas en login)
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}
 
// Variables del controlador si existen
$error     = $error     ?? '';
$success   = $success   ?? '';
$logo_url  = $logo_url  ?? '';   // igual que en login
$bgUrl     = $bgUrl     ?? null; // si manejas fondo dinámico desde Option activa
 
// ✅ Fallbacks (fondo como al principio)
$bgDefault = asset("img/579801593549840d8cf000e7cb35cfd6.jpg");
$lgDefault = asset("img/logo.png");
 
// Fondo final (si no llegó del controlador, usa default)
$bgFinal = !empty($bgUrl) ? $bgUrl : $bgDefault;
 
// ✅ Logo exactamente como en login (con fallback)
$logoSrc = !empty($logo_url) ? asset($logo_url) : $lgDefault;
 
// Escapar para HTML/CSS
$bgFinalEsc = htmlspecialchars($bgFinal, ENT_QUOTES, 'UTF-8');
$logoSrcEsc = htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Olvidé mi Contraseña - Asociación de Artistas</title>
 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap%22 rel="stylesheet"/>
 
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        :root{
            --cream-50:#f9f8f6; --cream-100:#f5f2ed; --cream-200:#e8e2d8; --cream-300:#d9d0c1;
            --cream-400:#cfc4b0; --cream-500:#b8ab94; --cream-600:#9c8f7a; --cream-700:#7a7164;
            --cream-800:#5a5343; --cream-900:#3a362e;
            --primary:#bca478; --primary-dark:#8a744f;
            --error:#c62828; --success:#2e7d32;
        }
 
        html, body { height: 100%; }
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: url('<?= $bgFinalEsc ?>') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            color: var(--cream-900);
        }
 
        .password-container{
            position:relative;
            z-index:1;
            background:rgba(255,255,255,0.98);
            border-radius:22px;
            box-shadow:0 12px 36px rgba(188,164,120,0.25);
            max-width:450px;
            width:100%;
            padding:40px;
            animation:fadeIn .7s ease-out;
            border:1px solid var(--cream-200);
        }
        @keyframes fadeIn{
            from{opacity:0;transform:translateY(-20px) scale(0.95)}
            to{opacity:1;transform:translateY(0) scale(1)}
        }
 
        .password-header{ text-align:center; margin-bottom:30px; }
        .password-header h2{
            color:var(--primary);
            font-family:'Playfair Display', serif;
            margin-bottom:12px;
            font-size:28px;
            font-weight:700;
        }
        .password-header p{ color:#666; font-size:15px; line-height:1.5; }
 
        .form-group{ margin-bottom:24px; }
        .form-group label{
            display:block; margin-bottom:8px; color:#555; font-weight:500; font-size:14px;
        }
        .form-group input{
            width:100%; padding:14px 16px; border:2px solid var(--cream-300);
            border-radius:12px; font-size:15px; transition:all .3s ease; background:#fff; font-family: inherit;
        }
        .form-group input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 3px rgba(188,164,120,.15);
            outline:none;
        }
 
        .btn-submit{
            width:100%; padding:15px; background:var(--primary); color:#fff; border:none;
            border-radius:12px; font-size:16px; font-weight:600; cursor:pointer;
            transition:all .3s ease; letter-spacing:.5px; margin-top:10px; font-family: inherit;
            display:flex; align-items:center; justify-content:center; gap:10px;
        }
        .btn-submit:hover{
            background:var(--primary-dark); transform:translateY(-2px);
            box-shadow: 0 6px 12px rgba(188,164,120,0.25);
        }
 
        .back-to-login{ text-align:center; margin-top:24px; }
        .back-to-login a{
            color:var(--primary); text-decoration:none; font-weight:500; font-size:15px; transition:color .3s;
            display:inline-flex; align-items:center; gap:6px;
        }
        .back-to-login a:hover{ color:var(--primary-dark); text-decoration:underline; }
 
        .alert{
            padding:14px 16px; border-radius:10px; margin-bottom:24px; font-size:14px; text-align:center;
            display:flex; align-items:center; justify-content:center; gap:10px;
        }
        .alert-error{ background:#ffebee; color:var(--error); border:1px solid #ffcdd2; }
        .alert-success{ background:#e8f5e9; color:var(--success); border:1px solid #c8e6c9; }
 
        .logo { text-align:center; margin-bottom:20px; }
        .logo img.logo-img{
            max-width:90px; max-height:90px; border-radius:16px; box-shadow:0 2px 12px #e1e5e9;
            background:#fff; padding:10px; display:block; margin:0 auto;
        }
 
        @media (max-width:480px){
            .password-container{ padding:30px 20px; }
            .password-header h2{ font-size:24px; }
            .password-header p { font-size: 14px; }
        }
    </style>
</head>
<body>
    <div class="password-container">
        <div class="logo">
            <!-- Logo igual que en login -->
            <img src="<?= $logoSrcEsc ?>" alt="Logo Asociación de Artistas" class="logo-img">
        </div>
 
        <div class="password-header">
            <h2>Recuperar Contraseña</h2>
            <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña</p>
        </div>
 
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
 
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
 
        <!-- El formulario lo procesa el controlador -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="ejemplo@dominio.com"
                    required
                    value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : '' ?>"
                />
            </div>
 
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación
            </button>
        </form>
 
        <div class="back-to-login">
            <a href="<?= htmlspecialchars(u('login'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="fas fa-arrow-left"></i> Volver al Inicio de Sesión
            </a>
        </div>
    </div>
 
    <script>
        // Validación básica del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
 
            if (!email) { e.preventDefault(); alert('Por favor, ingresa tu correo electrónico'); return false; }
            if (!emailRegex.test(email)) { e.preventDefault(); alert('Por favor, ingresa un correo electrónico válido'); return false; }
        });
    </script>
</body>
</html>