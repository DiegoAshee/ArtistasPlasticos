<?php
// app/Controllers/UserController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php'; // Para BASE_URL

class UserController
{
    // ===== Helpers base =====
    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Endurecer cookie (activa 'secure' si usas HTTPS)
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_samesite', 'Lax');
            // ini_set('session.cookie_secure', '1'); // en HTTPS
            session_start();
        }
    }

    private function redirect(string $path): void
    {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit();
    }

    private function addDebug(string $msg): void
    {
        // Solo loggea si tienes APP_DEBUG=true definido en tu config
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log($msg);
        }
    }

    // ====== LOGIN ======
    // Mostrar el formulario de login y procesar autenticación
    public function login(): void
    {
        $this->startSession();

        // Si ya está logueado y es GET, ir directo al dashboard
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && !empty($_SESSION['user_id'] ?? null)) {
            $this->redirect('dashboard');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $login    = isset($_POST['login']) ? trim((string)$_POST['login']) : '';
            $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

            if ($login === '' || $password === '') {
                $error = "Por favor, complete todos los campos";
            } else {
                $modelPath = __DIR__ . '/../Models/Usuario.php';
                if (is_file($modelPath)) {
                    require_once $modelPath;
                    $userModel = new \Usuario();
                    $user = $userModel->authenticate($login, $password);
                } else {
                    $error = "Error del sistema: Modelo no encontrado";
                    $user = null;
                }

                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user_id']  = (int)$user['idUser'];
                    $_SESSION['username'] = (string)$user['login'];
                    $_SESSION['role']     = (int)$user['idRol'];

                    // (opcional) primer login -> enviar correo
                    if ((int)($user['firstLogin'] ?? 0) === 0) {
                        $this->sendFirstLoginEmailSafe($user['email'] ?? '', $user['login']);
                    }

                    // Ir siempre al dashboard tras login correcto
                    $this->redirect('dashboard');
                } else {
                    $error = "Usuario o contraseña incorrectos";
                }
            }
        }

        // GET o POST con error -> mostrar formulario
        $viewData = isset($error) ? ['error' => $error] : [];
        $this->view('login', $viewData);
    }

    // ====== DASHBOARD ======
    public function dashboard(): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) { $this->redirect('login'); }

    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);

    $menuOptions = (new \Competence())->getByRole($roleId);

    $this->view('dashboard', [
        'menuOptions' => $menuOptions,  // ← nombre esperado por la vista
        'currentPath' => 'dashboard',
        'roleId'      => $roleId,
    ]);
}





    // ====== LOGOUT ======
    public function logout(): void
    {
        $this->startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        $this->redirect('login');
    }

    // ====== VISTAS ======
    private function view(string $view, array $data = []): void
{
    if (!empty($data)) extract($data);
    $viewPath = __DIR__ . '/../Views/' . $view . '.php';
    if (is_file($viewPath)) {
        ob_start();
        try {
            // 👉 Asegura u()/asset() en TODAS las vistas/partials
            include_once __DIR__ . '/../Views/helpers.php';

            include $viewPath;
            ob_end_flush();
        } catch (\Throwable $e) {
            ob_end_clean();
            echo "<h1>Error en la vista</h1>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }
    } else {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        echo "<h1>Error 404</h1>";
        echo "<p>Vista no encontrada: " . htmlspecialchars($view, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p>Ruta buscada: " . htmlspecialchars($viewPath, ENT_QUOTES, 'UTF-8') . "</p>";
        echo "<p><a href='" . htmlspecialchars($base . "/login", ENT_QUOTES, 'UTF-8') . "'>Ir al Login</a></p>";
    }
}



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
        $login            = trim((string)($_POST['login'] ?? ''));
        $email            = trim((string)($_POST['email'] ?? ''));
        $idRole           = isset($_POST['idRole']) ? (int)$_POST['idRole'] : 0;
        $name             = trim((string)($_POST['name'] ?? ''));
        $ci               = trim((string)($_POST['ci'] ?? ''));
        $cellPhoneNumber  = trim((string)($_POST['cellPhoneNumber'] ?? ''));
        $address          = trim((string)($_POST['address'] ?? ''));
        $birthday         = trim((string)($_POST['birthday'] ?? ''));

        if ($login === '' || $email === '') {
            $error = "El campo Login y Email son obligatorios";
        } elseif ($idRole === 2 && ($name === '' || $ci === '' || $cellPhoneNumber === '' || $address === '' || $birthday === '')) {
            $error = "Todos los campos de Socio son obligatorios";
        } else {
            require_once __DIR__ . '/../Models/Partner.php';
            require_once __DIR__ . '/../Models/Usuario.php';

            $partnerModel = new \Partner();
            $userModel    = new \Usuario();
            $db           = \Database::singleton()->getConnection();

            $db->beginTransaction();
            $hashedPassword = password_hash($login, PASSWORD_BCRYPT);

            try {
                if ($idRole === 2) { // Socio
                    $dateCreation     = date('Y-m-d H:i:s');
                    $dateRegistration = date('Y-m-d H:i:s');  // <-- automático

                    $partnerId = $partnerModel->create(
                        $name,
                        $ci,
                        $cellPhoneNumber,
                        $address,
                        $dateCreation,
                        $birthday,
                        $dateRegistration
                    );
                    if (!$partnerId) {
                        throw new \Exception("Failed to create Partner");
                    }
                    if (!$userModel->create($login, $hashedPassword, $email, $idRole, (int)$partnerId)) {
                        throw new \Exception("Failed to create User for Partner ID: $partnerId");
                    }
                } else { // Otros roles
                    if (!$userModel->create($login, $hashedPassword, $email, $idRole, null)) {
                        throw new \Exception("Failed to create User");
                    }
                }

                $db->commit();
                $this->redirect('users/list');
                return;
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Transaction failed: " . $e->getMessage());
                $error = "Error al crear el usuario o socio: " . $e->getMessage();
            }
        }
    }

    // Prepare view data
    $viewData = [
        'menuOptions' => $menuOptions,
        'currentPath' => 'partner/create',
        'roleId' => $roleId
    ];

    // Add error message if exists
    if (isset($error)) {
        $viewData['error'] = $error;
    }

    // Render del formulario
    $this->view('partner/create', $viewData);
}


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

   


    public function listUsers(): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) { 
        $this->redirect('login'); 
    }

    // Menú dinámico desde BD (según rol)
    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole(roleId: $roleId);

    // Usuarios administradores desde tu modelo
    require_once __DIR__ . '/../Models/Usuario.php';
    $userModel = new \Usuario();
    $users = $userModel->getUsersAdmin();

    // Render
    $this->view('users/list', [
        'users'       => $users,
        'menuOptions' => $menuOptions, // ← lo consumen los partials (sidebar)
        'roleId'      => $roleId,
        // 'currentPath' lo fija la propia vista como 'users'
    ]);
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
        $userModel    = new \Usuario();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $login            = (string)($_POST['login'] ?? '');
            $idRole           = (int)($_POST['idRole'] ?? 2);
            $name             = (string)($_POST['name'] ?? '');
            $ci               = (string)($_POST['ci'] ?? '');
            $cellPhoneNumber  = (string)($_POST['cellPhoneNumber'] ?? '');
            $address          = (string)($_POST['address'] ?? '');
            $birthday         = (string)($_POST['birthday'] ?? '');
            $dateRegistration = (string)($_POST['dateRegistration'] ?? '');

            if ($login === '') {
                $error = "El campo Login es obligatorio";
            } elseif ($idRole === 2 && ($name === '' || $ci === '' || $cellPhoneNumber === '' || $address === '' || $birthday === '' || $dateRegistration === '')) {
                $error = "Todos los campos de Socio son obligatorios";
            } else {
                $db = \Database::singleton()->getConnection();
                $db->beginTransaction();
                $hashedPassword = password_hash($login, PASSWORD_BCRYPT);

                try {
                    $partner = $partnerModel->findById((int)$id);
                    if (!$partner && $idRole === 2) {
                        throw new \Exception("Partner no encontrado");
                    }

                    if ($idRole === 2) {
                        if (!$partnerModel->update((int)$id, $name, $ci, $cellPhoneNumber, $address, $birthday, $dateRegistration)) {
                            throw new \Exception("Failed to update Partner");
                        }
                        $user = $userModel->findByPartnerId((int)$id);
                        if (!$user) {
                            throw new \Exception("User no encontrado para Partner ID: $id");
                        }
                        if (!$userModel->update((int)$user['idUser'], $login, $hashedPassword, (int)$idRole, (int)$id)) {
                            throw new \Exception("Failed to update User");
                        }
                    } else {
                        $user = $userModel->findByLogin($login);
                        if (!$user) {
                            throw new \Exception("User no encontrado");
                        }
                        if (!$userModel->update((int)$user['idUser'], $login, $hashedPassword, (int)$idRole, null)) {
                            throw new \Exception("Failed to update User");
                        }
                    }
                    $db->commit();
                    $this->redirect('socios/list');
                    return;
                } catch (\Throwable $e) {
                    $db->rollBack();
                    error_log("Transaction failed: " . $e->getMessage());
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $partner = $partnerModel->findById((int)$id);
        if (!$partner) {
            $_SESSION['error'] = 'Socio no encontrado';
            $this->redirect('socios/list');
        }

        $user = $userModel->findByPartnerId((int)$id);
        if (!$user) {
            $_SESSION['error'] = 'Usuario no encontrado para este socio';
            $this->redirect('socios/list');
        }

        // Pasar los datos a la vista incluyendo las opciones del menú
        $this->view('partner/edit', [
            'partner' => $partner,
            'user' => $user,
            'menuOptions' => $menuOptions,  // Asegúrate de que esta variable se pase a la vista
            'currentPath' => 'partner/edit/' . $id
        ]);
    }

    public function deletePartner(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['role'] ?? 0) !== 1) {
            $this->redirect('login');
        }

        require_once __DIR__ . '/../Models/Partner.php';
        require_once __DIR__ . '/../Models/Usuario.php';
        $partnerModel = new \Partner();
        $userModel    = new \Usuario();
        $db           = \Database::singleton()->getConnection();

        $db->beginTransaction();
        try {
            $partner = $partnerModel->findById((int)$id);
            if ($partner) {
                $user = $userModel->findByPartnerId((int)$id);
                if ($user && !$userModel->delete((int)$user['idUser'])) {
                    throw new \Exception("Failed to delete User");
                }
                if (!$partnerModel->delete((int)$id)) {
                    throw new \Exception("Failed to delete Partner");
                }
            } else {
                $user = $userModel->findById((int)$id);
                if ($user && !$userModel->delete((int)$user['idUser'])) {
                    throw new \Exception("Failed to delete User");
                }
            }
            $db->commit();
            $this->redirect('socios/list');
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Transaction failed: " . $e->getMessage());
            $this->view('partner/list', ['socios' => $partnerModel->getAllSocios(), 'error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function registerPartner(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $name            = (string)($_POST['name'] ?? '');
        $ci              = (string)($_POST['ci'] ?? '');
        $cellPhoneNumber = (string)($_POST['cellPhoneNumber'] ?? '');
        $address         = (string)($_POST['address'] ?? '');
        $birthday        = (string)($_POST['birthday'] ?? '');
        $email           = (string)($_POST['email'] ?? '');

        // Validaciones
        if ($name === '' || $ci === '' || $cellPhoneNumber === '' || $address === '' || $birthday === '' || $email === '') {
            $error = "Todos los campos son obligatorios.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo no tiene un formato válido.";
        } else {
            require_once __DIR__ . '/../Models/PartnerOnline.php';
            $partnerOnlineModel = new \PartnerOnline();

            // (Opcional) comprobar duplicados básicos
            if ($partnerOnlineModel->emailExists($email)) {
                $error = "Este correo ya tiene una solicitud registrada.";
            } elseif ($partnerOnlineModel->ciExists($ci)) {
                $error = "Ya existe una solicitud con ese CI.";
            } else {
                // El modelo pondrá NOW() en dateCreation y dateRegistration
                $ok = $partnerOnlineModel->create($name, $ci, $cellPhoneNumber, $address, $birthday, $email);
                if ($ok) {
                    $this->redirect('partner/register?success=1');
                    return;
                }
                $error = "Error al enviar la solicitud.";
            }
        }
    } elseif (isset($_GET['success'])) {
        $success = "Solicitud enviada con éxito. Será revisada por un administrador.";
        $this->view('partner/register', ['success' => $success]);
        return;
    }

    $this->view('partner/register', isset($error) ? ['error' => $error] : []);
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


    public function changePassword(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $new     = (string)($_POST['new_password']     ?? '');
            $confirm = (string)($_POST['confirm_password'] ?? '');

            if ($new === $confirm && $new !== '') {
                require_once __DIR__ . '/../Models/Usuario.php';
                $userModel = new \Usuario();
                if ($userModel->updatePasswordAndUnsetFirstLogin((int)$_SESSION['user_id'], $new)) {
                    unset($_SESSION['force_pw_change']);
                    $this->redirect('dashboard');
                    return;
                } else {
                    echo "Error al actualizar la contraseña";
                }
            } else {
                echo "Las contraseñas no coinciden";
            }
        }

        $this->view('change_password');
    }

    /**
     * Enviar correo de primer inicio de sesión con seguridad.
     * Usa Mailer si está disponible; de lo contrario, mail().
     */
    private function sendFirstLoginEmailSafe(string $to, string $username): void
    {
        // 1) Intentar con Mailer si existe
        $mailerPath = __DIR__ . '/../Lib/Mailer.php';
        if (is_file($mailerPath)) {
            require_once $mailerPath;
            if (class_exists('Mailer') && method_exists('Mailer', 'sendFirstLoginEmail')) {
                try {
                    //\Mailer::sendFirstLoginEmail($to, $username);
                    return;
                } catch (\Throwable $e) {
                    error_log("Mailer error, fallback to mail(): " . $e->getMessage());
                }
            }
        }

        // 2) Fallback a mail()
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = 'Primer inicio de sesión detectado';
        $message = "Hola {$username},\r\n\r\n" .
                   "Hemos detectado tu primer inicio de sesión en el Sistema MVC.\r\n" .
                   "Por seguridad, se te solicitará cambiar la contraseña.\r\n\r\n" .
                   "Si no fuiste tú, contacta al administrador.\r\n\r\n" .
                   "Saludos,\r\nSistema MVC";

        $headers = [];
        $headers[] = 'From: Sistema MVC <no-reply@localhost>';
        $headers[] = 'Reply-To: no-reply@localhost';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        @mail($to, $subject, $message, implode("\r\n", $headers));
    }

    // ====== RECUPERAR CONTRASEÑA ======
    public function forgotPassword(): void
    {
        $this->addDebug("=== INICIO forgotPassword ===");
        $this->addDebug("VERIFICANDO MODELO A USAR...");
        $this->startSession();

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $email = (string)($_POST['email'] ?? '');
            $this->addDebug("Email recibido: " . $email);

            if ($email === '') {
                $error = "Por favor, ingresa un correo electrónico.";
                $this->addDebug("Error: Email vacío");
            } else {
                try {
                    // VERIFICAR QUÉ MODELO EXISTE
                    $userModelPath     = __DIR__ . '/../Models/User.php';     // por si existe en tu proyecto
                    $usuarioModelPath  = __DIR__ . '/../Models/Usuario.php';  // el que usaremos

                    $this->addDebug("Verificando modelo User.php: " . $userModelPath . ' => ' . (is_file($userModelPath) ? 'SÍ' : 'NO'));
                    $this->addDebug("Verificando modelo Usuario.php: " . $usuarioModelPath . ' => ' . (is_file($usuarioModelPath) ? 'SÍ' : 'NO'));

                    if (is_file($usuarioModelPath)) {
                        require_once $usuarioModelPath;
                        $userModel = new \Usuario();
                        $modelName = "Usuario";
                    } elseif (is_file($userModelPath)) {
                        // Fallback raro, pero mantenido
                        require_once $userModelPath;
                        $userModel = new \Usuario(); // Mantener clase Usuario como pediste
                        $modelName = "Usuario(User.php)";
                    } else {
                        $this->addDebug("ERROR: NINGÚN MODELO ENCONTRADO");
                        $error = "Error del sistema: Modelo no encontrado";
                        $userModel = null;
                        $modelName = "NONE";
                    }

                    if ($userModel) {
                        $this->addDebug("Instancia de {$modelName} creada exitosamente");

                        if (!method_exists($userModel, 'findByEmail')) {
                            $this->addDebug("ERROR: Método findByEmail no existe en {$modelName}");
                            $error = "Error del sistema: Método no encontrado";
                        } else {
                            $this->addDebug("Buscando usuario por email: " . $email);
                            $user = $userModel->findByEmail($email);
                            $this->addDebug("Resultado de findByEmail: " . ($user ? "Usuario encontrado" : "Usuario no encontrado"));

                            if ($user) {
                                $this->addDebug("Datos del usuario encontrado:");
                                $this->addDebug("- ID: " . ($user['idUser'] ?? 'N/A'));
                                $this->addDebug("- Login: " . ($user['login'] ?? 'N/A'));
                                $this->addDebug("- Email: " . ($user['email'] ?? 'N/A'));
                                $this->addDebug("- tokenRecovery actual: " . ($user['tokenRecovery'] ?? 'NULL'));
                                $this->addDebug("- tokenExpiration actual: " . ($user['tokenExpiration'] ?? $user['tokeExpiration'] ?? 'NULL'));

                                // Generar token
                                $token = bin2hex(random_bytes(50));
                                $this->addDebug("Token generado: " . substr($token, 0, 20) . "...");

                                if (!method_exists($userModel, 'savePasswordResetToken')) {
                                    $this->addDebug("ERROR: Método savePasswordResetToken no existe en {$modelName}");
                                    $error = "Error del sistema: Método savePasswordResetToken no encontrado";
                                } else {
                                    $this->addDebug("Intentando guardar token en BD con {$modelName}");

                                    // DEBUG extra: introspección de conexión
                                    try {
                                        $this->addDebug("Verificando conexión de BD del modelo...");
                                        $reflection = new \ReflectionClass($userModel);
                                        if ($reflection->hasProperty('db')) {
                                            $dbProperty = $reflection->getProperty('db');
                                            $dbProperty->setAccessible(true);
                                            $db = $dbProperty->getValue($userModel);
                                            $this->addDebug("Conexión BD: " . ($db ? "OK" : "FAIL"));

                                            if ($db) {
                                                $checkTable = $db->query("DESCRIBE `user`");
                                                if ($checkTable !== false) {
                                                    $columns = $checkTable->fetchAll(\PDO::FETCH_COLUMN);
                                                    $this->addDebug("Columnas en tabla user: " . implode(', ', (array)$columns));
                                                }
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        $this->addDebug("Error al verificar BD: " . $e->getMessage());
                                    }

                                    $saveResult = $userModel->savePasswordResetToken($email, $token);
                                    $this->addDebug("Resultado savePasswordResetToken: " . ($saveResult ? "Éxito" : "Fallo"));

                                    if ($saveResult) {
                                        $this->addDebug("Token guardado exitosamente");

                                        // Crear el enlace para restablecer la contraseña
                                        $resetLink = (defined('BASE_URL') ? BASE_URL : '') . "reset-password?token=" . $token;
                                        $this->addDebug("Link de reset creado: " . $resetLink);

                                        // Enviar el correo
                                        $this->addDebug("Intentando enviar correo");
                                        $emailResult = $this->sendResetPasswordEmailSimple($email, $resetLink);
                                        $this->addDebug("Resultado envío correo: " . ($emailResult ? "Éxito" : "Fallo"));

                                        if ($emailResult) {
                                            $this->addDebug("Correo enviado exitosamente");
                                            $success = "Te hemos enviado un enlace para restablecer tu contraseña. Revisa tu correo.";
                                        } else {
                                            $this->addDebug("Error al enviar correo");
                                            $error = "No se pudo enviar el correo. Intenta nuevamente.";
                                        }
                                    } else {
                                        $this->addDebug("Error al guardar token en BD");
                                        $error = "Error interno al guardar el token.";
                                    }
                                }
                            } else {
                                $this->addDebug("Usuario no encontrado para email: " . $email);
                                $error = "Este correo electrónico no está registrado.";
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $this->addDebug("EXCEPCIÓN: " . $e->getMessage());
                    $this->addDebug("Archivo: " . $e->getFile());
                    $this->addDebug("Línea: " . $e->getLine());
                    $this->addDebug("Stack trace: " . $e->getTraceAsString());
                    $error = "Ha ocurrido un error: " . $e->getMessage();
                }
            }
        }

        $this->addDebug("=== FIN forgotPassword ===");
        $this->view('forgot-password', isset($error) ? ['error' => $error] : (isset($success) ? ['success' => $success] : []));
    }

    // ====== RESET CONTRASEÑA ======
    public function resetPassword(): void
{
    $this->startSession();

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // CORRECCIÓN: Obtener el token de $_POST en lugar de $_GET
        $token    = (string)($_POST['token'] ?? ''); // ← Cambiado de $_GET a $_POST
        $password = (string)($_POST['password'] ?? '');

        if ($token === '' || $password === '') {
            $error = "El token o la contraseña no son válidos";
        } else {
            require_once __DIR__ . '/../Models/Usuario.php';
            $userModel = new \Usuario();

            // Verificar el token
            $user = $userModel->verifyPasswordResetToken($token);
            if ($user) {
                if ($userModel->updatePassword((int)$user['idUser'], $password)) {
                    $success = "Tu contraseña ha sido restablecida. Ya puedes iniciar sesión.";
                    
                    // Redirigir después de 1 segundo para que se vea el mensaje
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '" . BASE_URL . "login';
                        }, 1000);
                    </script>";
                } else {
                    $error = "Hubo un error al restablecer la contraseña.";
                }
            } else {
                $error = "El token de recuperación es inválido o ha expirado.";
            }
        }
    } else {
        // Para solicitudes GET, mostrar el formulario con el token de la URL
        $token = (string)($_GET['token'] ?? '');
        if (empty($token)) {
            $error = "Token de recuperación no proporcionado";
        }
    }

    $viewData = [];
    if (isset($error)) $viewData['error'] = $error;
    if (isset($success)) $viewData['success'] = $success;
    if (isset($token)) $viewData['token'] = $token;

    $this->view('reset-password', $viewData);
}

    /**
     * Versión simplificada para enviar correo de recuperación
     * Con debugging detallado
     */
    private function sendResetPasswordEmailSimple(string $email, string $resetLink): bool
{
    try {
        $this->addDebug("=== INICIO sendResetPasswordEmailSimple ===");

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addDebug("Email inválido: " . $email);
            return false;
        }
        $this->addDebug("Email válido: " . $email);

        // RUTAS CORRECTAS - verificamos la estructura exacta
        $phpmailerPath = __DIR__ . '/../Lib/PHPMailer/PHPMailer.php';
        $exceptionPath = __DIR__ . '/../Lib/PHPMailer/Exception.php';
        $smtpPath = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

        $this->addDebug("Buscando PHPMailer en: " . $phpmailerPath);

        // Verificar que todos los archivos existan
        if (!is_file($phpmailerPath) || !is_file($exceptionPath) || !is_file($smtpPath)) {
            $this->addDebug("❌ Archivos PHPMailer no encontrados:");
            $this->addDebug("PHPMailer.php: " . (is_file($phpmailerPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
            $this->addDebug("Exception.php: " . (is_file($exceptionPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
            $this->addDebug("SMTP.php: " . (is_file($smtpPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
            $this->addDebug("Usando mail() como fallback");
            return $this->sendWithMailFunction($email, $resetLink);
        }

        // Cargar PHPMailer manualmente
        require_once $phpmailerPath;
        require_once $exceptionPath;
        require_once $smtpPath;

        $this->addDebug("✅ PHPMailer cargado correctamente");

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // CONFIGURACIÓN SMTP - OPTIMIZADA
            $mail->isSMTP();
            $mail->Host = 'mail.algoritmos.com.bo';
            $mail->SMTPAuth = true;
            $mail->Username = 'abap@algoritmos.com.bo';
            $mail->Password = 'Pl4st1c0s2025*';
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Configuración SSL mejorada
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ]
            ];

            // Configuración general
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 30;
            $mail->SMTPDebug = 0; // Desactivar debug output

            // Remitente y destinatario
            $mail->setFrom('abap@algoritmos.com.bo', 'Asociación de Artistas');
            $mail->addAddress($email);
            $mail->addReplyTo('abap@algoritmos.com.bo', 'No Responder');

            // Contenido del correo - MEJOR FORMATEADO
            $mail->isHTML(false);
            $mail->Subject = 'Recuperación de Contraseña - Asociación de Artistas';

            $message = "Hola,\n\n" .
                       "Has solicitado restablecer tu contraseña en el Sistema de la Asociación de Artistas.\n" .
                       "Visita el siguiente enlace para continuar:\n\n" .
                       $resetLink . "\n\n" .
                       "Este enlace expirará en 24 horas por seguridad.\n\n" .
                       "Si no solicitaste este cambio, puedes ignorar este correo.\n\n" .
                       "Saludos,\nEquipo de Asociación de Artistas";

            $mail->Body = $message;
            $mail->AltBody = $message; // Versión texto plano adicional

            // Intentar enviar
            $result = $mail->send();
            $this->addDebug("PHPMailer resultado: " . ($result ? "ÉXITO" : "FALLO"));

            if ($result) {
                $this->addDebug("✅ Correo enviado exitosamente con SMTP");
                return true;
            }

            $this->addDebug("❌ Error al enviar con PHPMailer: " . $mail->ErrorInfo);
            return $this->sendWithMailFunction($email, $resetLink);
            
        } catch (\Exception $e) {
            $this->addDebug("❌ Excepción PHPMailer: " . $e->getMessage());
            return $this->sendWithMailFunction($email, $resetLink);
        }
    } catch (\Throwable $e) {
        $this->addDebug("❌ Error general: " . $e->getMessage());
        return $this->sendWithMailFunction($email, $resetLink);
    } finally {
        $this->addDebug("=== FIN sendResetPasswordEmailSimple ===");
    }
}


    // Método de fallback con mail()
    private function sendWithMailFunction(string $email, string $resetLink): bool
    {
        try {
            $subject = 'Recuperación de Contraseña - Asociación de Artistas';
            $message = "Hola,\r\n\r\n" .
                       "Has solicitado restablecer tu contraseña.\r\n" .
                       "Visita el siguiente enlace para continuar:\r\n\r\n" .
                       $resetLink . "\r\n\r\n" .
                       "Este enlace expirará en 24 horas por seguridad.\r\n\r\n" .
                       "Si no solicitaste este cambio, puedes ignorar este correo.\r\n\r\n" .
                       "Saludos,\r\nAsociación de Artistas";

            $headers = [
                'From: Asociación de Artistas <juancarlosrojasvargas2022@gmail.com>',
                'Reply-To: juancarlosrojasvargas2022@gmail.com',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];

            $result = mail($email, $subject, $message, implode("\r\n", $headers));
            $this->addDebug("Resultado mail() fallback: " . ($result ? "TRUE" : "FALSE"));

            return (bool)$result;
        } catch (\Throwable $e) {
            $this->addDebug("Error en fallback mail(): " . $e->getMessage());
            return false;
        }
    }
}
