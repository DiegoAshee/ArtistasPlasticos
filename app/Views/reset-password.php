<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Asociación de Artistas</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', Arial, sans-serif;
            background: url('<?= asset("img/579801593549840d8cf000e7cb35cfd6.jpg") ?>') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }
        body::before{
            content:''; 
            position:absolute; 
            inset:0; 
            background:rgba(255,255,255,.3); 
            z-index:0;
        }
        .reset-container {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.98);
            padding: 40px 30px;
            border-radius: 22px;
            box-shadow: 0 12px 36px rgba(188,164,120,0.25);
            width: 100%;
            max-width: 420px;
            border: 1px solid #e8e2d8;
            animation: fadeIn .7s ease-out;
        }
        @keyframes fadeIn {
            from{opacity:0;transform:translateY(-20px) scale(0.95)}
            to{opacity:1;transform:translateY(0) scale(1)}
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            max-width: 90px; 
            max-height: 90px;
            border-radius: 16px;
            box-shadow: 0 2px 12px #e1e5e9;
            background: #fff;
            padding: 10px;
            display: block;
            margin: 0 auto;
        }
        h2 {
            color: #bca478;
            text-align: center;
            margin-bottom: 20px;
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 15px;
            transition: border-color 0.3s;
            font-family: inherit;
        }
        input[type="password"]:focus {
            border-color: #bca478;
            outline: none;
            box-shadow: 0 0 0 3px rgba(188,164,120,0.2);
        }
        button {
            width: 100%;
            padding: 14px;
            background: #bca478;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        button:hover {
            background: #a8926a;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(188,164,120,0.25);
        }
        .message {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
        }
        .error { background: #ffebee; color: #c62828; border:1px solid #ffcdd2; }
        .success { background: #e8f5e9; color: #2e7d32; border:1px solid #c8e6c9; }
    </style>
</head>
<body>
    <div class="reset-container">
        <!-- Logo -->
        <div class="logo">
            <img "<?= asset($logo_url) ?>" alt="Logo <?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <h2>Restablecer Contraseña</h2>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
        <form method="POST" action="<?= htmlspecialchars(BASE_URL . 'reset-password', ENT_QUOTES, 'UTF-8') ?>">
            <!-- TOKEN OCULTO -->
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            
            <div class="form-group">
                <label for="password">Nueva Contraseña:</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit">Restablecer Contraseña</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }
            if (password.length < 8) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }
        });
    </script>
</body>
</html>
