<?php
// =========================
// app/Views/login.php
// =========================

// Helpers (si ya los defines globalmente en config.php puedes quitar esta sección)
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Asociación de Artistas</title>

    <!-- Tailwind (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: {
                            50: 'var(--cream-50)',
                            100: 'var(--cream-100)',
                            200: 'var(--cream-200)',
                            300: 'var(--cream-300)',
                            400: 'var(--cream-400)',
                            500: 'var(--cream-500)',
                            600: 'var(--cream-600)',
                            700: 'var(--cream-700)',
                            800: 'var(--cream-800)',
                            900: 'var(--cream-900)',
                        }
                    },
                    fontFamily: {
                        playfair: ['Playfair Display','serif'],
                        poppins: ['Poppins','sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Iconos + Fuentes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet"/>

    <style>
        :root{
            --cream-50:#f9f8f6; --cream-100:#f5f2ed; --cream-200:#e8e2d8; --cream-300:#d9d0c1;
            --cream-400:#cfc4b0; --cream-500:#b8ab94; --cream-600:#9c8f7a; --cream-700:#7a7164;
            --cream-800:#5a5343; --cream-900:#3a362e;
        }
        body{
            font-family:'Poppins',sans-serif;
            min-height:100vh;
            /* ✅ portable: usa asset() para path del fondo */
            background:url('<?= asset("img/579801593549840d8cf000e7cb35cfd6.jpg") ?>') no-repeat center center fixed;
            background-size:cover;
            position:relative;
            color:var(--cream-900);
        }
        body::before{
            content:''; position:absolute; inset:0; background:rgba(255,255,255,.3); z-index:0;
        }
        .login-container{ position:relative; z-index:1; }
        .login-box{
            background:rgba(255,255,255,.98);
            border:1px solid var(--cream-200);
            box-shadow:0 8px 32px rgba(0,0,0,.1);
            backdrop-filter:blur(4px);
        }
        .login-header{ background:var(--cream-50); border-bottom:1px solid var(--cream-200); }
        .input-field{
            border:1px solid var(--cream-200); transition:all .3s; background:#fff; color:var(--cream-900);
        }
        .input-field:focus{
            border-color:var(--cream-400); box-shadow:0 0 0 3px rgba(207,196,176,.2); background:#fff;
        }
        .btn-login{ background-color:var(--cream-400); color:var(--cream-900); transition:all .3s; }
        .btn-login:hover{ background-color:var(--cream-500); }
        .divider{ display:flex; align-items:center; text-align:center; color:var(--cream-600); }
        .divider::before,.divider::after{ content:''; flex:1; border-bottom:1px solid var(--cream-200); }
        .divider:not(:empty)::before{ margin-right:1rem; } .divider:not(:empty)::after{ margin-left:1rem; }

        /* Logo centrado */
        .logo-center{ display:flex; justify-content:center; align-items:center; margin:2.2rem 0; width:100%; }
        .logo-center img{
            max-width:90px; max-height:90px; border-radius:16px; box-shadow:0 2px 12px #e1e5e9;
            background:#fff; padding:10px; display:block; margin:0 auto;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="login-container w-full max-w-md">
        <div class="login-box rounded-xl overflow-hidden">
            <!-- Logo -->
            <div class="logo-center">
                <img src="<?= asset('img/logo.png') ?>" alt="Logo Asociación de Artistas">
            </div>

            <!-- Encabezado -->
            <div class="login-header p-8 text-center">
                <h1 class="text-2xl font-playfair font-bold text-cream-800 mb-2">Asociación Boliviana de Artistas Plásticos</h1>
                <p class="text-cream-600">Inicia sesión para acceder a tu cuenta</p>
            </div>

            <!-- Formulario -->
            <!-- ✅ action y enlaces via u() para portabilidad -->
            <form method="POST" action="<?= u('login') ?>" class="p-8" autocomplete="on" novalidate>
                <?php if (isset($error) && $error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Usuario -->
                <div class="mb-6">
                    <label for="login" class="block text-sm font-medium text-cream-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Usuario
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-palette text-cream-400"></i>
                        </div>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="input-field w-full pl-10 pr-3 py-2 rounded-lg"
                            placeholder="Coloca tu Usuario"
                            value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['login']) : '' ?>"
                            required
                            autocomplete="username"
                        />
                    </div>
                </div>

                <!-- Contraseña -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-cream-700">
                            <i class="fas fa-key mr-2"></i>Contraseña
                        </label>
                        <a href="<?= u('forgot-password') ?>" class="text-sm text-cream-600 hover:text-cream-800">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-cream-400"></i>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="input-field w-full pl-10 pr-10 py-2 rounded-lg"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-cream-400 hover:text-cream-600"
                            id="togglePassword"
                            aria-label="Mostrar u ocultar contraseña"
                        >
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Recordar sesión -->
                <div class="flex items-center mb-6">
                    <input
                        type="checkbox"
                        id="remember_me"
                        name="remember_me"
                        class="h-4 w-4 text-cream-500 rounded border-cream-300 focus:ring-cream-400"
                        <?= !empty($_POST['remember_me']) ? 'checked' : '' ?>
                    />
                    <label for="remember_me" class="ml-2 block text-sm text-cream-700">
                        Mantener sesión iniciada
                    </label>
                </div>

                <!-- Botón -->
                <button type="submit" class="btn-login w-full py-3 px-4 rounded-lg font-medium text-white mb-6">
                    Iniciar sesión
                </button>

                

                <!-- Registro -->
                <p class="text-center text-sm text-cream-600">
                    ¿No tienes una cuenta?
                    <a href="<?= u('partner/register') ?>" class="font-medium text-cream-700 hover:text-cream-900">
                        Regístrate
                    </a>
                </p>
            </form>
        </div>
    </div>

    <script>
        // Toggle password
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
