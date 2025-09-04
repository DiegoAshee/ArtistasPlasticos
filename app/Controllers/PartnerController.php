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
            
            if (!method_exists($partnerModel, 'getAllSocios')) {
                throw new Exception('El método getAllSocios no existe en el modelo Partner');
            }
            
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
}
