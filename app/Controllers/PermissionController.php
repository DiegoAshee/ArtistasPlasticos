<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/database.php'; // Asegúrate de tener tu clase Database aquí
require_once __DIR__ . '/../Models/Competence.php'; // Asegúrate de que el modelo Competence esté cargado correctamente

class PermissionController extends BaseController
{
    // Constructor de la clase, se ejecuta cuando se crea una instancia de la clase
    public function __construct()
    {
        parent::__construct();  // Llama al constructor de la clase base (BaseController)
        $this->db = Database::singleton()->getConnection();  // Establece la conexión con la base de datos
    }

    // Método principal que se ejecuta para cargar la página de gestión de permisos
    public function index()
    {
        // Verificar si el usuario ha iniciado sesión
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debe iniciar sesión para acceder a esta sección';  // Establece un mensaje de error en la sesión
            $this->redirect('login');  // Redirige al usuario al formulario de inicio de sesión
            return;
        }

        // Limpiar mensajes de error previos
        unset($_SESSION['error']);  // Elimina cualquier mensaje de error que pueda haber quedado en la sesión

        // Obtener el ID del usuario actual desde la sesión
        $userId = $_SESSION['user_id'];

        // Verificar si el usuario tiene acceso al menú de permisos
        $db = $this->db;
        
        // Primero obtenemos el rol del usuario
        $stmt = $db->prepare("
            SELECT idRol FROM user WHERE idUser = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);  // Ejecuta la consulta pasando el ID del usuario
        $userRole = $stmt->fetch(PDO::FETCH_COLUMN);  // Obtiene el rol del usuario
        
        // Verificamos si el rol tiene permiso para acceder a 'Permisos' en el menú
        $stmt = $db->prepare("
            SELECT 1 
            FROM permission p
            JOIN competence c ON p.idCompetence = c.idCompetence
            WHERE p.idRol = :rol_id AND c.menuOption = 'Permisos'
            LIMIT 1
        ");
        $stmt->execute([':rol_id' => $userRole]);  // Ejecuta la consulta para verificar el permiso
        $hasPermission = $stmt->fetchColumn();  // Si se obtiene un valor, significa que el usuario tiene el permiso para "Permisos"
        
        // Si el usuario no tiene permiso, se redirige al dashboard
        if (!$hasPermission) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección';  // Establece un mensaje de error
            $this->redirect('dashboard');  // Redirige al usuario al dashboard
            return;
        }

        // Si el usuario tiene el permiso, continua con la carga de la vista de permisos
        try {
            // Obtener roles disponibles
            $rolesQuery = $db->query("SELECT idRol, rol FROM rol ORDER BY idRol");
            $roles = $rolesQuery->fetchAll(PDO::FETCH_ASSOC);  // Obtiene todos los roles disponibles

            // Obtener opciones de menú disponibles
            $competencesQuery = $db->query("SELECT idCompetence, menuOption FROM competence ORDER BY menuOption");
            $competences = $competencesQuery->fetchAll(PDO::FETCH_ASSOC);  // Obtiene todas las opciones de menú

            // Obtener permisos actuales para cada competencia (permiso por rol)
            $permissionsQuery = $db->query("
                SELECT p.idCompetence, p.idRol 
                FROM permission p
                INNER JOIN competence c ON p.idCompetence = c.idCompetence
                INNER JOIN rol r ON p.idRol = r.idRol
            ");
            $permissions = $permissionsQuery->fetchAll(PDO::FETCH_ASSOC);  // Obtiene los permisos actuales

            // Construir la vista de permisos
            $permissionsView = [];
            foreach ($competences as $menu) {
                // Para cada menú, inicializa los roles sin permisos
                $permissionsView[$menu['idCompetence']] = [
                    'id' => $menu['idCompetence'],
                    'name' => $menu['menuOption'],
                    'roles' => []
                ];

                // Inicializa todos los roles como no seleccionados
                foreach ($roles as $role) {
                    $permissionsView[$menu['idCompetence']]['roles'][$role['idRol']] = false;
                }
            }

            // Marcar los permisos existentes en la vista
            foreach ($permissions as $permission) {
                if (isset($permissionsView[$permission['idCompetence']])) {
                    $permissionsView[$permission['idCompetence']]['roles'][$permission['idRol']] = true;
                }
            }

            // Pasar la información a la vista
            $data = [
                'title' => 'Gestión de Permisos',  // Título de la página
                'permissions' => $permissionsView,  // Datos de permisos para mostrar en la vista
                'roles' => $roles,  // Datos de roles disponibles
                'currentPath' => 'permissions',  // Ruta actual
                'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)  // Opciones de menú según el rol del usuario
            ];

            // Renderizar la vista de permisos
            $this->view('permissions/permissions', $data);

        } catch (Exception $e) {
            // Manejo de errores en caso de que algo falle durante la obtención de datos o la construcción de la vista
            $_SESSION['error'] = 'Ocurrió un error al cargar los permisos. Intente de nuevo más tarde.';  // Establece un mensaje de error en la sesión
            error_log('Error en PermissionController::index(): ' . $e->getMessage());  // Loguea el error
            // Mostrar el error en la vista de error
            $this->view('error/error', ['message' => $_SESSION['error']]);
        }
    }

    // Método para crear un nuevo permiso (solo accesible para administradores)
    public function create()
    {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');  // Si no está autenticado, redirige al login
            return;
        }

        // Verificar si el usuario tiene la opción "Permisos" en el menú
        $userId = $_SESSION['user_id'];
        $db = $this->db;

        // Consulta para verificar si el usuario tiene permiso para "Permisos"
        $stmt = $db->prepare("
            SELECT c.menuOption
            FROM permission p
            JOIN competence c ON p.idCompetence = c.idCompetence
            JOIN rol r ON p.idRol = r.idRol
            JOIN user u ON u.idRol = r.idRol
            WHERE u.idUser = :user_id AND c.menuOption = 'Permisos'
        ");
        $stmt->execute([':user_id' => $userId]);
        $hasPermission = $stmt->fetchColumn();  // Si existe, el usuario tiene permiso

        // Si no tiene el permiso, muestra un mensaje de error
        if (!$hasPermission) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esta sección';
            $this->view('error/error', ['message' => $_SESSION['error']]);  // Mostrar el error sin redirigir al dashboard
            return;
        }

        try {
            $db = $this->db;

            // Obtener todos los roles
            $stmt = $db->query("SELECT * FROM rol");
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Pasar los datos a la vista
            $data = [
                'title' => 'Crear Nuevo Permiso',
                'roles' => $roles,
                'currentPath' => 'permissions',
                'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)
            ];

            // Renderizar la vista de creación de permisos
            $this->view('permissions/create', $data);
            
        } catch (Exception $e) {
            error_log('Error in PermissionController::create(): ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar el formulario de creación de permisos';
            // Mostrar el error sin redirigir
            $this->view('error/error', ['message' => $_SESSION['error']]);
        }
    }

    // Método para actualizar permisos (solo accesible para administradores)
    public function update()
    {
        // Verificar que el usuario tenga el permiso adecuado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Verificar si el usuario tiene la opción "Permisos" en el menú
        $userId = $_SESSION['user_id'];
        $db = $this->db;

        // Consulta para verificar si el usuario tiene el permiso para editar "Permisos"
        $stmt = $db->prepare("
            SELECT c.menuOption
            FROM permission p
            JOIN competence c ON p.idCompetence = c.idCompetence
            JOIN rol r ON p.idRol = r.idRol
            JOIN user u ON u.idRol = r.idRol
            WHERE u.idUser = :user_id AND c.menuOption = 'Permisos'
        ");
        $stmt->execute([':user_id' => $userId]);
        $hasPermission = $stmt->fetchColumn();

        if (!$hasPermission) {
            $_SESSION['error'] = 'No tienes permiso para editar los permisos';
            $this->view('error/error', ['message' => $_SESSION['error']]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['permissions'])) {
            $permissions = $_POST['permissions'];
            
            try {
                $db->beginTransaction();

                // Eliminar todos los permisos existentes
                $db->query('DELETE FROM permission');

                // Insertar los nuevos permisos
                $stmt = $db->prepare('INSERT INTO permission (idCompetence, idRol) VALUES (?, ?)');
                
                // Recorrer las competencias y sus roles
                foreach ($permissions as $permission) {
                    $competenceId = $permission['id'];
                    
                    // Verificar si hay roles seleccionados para esta competencia
                    if (isset($permission['roles']) && is_array($permission['roles'])) {
                        foreach ($permission['roles'] as $roleId => $isChecked) {
                            // Si el checkbox está marcado (valor '1'), insertar el permiso
                            if ($isChecked === '1') {
                                $stmt->execute([$competenceId, $roleId]);
                            }
                        }
                    }
                }

                $db->commit();
                $_SESSION['success'] = 'Permisos actualizados correctamente';
                
                // Obtener datos actualizados para la vista
                $this->refreshViewData();
                
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Error al actualizar permisos: ' . $e->getMessage());
                $_SESSION['error'] = 'Error al actualizar los permisos: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = 'No se recibieron datos para actualizar';
        }
        
        // Redirigir de vuelta a la vista de permisos
        $this->redirect('permissions');
    }
    
    // Método auxiliar para refrescar los datos de la vista
    private function refreshViewData()
    {
        $db = $this->db;
        
        // Obtener roles disponibles
        $rolesQuery = $db->query("SELECT idRol, rol FROM rol ORDER BY idRol");
        $roles = $rolesQuery->fetchAll(PDO::FETCH_ASSOC);

        // Obtener opciones de menú disponibles
        $competencesQuery = $db->query("SELECT idCompetence, menuOption FROM competence ORDER BY menuOption");
        $competences = $competencesQuery->fetchAll(PDO::FETCH_ASSOC);

        // Obtener permisos actuales
        $permissionsQuery = $db->query("
            SELECT p.idCompetence, p.idRol 
            FROM permission p
            INNER JOIN competence c ON p.idCompetence = c.idCompetence
            INNER JOIN rol r ON p.idRol = r.idRol
        ");
        $permissions = $permissionsQuery->fetchAll(PDO::FETCH_ASSOC);

        // Construir la vista de permisos
        $permissionsView = [];
        foreach ($competences as $menu) {
            $permissionsView[$menu['idCompetence']] = [
                'id' => $menu['idCompetence'],
                'name' => $menu['menuOption'],
                'roles' => []
            ];

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

        return [
            'title' => 'Gestión de Permisos',
            'permissions' => $permissionsView,
            'roles' => $roles,
            'currentPath' => 'permissions',
            'menuOptions' => (new Competence())->getByRole($_SESSION['role'] ?? 2)
        ];
    }
}
