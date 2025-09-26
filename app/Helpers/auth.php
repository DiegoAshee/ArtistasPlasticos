<?php
// app/Helpers/auth.php
declare(strict_types=1);

function requireRole(array $rolesPermitidos, string $redirect = 'dashboard'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rol = $_SESSION['role'] ?? null;

    if ($rol === null || !in_array((int)$rol, $rolesPermitidos, true)) {
        // Usar ruta relativa para evitar dependencia de BASE_URL
        header("Location: " . $redirect);
        exit;
    }
}

// Nueva función para validar rutas en el index.php
function checkRoutePermissions(string $route, array $protectedRoutes): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rol = $_SESSION['role'] ?? null;

    // Si se fuerza el cambio de contraseña, bloquear acceso a rutas protegidas
    // excepto a la propia ruta de cambio de contraseña o logout
    if (!empty($_SESSION['force_pw_change'] ?? null)) {
        $routeNormalized = ltrim($route, '/');
        // Permitir: change-password (página), logout, login y el endpoint AJAX del perfil
        $allowed = ['change-password', 'logout', 'login', 'users/change-password-profile'];
        if (!in_array($routeNormalized, $allowed, true)) {
            // Redirigir directamente a la ruta de cambio de contraseña para evitar loop con login
            header("Location: change-password");
            exit;
        }
    }

    if (isset($protectedRoutes[$route])) {
        $rolesPermitidos = $protectedRoutes[$route];
        if ($rol === null || !in_array((int)$rol, $rolesPermitidos, true)) {
            // Usar ruta relativa para evitar dependencia de BASE_URL
            header("Location: login");
            exit;
        }
    }
}
