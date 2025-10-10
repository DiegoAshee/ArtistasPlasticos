<?php
require_once __DIR__ . '/../Config/helpers.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Models/Competence.php';
require_once __DIR__ . '/BaseController.php';

use App\Models\Notification;

class NotificationController extends BaseController
{
    protected $model;
    protected $competenceModel;

    public function __construct()
    {
        parent::__construct(); // Llama al constructor del BaseController
        $this->model = new Notification();
        $this->competenceModel = new \Competence();
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Muestra la vista de notificaciones
     */
    public function index()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . u('login'));
            exit();
        }

        // Inicializar variables de sesión
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $isAdmin = !empty($_SESSION['is_admin']);
        $roleId = (int)($_SESSION['role'] ?? 2); // 2 = Rol por defecto si no está definido

        try {
            // Obtener notificaciones
            $notifications = $this->model->getNotificationsForUser($currentUserId, $roleId);
        } catch (Exception $e) {
            // En caso de error, inicializar notificaciones como array vacío
            $notifications = [];
            error_log('Error al obtener notificaciones: ' . $e->getMessage());
        }

            // Configuración para la vista
            $title = 'Notificaciones';
            $currentPath = 'notifications';
            
            // Configurar breadcrumbs
            $breadcrumbs = [
                ['label' => 'Inicio', 'url' => u('dashboard')],
                ['label' => 'Notificaciones', 'url' => null]
            ];

            // Contar notificaciones no leídas
            $unreadCount = 0;
            if (is_array($notifications)) {
                foreach ($notifications as $n) {
                    if (empty($n['user_is_read'])) {
                        $unreadCount++;
                    }
                }
            } else {
                // Si no hay notificaciones, inicializar como array vacío
                $notifications = [];
            }

            // Actualizar el contador de notificaciones no leídas en el menú
            $menuOptions = $this->competenceModel->getByRole($roleId);
            if (is_array($menuOptions)) {
                foreach ($menuOptions as &$menuItem) {
                    if (isset($menuItem['url']) && $menuItem['url'] === 'notifications') {
                        $menuItem['badge'] = $unreadCount > 0 ? $unreadCount : null;
                    }
                    
                    // Establecer el estado activo basado en la ruta actual
                    if (isset($menuItem['url'])) {
                        $menuItem['active'] = ($currentPath === $menuItem['url']);
                    }
                }
                unset($menuItem); // Romper la referencia
            }

        // Pasar las variables a la vista usando el método view() del BaseController
        $this->view('notifications/notifications', [
            'title' => $title,
            'currentPath' => $currentPath,
            'breadcrumbs' => $breadcrumbs,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'menuOptions' => $menuOptions
        ]);
    }
    // Marca todas como leídas (AJAX)
    /**
     * Obtiene el menú según el rol del usuario
     */
    /**
     * Obtiene el menú para el rol especificado desde la base de datos
     * 
     * @deprecated 1.0.0 Usar $this->competenceModel->getByRole() en su lugar
     */
    protected function getMenuForRole($roleId, $currentPath = '')
    {
        // Este método ahora está obsoleto ya que el menú se carga directamente desde la base de datos
        // a través de $this->competenceModel->getByRole()
        return [];

        // Devolver el menú según el rol, o un menú vacío si no se encuentra
        return $menus[$roleId] ?? $menus[2] ?? []; // Por defecto, devuelve el menú de usuarios normales
    }

    /**
     * Marca todas las notificaciones como leídas
     */
    public function markAllRead()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        $currentUserId = $_SESSION['user_id'] ?? 0;
        $roleId = $_SESSION['role'] ?? 0;
        $ok = $this->model->markAllNotificationsAsRead($currentUserId, $roleId);
        echo json_encode(['success' => (bool)$ok]);
    }

    // Elimina una notificación (AJAX). Si es admin puede eliminar cualquier notificación.
    public function delete()
    {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $currentUserId = $_SESSION['user_id'] ?? null;
        $roleId = $_SESSION['role'] ?? null;
        $isAdmin = !empty($_SESSION['is_admin']);
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        // Si no es admin, pasar roleId para validación por rol
        $ok = $this->model->delete($id, $isAdmin ? null : $roleId);
        echo json_encode(['success' => (bool)$ok]);
    }
}