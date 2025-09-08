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
    $registrations = array_values(array_filter($pending, static fn(array $r): bool => empty($r['idUser'])));
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

    $this->redirect('partnerOnline/pending');
}













}
?>