<?php
// app/Controllers/UserController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController
{
    public function UserProfile(): void
    {
        $this->startSession();
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Competence.php';
        
        // Get user profile data
        $userModel = new \Usuario();
        $users = $userModel->getUserProfile((int)($_SESSION['role'] ?? 0), (int)($_SESSION['user_id'] ?? 0));
        
        // Get menu options for the sidebar
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
        
        $this->view('users/perfil', [
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'users/profile',
            'roleId' => $roleId
        ]);
    }

    public function editProfile(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Competence.php';
        
        // Obtener datos del usuario
        $userModel = new \Usuario();
        $users = $userModel->getUserProfile((int)($_SESSION['role'] ?? 0), (int)($_SESSION['user_id'] ?? 0));
        
        // Obtener opciones del menú para la barra lateral
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
        
        $this->view('users/editar_perfil', [
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'users/profile/edit',
            'roleId' => $roleId
        ]);
    }

    public function updateProfile(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        // Aquí iría la lógica para actualizar el perfil
        // Por ahora solo redirigimos de vuelta al perfil
        $this->redirect('users/profile');
    }
    
   // Modificaciones en el controlador UserController.php

// En listUsers: cambiar a getNonSocioUsers y ajustar debug
public function listUsers(): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) { 
        $this->redirect('login'); 
    }

    // Debug: verificar sesión
    error_log("DEBUG UserController::listUsers - Usuario en sesión: " . ($_SESSION['user_id'] ?? 'NO_ID'));
    error_log("DEBUG UserController::listUsers - Role: " . ($_SESSION['role'] ?? 'NO_ROLE'));

    // Menú dinámico desde BD (según rol)
    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);

    // Usuarios no socios desde el modelo
    require_once __DIR__ . '/../Models/Usuario.php';
    $userModel = new \Usuario();
    $users = $userModel->getNonSocioUsers();

    // Debug: verificar datos
    error_log("DEBUG UserController::listUsers - Total usuarios obtenidos: " . count($users));
    if (!empty($users)) {
        error_log("DEBUG UserController::listUsers - Primer usuario: " . print_r($users[0], true));
    } else {
        error_log("DEBUG UserController::listUsers - ADVERTENCIA: Array de usuarios está vacío");
        
        // Hacer una consulta de debug adicional
        try {
            $debugUsers = $userModel->getAll(); // Este método sí debería devolver datos
            error_log("DEBUG UserController::listUsers - Usuarios con getAll(): " . count($debugUsers));
        } catch (Exception $e) {
            error_log("DEBUG UserController::listUsers - Error en getAll(): " . $e->getMessage());
        }
    }

    // Render
    $this->view('users/list', [
        'users'       => $users,
        'menuOptions' => $menuOptions, // ← lo consumen los partials (sidebar)
        'roleId'      => $roleId,
        // 'currentPath' lo fija la propia vista como 'users'
    ]);
}

public function createUser(): void
{
    $this->startSession();

    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    // Get menu options for the sidebar
    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);

    // Cargar roles excluyendo el 2
    require_once __DIR__ . '/../Models/Role.php'; // Corregido a Rol
    $rolModel = new \Role();
    $allRoles = $rolModel->getAll();
    $roles = array_filter($allRoles, function($rol) {
        return (int)$rol['idRol'] !== 2;
    });

    // Debug: Verificar roles cargados
    error_log("DEBUG createUser - Roles cargados: " . print_r($roles, true));

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // Obtener datos del formulario
        $login = trim((string)($_POST['ci'] ?? ''));      // CI como login
        $email = trim((string)($_POST['email'] ?? ''));
        $idRol = (int)($_POST['idRol'] ?? 0);

        error_log("DEBUG - Datos recibidos del formulario:");
        error_log("- CI/login: " . $login);
        error_log("- email: " . $email);
        error_log("- idRol: " . $idRol);

        // Validaciones
        $errors = [];

        if ($login === '') {
            $errors[] = "La cédula de identidad es obligatoria";
        } elseif (strlen($login) < 7 || strlen($login) > 10) {
            $errors[] = "La cédula debe tener entre 7 y 10 dígitos";
        } elseif (!ctype_digit($login)) {
            $errors[] = "La cédula debe contener solo números";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        }

        if ($idRol <= 0 || $idRol === 2) {
            $errors[] = "Debe seleccionar un rol válido (no socio)";
        }

        // Verificar duplicados
        require_once __DIR__ . '/../Models/Usuario.php';
        $userModel = new \Usuario();
        
        if ($userModel->loginExists($login)) {
            $errors[] = "La cédula $login ya está registrada";
        }
        
        if ($userModel->emailExists($email)) {
            $errors[] = "El email $email ya está registrado";
        }

        if (empty($errors)) {
            try {
                error_log("DEBUG - Creando usuario...");
                
                // Usar la cédula como contraseña temporal
                $temporalPassword = $login;
                
                $userId = $userModel->create([
                    'login'     => $login,             // CI como login
                    'password'  => $temporalPassword,  // CI como contraseña temporal
                    'email'     => $email,             // Email
                    'idRol'     => $idRol,             // Rol seleccionado
                    'idPartner' => 226,                // Valor temporal (comentar cuando permita NULL)
                    'status'    => 1,                  // Activo
                    'firstSession' => 1                // Primer inicio de sesión
                ]);
                
                error_log("DEBUG - Usuario ID creado: " . ($userId ?: 'FALSO'));
                
                // Validar que se creó correctamente
                if (!$userId || $userId === false) {
                    throw new \Exception("No se pudo crear la cuenta de usuario");
                }

                error_log("DEBUG - Usuario creado exitosamente");
                
                // Mensaje de éxito
                $_SESSION['success_message'] = "Usuario creado exitosamente.<br><strong>Login:</strong> $login<br><strong>Contraseña temporal:</strong> $temporalPassword";
                $this->redirect('users/list');
                return;

            } catch (\Throwable $e) {
                error_log("DEBUG - Error al crear usuario: " . $e->getMessage());
                error_log("DEBUG - Trace: " . $e->getTraceAsString());
                $errors[] = "Error interno: " . $e->getMessage();
            }
        }

        // Si hay errores, guardarlos para mostrar
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            error_log("DEBUG - Errores de validación: " . $error);
        }
    }

    // Preparar datos para la vista
    $viewData = [
        'menuOptions' => $menuOptions,
        'currentPath' => 'users/create',
        'roleId' => $roleId,
        'roles' => $roles  // Pasar roles a la vista
    ];

    if (isset($error)) {
        $viewData['error'] = $error;
    }

    // Debug: Verificar datos pasados a la vista
    error_log("DEBUG createUser - Datos para la vista: " . print_r($viewData, true));

    // Renderizar formulario
    $this->view('users/create', $viewData);
}

// En editUser: agregar carga de roles, excluir 2, y prevenir edición de rol 2
public function editUser(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $userId = (int)($id ?? $_GET['id'] ?? 0);
    if ($userId <= 0) {
        $_SESSION['error_message'] = "ID de usuario inválido";
        $this->redirect('users/list');
        return;
    }

    require_once __DIR__ . '/../Models/Usuario.php';
    require_once __DIR__ . '/../Models/Competence.php';
    require_once __DIR__ . '/../Models/Role.php';

    $userModel = new \Usuario();
    $user = $userModel->findByIdIncludingInactive($userId);

    if (!$user) {
        $_SESSION['error_message'] = "Usuario no encontrado";
        $this->redirect('users/list');
        return;
    }

    // Prevenir edición de usuarios con rol 2 (socio)
    if ((int)$user['idRol'] === 2) {
        $_SESSION['error_message'] = "Los usuarios socios se editan en un formulario aparte";
        $this->redirect('users/list');
        return;
    }

    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);

    // Cargar roles excluyendo el 2
    $rolModel = new \Role();
    $allRoles = $rolModel->getAll();
    $roles = array_filter($allRoles, function($rol) {
        return (int)$rol['idRol'] !== 2;
    });

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $login = trim((string)($_POST['login'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idRol = (int)($_POST['idRol'] ?? 0);
        $password = trim((string)($_POST['password'] ?? ''));

        $errors = [];

        if ($login === '') {
            $errors[] = "El login es obligatorio";
        } elseif ($userModel->loginExists($login, $userId)) {
            $errors[] = "El login ya está en uso";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        } elseif ($userModel->emailExists($email, $userId)) {
            $errors[] = "El email ya está en uso";
        }

        if ($idRol <= 0 || $idRol === 2) {
            $errors[] = "Debe seleccionar un rol válido (no socio)";
        }

        if (empty($errors)) {
            $updateData = [
                'login' => $login,
                'email' => $email,
                'idRol' => $idRol
            ];

            // Solo actualizar password si se proporciona
            if ($password !== '') {
                $updateData['password'] = $password;
            }

            if ($userModel->update($userId, $updateData)) {
                $_SESSION['success_message'] = "Usuario actualizado correctamente";
                $this->redirect('users/list');
                return;
            } else {
                $errors[] = "Error al actualizar el usuario";
            }
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        }
    }

    $this->view('users/edit', [
        'user' => $user,
        'menuOptions' => $menuOptions,
        'currentPath' => 'users/edit',
        'roleId' => $roleId,
        'error' => $error ?? null,
        'roles' => $roles  // Pasar roles a la vista
    ]);
}

// En deleteUser: agregar check para no eliminar rol 2
public function deleteUser(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $userId = (int)($id ?? $_GET['id'] ?? 0);
    if ($userId <= 0) {
        $_SESSION['error_message'] = "ID de usuario inválido";
        $this->redirect('users/list');
        return;
    }

    // No permitir auto-eliminación
    if ($userId === (int)$_SESSION['user_id']) {
        $_SESSION['error_message'] = "No puedes eliminar tu propia cuenta";
        $this->redirect('users/list');
        return;
    }

    require_once __DIR__ . '/../Models/Usuario.php';
    $userModel = new \Usuario();

    $user = $userModel->findByIdIncludingInactive($userId);
    if (!$user) {
        $_SESSION['error_message'] = "Usuario no encontrado";
        $this->redirect('users/list');
        return;
    }

    // Prevenir eliminación de usuarios con rol 2 (socio)
    if ((int)$user['idRol'] === 2) {
        $_SESSION['error_message'] = "Los usuarios socios se gestionan en un formulario aparte";
        $this->redirect('users/list');
        return;
    }

    // Solo procesar POST - el modal envía directamente el POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        try {
            // Realizar soft delete
            $success = $userModel->delete($userId);
            
            if ($success) {
                error_log("DEBUG - Usuario $userId desactivado exitosamente");
                $_SESSION['success_message'] = "Usuario <strong>" . htmlspecialchars($user['login'] ?? '') . "</strong> ha sido desactivado correctamente";
            } else {
                error_log("DEBUG - Error al desactivar usuario $userId");
                $_SESSION['error_message'] = "Error al desactivar el usuario";
            }
            
        } catch (\Throwable $e) {
            error_log("DEBUG - Exception al desactivar usuario: " . $e->getMessage());
            $_SESSION['error_message'] = "Error interno: " . $e->getMessage();
        }
        
        $this->redirect('users/list');
        return;
    }

    // Si es GET, redirigir a la lista (el modal se maneja desde JavaScript)
    $this->redirect('users/list');
}

public function createAdmin(): void
{
    $this->startSession();

    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    // Get menu options for the sidebar
    require_once __DIR__ . '/../Models/Competence.php';
    require_once __DIR__ . '/../Models/Role.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);
    
    // Cargar roles disponibles (todos excepto socio - rol 2)
    $rolModel = new \Role();
    $availableRoles = array_filter($rolModel->getAll(), function($role) {
        return (int)$role['idRol'] !== 2; // Excluir rol socio
    });

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // Obtener datos del formulario
        $login = trim((string)($_POST['ci'] ?? ''));      // CI como login
        $email = trim((string)($_POST['email'] ?? ''));
        $selectedRoleId = (int)($_POST['idRole'] ?? 1); // Role seleccionado del formulario

        error_log("DEBUG - Datos recibidos del formulario:");
        error_log("- CI/login: " . $login);
        error_log("- email: " . $email);
        error_log("- idRole: " . $selectedRoleId);

        // Validaciones
        $errors = [];

        if ($login === '') {
            $errors[] = "La cédula de identidad es obligatoria";
        } elseif (strlen($login) < 7 || strlen($login) > 10) {
            $errors[] = "La cédula debe tener entre 7 y 10 dígitos";
        } elseif (!ctype_digit($login)) {
            $errors[] = "La cédula debe contener solo números";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        }

        // Validar que el rol seleccionado sea válido y no sea socio
        $validRoleIds = array_column($availableRoles, 'idRol');
        if (!in_array($selectedRoleId, $validRoleIds)) {
            $errors[] = "El rol seleccionado no es válido";
        }

        // Verificar duplicados
        require_once __DIR__ . '/../Models/Usuario.php';
        $userModel = new \Usuario();
        
        if ($userModel->loginExists($login)) {
            $errors[] = "La cédula $login ya está registrada";
        }
        
        if ($userModel->emailExists($email)) {
            $errors[] = "El email $email ya está registrado";
        }

        if (empty($errors)) {
            try {
                error_log("DEBUG - Creando usuario con rol: " . $selectedRoleId);
                
                // Usar la cédula como contraseña temporal
                $temporalPassword = $login;
                
                $userId = $userModel->create([
                    'login'     => $login,             // CI como login
                    'password'  => $temporalPassword,  // CI como contraseña temporal
                    'email'     => $email,             // Email
                    'idRol'     => $selectedRoleId,    // Role seleccionado
                    'idPartner' => 226,                // Valor temporal (comentar cuando permita NULL)
                    'status'    => 1,                  // Activo
                    'firstSession' => 1                // Primer inicio de sesión
                ]);
                
                error_log("DEBUG - Usuario ID creado: " . ($userId ?: 'FALSO'));
                
                // Validar que se creó correctamente
                if (!$userId || $userId === false) {
                    throw new \Exception("No se pudo crear la cuenta de usuario");
                }

                error_log("DEBUG - Usuario creado exitosamente");
                
                // Buscar nombre del rol para el mensaje
                $roleName = 'Usuario';
                foreach ($availableRoles as $role) {
                    if ((int)$role['idRol'] === $selectedRoleId) {
                        $roleName = $role['rol'];
                        break;
                    }
                }
                
                // Mensaje de éxito
                $_SESSION['success_message'] = "Usuario $roleName creado exitosamente.<br><strong>Login:</strong> $login<br><strong>Contraseña temporal:</strong> $temporalPassword";
                $this->redirect('users/list');
                return;

            } catch (\Throwable $e) {
                error_log("DEBUG - Error al crear usuario: " . $e->getMessage());
                error_log("DEBUG - Trace: " . $e->getTraceAsString());
                $errors[] = "Error interno: " . $e->getMessage();
            }
        }

        // Si hay errores, guardarlos para mostrar
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            error_log("DEBUG - Errores de validación: " . $error);
        }
    }

    // Preparar datos para la vista
    $viewData = [
        'menuOptions' => $menuOptions,
        'currentPath' => 'users/create',
        'roleId' => $roleId,
        'availableRoles' => $availableRoles // Pasar roles a la vista
    ];

    if (isset($error)) {
        $viewData['error'] = $error;
    }

    // Renderizar formulario
    $this->view('users/create', $viewData);
}

/**
 * Editar usuario administrador
 */
/* public function editUser(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $userId = (int)($id ?? $_GET['id'] ?? 0);
    if ($userId <= 0) {
        $_SESSION['error_message'] = "ID de usuario inválido";
        $this->redirect('users/list');
        return;
    }

    require_once __DIR__ . '/../Models/Usuario.php';
    require_once __DIR__ . '/../Models/Competence.php';
    require_once __DIR__ . '/../Models/Role.php';

    $userModel = new \Usuario();
    $user = $userModel->findByIdIncludingInactive($userId);

    if (!$user) {
        $_SESSION['error_message'] = "Usuario no encontrado";
        $this->redirect('users/list');
        return;
    }

    // Solo permitir editar usuarios que no sean socios (rol 2)
    if ((int)$user['idRol'] === 2) {
        $_SESSION['error_message'] = "Los usuarios socios se editan desde otro formulario";
        $this->redirect('users/list');
        return;
    }

    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);
    
    // Cargar roles disponibles (todos excepto socio)
    $rolModel = new \Role();
    $availableRoles = array_filter($rolModel->getAll(), function($role) {
        return (int)$role['idRol'] !== 2;
    });

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $login = trim((string)($_POST['login'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $selectedRoleId = (int)($_POST['idRole'] ?? $user['idRol']);
        $password = trim((string)($_POST['password'] ?? ''));

        $errors = [];

        if ($login === '') {
            $errors[] = "El login es obligatorio";
        } elseif ($userModel->loginExists($login, $userId)) {
            $errors[] = "El login ya está en uso";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        } elseif ($userModel->emailExists($email, $userId)) {
            $errors[] = "El email ya está en uso";
        }

        // Validar rol
        $validRoleIds = array_column($availableRoles, 'idRol');
        if (!in_array($selectedRoleId, $validRoleIds)) {
            $errors[] = "El rol seleccionado no es válido";
        }

        if (empty($errors)) {
            $updateData = [
                'login' => $login,
                'email' => $email,
                'idRol' => $selectedRoleId
            ];

            // Solo actualizar password si se proporciona
            if ($password !== '') {
                $updateData['password'] = $password;
            }

            if ($userModel->update($userId, $updateData)) {
                $_SESSION['success_message'] = "Usuario actualizado correctamente";
                $this->redirect('users/list');
                return;
            } else {
                $errors[] = "Error al actualizar el usuario";
            }
        }

        if (!empty($errors)) {
            $error = implode('<br>', $errors);
        }
    }

    $this->view('users/edit', [
        'user' => $user,
        'menuOptions' => $menuOptions,
        'currentPath' => 'users/edit',
        'roleId' => $roleId,
        'availableRoles' => $availableRoles,
        'error' => $error ?? null
    ]);
}

 // Eliminar usuario (soft delete)

public function deleteUser(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $userId = (int)($id ?? $_GET['id'] ?? 0);
    if ($userId <= 0) {
        $_SESSION['error_message'] = "ID de usuario inválido";
        $this->redirect('users/list');
        return;
    }

    // No permitir auto-eliminación
    if ($userId === (int)$_SESSION['user_id']) {
        $_SESSION['error_message'] = "No puedes eliminar tu propia cuenta";
        $this->redirect('users/list');
        return;
    }

    require_once __DIR__ . '/../Models/Usuario.php';
    $userModel = new \Usuario();

    $user = $userModel->findByIdIncludingInactive($userId);
    if (!$user) {
        $_SESSION['error_message'] = "Usuario no encontrado";
        $this->redirect('users/list');
        return;
    }

    // Solo permitir eliminar usuarios que no sean socios
    if ((int)$user['idRol'] === 2) {
        $_SESSION['error_message'] = "Los usuarios socios se eliminan desde otro formulario";
        $this->redirect('users/list');
        return;
    }

    // Solo procesar POST - el modal envía directamente el POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        try {
            // Realizar soft delete
            $success = $userModel->delete($userId);
            
            if ($success) {
                error_log("DEBUG - Usuario $userId desactivado exitosamente");
                $_SESSION['success_message'] = "Usuario <strong>" . htmlspecialchars($user['login'] ?? '') . "</strong> ha sido desactivado correctamente";
            } else {
                error_log("DEBUG - Error al desactivar usuario $userId");
                $_SESSION['error_message'] = "Error al desactivar el usuario";
            }
            
        } catch (\Throwable $e) {
            error_log("DEBUG - Exception al desactivar usuario: " . $e->getMessage());
            $_SESSION['error_message'] = "Error interno: " . $e->getMessage();
        }
        
        $this->redirect('users/list');
        return;
    }

    // Si es GET, redirigir a la lista (el modal se maneja desde JavaScript)
    $this->redirect('users/list');
} */
}
