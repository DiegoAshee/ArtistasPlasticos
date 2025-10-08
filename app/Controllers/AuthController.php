<?php
// app/Controllers/AuthController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/helpers.php';

class AuthController extends BaseController
{
    // ====== LOGIN ======
    // Mostrar el formulario de login y procesar autenticación
    public function login(): void
    {
        $this->startSession();

        // Si ya está logueado y es GET, respetar forzado de cambio de contraseña
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && !empty($_SESSION['user_id'] ?? null)) {
            if (!empty($_SESSION['force_pw_change'] ?? null)) {
                $this->redirect('change-password');
                return;
            }
            $this->redirect('dashboard');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            // === reCAPTCHA ===
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $secretKey = RECAPTCHA_SECRET; // From config.php
            $verify = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .
                '&response=' . urlencode($recaptchaResponse)
            );
            $resp = json_decode($verify);

            if (!$resp->success) {
                $this->view('login', ['error' => 'Captcha inválido o no verificado. Por favor, marca "No soy un robot".']);
                return;
            }
            
            
            $login    = isset($_POST['login']) ? trim((string)$_POST['login']) : '';
            $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

            if ($login === '' || $password === '') {
                $error = "Por favor, complete todos los campos";
            } else {
                $modelPath = __DIR__ . '/../Models/Usuario.php';
                $modelPathOption = __DIR__ . '/../Models/Option.php';
                if (is_file($modelPath)&&is_file($modelPathOption)) {
                    require_once $modelPath;
                    require_once $modelPathOption;
                    
                    $userModel = new \Usuario();
                    $optionModel = new \Option();

                    // Verificar si el usuario está bloqueado
                    if ($userModel->isUserBlocked($login)) {
                        $error = "Su cuenta ha sido bloqueada por exceso de intentos fallidos. Contacte al administrador.";
                    } else {
                        // Obtener el límite de intentos desde la configuración
                        $activeOption = $optionModel->getActive();
                        $maxAttempts = $activeOption ? (int)($activeOption['NumberAttempts'] ?? 3) : 3;
                        
                        // Intentar autenticar
                        $user = $userModel->authenticate($login, $password);
                        
                        if ($user) {
                            // Login exitoso - resetear intentos fallidos
                            $userModel->resetFailedAttempts((int)$user['idUser']);
                            
                            session_regenerate_id(true);
                            $_SESSION['user_id']  = (int)$user['idUser'];
                            $_SESSION['username'] = (string)$user['login'];
                            $_SESSION['role']     = (int)$user['idRol'];

                            // Forzar cambio de contraseña si es primer inicio de sesión
                            if ((int)($user['firstSession'] ?? 1) === 0) {
                                // Enviar correo de primer inicio (opcional)
                                $_SESSION['force_pw_change'] = true;
                                $this->redirect('change-password');
                                return;
                            }

                            // Ir al dashboard si no requiere cambio de contraseña
                            $this->redirect('dashboard');
                        } else {
                            // Login fallido - incrementar intentos
                            $userModel->incrementFailedAttempts($login);
                            $currentAttempts = $userModel->getFailedAttempts($login);
                            
                            // Verificar si se alcanzó el límite
                            if ($currentAttempts >= $maxAttempts) {
                                $userModel->blockUser($login);
                                $error = "Ha excedido el número máximo de intentos ({$maxAttempts}). Su cuenta ha sido bloqueada. Contacte al administrador.";
                            } else {
                                $remainingAttempts = $maxAttempts - $currentAttempts;
                                $error = "Usuario o contraseña incorrectos. Le quedan {$remainingAttempts} intentos antes de que su cuenta sea bloqueada.";
                            }
                        }
                    }
                } else {
                    $error = "Error del sistema: Modelo no encontrado";
                }
            }
        }

        // GET o POST con error -> mostrar formulario
        $viewData = isset($error) ? ['error' => $error] : [];
        $this->view('login', $viewData);
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
                                        $emailResult = sendPasswordResetEmail($email, $resetLink);
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

    public function changePassword(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $new     = trim((string)($_POST['new_password']     ?? ''));
            $confirm = trim((string)($_POST['confirm_password'] ?? ''));

            $errors = [];

            // Reglas: 8-12, 1 mayúscula, 1 minúscula, 1 número, 1 símbolo
            if ($new === '') {
                $errors[] = 'La contraseña no puede estar vacía';
            }
            if (strlen($new) < 8 || strlen($new) > 12) {
                $errors[] = 'La contraseña debe tener entre 8 y 12 caracteres';
            }
            if (!preg_match('/[A-Z]/', $new)) {
                $errors[] = 'Debe contener al menos una letra mayúscula';
            }
            if (!preg_match('/[a-z]/', $new)) {
                $errors[] = 'Debe contener al menos una letra minúscula';
            }
            if (!preg_match('/[0-9]/', $new)) {
                $errors[] = 'Debe contener al menos un número';
            }
            if (!preg_match('/[^A-Za-z0-9]/', $new)) {
                $errors[] = 'Debe contener al menos un símbolo';
            }
            if ($new !== $confirm) {
                $errors[] = 'Las contraseñas no coinciden';
            }

            if (empty($errors)) {
                require_once __DIR__ . '/../Models/Usuario.php';
                $userModel = new \Usuario();
                if ($userModel->updatePasswordAndUnsetFirstLogin((int)$_SESSION['user_id'], $new)) {
                    unset($_SESSION['force_pw_change']);
                    $this->redirect('dashboard');
                    return;
                } else {
                    $error = 'Error al actualizar la contraseña. Intenta nuevamente.';
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        $viewData = isset($error) ? ['error' => $error] : [];
        $this->view('change_password', $viewData);
    }

}
