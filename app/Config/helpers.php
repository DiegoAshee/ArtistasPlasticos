<?php
// app/Config/helpers.php
 
if (!function_exists('u')) {
    /**
     * Construye URL absoluta a partir de BASE_URL.
     * Ej: u('images/carnets/foto.jpg')
     * => https://algoritmos.com.bo/pasantes/jcrojas/images/carnets/foto.jpg
     */
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
 
if (!function_exists('asset')) {
    function asset(string $path): string {
        return u($path);
    }
}
 
if (!function_exists('p')) {
    /**
     * Construye ruta física en disco a partir de BASE_PATH.
     * Ej: p('images/carnets')
     * => /home/USUARIO/public_html/pasantes/jcrojas/images/carnets
     */
    function p(string $path = ''): string {
        $base = rtrim(defined('BASE_PATH') ? BASE_PATH : __DIR__, DIRECTORY_SEPARATOR);
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, ltrim($path, '/'));
        return $base . DIRECTORY_SEPARATOR . $normalized;
    }
}