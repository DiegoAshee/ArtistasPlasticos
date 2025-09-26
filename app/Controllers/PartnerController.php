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
        requireRole([1], 'login');

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

            // Validación de archivos - solo para socios (OPCIONALES)
            $frontImageRel = null;
            $backImageRel = null;
            
            if ($idRole === 2) {
                // Validar solo si se subieron archivos (opcional)
                if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['frontImage']['size'] > 2 * 1024 * 1024) {
                        $errors[] = "La imagen frontal del CI excede los 2MB permitidos.";
                    }
                }
                
                if (isset($_FILES['backImage']) && $_FILES['backImage']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['backImage']['size'] > 2 * 1024 * 1024) {
                        $errors[] = "La imagen posterior del CI excede los 2MB permitidos.";
                    }
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
                
                // Usar la cédula como contraseña temporal (hasheada)
                $temporalPassword = $login;
                $hashedPassword = password_hash($temporalPassword, PASSWORD_BCRYPT);

                try {
                    if ($idRole === 2) { // Crear SOCIO
                        error_log("DEBUG - Creando socio...");
                        
                        // Procesar uploads de imágenes (OPCIONAL)
                        try {
                            if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] === UPLOAD_ERR_OK) {
                                $frontImageRel = $this->handleUpload('frontImage', $ci, 'front');
                            }
                            
                            if (isset($_FILES['backImage']) && $_FILES['backImage']['error'] === UPLOAD_ERR_OK) {
                                $backImageRel = $this->handleUpload('backImage', $ci, 'back');
                            }
                        } catch (\Exception $e) {
                            throw new \Exception("Error al subir archivos: " . $e->getMessage());
                        }

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
                            $dateRegistration,
                            $frontImageRel,      // Ruta de la imagen frontal (puede ser null)
                            $backImageRel        // Ruta de la imagen posterior (puede ser null)
                        );
                        
                        error_log("DEBUG - Partner ID creado: " . ($partnerId ?: 'FALSO'));
                        error_log("DEBUG - Imagen frontal: " . ($frontImageRel ?: 'NO SUBIDA'));
                        error_log("DEBUG - Imagen posterior: " . ($backImageRel ?: 'NO SUBIDA'));
                        
                        if (!$partnerId) {
                            throw new \Exception("No se pudo crear el socio en la tabla partner");
                        }

                        // 2. Crear el usuario asociado al socio
                        $userId = $userModel->create(
                            $login,             // CI como login
                            $hashedPassword,    // Contraseña hasheada
                            $email,             // Email
                            $idRole,            // Rol 2 = Socio
                            (int)$partnerId     // ID del socio recién creado
                        );
                        
                        error_log("DEBUG - Usuario ID creado: " . ($userId ?: 'FALSO'));
                        
                        // Validar que se creó correctamente
                        if (!$userId || $userId === false) {
                            throw new \Exception("No se pudo crear la cuenta de usuario para el socio");
                        }

                    } else { // Crear ADMINISTRADOR (idRole = 1)
                        error_log("DEBUG - Creando administrador...");
                        
                        $userId = $userModel->create(
                            $login,             // CI como login
                            $hashedPassword,    // Contraseña hasheada
                            $email,             // Email
                            $idRole,            // Rol 1 = Admin
                            null                // Sin partner asociado
                        );
                        
                        error_log("DEBUG - Administrador ID creado: " . ($userId ?: 'FALSO'));
                        
                        // Validar que se creó correctamente
                        if (!$userId || $userId === false) {
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

    /**
     * Handles file uploads (optional).
     */
    private function handleUpload(string $inputName, string $ci, string $prefix): ?string
    {
        error_log("[$inputName] Attempting upload at " . date('Y-m-d H:i:s') . ": " . print_r($_FILES[$inputName] ?? 'No file', true));

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log("[$inputName] No file uploaded or upload error: " . ($_FILES[$inputName]['error'] ?? 'No file'));
            return null;
        }

        $fileTmp  = $_FILES[$inputName]['tmp_name'];
        $fileName = basename((string)$_FILES[$inputName]['name']);
        $fileSize = (int)$_FILES[$inputName]['size'];

        // MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = $finfo ? finfo_file($finfo, $fileTmp) : @mime_content_type($fileTmp);
        if ($finfo) finfo_close($finfo);

        // Validations
        if ($fileSize > 2 * 1024 * 1024) {
            error_log("[$inputName] File size exceeds 2MB: $fileSize bytes");
            throw new \RuntimeException("El archivo {$inputName} excede los 2MB permitidos.");
        }
        if (!in_array($fileType, ['image/jpeg', 'image/png'], true)) {
            error_log("[$inputName] Invalid file type: $fileType");
            throw new \RuntimeException("El archivo {$inputName} debe ser JPG o PNG.");
        }

        $publicDir = defined('UPLOAD_PUBLIC_DIR') ? UPLOAD_PUBLIC_DIR : 'images/carnets';
        $uploadDirFs = rtrim(p($publicDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        error_log("[$inputName] Target directory (fs): $uploadDirFs");

        if (!is_dir($uploadDirFs)) {
            error_log("[$inputName] Directory $uploadDirFs does not exist, attempting to create...");
            if (!mkdir($uploadDirFs, 0777, true) && !is_dir($uploadDirFs)) {
                error_log("[$inputName] Failed to create directory $uploadDirFs");
                throw new \RuntimeException("No se pudo crear el directorio para guardar el archivo.");
            }
            error_log("[$inputName] Directory $uploadDirFs created successfully.");
        } elseif (!is_writable($uploadDirFs)) {
            error_log("[$inputName] Directory $uploadDirFs is not writable.");
            throw new \RuntimeException("El directorio de destino no tiene permisos de escritura.");
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        $newName    = "{$prefix}_{$ci}." . $ext;
        $destPathFs = $uploadDirFs . $newName;
        error_log("[$inputName] Destination path (fs): $destPathFs");

        if (!move_uploaded_file($fileTmp, $destPathFs)) {
            error_log("[$inputName] move_uploaded_file failed for $destPathFs");
            throw new \RuntimeException("No se pudo guardar el archivo {$inputName}.");
        }
        error_log("[$inputName] File uploaded successfully to $destPathFs");

        $publicRelative = trim($publicDir, '/\\') . '/' . $newName;
        return $publicRelative;
    }

    // ... (el resto de los métodos se mantienen igual)
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
        if (!isset($_SESSION['user_id'])) { 
            $this->redirect('login'); 
            return;
        }
        requireRole([1], 'login');

        // Menú dinámico desde BD (según rol)
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId      = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Socios desde tu modelo
        require_once __DIR__ . '/../Models/Partner.php';
        $partnerModel = new \Partner();
        $socios = $partnerModel->getAllSocios();

        // Verificar si hay mensajes de sesión
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error'] ?? null;
        
        // Limpiar mensajes de sesión después de leerlos
        unset($_SESSION['success_message'], $_SESSION['error']);

        // Render
        $this->view('partner/list', [
            'socios'         => $socios,
            'menuOptions'    => $menuOptions,
            'roleId'         => $roleId,
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage,
        ]);
    }
/**
 * Desbloquear usuario (AJAX) - CORREGIDO
 */
public function unblock(): void
{
    $this->startSession();
    
    // Verificar que sea administrador
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sin permisos']);
        exit;
    }

    // Solo aceptar POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $login = trim($_POST['login'] ?? '');
    $idPartner = (int)($_POST['idPartner'] ?? 0); // ← Corregido: usar idPartner

    if ($login === '' || $idPartner <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    try {
        require_once __DIR__ . '/../Models/Partner.php';
        $partnerModel = new \Partner();
        
        // Verificar que el usuario existe
        $partner = $partnerModel->getUserByIdPartner($idPartner);
        if (!$partner || $partner['login'] !== $login) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // Desbloquear usuario
        $success = $partnerModel->unblockUser($idPartner);
        
        if ($success) {
            // Log de la acción
            error_log("Admin {$_SESSION['username']} desbloqueó al socio: {$login}");
            
            echo json_encode([
                'success' => true, 
                'message' => 'Socio desbloqueado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Error al desbloquear socio'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en PartnerController::unblock: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error interno del servidor'
        ]);
    }
    exit;
}

/**
 * Resetear intentos fallidos (AJAX) - CORREGIDO
 */
public function resetAttempts(): void
{
    $this->startSession();
    
    // Verificar que sea administrador
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Sin permisos']);
        exit;
    }

    // Solo aceptar POST
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $login = trim($_POST['login'] ?? '');
    $idPartner = (int)($_POST['idPartner'] ?? 0); // ← Corregido: usar idPartner

    if ($login === '' || $idPartner <= 0) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        exit;
    }

    try {
        require_once __DIR__ . '/../Models/Partner.php';
        $partnerModel = new \Partner();
        
        // Verificar que el usuario existe
        $user = $partnerModel->getUserByIdPartner($idPartner);
        if (!$user || $user['login'] !== $login) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // Solo resetear intentos fallidos
        $partnerModel->resetFailedAttempts($idPartner);
        
        // Log de la acción
        error_log("Admin {$_SESSION['username']} reseteó intentos fallidos del socio: {$login}");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Intentos fallidos reseteados'
        ]);
        
    } catch (Exception $e) {
        error_log("Error en PartnerController::resetAttempts: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error interno del servidor'
        ]);
    }
    exit;
}
    /*public function updatePartner(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
            $this->redirect('login');
            return;
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
                        
                        $userUpdated = $userModel->update($user['idUser'], [
                            'login' => $login,
                            'email' => $email,
                            'idRol' => $idRole
                        ]);
                        
                        error_log("DEBUG - Usuario actualizado: " . ($userUpdated ? 'SÍ' : 'NO'));
                        
                        if (!$userUpdated) {
                            throw new \Exception("No se pudo actualizar el usuario del socio. Ver logs para detalles.");
                        }

                    } else { // Actualizar administrador (idRole = 1)
                        error_log("DEBUG - Actualizando administrador");
                        
                        $userUpdated = $userModel->update($user['idUser'], [
                            'login' => $login,
                            'email' => $email,
                            'idRol' => $idRole,
                            'idPartner' => null // Los admins no tienen partner
                        ]);
                        
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
    }*/



    




    public function updatePartner(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
        $this->redirect('login');
        return;
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

        // Validación de archivos - solo para socios (OPCIONAL)
        if ($idRole === 2) {
            if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['frontImage']['size'] > 2 * 1024 * 1024) {
                    $errors[] = "La imagen frontal del CI excede los 2MB permitidos.";
                }
            }
            
            if (isset($_FILES['backImage']) && $_FILES['backImage']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['backImage']['size'] > 2 * 1024 * 1024) {
                    $errors[] = "La imagen posterior del CI excede los 2MB permitidos.";
                }
            }
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
                    
                    // Procesar uploads de imágenes (OPCIONAL)
                    $frontImageURL = $partner['frontImageURL'] ?? null;
                    $backImageURL = $partner['backImageURL'] ?? null;
                    
                    try {
                        if (isset($_FILES['frontImage']) && $_FILES['frontImage']['error'] === UPLOAD_ERR_OK) {
                            $frontImageURL = $this->handleUpload('frontImage', $ci, 'front');
                        }
                        
                        if (isset($_FILES['backImage']) && $_FILES['backImage']['error'] === UPLOAD_ERR_OK) {
                            $backImageURL = $this->handleUpload('backImage', $ci, 'back');
                        }
                    } catch (\Exception $e) {
                        throw new \Exception("Error al subir archivos: " . $e->getMessage());
                    }

                    // 1. Actualizar tabla partner con imágenes
                    $partnerUpdated = $partnerModel->update(
                        $id, $name, $ci, $cellPhoneNumber, 
                        $address, $birthday, $dateRegistration,
                        $frontImageURL,  // Imagen frontal
                        $backImageURL    // Imagen posterior
                    );
                    
                    error_log("DEBUG - Partner actualizado: " . ($partnerUpdated ? 'SÍ' : 'NO'));
                    error_log("DEBUG - Imágenes: front=$frontImageURL, back=$backImageURL");
                    
                    if (!$partnerUpdated) {
                        throw new \Exception("No se pudo actualizar los datos del socio");
                    }

                    // 2. Actualizar usuario asociado
                    error_log("DEBUG - Actualizando usuario ID: " . $user['idUser']);
                    
                    $userUpdated = $userModel->update($user['idUser'], [
                        'login' => $login,
                        'email' => $email,
                        'idRol' => $idRole
                    ]);
                    
                    error_log("DEBUG - Usuario actualizado: " . ($userUpdated ? 'SÍ' : 'NO'));
                    
                    if (!$userUpdated) {
                        throw new \Exception("No se pudo actualizar el usuario del socio. Ver logs para detalles.");
                    }

                } else { // Actualizar administrador (idRole = 1)
                    error_log("DEBUG - Actualizando administrador");
                    
                    $userUpdated = $userModel->update($user['idUser'], [
                        'login' => $login,
                        'email' => $email,
                        'idRol' => $idRole,
                        'idPartner' => null // Los admins no tienen partner
                    ]);
                    
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
                // ELIMINAR SOCIO (soft delete)
                error_log("DEBUG - Eliminando socio ID: $id");
                
                // 1. Primero eliminar/desactivar el usuario asociado
                if ($user) {
                    error_log("DEBUG - Desactivando usuario ID: " . $user['idUser']);
                    
                    // Soft delete del usuario (cambiar status a 0)
                    if (!$userModel->update($user['idUser'], ['status' => 0])) {
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
                if (!$userModel->update($id, ['status' => 0])) {
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

    /* public function manageRegistrations(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
            $this->redirect('login');
            return;
        }

        // Obtener opciones del menú
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 1);
        $menuOptions = (new \Competence())->getByRole($roleId);

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

                        // 1. Crear el socio en la tabla partner
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
                            throw new \Exception("No se pudo crear el socio en la tabla partner");
                        }

                        // 2. Crear el usuario asociado
                        $login          = $registration['CI']; // CI como login
                        $hashedPassword = password_hash($login, PASSWORD_BCRYPT); // CI como contraseña temporal hasheada
                        $email          = (string)($registration['email'] ?? '');

                        $userId = $userModel->create($login, $hashedPassword, $email, 2, (int)$partnerId);
                        
                        if (!$userId) {
                            throw new \Exception("No se pudo crear el usuario para el socio");
                        }

                        // 3. Eliminar la solicitud de registro
                        $partnerOnlineModel->delete($id);
                        
                        $_SESSION['success_message'] = "Solicitud aceptada y socio creado exitosamente";
                        
                    } elseif ($action === 'reject') {
                        // Solo eliminar la solicitud
                        $partnerOnlineModel->delete($id);
                        $_SESSION['success_message'] = "Solicitud rechazada correctamente";
                    }

                    $db->commit();
                } else {
                    throw new \Exception("Solicitud no encontrada");
                }
                
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Error en manageRegistrations: " . $e->getMessage());
                error_log("Trace: " . $e->getTraceAsString());
                $_SESSION['error'] = $e->getMessage();
            }
            
            $this->redirect('partner/manage');
            return;
        }

        // Verificar mensajes de sesión
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error'] ?? null;
        
        // Limpiar mensajes de sesión
        unset($_SESSION['success_message'], $_SESSION['error']);

        $this->view('partner/manage', [
            'registrations'  => $registrations,
            'menuOptions'    => $menuOptions,
            'roleId'         => $roleId,
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage,
        ]);
    } */
}