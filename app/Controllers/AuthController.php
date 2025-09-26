<?php
// app/Controllers/AuthController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

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
                            $userModel->resetFailedAttempts($login);
                            
                            session_regenerate_id(true);
                            $_SESSION['user_id']  = (int)$user['idUser'];
                            $_SESSION['username'] = (string)$user['login'];
                            $_SESSION['role']     = (int)$user['idRol'];

                            // Forzar cambio de contraseña si es primer inicio de sesión
                            if ((int)($user['firstSession'] ?? 1) === 0) {
                                // Enviar correo de primer inicio (opcional)
                                $this->sendFirstLoginEmailSafe($user['email'] ?? '', $user['login']);
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
/**
     * Método para que los administradores desbloqueen usuarios
     */
    public function unblockUser(): void
    {
        $this->startSession();
        
        // Verificar que sea un administrador (ajusta según tu lógica de roles)
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) !== 1) {
            $this->redirect('login');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $loginToUnblock = isset($_POST['login']) ? trim((string)$_POST['login']) : '';
            
            if ($loginToUnblock !== '') {
                $modelPath = __DIR__ . '/../Models/Usuario.php';
                if (is_file($modelPath)) {
                    require_once $modelPath;
                    $userModel = new \Usuario();
                    
                    if ($userModel->unblockUser($loginToUnblock)) {
                        $success = "Usuario {$loginToUnblock} desbloqueado correctamente.";
                    } else {
                        $error = "Error al desbloquear el usuario.";
                    }
                } else {
                    $error = "Error del sistema: Modelo no encontrado";
                }
            } else {
                $error = "Debe especificar un usuario para desbloquear.";
            }
        }

        $viewData = [];
        if (isset($success)) $viewData['success'] = $success;
        if (isset($error)) $viewData['error'] = $error;
        
        $this->view('admin/unblock_user', $viewData);
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
                   "Saludos";

        $headers = [];
        $headers[] = 'From: Sistema MVC <no-reply@localhost>';
        $headers[] = 'Reply-To: no-reply@localhost';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        @mail($to, $subject, $message, implode("\r\n", $headers));
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
            $smtpPath      = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

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
                $mail->Host       = 'mail.algoritmos.com.bo';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'abap@algoritmos.com.bo';
                $mail->Password   = 'Pl4st1c0s2025*';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Configuración SSL mejorada
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ]
                ];

                // Configuración general
                $mail->CharSet   = 'UTF-8';
                $mail->Timeout   = 30;
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

                $mail->Body    = $message;
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
