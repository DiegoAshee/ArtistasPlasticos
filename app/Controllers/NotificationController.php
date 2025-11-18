<?php
require_once __DIR__ . '/../Config/helpers.php';
require_once __DIR__ . '/../Config/database.php';
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

        // Obtener rol desde BD para depurar diferencias
        try {
            $db = \Database::singleton()->getConnection();
            $stmt = $db->prepare('SELECT idRol FROM users WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', (int)$currentUserId, \PDO::PARAM_INT);
            $stmt->execute();
            $dbRole = $stmt->fetchColumn();
            $dbRole = $dbRole !== false ? (int)$dbRole : null;
        } catch (\Throwable $e) {
            $dbRole = null;
        }

        // Pasar las variables a la vista usando el método view() del BaseController
        $this->view('notifications/notifications', [
            'title' => $title,
            'currentPath' => $currentPath,
            'breadcrumbs' => $breadcrumbs,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'sessionRole' => (int)($_SESSION['role'] ?? 0),
            'dbRole' => $dbRole,
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
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        if ($currentUserId <= 0) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            return;
        }
        $ok = $this->model->markAllAsRead($currentUserId);
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

    public function markRead()
{
    // Inicializar la respuesta
    $response = [
        'success' => false,
        'message' => 'Error desconocido',
        'code' => 500
    ];

    // Configurar el tipo de contenido como JSON
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        // Verificar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['message'] = 'Método no permitido';
            $response['code'] = 405;
            throw new Exception($response['message'], $response['code']);
        }
        
        // Obtener y validar parámetros
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        $notificationId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($currentUserId <= 0) {
            $response['message'] = 'Usuario no autenticado';
            $response['code'] = 401;
            throw new Exception($response['message'], $response['code']);
        }
        
        if ($notificationId <= 0) {
            $response['message'] = 'ID de notificación inválido';
            $response['code'] = 400;
            throw new Exception($response['message'], $response['code']);
        }
        
        // Marcar como leída
        $result = $this->model->markNotificationAsRead($notificationId, $currentUserId);
        
        if ($result === false) {
            $errorInfo = $this->model->getErrorInfo();
            $response['message'] = 'No se pudo marcar la notificación como leída';
            if (isset($errorInfo['message'])) {
                $response['message'] .= ': ' . $errorInfo['message'];
            }
            $response['error'] = $errorInfo;
            throw new Exception($response['message'], 500);
        }
        
        // Respuesta exitosa
        $response = [
            'success' => true,
            'message' => 'Notificación marcada como leída',
            'notificationId' => $notificationId,
            'code' => 200
        ];
        
    } catch (Exception $e) {
        // Si no se ha establecido el código de estado, usar 500 por defecto
        $response['code'] = $e->getCode() ?: 500;
        $response['message'] = $e->getMessage();
        http_response_code($response['code']);
    } finally {
        // Asegurarse de que la respuesta sea siempre JSON válido
        if (!headers_sent()) {
            header_remove('X-Powered-By');
            header('Content-Type: application/json');
        }
        
        // Limpiar cualquier salida previa
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        // Enviar respuesta JSON
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

    /**
     * Abre una notificación: marca como leída y redirige al destino calculado.
     * Se puede acceder por GET para facilitar enlaces desde la UI.
     * @param int $id
     */
    public function open($id = 0)
    {
        $this->startSession();
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);
        if ($currentUserId <= 0) {
            $this->redirect('login');
            return;
        }

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect('notifications');
            return;
        }

        $notif = $this->model->getById($id);
        if (!$notif) {
            $this->redirect('notifications');
            return;
        }

        // Intentar marcar como leída para el usuario actual (si falla, continuar)
        try {
            $this->model->markNotificationAsRead($id, $currentUserId);
        } catch (\Throwable $e) {
            // Ignorar errores de marcado
        }

        // Calcular destino:
        $dest = '';
        // Si la notificación trae columna 'url', usarla
        if (!empty($notif['url'])) {
            $dest = $notif['url'];
        }

        // Intentar decodificar data JSON
        if (empty($dest) && !empty($notif['data'])) {
            $decoded = json_decode($notif['data'], true);
            if (is_array($decoded)) {
                // Priorizar keys útiles
                if (!empty($decoded['url'])) {
                    $dest = $decoded['url'];
                } elseif (!empty($decoded['route'])) {
                    $dest = $decoded['route'];
                } elseif (!empty($decoded['entity']) && !empty($decoded['id'])) {
                    $entity = strtolower((string)$decoded['entity']);
                    $eid = (int)$decoded['id'];
                    switch ($entity) {
                        case 'payment':
                        case 'pago':
                            $dest = 'payment/edit/' . $eid;
                            break;
                        case 'partner':
                        case 'socio':
                            $dest = 'admin/review-payments?mode=partners&partner=' . $eid;
                            break;
                        case 'contribution':
                        case 'contribucion':
                            // Abrir la vista de edición/detalle de la contribución concreta si se tiene el id
                            $dest = 'contribution/edit/' . $eid;
                            break;
                        default:
                            $dest = '';
                    }
                } elseif (!empty($decoded['payment_id'])) {
                    $dest = 'payment/edit/' . (int)$decoded['payment_id'];
                } elseif (!empty($decoded['partner_id'])) {
                    $dest = 'admin/review-payments?mode=partners&partner=' . (int)$decoded['partner_id'];
                }
                // Además, keys comunes que pueden venir directamente en el JSON
                if (empty($dest)) {
                    if (!empty($decoded['contribution_id'])) {
                        $dest = 'contribution/edit/' . (int)$decoded['contribution_id'];
                    } elseif (!empty($decoded['idContribution'])) {
                        $dest = 'contribution/edit/' . (int)$decoded['idContribution'];
                    } elseif (!empty($decoded['contributionId'])) {
                        $dest = 'contribution/edit/' . (int)$decoded['contributionId'];
                    }
                }
            }
        }

        // Normalizar destino y redirigir
        if ($dest === '' || $dest === '#') {
            $this->redirect('notifications');
            return;
        }

        // Depuración: registrar la notificación y el destino calculado
        try {
            error_log('[Notification::open] id=' . $id . ' computed_dest(before_norm)=' . $dest . ' raw_data=' . substr(($notif['data'] ?? ''), 0, 1000));
        } catch (\Throwable $e) {
            // ignore
        }

        // Normalizar destinos que contienen el host pero no el esquema (ej. "algoritmos.com.bo/pa...")
        try {
            $base = defined('BASE_URL') ? BASE_URL : '';
            $baseHost = '';
            $baseScheme = '';
            if ($base) {
                $p = parse_url($base);
                $baseHost = $p['host'] ?? '';
                $baseScheme = $p['scheme'] ?? '';
            }
            if (!empty($dest) && !preg_match('#^https?://#i', $dest) && $baseHost !== '' && stripos($dest, $baseHost) !== false) {
                // Si la URL ya contiene el host pero falta el esquema, añádelo usando el esquema de BASE_URL
                $scheme = $baseScheme ?: 'https';
                // Si dest ya comienza con '//' dejar tal cual (protocol-relative)
                if (strpos($dest, '//') === 0) {
                    $dest = $scheme . ':' . $dest;
                } elseif (!preg_match('#^//|^[a-z]+:#i', $dest)) {
                    $dest = $scheme . '://' . ltrim($dest, '/');
                }
                error_log('[Notification::open] id=' . $id . ' normalized_dest(to_absolute)=' . $dest);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Depuración: registrar destino final antes de redirección
        try {
            error_log('[Notification::open] id=' . $id . ' computed_dest(after_norm)=' . $dest);
        } catch (\Throwable $e) {
            // ignore
        }

        // Si es absoluta, redirigir tal cual
        if (preg_match('#^https?://#i', $dest)) {
            // Si la URL absoluta apunta a partner/pending-payments con contribution,
            // intentar calcular la última página igual que para rutas relativas.
            try {
                $parsedA = parse_url($dest);
                $pathA = $parsedA['path'] ?? '';
                $queryA = [];
                if (!empty($parsedA['query'])) parse_str($parsedA['query'], $queryA);
                if (stripos($pathA, 'partner/pending-payments') !== false && (!empty($queryA['contribution']) || (!empty($decoded) && !empty($decoded['id']) && ($decoded['entity'] ?? '') === 'contribution'))) {
                    $contributionId = isset($queryA['contribution']) ? (int)$queryA['contribution'] : ((int)($decoded['id'] ?? 0));
                    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
                    if ($currentUserId > 0) {
                        require_once __DIR__ . '/../Models/Usuario.php';
                        require_once __DIR__ . '/../Models/Payment.php';
                        $userModel = new \Usuario();
                        $user = $userModel->findById($currentUserId);
                        $idPartner = (int)($user['idPartner'] ?? 0);
                        if ($idPartner > 0) {
                            $paymentModel = new \Payment();
                            $pageSize = 10;
                            $res = $paymentModel->getPendingByPartner($idPartner, null, 1, $pageSize);
                            $total = (int)($res['total'] ?? 0);
                            $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;
                            if ($totalPages > 1) {
                                $queryA['page'] = $totalPages;
                                $queryA['pageSize'] = $pageSize;
                            }
                            $_SESSION['notification_flash'] = 'La contribución solicitada aparece en la última página.';
                            $baseA = ($parsedA['scheme'] ?? '') . (isset($parsedA['scheme']) ? '://' : '') . ($parsedA['host'] ?? '') . ($parsedA['path'] ?? '');
                            $dest = $baseA . '?' . http_build_query($queryA);
                        }
                    }
                }
            } catch (\Throwable $e) {
                error_log('Notification::open absolute pagination calc error: ' . $e->getMessage());
            }

            if (!headers_sent()) {
                header('Location: ' . $dest);
                exit();
            }
            // fallback JS redirect cuando ya se envió salida
            echo "<script>window.location.href='" . htmlspecialchars($dest, ENT_QUOTES) . "'</script>";
            exit();
        }

        // En otro caso, construir URL absoluta con u()
        $final = $dest;
        // Si es ruta relativa, construir con u()
        if (!preg_match('#^https?://#i', $final)) {
            $final = u($final);
        }

        // Si la URL apunta a partner/pending-payments con param contribution, intentar calcular la última página
        try {
            $parsed = parse_url($final);
            $path = $parsed['path'] ?? '';
            $query = [];
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $query);
            }
            if (stripos($path, 'partner/pending-payments') !== false && (!empty($query['contribution']) || (!empty($decoded) && !empty($decoded['id']) && ($decoded['entity'] ?? '') === 'contribution'))) {
                // Obtener idContribution del query o del data
                $contributionId = isset($query['contribution']) ? (int)$query['contribution'] : ((int)($decoded['id'] ?? 0));
                // Intentar obtener idPartner del usuario actual
                $currentUserId = (int)($_SESSION['user_id'] ?? 0);
                if ($currentUserId > 0) {
                    require_once __DIR__ . '/../Models/Usuario.php';
                    require_once __DIR__ . '/../Models/Payment.php';
                    $userModel = new \Usuario();
                    $user = $userModel->findById($currentUserId);
                    $idPartner = (int)($user['idPartner'] ?? 0);
                    if ($idPartner > 0) {
                        $paymentModel = new \Payment();
                        // Usar mismo pageSize que view (10)
                        $pageSize = 10;
                        $res = $paymentModel->getPendingByPartner($idPartner, null, 1, $pageSize);
                        $total = (int)($res['total'] ?? 0);
                        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;
                        // Si hay páginas, forzar ir a la última
                        if ($totalPages > 1) {
                            $query['page'] = $totalPages;
                            $query['pageSize'] = $pageSize;
                        }
                        // Añadir flash indicando que la contribución está en la última página
                        $_SESSION['notification_flash'] = 'La contribución solicitada aparece en la última página.';
                        // Reconstruir final
                        $base = ($parsed['scheme'] ?? '') . (isset($parsed['scheme']) ? '://' : '') . ($parsed['host'] ?? '') . ($parsed['path'] ?? '');
                        $final = $base . '?' . http_build_query($query);
                    }
                }
            }
        } catch (\Throwable $e) {
            // no bloquear redirección por errores en cálculo de página
            error_log('Notification::open pagination calc error: ' . $e->getMessage());
        }
        if (!headers_sent()) {
            header('Location: ' . $final);
            exit();
        }

        // Si ya se envió salida (headers_sent), forzar redirect por JS
        echo "<script>window.location.href='" . htmlspecialchars($final, ENT_QUOTES) . "'</script>";
        exit();
    }


}