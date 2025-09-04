<?php 

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/database.php'; // Asegúrate de tener tu clase Database aquí
require_once __DIR__ . '/../Models/Competence.php';

class PermissionController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::singleton()->getConnection();
    }

    public function index()
    {
        // Verificar si el usuario está autenticado y tiene rol de administrador (rol = 1)
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }

        $db = $this->db;
        
        // Consulta dinámica de permisos por roles
        $query = "
            SELECT 
                c.menuOption AS 'Menu Opcion',
                MAX(CASE WHEN r.rol = 'Socio' THEN '✔' ELSE '' END) AS 'Rol Socio',
                MAX(CASE WHEN r.rol = 'Administrador' THEN '✔' ELSE '' END) AS 'Rol Admin'
            FROM competence c
            LEFT JOIN permission p ON c.idCompetence = p.idCompetence
            LEFT JOIN rol r ON p.idRol = r.idRol
            GROUP BY c.menuOption
            ORDER BY c.idCompetence
        ";
        
        $stmt = $db->query($query);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'title'       => 'Gestión de Permisos',
            'permissions' => $permissions,
            'currentPath' => 'permissions',
            'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)
        ];

        // Renderizar la vista
        $this->view('permissions/permissions', $data);
    }

    public function create()
    {
        error_log('PermissionController::create() called'); // Debug log
        
        // Verify admin access
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            error_log('Access denied - User ID: ' . ($_SESSION['user_id'] ?? 'not set') . ', Role: ' . ($_SESSION['role'] ?? 'not set'));
            $this->redirect('dashboard');
            return;
        }

        try {
            $db = $this->db;
            
            // Get all roles
            $stmt = $db->query("SELECT * FROM rol");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log('Roles found: ' . count($roles)); // Debug log

            $data = [
                'title' => 'Crear Nuevo Permiso',
                'roles' => $roles,
                'currentPath' => 'permissions',
                'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)
            ];

            $viewPath = __DIR__ . '/../Views/permissions/create.php';
            error_log('View path: ' . $viewPath); // Debug log
            error_log('View exists: ' . (file_exists($viewPath) ? 'yes' : 'no')); // Debug log
            
            $this->view('permissions/create', $data);
            
        } catch (Exception $e) {
            error_log('Error in PermissionController::create(): ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el formulario de creación de permisos';
            $this->redirect('permissions');
        }
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permissions'])) {
            $permissions = $_POST['permissions'];
            
            try {
                $db = $this->db;
                $db->query('DELETE FROM role_permissions');
                
                $stmt = $db->prepare('INSERT INTO role_permissions (role_id, menu_id) VALUES (?, ?)');
                
                foreach ($permissions as $menuId => $roles) {
                    foreach ($roles as $roleId => $value) {
                        $stmt->execute([$roleId, $menuId]);
                    }
                }
                
                $_SESSION['success'] = 'Permisos actualizados correctamente';
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Error al actualizar los permisos: ' . $e->getMessage();
            }
            
            $this->redirect('permissions');
        }
    }

    // Helpers internos =========================

    private function getAllMenus()
    {
        $db = $this->db;
        $query = 'SELECT * FROM menu_options ORDER BY display_order ASC';
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllRoles()
    {
        $db = $this->db;
        $query = 'SELECT * FROM roles ORDER BY id ASC';
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllPermissions()
    {
        $db = $this->db;
        $query = 'SELECT role_id, menu_id FROM role_permissions';
        $stmt = $db->query($query);
        $permissions = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $permissions[$row['menu_id']][$row['role_id']] = true;
        }
        
        return $permissions;
    }
}
