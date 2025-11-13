<?php
// app/Controllers/OnlinePartnerController.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/helpers.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PartnerOnline.php';
require_once __DIR__ . '/../Models/Option.php';

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
                // Usar el nuevo helper para envío de email
                $emailSent = sendVerificationEmail($email, $verificationToken, [
                    'name' => $name
                ]);
                
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
     * Handles token verification and sends confirmation email.
     */
    public function verifyEmail(): void
    {
       // 🔍 GUARDAR EN ARCHIVO PERSONALIZADO
    $logFile = __DIR__ . '/../logs/verification_debug.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    
    $logData = sprintf(
        "[%s] IP: %s | User-Agent: %s | Token: %s | Referer: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
        substr($_GET['token'] ?? 'N/A', 0, 10) . '...',
        $_SERVER['HTTP_REFERER'] ?? 'N/A'
    );
    
    file_put_contents($logFile, $logData, FILE_APPEND);
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

        // Usar el nuevo helper para envío de email de confirmación
        $emailSent = sendRegistrationConfirmationEmail($record['email'], [
            'name'             => $record['name'],
            'ci'               => $record['ci'],
            'email'            => $record['email'],
            'address'            => $record['address'],
            'cellphoneNumber'  => $record['cellPhoneNumber'],
            'birthday'         => $record['birthday']
        ]);

        if (!$emailSent) {
            error_log("No se pudo enviar email de confirmación para: " . $record['email']);
        }
        // Include Notification class
        require_once __DIR__ . '/../Models/Notification.php';
        
        // Create a notification for the new contribution
        $notificationData = [
            'title' => 'Nuevo Registro de Solicitud de Socio',
            'message' => "Se ha registrado un nueva solicitud de socio",
            'type' => 'info',
            'data' => json_encode([
                'name'             => $record['name'],
                'ci'               => $record['ci'],
                'email'            => $record['email'],
            ]),
            'idRol' => 1 // Asegurarse de que el rol esté definido
        ];

        $notification = new \App\Models\Notification();
        $notificationId = $notification->create($notificationData);
        
        if ($notificationId === false) {
            error_log("Error: No se pudo crear la notificación para la solicitud de registro");
            throw new \Exception("Fallo al crear la notificación de la solicitud de registro");
        } else {
            error_log("Notificación creada con ID: " . $notificationId);
        }
        $this->view('partner/verify', ['success' => 'Correo verificado con éxito. Tu solicitud está en revisión por un administrador.']);
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