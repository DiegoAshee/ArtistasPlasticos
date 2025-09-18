<?php
// app/Controllers/PartnerController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

class PartnerController extends BaseController
{
    // ====== SOCIOS / USUARIOS ======
 public function createPartner(): void
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

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // Obtener datos del formulario
        $login            = trim((string)($_POST['ci'] ?? ''));      // CI como login
        $ci               = trim((string)($_POST['ci'] ?? ''));      // CI para tabla partner
        $email            = trim((string)($_POST['email'] ?? ''));
        $idRole           = isset($_POST['idRole']) ? (int)$_POST['idRole'] : 0;
        $name             = trim((string)($_POST['name'] ?? ''));
        $cellPhoneNumber  = trim((string)($_POST['cellPhoneNumber'] ?? ''));
        $address          = trim((string)($_POST['address'] ?? ''));
        $birthday         = trim((string)($_POST['birthday'] ?? ''));

        error_log("DEBUG - Datos recibidos del formulario:");
        error_log("- CI/login: " . $login);
        error_log("- email: " . $email);
        error_log("- idRole: " . $idRole);
        error_log("- name: " . $name);

        // Validaciones
        $errors = [];

        if ($login === '') {
            $errors[] = "La cédula de identidad es obligatoria";
        } elseif (strlen($login) < 7 || strlen($login) > 10) {
            $errors[] = "La cédula debe tener entre 7 y 10 dígitos";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        }

        if ($idRole === 2) { // Solo validar datos adicionales si es socio
            if ($name === '') {
                $errors[] = "El nombre completo es obligatorio para socios";
            }
            if ($cellPhoneNumber === '') {
                $errors[] = "El número de celular es obligatorio para socios";
            }
            if ($address === '') {
                $errors[] = "La dirección es obligatoria para socios";
            }
            if ($birthday === '' || !DateTime::createFromFormat('Y-m-d', $birthday)) {
                $errors[] = "La fecha de nacimiento es obligatoria y debe ser válida";
            }
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
            require_once __DIR__ . '/../Models/Partner.php';
            $partnerModel = new \Partner();
            $db = \Database::singleton()->getConnection();

            $db->beginTransaction();
            
            // Usar la cédula como contraseña temporal
            $temporalPassword = $login;

            try {
                if ($idRole === 2) { // Crear SOCIO
                    error_log("DEBUG - Creando socio...");
                    
                    // Fechas para el socio
                    $dateCreation = date('Y-m-d H:i:s');
                    $dateRegistration = date('Y-m-d');

                    // 1. Crear el socio en la tabla partner
                    $partnerId = $partnerModel->create(
                        $name,
                        $ci,                 // CI en tabla partner
                        $cellPhoneNumber,
                        $address,
                        $dateCreation,
                        $birthday,
                        $dateRegistration
                    );
                    
                    error_log("DEBUG - Partner ID creado: " . ($partnerId ?: 'FALSO'));
                    
                    if (!$partnerId) {
                        throw new \Exception("No se pudo crear el socio en la tabla partner");
                    }

                    // 2. Crear el usuario asociado al socio
                    $userCreated = $userModel->create(
                        $login,             // CI como login
                        $temporalPassword,  // CI como contraseña temporal
                        $email,             // Email
                        $idRole,            // Rol 2 = Socio
                        (int)$partnerId     // ID del socio recién creado
                    );
                    
                    error_log("DEBUG - Usuario creado: " . ($userCreated ? 'SÍ' : 'NO'));
                    
                    if (!$userCreated) {
                        throw new \Exception("No se pudo crear la cuenta de usuario para el socio");
                    }

                } else { // Crear ADMINISTRADOR (idRole = 1)
                    error_log("DEBUG - Creando administrador...");
                    
                    $userCreated = $userModel->create(
                        $login,             // CI como login
                        $temporalPassword,  // CI como contraseña temporal
                        $email,             // Email
                        $idRole,            // Rol 1 = Admin
                        null                // Sin partner asociado
                    );
                    
                    if (!$userCreated) {
                        throw new \Exception("No se pudo crear la cuenta de administrador");
                    }
                }

                $db->commit();
                error_log("DEBUG - Transacción completada exitosamente");
                
                // Mensaje de éxito
                $_SESSION['success_message'] = "Usuario creado exitosamente. Login: $login - Contraseña temporal: $temporalPassword";
                $this->redirect('partner/list');
                return;

            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("DEBUG - Error en transacción: " . $e->getMessage());
                error_log("DEBUG - Trace: " . $e->getTraceAsString());
                $errors[] = $e->getMessage();
            }
        }

        // Si hay errores, mostrarlos
        if (!empty($errors)) {
            $error = implode('<br>', $errors);
            error_log("DEBUG - Errores: " . $error);
        }
    }

    // Preparar datos para la vista
    $viewData = [
        'menuOptions' => $menuOptions,
        'currentPath' => 'partner/create',
        'roleId' => $roleId
    ];

    if (isset($error)) {
        $viewData['error'] = $error;
    }

    // Renderizar formulario
    $this->view('partner/create', $viewData);
}

    public function export(): void
    {
        try {
            $this->startSession();
            
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('No autorizado: Sesión no iniciada');
            }
            
            // Obtener todos los socios
            require_once __DIR__ . '/../Models/Partner.php';
            $partnerModel = new \Partner();
            
            if (!method_exists($partnerModel, 'getAllSocios')) {
                throw new Exception('El método getAllSocios no existe en el modelo Partner');
            }
            
            $socios = $partnerModel->getAllSocios();
            
            if ($socios === false) {
                throw new Exception('Error al obtener los socios de la base de datos');
            }
            
            // Configurar cabeceras para respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $socios
            ]);
            exit;
            
        } catch (Exception $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            exit;
        }
    }

    public function listSocios(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { $this->redirect('login'); }

        // Menú dinámico desde BD (según rol)
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId      = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Socios desde tu modelo
        require_once __DIR__ . '/../Models/Partner.php';
        $partnerModel = new \Partner();
        $socios = $partnerModel->getAllSocios();

        // Render
        $this->view('partner/list', [
            'socios'      => $socios,
            'menuOptions' => $menuOptions, // ← lo consumen los partials (sidebar)
            'roleId'      => $roleId,
            // 'currentPath' lo fija la propia vista como 'partner/list'
        ]);
    }

    public function updatePartner(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
        $this->redirect('login');
    }

    // Obtener opciones del menú para la barra lateral
    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);

    require_once __DIR__ . '/../Models/Partner.php';
    require_once __DIR__ . '/../Models/Usuario.php';
    $partnerModel = new \Partner();
    $userModel = new \Usuario();

    error_log("DEBUG updatePartner - ID recibido: $id");

    // Obtener datos actuales antes de cualquier operación
    $partner = $partnerModel->findById($id);
    error_log("DEBUG - Partner encontrado: " . print_r($partner, true));

    $user = null;
    if ($partner) {
        // Buscar usuario por idPartner
        $user = $userModel->findByPartnerId($id);
        error_log("DEBUG - User encontrado: " . print_r($user, true));
    }

    if (!$partner) {
        error_log("DEBUG - Partner no encontrado para ID: $id");
        $_SESSION['error'] = 'Socio no encontrado';
        $this->redirect('partner/list');
        return;
    }

    if (!$user) {
        error_log("DEBUG - Usuario no encontrado para Partner ID: $id");
        $_SESSION['error'] = 'Usuario no encontrado para este socio';
        $this->redirect('partner/list');
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos del formulario
        $login = trim((string)($_POST['login'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $idRole = (int)($_POST['idRole'] ?? 2);
        
        // Datos del partner (solo si es socio)
        $name = trim((string)($_POST['name'] ?? ''));
        $ci = trim((string)($_POST['ci'] ?? ''));
        $cellPhoneNumber = trim((string)($_POST['cellPhoneNumber'] ?? ''));
        $address = trim((string)($_POST['address'] ?? ''));
        $birthday = trim((string)($_POST['birthday'] ?? ''));
        $dateRegistration = trim((string)($_POST['dateRegistration'] ?? ''));

        error_log("DEBUG Update - Datos del formulario:");
        error_log("- login: '$login'");
        error_log("- email: '$email'");
        error_log("- idRole: $idRole");
        error_log("- name: '$name'");
        error_log("- ci: '$ci'");
        error_log("- cellPhoneNumber: '$cellPhoneNumber'");
        error_log("- address: '$address'");
        error_log("- birthday: '$birthday'");
        error_log("- dateRegistration: '$dateRegistration'");

        // Validaciones
        $errors = [];

        if ($login === '') {
            $errors[] = "El campo Login es obligatorio";
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El email es obligatorio y debe ser válido";
        }

        if ($idRole === 2) { // Socio
            if ($name === '') $errors[] = "El nombre es obligatorio";
            if ($ci === '') $errors[] = "La cédula es obligatoria";
            if ($cellPhoneNumber === '') $errors[] = "El celular es obligatorio";
            if ($address === '') $errors[] = "La dirección es obligatoria";
            if ($birthday === '') $errors[] = "La fecha de nacimiento es obligatoria";
            if ($dateRegistration === '') $errors[] = "La fecha de registro es obligatoria";
        }

        // Verificar duplicados (excluyendo el usuario actual)
        if ($userModel->loginExists($login, $user['idUser'])) {
            $errors[] = "El login $login ya está en uso por otro usuario";
        }

        if ($userModel->emailExists($email, $user['idUser'])) {
            $errors[] = "El email $email ya está en uso por otro usuario";
        }

        if (!empty($errors)) {
            error_log("DEBUG - Errores de validación: " . implode(', ', $errors));
            $error = implode('<br>', $errors);
        } else {
            $db = \Database::singleton()->getConnection();
            $db->beginTransaction();

            try {
                if ($idRole === 2) { // Actualizar socio
                    error_log("DEBUG - Actualizando socio ID: $id");
                    
                    // 1. Actualizar tabla partner
                    $partnerUpdated = $partnerModel->update(
                        $id, $name, $ci, $cellPhoneNumber, 
                        $address, $birthday, $dateRegistration
                    );
                    
                    error_log("DEBUG - Partner actualizado: " . ($partnerUpdated ? 'SÍ' : 'NO'));
                    
                    if (!$partnerUpdated) {
                        throw new \Exception("No se pudo actualizar los datos del socio");
                    }

                    // 2. Actualizar usuario asociado
                    error_log("DEBUG - Actualizando usuario ID: " . $user['idUser']);
                    
                    // Preparar datos para actualizar usuario
                    $userUpdateData = [
                        'login' => $login,
                        'email' => $email,
                        'idRol' => $idRole
                    ];
                    
                    error_log("DEBUG - Datos para actualizar usuario: " . print_r($userUpdateData, true));
                    
                    $userUpdated = $userModel->update($user['idUser'], $userUpdateData);
                    
                    error_log("DEBUG - Usuario actualizado: " . ($userUpdated ? 'SÍ' : 'NO'));
                    
                    if (!$userUpdated) {
                        throw new \Exception("No se pudo actualizar el usuario del socio. Ver logs para detalles.");
                    }

                } else { // Actualizar administrador (idRole = 1)
                    error_log("DEBUG - Actualizando administrador");
                    
                    $userUpdateData = [
                        'login' => $login,
                        'email' => $email,
                        'idRol' => $idRole,
                        'idPartner' => null // Los admins no tienen partner
                    ];
                    
                    error_log("DEBUG - Datos para actualizar admin: " . print_r($userUpdateData, true));
                    
                    $userUpdated = $userModel->update($user['idUser'], $userUpdateData);
                    
                    if (!$userUpdated) {
                        throw new \Exception("No se pudo actualizar el administrador. Ver logs para detalles.");
                    }
                }

                $db->commit();
                error_log("DEBUG - Actualización exitosa, redirigiendo...");
                
                $_SESSION['success_message'] = "Usuario actualizado exitosamente";
                $this->redirect('partner/list');
                return;

            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("DEBUG - Error en transacción de actualización: " . $e->getMessage());
                error_log("DEBUG - Trace: " . $e->getTraceAsString());
                $error = $e->getMessage();
            }
        }
    }

    // Preparar datos para la vista
    $viewData = [
        'partner' => $partner,
        'user' => $user,
        'menuOptions' => $menuOptions,
        'currentPath' => 'partner/edit/' . $id,
        'roleId' => $roleId
    ];

    if (isset($error)) {
        $viewData['error'] = $error;
        error_log("DEBUG - Error enviado a vista: $error");
    }

    $this->view('partner/edit', $viewData);
}

   public function deletePartner(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
        $this->redirect('login');
        return;
    }

    // Obtener return_url de la query string para redirigir de vuelta
    $returnUrl = $_GET['return_url'] ?? 'partner/list';

    require_once __DIR__ . '/../Models/Partner.php';
    require_once __DIR__ . '/../Models/Usuario.php';
    $partnerModel = new \Partner();
    $userModel = new \Usuario();
    
    error_log("DEBUG deletePartner - ID recibido: $id");
    error_log("DEBUG deletePartner - Return URL: $returnUrl");

    // Buscar los datos antes de eliminar
    $partner = $partnerModel->findById($id);
    $user = null;
    
    if ($partner) {
        // Es un socio - buscar su usuario asociado
        $user = $userModel->findByPartnerId($id);
        error_log("DEBUG - Socio encontrado: " . $partner['name']);
        error_log("DEBUG - Usuario asociado: " . ($user ? $user['login'] : 'NO ENCONTRADO'));
    } else {
        // Podría ser un admin - buscar directamente por idUser
        $user = $userModel->findById($id);
        error_log("DEBUG - Usuario encontrado: " . ($user ? $user['login'] : 'NO ENCONTRADO'));
    }

    if (!$partner && !$user) {
        $_SESSION['error'] = 'Usuario o socio no encontrado';
        $this->redirect($returnUrl);
        return;
    }

    $db = \Database::singleton()->getConnection();
    $db->beginTransaction();

    try {
        if ($partner) {
            // ELIMINAR SOCIO (soft delete recomendado)
            error_log("DEBUG - Eliminando socio ID: $id");
            
            // 1. Primero eliminar/desactivar el usuario asociado
            if ($user) {
                error_log("DEBUG - Desactivando usuario ID: " . $user['idUser']);
                
                // Soft delete del usuario (cambiar status a 0)
                $userUpdateData = ['status' => 0];
                if (!$userModel->update($user['idUser'], $userUpdateData)) {
                    throw new \Exception("No se pudo desactivar el usuario del socio");
                }
            }
            
            // 2. Luego soft delete del partner
            if (!$partnerModel->delete($id)) {
                throw new \Exception("No se pudo eliminar el socio");
            }
            
            $message = "Socio eliminado correctamente";
            
        } else if ($user) {
            // ELIMINAR SOLO USUARIO (administrador)
            error_log("DEBUG - Eliminando usuario ID: $id");
            
            // Soft delete del usuario
            $userUpdateData = ['status' => 0];
            if (!$userModel->update($id, $userUpdateData)) {
                throw new \Exception("No se pudo eliminar el usuario");
            }
            
            $message = "Usuario eliminado correctamente";
        }

        $db->commit();
        error_log("DEBUG - Eliminación exitosa");
        
        $_SESSION['success_message'] = $message;
        $this->redirect($returnUrl);
        
    } catch (\Throwable $e) {
        $db->rollBack();
        error_log("DEBUG - Error en eliminación: " . $e->getMessage());
        error_log("DEBUG - Trace: " . $e->getTraceAsString());
        
        $_SESSION['error'] = $e->getMessage();
        $this->redirect($returnUrl);
    }
}

    public function manageRegistrations(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
            $this->redirect('login');
        }

        require_once __DIR__ . '/../Models/PartnerOnline.php';
        $partnerOnlineModel = new \PartnerOnline();
        $registrations = $partnerOnlineModel->getAll();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['action'], $_POST['id'])) {
            $id     = (int)$_POST['id'];
            $action = (string)$_POST['action'];

            require_once __DIR__ . '/../Models/Partner.php';
            require_once __DIR__ . '/../Models/Usuario.php';
            $partnerModel = new \Partner();
            $userModel    = new \Usuario();
            $db           = \Database::singleton()->getConnection();

            $db->beginTransaction();
            try {
                $registration = $partnerOnlineModel->findById($id);
                if ($registration) {
                    if ($action === 'accept') {
                        $dateConfirmation = date('Y-m-d H:i:s');

                        // Alta de socio (usa la fecha de registro que ya se guardó en la solicitud)
                        $partnerId = $partnerModel->create(
                            $registration['name'],
                            $registration['CI'],
                            $registration['cellPhoneNumber'],
                            $registration['address'],
                            $dateConfirmation,                        // dateCreation del socio
                            $registration['birthday'],
                            $registration['dateRegistration']         // viene de NOW() en la solicitud
                        );
                        if (!$partnerId) {
                            throw new \Exception("Failed to create Partner");
                        }

                        // Alta de usuario con email de la solicitud (si existe)
                        $login          = $registration['CI']; // tu lógica de login
                        $hashedPassword = password_hash($login, PASSWORD_BCRYPT);
                        $email          = (string)($registration['email'] ?? '');

                        if (!$userModel->create($login, $hashedPassword, $email, 2, (int)$partnerId)) {
                            throw new \Exception("Failed to create User");
                        }

                        // Borrar solicitud
                        $partnerOnlineModel->delete($id);
                    } elseif ($action === 'reject') {
                        $partnerOnlineModel->delete($id);
                    }

                    $db->commit();
                    $this->redirect('partner/manage');
                    return;
                }
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Transaction failed: " . $e->getMessage());
            }
        }

        $this->view('partner/manage', ['registrations' => $registrations]);
    }
}
