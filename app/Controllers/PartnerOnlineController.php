<?php 
// app/Controllers/PartnerOnlineController.php

declare(strict_types=1);

// Incluir archivos necesarios
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PartnerOnline.php';
require_once __DIR__ . '/../Config/helpers.php';  // Asegúrate de incluir helpers.php donde está la función u()

class PartnerOnlineController extends BaseController
{
    // Crear solicitud de cambio
 



    //metodo solicitud cambios por parte de socio
    // Crear solicitud de cambio
    public function createRequest(): void
    {
        $this->startSession();

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Si la solicitud es POST, procesar los datos
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos del formulario
            $name            = trim($_POST['name'] ?? '');
            $ci              = trim($_POST['ci'] ?? '');
            $cellPhoneNumber = trim($_POST['cellPhoneNumber'] ?? '');
            $address         = trim($_POST['address'] ?? '');
            $birthday        = trim($_POST['birthday'] ?? '');
            $email           = trim($_POST['email'] ?? '');

            // Validar que no falten campos
            if ($name === '' || $ci === '' || $cellPhoneNumber === '' || $address === '' || $birthday === '' || $email === '') {
                $error = "Todos los campos son obligatorios.";
                $this->view('partnerOnline/create', ['error' => $error]);
                return;
            }

            // Crear instancia del modelo PartnerOnline y obtener el ID del usuario
            $partnerOnlineModel = new \PartnerOnline();
            $userId = $_SESSION['user_id'];

            // Crear solicitud de cambio
            $requestId = $partnerOnlineModel->createChangeRequest($name, $ci, $cellPhoneNumber, $address, $birthday, $email, $userId);

            if ($requestId) {
                // Mensaje de éxito en la sesión
                $_SESSION['success'] = "Solicitud enviada correctamente. Espera la aprobación del administrador.";
                
                // Redirigir al perfil usando la URL correcta generada por la función u()
                $this->redirect(('users/profile'));  // Debería funcionar con BASE_URL correctamente
            } else {
                // En caso de error, mostramos un mensaje de error en la vista
                $error = "Hubo un problema al crear la solicitud.";
                $this->view('partnerOnline/create', ['error' => $error]);
            }
        }
    }


    //mostrar solicitudes 

    // app/Controllers/PartnerOnlineController.php

    // app/Controllers/PartnerOnlineController.php (método completo)

public function pending(): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) { $this->redirect('login'); return; }

    // Menú dinámico desde BD
    require_once __DIR__ . '/../Models/Competence.php';
    $roleId      = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new \Competence())->getByRole($roleId);

    // Datos de solicitudes
    require_once __DIR__ . '/../Models/PartnerOnline.php';
    $model = new \PartnerOnline();
    $rows  = $model->getAll();

    // Helper para fechas vacías (NULL, '', 0000-00-00)
    $isEmptyDate = static function($v): bool {
        if ($v === null) return true;
        if ($v === '')   return true;
        $v = trim((string)$v);
        return $v === '0000-00-00' || $v === '0000-00-00 00:00:00';
    };

    // PENDIENTE = sin confirmación
    $pending = array_filter($rows, static fn(array $r): bool => $isEmptyDate($r['dateConfirmation'] ?? null));

    // Tipo: nuevos (idUser NULL) vs modificaciones (idUser NOT NULL)
    //$registrations = array_values(array_filter($pending, static fn(array $r): bool => empty($r['idUser'])));
    $registrations = $model->getAllPending();
    $changes       = array_values(array_filter($pending, static fn(array $r): bool => !empty($r['idUser'])));

    $this->view('partnerOnline/pending', [
        'registrations' => $registrations,
        'changes'       => $changes,
        // Sidebar dinámico como en dashboard
        'menuOptions'   => $menuOptions,
        'currentPath'   => 'partnerOnline/pending',
        'roleId'        => $roleId,
    ]);
}







//Aceptar o rechazar solicitudes de nuevos socios
public function approve(): void
{
    $this->startSession();
    
    if (!isset($_SESSION['user_id'])) { 
        $this->redirect('login'); 
        return; 
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { 
        $this->redirect('partnerOnline/pending'); 
        return; 
    }
    
    if (!isset($_POST['id'])) { 
        $this->redirect('partnerOnline/pending'); 
        return; 
    }

    $id = (int)$_POST['id'];
    if ($id <= 0) { 
        $_SESSION['error'] = 'ID de solicitud inválido.';
        $this->redirect('partnerOnline/pending'); 
        return; 
    }

    // Cargar modelos necesarios
    require_once __DIR__ . '/../Models/PartnerOnline.php';
    require_once __DIR__ . '/../Models/Partner.php';
    require_once __DIR__ . '/../Models/Usuario.php';

    $partnerOnlineModel = new \PartnerOnline();
    $partnerModel = new \Partner();
    $userModel = new \Usuario();

    // Usar transacción para garantizar consistencia
    $db = \Database::singleton()->getConnection();
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    try {
        $db->beginTransaction();

        // 1. Obtener los datos de la solicitud online
        $solicitud = $partnerOnlineModel->findById($id);
        
        if (!$solicitud) {
            throw new \Exception('La solicitud no existe.');
        }

        if (!empty($solicitud['dateConfirmation']) && $solicitud['dateConfirmation'] !== '0000-00-00' && $solicitud['dateConfirmation'] !== '0000-00-00 00:00:00') {
            throw new \Exception('La solicitud ya fue confirmada previamente.');
        }

        if (!$solicitud['isverified'] || $solicitud['isverified'] == 0) {
            throw new \Exception('La solicitud aún no ha sido verificada por email.');
        }

        // 2. Verificar duplicados antes de crear
        if ($userModel->loginExists($solicitud['ci'])) {
            throw new \Exception('Ya existe un usuario con la cédula ' . $solicitud['ci']);
        }

        if ($userModel->emailExists($solicitud['email'])) {
            throw new \Exception('Ya existe un usuario con el email ' . $solicitud['email']);
        }

        // 3. Crear el Partner (socio) en la tabla partner
        $partnerId = $partnerModel->create(
            $solicitud['name'],
            $solicitud['ci'],
            $solicitud['cellPhoneNumber'],
            $solicitud['address'],
            date('Y-m-d H:i:s'), // dateCreation
            $solicitud['birthday'],
            date('Y-m-d'), // dateRegistration
            $solicitud['frontImageURL'] ?? null,
            $solicitud['backImageURL'] ?? null
        );

        if (!$partnerId) {
            throw new \Exception('No se pudo crear el registro del socio.');
        }

        // 4. Crear el Usuario asociado usando el modelo actualizado que retorna el ID
        $login = substr($solicitud['ci'], 0, 20);
        $password = $solicitud['ci']; // CI como contraseña temporal
        $idRole = 2; // Rol de socio

        // Verificar si el login ya existe y generar uno único si es necesario
        $loginFinal = $login;
        $counter = 1;
        while ($userModel->loginExists($loginFinal)) {
            $suffix = (string)$counter++;
            $loginFinal = substr($login, 0, max(1, 20 - strlen($suffix))) . $suffix;
        }

        // Usar el modelo actualizado que ahora retorna el ID
        $userId = $userModel->create(
            $loginFinal,
            $password, // El modelo se encarga del hash
            $solicitud['email'],
            $idRole,
            (int)$partnerId
        );

        if (!$userId || $userId === false) {
            throw new \Exception('No se pudo crear la cuenta de usuario.');
        }

        // 5. Actualizar la solicitud online con los IDs creados
        $updated = $partnerOnlineModel->updateConfirmation($id, (int)$userId, (int)$partnerId);
        
        if (!$updated) {
            throw new \Exception('No se pudo actualizar la solicitud.');
        }

        // 6. Enviar correo de confirmación
        $emailSent = $this->sendApprovalEmail($solicitud['email'], [
            'name' => $solicitud['name'],
            'login' => $loginFinal,
            'password' => $password
        ]);

        // Confirmar transacción
        $db->commit();

        $successMessage = "Solicitud aprobada exitosamente. Usuario: $loginFinal, Contraseña temporal: {$password}";
        if (!$emailSent) {
            $successMessage .= " (Nota: No se pudo enviar el correo de confirmación)";
        }

        $_SESSION['success'] = $successMessage;
        error_log("Solicitud $id aprobada - Usuario: $loginFinal, Partner: $partnerId, User: $userId");

    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        $_SESSION['error'] = 'Error al aprobar la solicitud: ' . $e->getMessage();
        error_log('approve error: ' . $e->getMessage());
        error_log('approve trace: ' . $e->getTraceAsString());
    }

    $this->redirect('partnerOnline/pending');
}

/**
 * Envía correo de confirmación de aprobación de solicitud
 */
private function sendApprovalEmail(string $email, array $data): bool
{
    try {
        error_log("=== INICIO sendApprovalEmail ===");
        error_log("Email destino: " . $email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            error_log("Email inválido: " . $email);
            return false;
        }

        // Rutas de PHPMailer
        $phpmailerPath = __DIR__ . '/../Lib/PHPMailer/PHPMailer.php';
        $exceptionPath = __DIR__ . '/../Lib/PHPMailer/Exception.php';
        $smtpPath      = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

        // Verificar que todos los archivos existan
        if (!is_file($phpmailerPath) || !is_file($exceptionPath) || !is_file($smtpPath)) {
            error_log("❌ Archivos PHPMailer no encontrados, usando mail() fallback");
            return $this->sendApprovalWithMailFunction($email, $data);
        }

        // Cargar PHPMailer
        require_once $phpmailerPath;
        require_once $exceptionPath;
        require_once $smtpPath;

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

            $mail->CharSet   = 'UTF-8';
            $mail->Timeout   = 30;
            $mail->SMTPDebug = 0;

            // Remitente y destinatario
            $mail->setFrom('abap@algoritmos.com.bo', 'Asociación de Artistas');
            $mail->addAddress($email, $data['name'] ?? '');
            $mail->addReplyTo('abap@algoritmos.com.bo', 'No Responder');

            // Contenido del correo
            $loginUrl = u('login');
            $mail->isHTML(true);
            $mail->Subject = '¡Solicitud Aprobada! - Asociación de Artistas';
            $mail->Body    = "
                <h2>¡Felicidades " . htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8') . "!</h2>
                <p>Tu solicitud de registro ha sido <strong>aprobada</strong> y ya eres socio de la Asociación de Artistas.</p>
                
                <div style=\"background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;\">
                    <h3 style=\"color: #28a745; margin-top: 0;\">Datos de acceso al sistema:</h3>
                    <p><strong>Usuario:</strong> " . htmlspecialchars($data['login'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>
                    <p><strong>Contraseña temporal:</strong> " . htmlspecialchars($data['password'] ?? '', ENT_QUOTES, 'UTF-8') . "</p>
                </div>
                
                <p><strong>IMPORTANTE:</strong> Por tu seguridad, al ingresar por primera vez al sistema deberás cambiar tu contraseña.</p>
                
                <p><a href=\"$loginUrl\" style=\"display:inline-block; padding:12px 24px; background:#28a745; color:#fff; text-decoration:none; border-radius:8px; font-weight:bold;\">Acceder al Sistema</a></p>
                
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                
                <br>
                <p>¡Bienvenido a la Asociación de Artistas!<br><b>Equipo Administrativo</b></p>
            ";
            $mail->AltBody = "¡Felicidades {$data['name']}!\n\n" .
                             "Tu solicitud de registro ha sido aprobada y ya eres socio de la Asociación de Artistas.\n\n" .
                             "DATOS DE ACCESO:\n" .
                             "Usuario: {$data['login']}\n" .
                             "Contraseña temporal: {$data['password']}\n\n" .
                             "IMPORTANTE: Al ingresar por primera vez deberás cambiar tu contraseña.\n\n" .
                             "Puedes acceder al sistema en: $loginUrl\n\n" .
                             "¡Bienvenido a la Asociación de Artistas!\n" .
                             "Equipo Administrativo";

            $result = $mail->send();
            error_log("PHPMailer resultado (aprobación): " . ($result ? "ÉXITO" : "FALLO"));

            if ($result) {
                error_log("✅ Correo de aprobación enviado exitosamente con SMTP");
                return true;
            }

            error_log("❌ Error al enviar con PHPMailer: " . $mail->ErrorInfo);
            return $this->sendApprovalWithMailFunction($email, $data);
            
        } catch (\Exception $e) {
            error_log("❌ Excepción PHPMailer (aprobación): " . $e->getMessage());
            return $this->sendApprovalWithMailFunction($email, $data);
        }
    } catch (\Throwable $e) {
        error_log("❌ Error general (aprobación): " . $e->getMessage());
        return $this->sendApprovalWithMailFunction($email, $data);
    } finally {
        error_log("=== FIN sendApprovalEmail ===");
    }
}

/**
 * Fallback method for approval email using mail() function.
 */
private function sendApprovalWithMailFunction(string $email, array $data): bool
{
    try {
        $loginUrl = u('login');
        $subject = '¡Solicitud Aprobada! - Asociación de Artistas';
        $message = "¡Felicidades {$data['name']}!\r\n\r\n" .
                   "Tu solicitud de registro ha sido aprobada y ya eres socio de la Asociación de Artistas.\r\n\r\n" .
                   "DATOS DE ACCESO:\r\n" .
                   "Usuario: {$data['login']}\r\n" .
                   "Contraseña temporal: {$data['password']}\r\n\r\n" .
                   "IMPORTANTE: Al ingresar por primera vez al sistema deberás cambiar tu contraseña.\r\n\r\n" .
                   "Puedes acceder al sistema en: $loginUrl\r\n\r\n" .
                   "¡Bienvenido a la Asociación de Artistas!\r\n" .
                   "Equipo Administrativo";

        $headers = [
            'From: Asociación de Artistas <abap@algoritmos.com.bo>',
            'Reply-To: abap@algoritmos.com.bo',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];

        $result = mail($email, $subject, $message, implode("\r\n", $headers));
        error_log("Resultado mail() fallback (aprobación): " . ($result ? "TRUE" : "FALSE"));

        return (bool)$result;
    } catch (\Throwable $e) {
        error_log("Error en fallback mail() (aprobación): " . $e->getMessage());
        return false;
    }
}


public function reject(): void
{
    $this->startSession();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('partnerOnline/pending'); return; }
    if (!isset($_POST['id'])) { $this->redirect('partnerOnline/pending'); return; }

    $id = (int)$_POST['id'];
    if ($id <= 0) { $this->redirect('partnerOnline/pending'); return; }

    require_once __DIR__ . '/../Models/PartnerOnline.php';
    $model = new \PartnerOnline();

    // Rechazar = eliminar solicitud (si prefieres histórico, luego lo cambiamos a status)
    try {
        $model->delete($id);
    } catch (\Throwable $e) {
        error_log('reject error: ' . $e->getMessage());
    }


    //mensajes para los alerts
     if ($model->delete($id)) {
        $_SESSION['success'] = "Solicitud rechazada correctamente";
    } else {
        $_SESSION['error'] = "Error al rechazar la solicitud";
    }



    $this->redirect('partnerOnline/pending');
}


public function accept(): void
{
    $this->startSession();
 
    if (!isset($_SESSION['user_id'])) { $this->redirect('login'); return; }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { $this->redirect('partnerOnline/pending'); return; }
 
    // Acepta alias por robustez
    $idPO = (int)($_POST['idPartnerOnline'] ?? $_POST['id'] ?? $_POST['idpo'] ?? 0);
    if ($idPO <= 0) {
        error_log('[accept] Falta idPartnerOnline. POST=' . json_encode($_POST, JSON_UNESCAPED_UNICODE));
        $_SESSION['error'] = 'Solicitud inválida.';
        $this->redirect('partnerOnline/pending');
        return;
    }
 
    // Usa el singleton de la BD
    $pdo = \Database::singleton()->getConnection();
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
 
    try {
        $pdo->beginTransaction();
 
        // 1) Leer y BLOQUEAR el registro (ahora incluye las URLs)
        $q = $pdo->prepare("
            SELECT idPartnerOnline, name, ci, cellPhoneNumber, address, birthday, email,
                   frontImageURL, backImageURL,
                   dateConfirmation, idUser, idPartner
            FROM partneronline
            WHERE idPartnerOnline = ?
            FOR UPDATE
        ");
        $q->execute([$idPO]);
        $po = $q->fetch(\PDO::FETCH_ASSOC);
 
        if (!$po) throw new \Exception('No existe el registro.');
        if (!empty($po['dateConfirmation']) && $po['dateConfirmation'] !== '0000-00-00' && $po['dateConfirmation'] !== '0000-00-00 00:00:00')
            throw new \Exception('La solicitud ya fue confirmada.');
        if (empty($po['ci'])) throw new \Exception('El CI está vacío.');
 
        // 2) Crear PARTNER (incluye frontImageURL y backImageURL)
        $insPartner = $pdo->prepare("
            INSERT INTO partner
              (name, ci, cellPhoneNumber, address, frontImageURL, backImageURL, birthday, dateRegistration)
            VALUES
              (?, ?, ?, ?, ?, ?, ?, CURDATE())
        ");
        $insPartner->execute([
            $po['name'],
            $po['ci'],
            $po['cellPhoneNumber'],
            $po['address'],
            $po['frontImageURL'] ?? null,
            $po['backImageURL'] ?? null,
            $po['birthday'],
        ]);
        $idPartner = (int)$pdo->lastInsertId();
 
        // 3) Crear USER: login=CI (máx 20), password=bcrypt(CI), rol=2, email del PO (evita duplicado)
        $login = substr((string)$po['ci'], 0, 20);
        $hash  = password_hash((string)$po['ci'], PASSWORD_BCRYPT);
        $idRol = 2;
 
        $loginFinal = $login; $i = 1;
        $chk = $pdo->prepare("SELECT 1 FROM user WHERE login = ? LIMIT 1");
        while (true) {
            $chk->execute([$loginFinal]);
            if (!$chk->fetchColumn()) break;
            $suf = (string)$i++;
            $loginFinal = substr($login, 0, max(1, 20 - strlen($suf))) . $suf;
        }
 
        $insUser = $pdo->prepare("
            INSERT INTO user (login, password, email, firstSession, status, idRol, idPartner)
            VALUES (?, ?, ?, 0, 1, ?, ?)
        ");
        $insUser->execute([
            $loginFinal,
            $hash,
            $po['email'] ?? null,
            $idRol,
            $idPartner
        ]);
        $idUser = (int)$pdo->lastInsertId();
 
        // 4) Confirmar y enlazar
        $upd = $pdo->prepare("
            UPDATE partneronline
            SET dateConfirmation = CURDATE(), idUser = ?, idPartner = ?
            WHERE idPartnerOnline = ?
        ");
        $upd->execute([$idUser, $idPartner, $idPO]);
 
        $pdo->commit();
        $_SESSION['success'] = 'Socio y usuario creados correctamente (con imágenes).';
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['error'] = 'Error al aceptar: ' . $e->getMessage();
    }
 
    $this->redirect('partnerOnline/pending');
}










}
?>