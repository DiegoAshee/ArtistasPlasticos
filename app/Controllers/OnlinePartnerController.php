<?php
// app/Controllers/OnlinePartnerController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/helpers.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PartnerOnline.php';

class OnlinePartnerController extends BaseController
{
public function registerPartner(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        // === Inputs ===
        $name             = trim((string)($_POST['name'] ?? ''));
        $ci               = trim((string)($_POST['ci'] ?? ''));
        $cellPhoneNumber  = trim((string)($_POST['cellPhoneNumber'] ?? ''));
        $address          = trim((string)($_POST['address'] ?? ''));
        $birthday         = trim((string)($_POST['birthday'] ?? ''));
        $email            = trim((string)($_POST['email'] ?? ''));
        $dateRegistration = trim((string)($_POST['dateRegistration'] ?? ''));
        $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';

        // Array para errores específicos por campo
        $fieldErrors = [];
        $generalErrors = [];

        // === VALIDACIONES UNIFICADAS ===
        
        // Validar campos básicos
        if ($name === '') {
            $fieldErrors['name'] = "El nombre es obligatorio.";
        } elseif (strlen($name) < 3) {
            $fieldErrors['name'] = "El nombre debe tener al menos 3 caracteres.";
        } elseif (strlen($name) > 100) {
            $fieldErrors['name'] = "El nombre no puede exceder 100 caracteres.";
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $name)) {
            $fieldErrors['name'] = "El nombre solo puede contener letras y espacios.";
        }

        if ($ci === '') {
            $fieldErrors['ci'] = "La cédula de identidad es obligatoria.";
        } elseif (!preg_match('/^[0-9]+$/', $ci)) {
            $fieldErrors['ci'] = "La cédula solo puede contener números.";
        } elseif (strlen($ci) < 6 || strlen($ci) > 12) {
            $fieldErrors['ci'] = "La cédula debe tener entre 6 y 12 dígitos.";
        }

        if ($cellPhoneNumber === '') {
            $fieldErrors['cellPhoneNumber'] = "El número de celular es obligatorio.";
        } elseif (!preg_match('/^[67][0-9]{7}$/', $cellPhoneNumber)) {
            $fieldErrors['cellPhoneNumber'] = "El celular debe tener 8 dígitos y comenzar con 6 o 7.";
        }

        if ($address === '') {
            $fieldErrors['address'] = "La dirección es obligatoria.";
        } elseif (strlen($address) < 10) {
            $fieldErrors['address'] = "La dirección debe ser más específica (mínimo 10 caracteres).";
        } elseif (strlen($address) > 255) {
            $fieldErrors['address'] = "La dirección no puede exceder 255 caracteres.";
        }

        if ($email === '') {
            $fieldErrors['email'] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fieldErrors['email'] = "El formato del correo no es válido.";
        } elseif (strlen($email) > 100) {
            $fieldErrors['email'] = "El correo no puede exceder 100 caracteres.";
        }

        if ($birthday === '') {
            $fieldErrors['birthday'] = "La fecha de nacimiento es obligatoria.";
        } else {
            try {
                $dob = new \DateTime($birthday);
                $today = new \DateTime('today');
                $age = $dob->diff($today)->y;
                
                $minDate = new \DateTime('-120 years');
                $maxDate = new \DateTime('-18 years');
                
                if ($dob > $today) {
                    $fieldErrors['birthday'] = "La fecha de nacimiento no puede ser futura.";
                } elseif ($dob < $minDate) {
                    $fieldErrors['birthday'] = "La fecha de nacimiento es demasiado antigua.";
                } elseif ($dob > $maxDate) {
                    $fieldErrors['birthday'] = "Debes ser mayor de edad (18+ años).";
                }
            } catch (\Exception $e) {
                $fieldErrors['birthday'] = "Formato de fecha inválido.";
            }
        }

        // Validar archivos
        $frontImageValid = $this->validateUploadedFile('frontImage', 'frontImage');
        if ($frontImageValid !== true) {
            $fieldErrors['frontImage'] = $frontImageValid;
        }

        $backImageValid = $this->validateUploadedFile('backImage', 'backImage');
        if ($backImageValid !== true) {
            $fieldErrors['backImage'] = $backImageValid;
        }

        // Validar reCAPTCHA
        if (empty($recaptchaResponse)) {
            $generalErrors[] = 'Por favor, complete el reCAPTCHA.';
        } else {
            $secretKey = RECAPTCHA_SECRET;
            $verify = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .
                '&response=' . urlencode($recaptchaResponse)
            );
            $resp = json_decode($verify);
            
            if (!$resp->success) {
                $generalErrors[] = 'Verificación de reCAPTCHA fallida. Por favor, intente nuevamente.';
            }
        }

        // Validaciones de base de datos (solo si no hay errores críticos)
        if (empty($fieldErrors) && empty($generalErrors)) {
            $partnerOnlineModel = new \PartnerOnline();
            $partnerOnlineModel->deleteExpiredUnverified();

            if ($partnerOnlineModel->emailExistsAnywhere($email)) {
                $fieldErrors['email'] = "Este correo ya está registrado en el sistema.";
            }
            if ($partnerOnlineModel->ciExistsAnywhere($ci)) {
                $fieldErrors['ci'] = "Esta cédula ya está registrada en el sistema.";
            }
        }

        // Si hay cualquier tipo de error, mostrar el formulario con errores
        if (!empty($fieldErrors) || !empty($generalErrors)) {
            $this->view('partner/register', [
                'field_errors' => $fieldErrors,
                'general_errors' => $generalErrors,
                'form_data' => $_POST,
                'uploaded_files' => [
                    'frontImage' => $this->getFileInfo('frontImage'),
                    'backImage' => $this->getFileInfo('backImage')
                ]
            ]);
            return;
        }

        // === PROCESAMIENTO EXITOSO ===
        try {
            $frontImageRel = $this->handleUpload('frontImage', $ci, 'front');
            $backImageRel = $this->handleUpload('backImage', $ci, 'back');
        } catch (\Exception $e) {
            $generalErrors[] = 'Error al procesar los archivos: ' . $e->getMessage();
            $this->view('partner/register', [
                'general_errors' => $generalErrors,
                'form_data' => $_POST
            ]);
            return;
        }

        // Crear registro
        $verificationToken = bin2hex(random_bytes(32));
        $tokenExpiresAt = (new \DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');

        $partnerId = $partnerOnlineModel->create(
            $name,
            $ci,
            $cellPhoneNumber,
            $address,
            $birthday,
            $email,
            $frontImageRel,
            $backImageRel,
            $verificationToken,
            $tokenExpiresAt
        );

        if ($partnerId) {
            $emailSent = $this->sendVerificationEmail($email, $verificationToken, [
                    'name' => $name
                ]);
            /* $emailSent = sendVerificationEmail($email, $verificationToken, [
                'name' => $name,
                'ci' => $ci,
                'email' => $email
            ]); */
            
            if ($emailSent) {
                $this->view('partner/register', [
                    'success' => 'Se ha enviado un enlace de verificación a tu correo. Por favor, verifica tu email para completar el registro.',
                    'email' => $email
                ]);
            } else {
                $partnerOnlineModel->delete((int)$partnerId);
                $generalErrors[] = 'No se pudo enviar el correo de verificación. Por favor, intenta nuevamente.';
                $this->view('partner/register', [
                    'general_errors' => $generalErrors,
                    'form_data' => $_POST
                ]);
            }
            return;
        }
        
        $generalErrors[] = 'Error interno del servidor. Por favor, intenta más tarde.';
        $this->view('partner/register', [
            'general_errors' => $generalErrors,
            'form_data' => $_POST
        ]);
        return;
    }

    // GET request
    if (isset($_GET['success'])) {
        $this->view('partner/register', [
            'success' => "Solicitud enviada con éxito. Por favor, verifica tu correo para completar el registro."
        ]);
        return;
    }

    $this->view('partner/register');
}

/**
 * Valida un archivo subido
 */
private function validateUploadedFile(string $fieldName, string $displayName): string|true
{
    if (!isset($_FILES[$fieldName])) {
        return "Falta el archivo de $displayName.";
    }

    $file = $_FILES[$fieldName];
    
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return "Debe seleccionar un archivo para $displayName.";
    }
    
    if ($file['error'] === UPLOAD_ERR_FORM_SIZE || $file['error'] === UPLOAD_ERR_INI_SIZE) {
        return "El archivo de $displayName excede el tamaño máximo de 2MB.";
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return "Error al subir el archivo de $displayName.";
    }

    // Validar tamaño (2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        return "El archivo de $displayName excede el tamaño máximo de 2MB.";
    }

    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        return "El archivo de $displayName debe ser JPG, PNG o JPEG.";
    }

    // Validar que no esté vacío
    if ($file['size'] === 0) {
        return "El archivo de $displayName está vacío.";
    }

    return true;
}

/**
 * Obtiene información básica del archivo para mantener referencia
 */
private function getFileInfo(string $fieldName): array|null
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    return [
        'name' => $_FILES[$fieldName]['name'],
        'size' => $_FILES[$fieldName]['size'],
        'type' => $_FILES[$fieldName]['type']
    ];
}

    /**
     * Handles token verification and sends confirmation email.
     */
    public function verifyEmail(): void
    {
        $token = trim((string)($_GET['token'] ?? ''));

        if (!$token) {
            $this->view('partner/verify', ['error' => 'Token inválido o no proporcionado.']);
            return;
        }

        $partnerOnlineModel = new \PartnerOnline();
        $record = $partnerOnlineModel->verifyToken($token);

        if (!$record) {
            $this->view('partner/verify', ['error' => 'El enlace de verificación es inválido o ha expirado.']);
            return;
        }
        $this->sendPartnerRegistrationEmail($record['email'], [
            'name'             => $record['name'],
            'ci'               => $record['ci'],
            'email'            => $record['email'],
            'cellphoneNumber'  => $record['cellPhoneNumber'],
            'birthday'         => $record['birthday']
        ]);

        // Send confirmation email after successful verification
        /* sendRegistrationConfirmationEmail($record['email'], [
        'name'             => $record['name'],
        'ci'               => $record['ci'],
        'email'            => $record['email'],
        'cellphoneNumber'  => $record['cellPhoneNumber'],
        'birthday'         => $record['birthday']
    ]); */

        $this->view('partner/verify', ['success' => 'Correo verificado con éxito. Tu solicitud está en revisión por un administrador.']);
    }

    /**
     * Sends the email verification link with detailed debugging.
     */
    private function sendVerificationEmail(string $email, string $token, array $data): bool
    {
        try {
            error_log("=== INICIO sendVerificationEmail ===");
            error_log("Email destino: " . $email);

            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("Email inválido: " . $email);
                return false;
            }
            error_log("Email válido: " . $email);

            // Rutas de PHPMailer
            $phpmailerPath = __DIR__ . '/../Lib/PHPMailer/PHPMailer.php';
            $exceptionPath = __DIR__ . '/../Lib/PHPMailer/Exception.php';
            $smtpPath      = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

            error_log("Buscando PHPMailer en: " . $phpmailerPath);

            // Verificar que todos los archivos existan
            if (!is_file($phpmailerPath) || !is_file($exceptionPath) || !is_file($smtpPath)) {
                error_log("❌ Archivos PHPMailer no encontrados:");
                error_log("PHPMailer.php: " . (is_file($phpmailerPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("Exception.php: " . (is_file($exceptionPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("SMTP.php: " . (is_file($smtpPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("Usando mail() como fallback");
                return $this->sendWithMailFunction($email, $token, $data);
            }

            // Cargar PHPMailer
            require_once $phpmailerPath;
            require_once $exceptionPath;
            require_once $smtpPath;

            error_log("✅ PHPMailer cargado correctamente");

            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host       = 'mail.algoritmos.com.bo';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'abap@algoritmos.com.bo';
                $mail->Password   = 'Pl4st1c0s2025*';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Configuración SSL
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
                $mail->SMTPDebug = 0; // Desactivar debug output en producción

                // Remitente y destinatario
                $mail->setFrom('abap@algoritmos.com.bo', 'Asociación Boliviana de Artistas Plásticos');
                $mail->addAddress($email, $data['name'] ?? '');
                $mail->addReplyTo('abap@algoritmos.com.bo', 'No Responder');

                // Contenido del correo
                $verificationLink = u('partner/verify?token=' . urlencode($token));
                $mail->isHTML(true);
                $mail->Subject = 'Verifica tu correo - Asociación Boliviana de Artistas Plásticos';
                $mail->Body    = "
                    <h2>¡Hola " . htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8') . "!</h2>
                    <p>Gracias por registrarte en la Asociación Boliviana de Artistas Plásticos.</p>
                    <p>Por favor, verifica tu correo haciendo clic en el siguiente enlace:</p>
                    <p><a href=\"$verificationLink\" style=\"display:inline-block; padding:10px 20px; background:#bca478; color:#fff; text-decoration:none; border-radius:8px;\">Verificar Correo</a></p>
                    <p>Este enlace expirará en 24 horas.</p>
                    <p>Si no solicitaste este registro, ignora este correo.</p>
                    <br>
                    <p>Saludos,<br><b>Asociación Boliviana de Artistas Plásticos</b></p>
                ";
                $mail->AltBody = "Hola {$data['name']},\n\n" .
                                 "Por favor, verifica tu correo haciendo clic en el siguiente enlace:\n" .
                                 "$verificationLink\n\n" .
                                 "Este enlace expirará en 24 horas.\n\n" .
                                 "Si no solicitaste este registro, ignora este correo.\n\n" .
                                 "Saludos,\nAsociación Boliviana de Artistas Plásticos";

                // Intentar enviar
                $result = $mail->send();
                error_log("PHPMailer resultado: " . ($result ? "ÉXITO" : "FALLO"));

                if ($result) {
                    error_log("✅ Correo enviado exitosamente con SMTP");
                    return true;
                }

                error_log("❌ Error al enviar con PHPMailer: " . $mail->ErrorInfo);
                return $this->sendWithMailFunction($email, $token, $data);
                
            } catch (\Exception $e) {
                error_log("❌ Excepción PHPMailer: " . $e->getMessage());
                return $this->sendWithMailFunction($email, $token, $data);
            }
        } catch (\Throwable $e) {
            error_log("❌ Error general: " . $e->getMessage());
            return $this->sendWithMailFunction($email, $token, $data);
        } finally {
            error_log("=== FIN sendVerificationEmail ===");
        }
    } 

    /**
     * Fallback method using mail() function.
     */
    private function sendWithMailFunction(string $email, string $token, array $data): bool
    {
        try {
            $verificationLink = u('partner/verify?token=' . urlencode($token));
            $subject = 'Verifica tu correo -Asociación Boliviana de Artistas Plásticos';
            $message = "Hola {$data['name']},\r\n\r\n" .
                       "Por favor, verifica tu correo haciendo clic en el siguiente enlace:\r\n" .
                       "$verificationLink\r\n\r\n" .
                       "Este enlace expirará en 24 horas.\r\n\r\n" .
                       "Si no solicitaste este registro, ignora este correo.\r\n\r\n" .
                       "Saludos,\r\nAsociación Boliviana de Artistas Plásticos";

            $headers = [
                'From: Asociación Boliviana de Artistas Plásticos<abap@algoritmos.com.bo>',
                'Reply-To: abap@algoritmos.com.bo',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];

            $result = mail($email, $subject, $message, implode("\r\n", $headers));
            error_log("Resultado mail() fallback: " . ($result ? "TRUE" : "FALSE"));

            return (bool)$result;
        } catch (\Throwable $e) {
            error_log("Error en fallback mail(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sends the partner registration confirmation email.
     */
    private function sendPartnerRegistrationEmail(string $email, array $data): bool
    {
        try {
            error_log("=== INICIO sendPartnerRegistrationEmail ===");
            error_log("Email destino: " . $email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                error_log("Email inválido: " . $email);
                return false;
            }
            error_log("Email válido: " . $email);

            $phpmailerPath = __DIR__ . '/../Lib/PHPMailer/PHPMailer.php';
            $exceptionPath = __DIR__ . '/../Lib/PHPMailer/Exception.php';
            $smtpPath      = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

            error_log("Buscando PHPMailer en: " . $phpmailerPath);

            if (!is_file($phpmailerPath) || !is_file($exceptionPath) || !is_file($smtpPath)) {
                error_log("❌ Archivos PHPMailer no encontrados:");
                error_log("PHPMailer.php: " . (is_file($phpmailerPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("Exception.php: " . (is_file($exceptionPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("SMTP.php: " . (is_file($smtpPath) ? 'ENCONTRADO' : 'NO ENCONTRADO'));
                error_log("Usando mail() como fallback");
                return $this->sendConfirmationWithMailFunction($email, $data);
            }

            require_once $phpmailerPath;
            require_once $exceptionPath;
            require_once $smtpPath;

            error_log("✅ PHPMailer cargado correctamente");

            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                // Configuración SMTP
                $mail->isSMTP();
                $mail->Host       = 'mail.algoritmos.com.bo';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'abap@algoritmos.com.bo';
                $mail->Password   = 'Pl4st1c0s2025*';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ]
                ];

                $mail->CharSet   = 'UTF-8';
                $mail->Timeout   = 30;
                $mail->SMTPDebug = 0;

                $mail->setFrom('abap@algoritmos.com.bo', 'Asociación Boliviana de Artistas Plásticos');
                $mail->addAddress($email, $data['name'] ?? '');
                $mail->addReplyTo('abap@algoritmos.com.bo', 'No Responder');

                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de Registro de Solicitud - Asociación Boliviana de Artistas Plásticos';
                $mail->Body    = "
                    <h2>¡Hola " . htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8') . "!</h2>
                    <p>Tu registro fue recibido exitosamente en la Asociación Boliviana de Artistas Plásticos.</p>
                    <p><b>CI:</b> " . htmlspecialchars($data['ci'] ?? '', ENT_QUOTES, 'UTF-8') . "<br>
                    <b>Correo:</b> " . htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8') . "<br>
                    <b>Celular:</b> " . htmlspecialchars($data['cellphoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') . "<br>
                    <b>Fecha de nacimiento:</b> " . htmlspecialchars($data['birthday'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>
                    <p>Nos pondremos en contacto contigo muy pronto.</p>
                    <br>
                    <p>Saludos,<br><b>Asociación Boliviana de Artistas Plásticos</b></p>
                ";
                $mail->AltBody = "Hola {$data['name']},\n\n" .
                                 "Tu registro fue recibido exitosamente.\n\n" .
                                 "CI: {$data['ci']}\n" .
                                 "Correo: {$data['email']}\n" .
                                 "Celular: {$data['cellphoneNumber']}\n" .
                                 "Fecha de nacimiento: {$data['birthday']}\n\n" .
                                 "Saludos,\nAsociación Boliviana de Artistas Plásticos";

                $result = $mail->send();
                error_log("PHPMailer resultado (confirmación): " . ($result ? "ÉXITO" : "FALLO"));

                if ($result) {
                    error_log("✅ Correo de confirmación enviado exitosamente con SMTP");
                    return true;
                }

                error_log("❌ Error al enviar con PHPMailer: " . $mail->ErrorInfo);
                return $this->sendConfirmationWithMailFunction($email, $data);
                
            } catch (\Exception $e) {
                error_log("❌ Excepción PHPMailer (confirmación): " . $e->getMessage());
                return $this->sendConfirmationWithMailFunction($email, $data);
            }
        } catch (\Throwable $e) {
            error_log("❌ Error general (confirmación): " . $e->getMessage());
            return $this->sendConfirmationWithMailFunction($email, $data);
        } finally {
            error_log("=== FIN sendPartnerRegistrationEmail ===");
        }
    } 

    /**
     * Fallback method for confirmation email using mail() function.
     */
    private function sendConfirmationWithMailFunction(string $email, array $data): bool
    {
        try {
            $subject = 'Confirmación de Registro - Asociación Boliviana de Artistas Plásticos';
            $message = "Hola {$data['name']},\r\n\r\n" .
                       "Tu registro fue recibido exitosamente.\r\n\n" .
                       "CI: {$data['ci']}\r\n" .
                       "Correo: {$data['email']}\r\n" .
                       "Celular: {$data['cellphoneNumber']}\r\n" .
                       "Fecha de nacimiento: {$data['birthday']}\r\n\r\n" .
                       "Saludos,\r\nAsociación Boliviana de Artistas Plásticos";

            $headers = [
                'From: Asociación Boliviana de Artistas Plásticos <abap@algoritmos.com.bo>',
                'Reply-To: abap@algoritmos.com.bo',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'X-Mailer: PHP/' . phpversion()
            ];

            $result = mail($email, $subject, $message, implode("\r\n", $headers));
            error_log("Resultado mail() fallback (confirmación): " . ($result ? "TRUE" : "FALSE"));

            return (bool)$result;
        } catch (\Throwable $e) {
            error_log("Error en fallback mail() (confirmación): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Handles file uploads.
     */
    private function handleUpload(string $inputName, string $ci, string $prefix): ?string
    {
        error_log("[$inputName] Attempting upload at " . date('Y-m-d H:i:s') . ": " . print_r($_FILES[$inputName] ?? 'No file', true));

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_FORM_SIZE) {
                throw new \RuntimeException("El archivo {$inputName} excede el tamaño máximo permitido de 2MB.");
            }
            error_log("[$inputName] Upload failed: Error code " . ($_FILES[$inputName]['error'] ?? 'No file uploaded'));
            throw new \RuntimeException("Error al subir el archivo {$inputName}.");
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
}