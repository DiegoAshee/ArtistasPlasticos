<?php
// app/Controllers/BaseController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php'; // Para BASE_URL

class BaseController
{
    // ===== Helpers base =====
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Endurecer cookie (activa 'secure' si usas HTTPS)
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            // ini_set('session.cookie_secure', '1'); // en HTTPS
            session_start();
        }
    }

    protected function redirect(string $path): void
    {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit();
    }

    protected function addDebug(string $msg): void
    {
        // Solo loggea si tienes APP_DEBUG=true definido en tu config
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log($msg);
        }
    }

    // ====== Vistas ======
    protected function view(string $view, array $data = []): void
    {
        if (!empty($data)) extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (is_file($viewPath)) {
            ob_start();
            try {
                // 👉 Asegura u()/asset() en TODAS las vistas/partials
                include_once __DIR__ . '/../Views/helpers.php';

                include $viewPath;
                ob_end_flush();
            } catch (\Throwable $e) {
                ob_end_clean();
                echo "<h1>Error en la vista</h1>";
                echo "<p>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            }
        } else {
            $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
            echo "<h1>Error 404</h1>";
            echo "<p>Vista no encontrada: " . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p>Ruta buscada: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p><a href='" . htmlspecialchars($base . "/login", ENT_QUOTES, 'UTF-8') . "'>Ir al Login</a></p>";
        }
    }
}
