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

        $this->showError('Ruta no encontrada', 404);
    }

    private function getNormalizedPath() {
        $basePath = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = '/' . trim(str_replace($basePath, '', $uri), '/');
        return $path === '' ? '/' : $path;
    }

    private function showError($message, $statusCode = 500) {
        http_response_code($statusCode);
        echo "<h1>Error $statusCode</h1>";
        echo "<p>$message</p>";
        exit;
    }
}
