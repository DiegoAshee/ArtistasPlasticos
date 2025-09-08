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
    public function registerPartner(): void
    {
        $this->startSession();

        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Debug: Log the entire POST and FILES to see if images are received
        error_log("POST Data: " . print_r($_POST, true));
        error_log("FILES Data: " . print_r($_FILES, true));

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
                $this->view('partner/register', ['error' => $error]);
                return;
            }

            // Manejar subida de imágenes
            try {
                $frontImageURL = $this->handleUpload('frontImage', $ci, 'front');
                $backImageURL  = $this->handleUpload('backImage', $ci, 'back');
            } catch (\RuntimeException $e) {
                $this->view('partner/register', ['error' => $e->getMessage()]);
                return;
            }

            // Crear instancia del modelo PartnerOnline y obtener el ID del usuario
            $partnerOnlineModel = new \PartnerOnline();
            $userId = $_SESSION['user_id'];

            // Crear solicitud de cambio
            $requestId = $partnerOnlineModel->create(
                $name,
                $ci,
                $cellPhoneNumber,
                $address,
                $birthday,
                $email,
                $frontImageURL,
                $backImageURL
            );

            if ($requestId) {
                // Mensaje de éxito en la sesión
                $_SESSION['success'] = "Solicitud enviada correctamente. Espera la aprobación del administrador.";
                $this->redirect('users/profile');
            } else {
                // En caso de error, mostramos un mensaje de error en la vista
                $error = "Hubo un problema al crear la solicitud.";
                $this->view('partner/register', ['error' => $error]);
            }
        } else {
            // If GET, render the view
            $this->view('partner/register');
        }
    }

    private function handleUpload(string $inputName, string $ci, string $prefix): ?string {
        // Debug: Log file upload attempt
        error_log("Handling upload for $inputName: " . print_r($_FILES[$inputName] ?? 'No file', true));

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $fileTmp  = $_FILES[$inputName]['tmp_name'];
        $fileName = basename($_FILES[$inputName]['name']);
        $fileSize = $_FILES[$inputName]['size'];
        $fileType = mime_content_type($fileTmp);

        // Validaciones
        if ($fileSize > 2 * 1024 * 1024) { // 2 MB
            throw new \RuntimeException("El archivo {$inputName} excede los 2MB.");
        }
        if (!in_array($fileType, ['image/jpeg', 'image/png'])) {
            throw new \RuntimeException("El archivo {$inputName} debe ser JPG o PNG.");
        }

        // Asegurar directorio
        $uploadDir = __DIR__ . '/../../img/carnets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 📌 Nombre basado en el CI
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newName = "{$prefix}_{$ci}." . strtolower($ext); // Ej: front_123456.jpg
        $destPath = $uploadDir . $newName;

        // Sobreescribir si ya existe (si actualiza las fotos)
        if (!move_uploaded_file($fileTmp, $destPath)) {
            throw new \RuntimeException("No se pudo guardar el archivo {$inputName}.");
        }

        // Retorna la ruta relativa para guardar en BD
        return "img/carnets/" . $newName;
    }








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













}
?>