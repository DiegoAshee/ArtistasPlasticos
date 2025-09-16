<?php 

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/database.php'; // Asegúrate de tener tu clase Database aquí
require_once __DIR__ . '/../Models/Competence.php';

class PermissionController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::singleton()->getConnection();
    }

    public function index()
    {
        // Solo administradores (rol = 1)
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }

        try {
            $db = $this->db;

            // 1️⃣ Obtener todos los roles dinámicamente
            $rolesQuery = $db->query("SELECT idRol, rol FROM rol ORDER BY idRol");
            $roles = $rolesQuery->fetchAll(PDO::FETCH_ASSOC);

            // 2️⃣ Obtener todas las opciones de menú (competencias)
            $competencesQuery = $db->query("SELECT idCompetence, menuOption FROM competence ORDER BY menuOption");
            $competences = $competencesQuery->fetchAll(PDO::FETCH_ASSOC);

            // 3️⃣ Obtener los permisos actuales
            $permissionsQuery = $db->query("
                SELECT p.idCompetence, p.idRol 
                FROM permission p
                INNER JOIN competence c ON p.idCompetence = c.idCompetence
                INNER JOIN rol r ON p.idRol = r.idRol
            ");
            $permissions = $permissionsQuery->fetchAll(PDO::FETCH_ASSOC);

            // 4️⃣ Construir arreglo de permisos para la vista
            $permissionsView = [];
            
            // Inicializar la matriz de permisos
            foreach ($competences as $menu) {
                $permissionsView[$menu['idCompetence']] = [
                    'id' => $menu['idCompetence'],
                    'name' => $menu['menuOption'],
                    'roles' => []
                ];
                
                // Inicializar todos los roles como no seleccionados
                foreach ($roles as $role) {
                    $permissionsView[$menu['idCompetence']]['roles'][$role['idRol']] = false;
                }
            }

            // Marcar los permisos existentes
            foreach ($permissions as $permission) {
                if (isset($permissionsView[$permission['idCompetence']])) {
                    $permissionsView[$permission['idCompetence']]['roles'][$permission['idRol']] = true;
                }
            }

            $data = [
                'title'       => 'Gestión de Permisos',
                'permissions' => $permissionsView,
                'roles'       => $roles,
                'currentPath' => 'permissions',
                'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)
            ];

            // Renderizar la vista
            $this->view('permissions/permissions', $data);
        } catch (Exception $e) {
            error_log('Error in PermissionController::index(): ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la página de permisos';
            $this->redirect('dashboard');
        }
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
        // Solo administradores (rol = 1)
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['permissions'])) {
            $permissions = $_POST['permissions'];
            
            try {
                $db = $this->db;
                
                // Iniciar transacción para asegurar la integridad de los datos
                $db->beginTransaction();
                
                // 1. Eliminar todos los permisos existentes
                $db->query('DELETE FROM permission');
                
                // 2. Insertar los nuevos permisos
                $stmt = $db->prepare('INSERT INTO permission (idCompetence, idRol) VALUES (?, ?)');
                
                foreach ($permissions as $permission) {
                    $competenceId = (int)$permission['id'];
                    
                    if (isset($permission['roles']) && is_array($permission['roles'])) {
                        foreach ($permission['roles'] as $roleId => $value) {
                            if ($value == '1') {
                                $roleId = (int)$roleId;
                                $stmt->execute([$competenceId, $roleId]);
                            }
                        }
                    }
                }
                
                // Confirmar la transacción
                $db->commit();
                
                $_SESSION['success'] = 'Permisos actualizados correctamente';
            } catch (PDOException $e) {
                // Revertir en caso de error
                if (isset($db) && $db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Error al actualizar permisos: ' . $e->getMessage());
                $_SESSION['error'] = 'Error al actualizar los permisos. Por favor, inténtelo de nuevo.';
            } catch (Exception $e) {
                if (isset($db) && $db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Error inesperado: ' . $e->getMessage());
                $_SESSION['error'] = 'Ocurrió un error inesperado. Por favor, contacte al administrador.';
            }
            
            $this->redirect('permissions');
        } else {
            // Si no es una petición POST o no vienen permisos, redirigir
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
