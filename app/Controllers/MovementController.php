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

        // Obtener todos los movimientos con la información relacionada
        $movements = $this->movementModel->getAllMovements();

        // Obtener datos para la vista
        $concepts = $this->conceptModel->getAll();
        $users = $this->userModel->getAll();
        $paymentTypes = $this->paymentTypeModel->getAll();

        // Variables de filtros (compatibilidad con la vista)
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $conceptId = $_GET['concept_id'] ?? null;
        $userId = $_GET['user_id'] ?? null;

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
     * Show create form (disabled)
     */
    public function create(): void
    {
        // Crear movimiento deshabilitado: redirigir al listado
        $this->redirect('movement/list');
    }

    /**
     * Store new movement (disabled)
     */
    public function store(): void
    {
        // Crear movimiento deshabilitado: redirigir al listado
        $this->redirect('movement/list');
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
        $this->view('movement/edit', $viewData);
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

        $this->redirect("movement/edit/{$id}?success=" . urlencode('Movimiento actualizado correctamente'));
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

        // Get menu options for the sidebar
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Mock movement data (replace with real database query)
        $movement = [
            'idMovement' => $id,
            'description' => 'Pago mensualidad enero',
            'amount' => '150.00',
            'dateCreation' => '2024-01-15 10:30:00',
            'idPaymentType' => 1,
            'idConcept' => 1,
            'idUser' => 1,
            'payment_type_description' => 'Efectivo',
            'concept_description' => 'Mensualidad',
            'user_login' => 'admin',
            'user_email' => 'admin@test.com'
        ];

        // Prepare view data
        $viewData = [
            'movement' => $movement,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/delete',
            'roleId' => $roleId
        ];

        // Add error message if exists
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }

        $this->view('movement/delete', $viewData);
    }

    /**
     * Actually delete movement (mock)
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

        // Mock deletion - in real app would delete from database
        // Here you would delete from database
        
        // Redirect to list with success message
        $this->redirect('movement/list');
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

        // Fetch real data for PDF export (same as list view)
        $movements = $this->movementModel->getAllMovements();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $movements
        ]);
    }
}