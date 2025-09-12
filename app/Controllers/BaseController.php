<?php
// app/Controllers/BaseController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php'; // Para BASE_URL
require_once __DIR__ . '/../Config/helpers.php'; // Helpers globales (u(), asset(), p())
require_once __DIR__ . '/../Config/database.php'; // Conexión a BD
require_once __DIR__ . '/../Models/Competence.php'; // Para el menú

class BaseController
{
    protected $db;
    protected $competenceModel;

    public function __construct()
    {
        // Arrancar la sesión en todos los controladores
        $this->startSession();
        
        // Inicializar conexión a BD y modelo
        $this->db = Database::singleton()->getConnection();
        $this->competenceModel = new Competence();
    }

    // ===== Helpers base =====
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Endurecer cookie
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_start();
        }
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . u($path));
        exit();
    }

    protected function addDebug(string $msg): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log($msg);
        }
    }

    /**
     * Obtener datos del header desde la base de datos
     */
    protected function getHeaderData(): array
    {
        try {
            // Ajusta el nombre de la tabla según tu estructura real
            // Basado en tu imagen: idOption, title, imageURL, status, idUser
            $sql = "SELECT title, imageURL FROM `option` WHERE status = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $option = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($option) {
                return [
                    'logo_url' => $option['imageURL'] ?? 'assets/images/logo.png',
                    'site_title' => $option['title'] ?? 'Asociación de Artistas',
                    'tagline' => 'de Artistas' // Puedes ajustar esto según tu campo
                ];
            }
            
            // Valores por defecto si no hay datos en BD
            return [
                'logo_url' => 'assets/images/logo.png',
                'site_title' => 'Asociación de Artistas',
                'tagline' => 'de Artistas'
            ];
            
        } catch (\PDOException $e) {
            error_log('BaseController::getHeaderData error: ' . $e->getMessage());
            // Valores por defecto en caso de error
            return [
                'logo_url' => 'assets/images/logo.png',
                'site_title' => 'Asociación de Artistas',
                'tagline' => 'de Artistas'
            ];
        }
    }

    // ====== Vistas ======
    protected function view(string $view, array $data = []): void
    {
        // Obtener datos del header
        $headerData = $this->getHeaderData();
        
        // Obtener opciones del menú según el rol del usuario
        $roleId = $_SESSION['role'] ?? 2;
        $menuOptions = $this->competenceModel->getByRole($roleId);

        // Default data
        $defaultData = [
            'menuOptions' => $menuOptions,
            'currentPath' => $_SERVER['REQUEST_URI'] ?? '',
            'logo_url' => $headerData['logo_url'],
            'site_title' => $headerData['site_title'],
            'tagline' => $headerData['tagline']
        ];

        // Merge with provided data
        $data = array_merge($defaultData, $data);
        
        if (!empty($data)) extract($data);
        $viewPath = p('app/Views/' . $view . '.php');
        
        if (is_file($viewPath)) {
            ob_start();
            try {
                include $viewPath;
                ob_end_flush();
            } catch (\Throwable $e) {
                ob_end_clean();
                echo "<h1>Error en la vista</h1>";
                echo "<p>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
            }
        } else {
            echo "<h1>Error 404</h1>";
            echo "<p>Vista no encontrada: " . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p>Ruta buscada: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p><a href='" . htmlspecialchars(u('login'), ENT_QUOTES, 'UTF-8') . "'>Ir al Login</a></p>";
        }
    }
}
?>