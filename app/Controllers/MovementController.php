<?php
// app/Controllers/MovementController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

class MovementController extends BaseController
{
    private $movementModel;
    private $conceptModel;
    private $paymentTypeModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../Models/Movement.php';
        require_once __DIR__ . '/../Models/Concept.php';
        require_once __DIR__ . '/../Models/PaymentType.php';
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Competence.php';
        
        $this->movementModel = new \Movement();
        $this->conceptModel = new \Concept();
        $this->paymentTypeModel = new \PaymentType();
        $this->userModel = new \Usuario();
    }

    /**
     * List all movements
     */
    public function list(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        $roleId = (int)($_SESSION['role'] ?? 2);
        $competenceModel = new \Competence();
        $menuOptions = $competenceModel->getByRole($roleId);

        // Variables de filtros con valores por defecto (primer día del mes hasta hoy)
        $startDate = !empty($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-01');
        $endDate = !empty($_GET['end_date']) ? date('Y-m-d 23:59:59', strtotime($_GET['end_date'])) : date('Y-m-d 23:59:59');
        $conceptId = $_GET['concept_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;

        // Obtener movimientos con filtros aplicados
        $movements = $this->movementModel->getAllMovements([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'concept_id' => $conceptId,
            'user_id' => $userId
        ]);
        
        // Obtener datos para la vista
        $concepts = $this->conceptModel->getAll();
        $users = $this->userModel->getAll();
        $paymentTypes = $this->paymentTypeModel->getAll();

        // Calculate totals
        $totalAmount = array_reduce($movements, function($sum, $movement) {
            return $sum + $movement['amount'];
        }, 0);

        $today = date('Y-m-d');
        $todayMovements = array_filter($movements, function($movement) use ($today) {
            return substr($movement['dateMovement'], 0, 10) === $today;
        });

        $todayTotal = array_reduce($todayMovements, function($sum, $movement) {
            return $sum + $movement['amount'];
        }, 0);

        // Prepare view data
        $viewData = [
            'movements' => $movements,
            'concepts' => $concepts,
            'paymentTypes' => $paymentTypes,
            'users' => $users,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'concept_id' => $conceptId,
                'user_id' => $userId
            ],
            'totalAmount' => $totalAmount,
            'totalMovements' => count($movements),
            'todayTotal' => $todayTotal,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/list',
            'roleId' => $roleId
        ];

        $this->view('movement/list', $viewData);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        // Sidebar menu options
        $roleId = (int)($_SESSION['role'] ?? 2);
        $competenceModel = new \Competence();
        $menuOptions = $competenceModel->getByRole($roleId);

        // Obtener datos para el formulario
        $paymentTypes = $this->paymentTypeModel->getAll();
        $concepts = $this->conceptModel->getAll();
        $users = $this->userModel->getAll();
        
        // Depuración - Ver los tipos de pago obtenidos
        error_log('Payment Types: ' . print_r($paymentTypes, true));
        
        // Depuración temporal - Verificar la consulta SQL
        error_log('SQL Query: SELECT idPaymentType, description FROM paymenttype ORDER BY description ASC');
        
        // Depuración temporal - Verificar la conexión a la base de datos
        $db = Database::singleton()->getConnection();
        $testQuery = $db->query("SELECT idPaymentType, description FROM paymenttype");
        $testResult = $testQuery->fetchAll(PDO::FETCH_ASSOC);
        error_log('Test Query Result: ' . print_r($testResult, true));

        $viewData = [
            'paymentTypes' => $paymentTypes,
            'concepts' => $concepts,
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/create',
            'roleId' => $roleId
        ];

        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }
        
        $this->view('movement/create_new', $viewData);
    }

    /**
     * Store new movement
     */
    public function store(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('movement/create');
            return;
        }

        $error = null;

        $description = trim($_POST['description'] ?? '');
        $amount = $_POST['amount'] ?? '';
        $dateCreation = $_POST['dateCreation'] ?? date('Y-m-d H:i:s');
        $idPaymentType = $_POST['idPaymentType'] ?? '';
        $idConcept = $_POST['idConcept'] ?? '';
        $idUser = $_POST['idUser'] ?? $_SESSION['user_id'];

        if ($description === '') {
            $error = 'La descripción es requerida';
        } elseif ($amount === '' || !is_numeric($amount)) {
            $error = 'El monto debe ser un número válido';
        } elseif ($idPaymentType === '') {
            $error = 'El tipo de pago es requerido';
        } elseif ($idConcept === '') {
            $error = 'El concepto es requerido';
        }

        if ($error) {
            $this->redirect('movement/create?error=' . urlencode($error));
            return;
        }

        // Preparar datos para insertar
        $movementData = [
            'description' => $description,
            'amount' => $amount,
            'dateCreation' => $dateCreation,
            'idPaymentType' => $idPaymentType,
            'idConcept' => $idConcept,
            'idUser' => $idUser
        ];

        try {
            // Insertar en la base de datos utilizando el método del modelo
            $success = $this->movementModel->create($movementData);

            if ($success) {
                $this->redirect('movement/list?success=' . urlencode('Movimiento creado correctamente'));
                return;
            } else {
                $this->redirect('movement/create?error=' . urlencode('Error al crear el movimiento'));
                return;
            }
        } catch (PDOException $e) {
            error_log("Error al crear movimiento: " . $e->getMessage());
            $this->redirect('movement/create?error=' . urlencode('Error en el servidor al crear el movimiento'));
            return;
        }
    }

    /**
     * Show edit form
     */
    public function edit(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        // Sidebar menu options
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Fetch real data from DB
        $movement = $this->movementModel->getById($id);
        if (!$movement) {
            $this->redirect('movement/list');
            return;
        }

        $paymentTypes = $this->paymentTypeModel->getAll();
        $concepts = $this->conceptModel->getAll();
        $users = $this->userModel->getAll();

        $viewData = [
            'movement' => $movement,
            'paymentTypes' => $paymentTypes,
            'concepts' => $concepts,
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/edit',
            'roleId' => $roleId
        ];

        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }
        if (isset($_GET['success'])) {
            $viewData['success'] = $_GET['success'];
        }
        $this->view('movement/edit_new', $viewData);
    }

    /**
     * Update movement (persist to DB)
     */
    public function update(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("movement/edit/{$id}");
            return;
        }

        $error = null;

        $description = trim($_POST['description'] ?? '');
        $amount = $_POST['amount'] ?? '';
        $dateCreation = $_POST['dateCreation'] ?? '';
        $idPaymentType = $_POST['idPaymentType'] ?? '';
        $idConcept = $_POST['idConcept'] ?? '';
        $idUser = $_POST['idUser'] ?? '';

        if ($description === '') {
            $error = 'La descripción es requerida';
        } elseif ($amount === '' || !is_numeric($amount)) {
            $error = 'El monto debe ser un número válido';
        } elseif ($dateCreation === '') {
            $error = 'La fecha de creación es requerida';
        } elseif ($idPaymentType === '') {
            $error = 'Debe seleccionar un tipo de pago';
        } elseif ($idConcept === '') {
            $error = 'Debe seleccionar un concepto';
        } elseif ($idUser === '') {
            $error = 'Debe seleccionar un usuario';
        }

        if ($error) {
            $this->redirect("movement/edit/{$id}?error=" . urlencode($error));
            return;
        }

        // Convert datetime-local to MySQL DATETIME
        $formattedDate = date('Y-m-d H:i:00', strtotime($dateCreation));

        $data = [
            'description' => $description,
            'amount' => (float)$amount,
            'dateCreation' => $formattedDate,
            'idPaymentType' => (int)$idPaymentType,
            'idConcept' => (int)$idConcept,
            'idUser' => (int)$idUser,
        ];

        $ok = $this->movementModel->update($id, $data);
        if (!$ok) {
            $this->redirect("movement/edit/{$id}?error=" . urlencode('No se pudo actualizar el movimiento'));
            return;
        }

        $this->redirect("movement/list?success=" . urlencode('Movimiento actualizado correctamente'));
    }

    /**
     * Show delete confirmation
     */
    public function delete(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        // Obtener opciones del menú para la barra lateral
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Obtener los datos reales del movimiento desde la base de datos
        $movement = $this->movementModel->getById($id);
        
        if (!$movement) {
            $this->redirect('movement/list?error=' . urlencode('El movimiento no existe o ya ha sido eliminado'));
            return;
        }

        // Obtener datos relacionados (tipo de pago, concepto, usuario)
        $paymentType = $this->paymentTypeModel->getById($movement['idPaymentType']);
        $concept = $this->conceptModel->find($movement['idConcept']);
        // Usar el modelo User o Users según corresponda
        $user = [];
        if (method_exists($this->userModel, 'find')) {
            $user = $this->userModel->find($movement['idUser']);
        } elseif (method_exists($this->userModel, 'getById')) {
            $user = $this->userModel->getById($movement['idUser']);
        }

        // Preparar los datos para la vista
        $movementData = [
            'idMovement' => $movement['idMovement'],
            'description' => $movement['description'] ?? 'Sin descripción',
            'amount' => $movement['amount'] ?? '0.00',
            'dateCreation' => $movement['dateCreation'] ?? date('Y-m-d H:i:s'),
            'idPaymentType' => $movement['idPaymentType'] ?? 0,
            'idConcept' => $movement['idConcept'] ?? 0,
            'idUser' => $movement['idUser'] ?? 0,
            'payment_type_description' => $paymentType['description'] ?? 'No especificado',
            'concept_description' => $concept['description'] ?? 'No especificado',
            'user_login' => $user['login'] ?? 'Usuario desconocido',
            'user_email' => $user['email'] ?? ''
        ];

        // Preparar datos para la vista
        $viewData = [
            'movement' => $movementData,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/delete',
            'roleId' => $roleId
        ];

        // Agregar mensaje de error si existe
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }

        $this->view('movement/delete', $viewData);
    }

    /**
     * Actually delete movement
     */
    public function destroy(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("movement/delete/{$id}");
            return;
        }

        // Intentar eliminar el movimiento
        $success = $this->movementModel->delete($id);
        
        if ($success) {
            $this->redirect('movement/list?success=' . urlencode('Movimiento eliminado correctamente'));
        } else {
            $this->redirect("movement/delete/{$id}?error=" . urlencode('No se pudo eliminar el movimiento'));
        }
    }

    /**
     * Export PDF (mock)
     */
    public function exportPdf(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            return;
        }
        requireRole([1], 'login');

        // Check if it's an AJAX request
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
            return;
        }

        // Obtener los mismos filtros que en la vista de lista
        $startDate = !empty($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-01');
        $endDate = !empty($_GET['end_date']) ? date('Y-m-d 23:59:59', strtotime($_GET['end_date'])) : date('Y-m-d 23:59:59');
        $conceptId = $_GET['concept_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;

        // Obtener movimientos con los mismos filtros que la vista
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'concept_id' => $conceptId,
            'user_id' => $userId
        ];

        // Obtener los movimientos con los filtros aplicados
        $movements = $this->movementModel->getAllMovements($filters);

        // Agregar información de filtros a la respuesta
        $response = [
            'success' => true,
            'data' => $movements,
            'filters' => $filters
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}