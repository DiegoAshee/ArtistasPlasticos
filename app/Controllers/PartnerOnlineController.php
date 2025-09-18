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







//Aceptar o rechazar solicitudes
public function approve(): void
{
    $this->startSession();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('partnerOnline/pending'); return; }
    if (!isset($_POST['id'])) { $this->redirect('partnerOnline/pending'); return; }

    $id = (int)$_POST['id'];
    if ($id <= 0) { $this->redirect('partnerOnline/pending'); return; }

    // Actualiza dateConfirmation = NOW() (aprobado)
    require_once __DIR__ . '/../Models/PartnerOnline.php';
    require_once __DIR__ . '/../Config/database.php';

    try {
        $db = \Database::singleton()->getConnection();
        $stmt = $db->prepare("UPDATE `partneronline` SET dateConfirmation = NOW() WHERE idPartnerOnline = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
    } catch (\Throwable $e) {
        error_log('approve error: ' . $e->getMessage());
        // puedes setear un flash si usas sesiones de mensajes
    }

    //mensajes para los alerts
     if ($stmt->execute()) {
        $_SESSION['success'] = "Solicitud aprobada correctamente";
    } else {
        $_SESSION['error'] = "Error al aprobar la solicitud";
    }



    $this->redirect('partnerOnline/pending');
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