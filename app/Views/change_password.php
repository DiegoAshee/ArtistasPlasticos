<?php
// Helpers (iguales a los de login)
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
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cambiar contraseña</title>

    <!-- Tailwind (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: {
                            50: 'var(--cream-50)', 100: 'var(--cream-100)', 200: 'var(--cream-200)',
                            300: 'var(--cream-300)', 400: 'var(--cream-400)', 500: 'var(--cream-500)',
                            600: 'var(--cream-600)', 700: 'var(--cream-700)', 800: 'var(--cream-800)',
                            900: 'var(--cream-900)'
                        }
                    },
                    fontFamily: { playfair: ['Playfair Display','serif'], poppins: ['Poppins','sans-serif'] }
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
            background:url('<?= asset("img/579801593549840d8cf000e7cb35cfd6.jpg") ?>') no-repeat center center fixed;
            background-size:cover; position:relative; color:var(--cream-900);
        }
        body::before{ content:''; position:absolute; inset:0; background:rgba(255,255,255,.3); z-index:0; }
        .box{ background:rgba(255,255,255,.98); border:1px solid var(--cream-200); box-shadow:0 8px 32px rgba(0,0,0,.1); backdrop-filter:blur(4px); }
        .input-field{ border:1px solid var(--cream-200); transition:all .3s; background:#fff; color:var(--cream-900); }
        .input-field:focus{ border-color:var(--cream-400); box-shadow:0 0 0 3px rgba(207,196,176,.2); background:#fff; }
        .btn-main{ background-color:var(--cream-500); color:#fff; transition:all .3s; }
        .btn-main:hover{ background-color:var(--cream-500); filter:brightness(0.95); }
    </style>

    <script>
        // Validación ligera en cliente (el servidor también valida)
        function validateForm(e){
            const pw = document.getElementById('new_password').value.trim();
            const cf = document.getElementById('confirm_password').value.trim();
            const reLen = /^.{8,12}$/; const reUp=/[A-Z]/; const reLo=/[a-z]/; const reNum=/[0-9]/; const reSym=/[^A-Za-z0-9]/;
            let msgs=[];
            if(!reLen.test(pw)) msgs.push('La contraseña debe tener entre 8 y 12 caracteres');
            if(!reUp.test(pw))  msgs.push('Debe contener al menos una letra mayúscula');
            if(!reLo.test(pw))  msgs.push('Debe contener al menos una letra minúscula');
            if(!reNum.test(pw)) msgs.push('Debe contener al menos un número');
            if(!reSym.test(pw)) msgs.push('Debe contener al menos un símbolo');
            if(pw !== cf) msgs.push('Las contraseñas no coinciden');
            if(msgs.length){ const box=document.getElementById('clientErrors'); if(box){ box.innerHTML=msgs.map(m=>`<div>${m}</div>`).join(''); box.classList.remove('hidden'); }
                e.preventDefault(); return false; }
            return true;
        }
        // Toggle genérico para inputs password
        function toggleVisibility(inputId, iconId){
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            if(!input || !icon) return;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md relative z-10">
        <div class="box rounded-xl overflow-hidden">
            <div class="p-8 text-center bg-cream-50 border-b border-cream-200">
                <h1 class="text-2xl font-playfair font-bold text-cream-800">Cambiar contraseña</h1>
                <p class="text-cream-600 text-sm mt-1">Configura una contraseña segura para continuar</p>
            </div>

            <form method="post" class="p-8" onsubmit="return validateForm(event)">
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded text-left">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-exclamation-circle text-red-500 mt-1"></i></div>
                            <div class="ml-3"><p class="text-sm text-red-700"><?= $error ?></p></div>
                        </div>
                    </div>
                <?php endif; ?>
                <div id="clientErrors" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded text-left"></div>

                <!-- Nueva contraseña -->
                <div class="mb-6">
                    <label for="new_password" class="block text-sm font-medium text-cream-700 mb-2">
                        <i class="fas fa-key mr-2"></i>Nueva contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-cream-400"></i>
                        </div>
                        <input id="new_password" type="password" name="new_password"
                               class="input-field w-full pl-10 pr-10 py-2 rounded-lg"
                               required minlength="8" maxlength="12"
                               pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,12}"
                               title="8-12 caracteres, incluir al menos: 1 mayúscula, 1 minúscula, 1 número y 1 símbolo"
                               placeholder="••••••••"/>
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-cream-400 hover:text-cream-600" aria-label="Mostrar u ocultar contraseña" onclick="toggleVisibility('new_password','icon_np')">
                            <i id="icon_np" class="far fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-xs text-cream-600 mt-2">8-12 caracteres. Debe incluir al menos: 1 mayúscula, 1 minúscula, 1 número y 1 símbolo.</p>
                </div>

                <!-- Confirmar contraseña -->
                <div class="mb-6">
                    <label for="confirm_password" class="block text-sm font-medium text-cream-700 mb-2">
                        <i class="fas fa-check mr-2"></i>Confirmar contraseña
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-cream-400"></i>
                        </div>
                        <input id="confirm_password" type="password" name="confirm_password"
                               class="input-field w-full pl-10 pr-10 py-2 rounded-lg"
                               required minlength="8" maxlength="12" placeholder="••••••••"/>
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-cream-400 hover:text-cream-600" aria-label="Mostrar u ocultar confirmación" onclick="toggleVisibility('confirm_password','icon_cp')">
                            <i id="icon_cp" class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-main w-full py-3 px-4 rounded-lg font-medium text-white">Guardar</button>
            </form>
        </div>
    </div>
</body>
</html>
