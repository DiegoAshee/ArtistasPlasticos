<?php
// app/Helpers/auth.php
declare(strict_types=1);

function requireRole(array $rolesPermitidos, string $redirect = 'dashboard'): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rol = $_SESSION['role'] ?? null;

    if ($rol === null || !in_array((int)$rol, $rolesPermitidos, true)) {
        header("Location: " . BASE_URL . $redirect);
        exit;
    }
}

// 👇 Nueva función para validar rutas en el index.php
function checkRoutePermissions(string $route, array $protectedRoutes): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $rol = $_SESSION['role'] ?? null;

    if (isset($protectedRoutes[$route])) {
        $rolesPermitidos = $protectedRoutes[$route];
        if ($rol === null || !in_array((int)$rol, $rolesPermitidos, true)) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
    }
}
