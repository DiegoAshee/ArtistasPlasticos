<?php
// app/Core/Router.php
// Compatible PHP 7.x

class Router {
    private $routes = [];

    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'controller' => $controller,
            'action'     => $action
        ];
    }

    public function dispatch() {
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
        $requestPath   = $this->getNormalizedPath(); // /pasantes/jcrojas/login -> /login

        error_log("[Router] $requestMethod $requestPath");

        foreach ($this->routes as $route) {
            $regex = '#^' . str_replace(
                array('/', '([0-9]+)'),
                array('\/', '(\d+)'),
                rtrim($route['path'], '/')
            ) . '$#i';

            if ($route['path'] === '/') {
                $regex = '#^\/$#i';
            }

            if (preg_match($regex, $requestPath, $matches) && $route['method'] === $requestMethod) {
                array_shift($matches);

                $controllerClass = $route['controller'];
                $action          = $route['action'];
                $controllerPath  = __DIR__ . '/../Controllers/' . $controllerClass . '.php';

                if (!is_file($controllerPath)) return $this->showError("Controlador $controllerClass no encontrado");
                require_once $controllerPath;

                if (!class_exists($controllerClass)) return $this->showError("Clase $controllerClass no encontrada");
                $controller = new $controllerClass();

                if (!method_exists($controller, $action)) return $this->showError("Método $action no encontrado en $controllerClass");

                error_log("[Router] matched $controllerClass@$action " . json_encode($matches));
                call_user_func_array(array($controller, $action), $matches);
                return;
            }
        }

        $this->showError("404 - Página no encontrada: " . $requestPath);
    }

    private function getNormalizedPath() {
        // Ruta solicitada sin query string
        $uri = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
        if (!$uri) $uri = '/';

        // Base path real (carpeta donde vive index.php): /pasantes/jcrojas
        $basePath = rtrim(str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '')), '/');

        // Quitar prefijo de subcarpeta si existe
        if ($basePath && $basePath !== '/') {
            $pattern = '#^' . preg_quote($basePath, '#') . '#';
            $uri = preg_replace($pattern, '', $uri);
        }

        if ($uri === '' || $uri === false) $uri = '/';
        if ($uri !== '/' && substr($uri, -1) === '/') $uri = rtrim($uri, '/');

        // Normalizar /index.php a /
        if ($uri === '/index.php') $uri = '/';

        return $uri;
    }

    private function showError($message) {
        http_response_code(404);
        $base = '/';
        $config = __DIR__ . '/../Config/config.php';
        if (is_file($config)) {
            require_once $config;
            if (defined('BASE_URL')) $base = rtrim(BASE_URL, '/') . '/';
        }
        echo "<h1>Error</h1>";
        echo "<p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><a href='" . htmlspecialchars($base . "login", ENT_QUOTES, 'UTF-8') . "'>Ir al Login</a></p>";
    }
}
