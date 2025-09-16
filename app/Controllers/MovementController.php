<?php
// app/Controllers/MovementController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

class MovementController extends BaseController
{
    /**
     * Show movements list
     */
    public function list(): void
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

        // Mock data for movements (replace with real data later)
         $movements = [
            [
                'idMovement' => 1,
                'description' => 'Pago mensualidad enero',
                'amount' => 150.00,
                'dateCreation' => '2024-01-15 10:30:00',
                'idPaymentType' => 1,
                'idConcept' => 1,
                'idUser' => 1,
                'payment_type_description' => 'Efectivo',
                'concept_description' => 'Mensualidad',
                'user_login' => 'admin',
                'user_email' => 'admin@test.com'
            ],
            [
                'idMovement' => 2,
                'description' => 'Compra materiales arte',
                'amount' => 450.50,
                'dateCreation' => '2024-01-14 15:20:00',
                'idPaymentType' => 2,
                'idConcept' => 2,
                'idUser' => 2,
                'payment_type_description' => 'Transferencia',
                'concept_description' => 'Materiales',
                'user_login' => 'usuario1',
                'user_email' => 'usuario1@test.com'
            ]
        ];

        // Prepare view data
        $viewData = [
            'movements' => $movements,
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

        // Get menu options for the sidebar
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Mock data for dropdowns (replace with real data later)
        $paymentTypes = [
            ['idPaymentType' => 1, 'description' => 'Efectivo'],
            ['idPaymentType' => 2, 'description' => 'Transferencia Bancaria'],
            ['idPaymentType' => 3, 'description' => 'Tarjeta de Crédito'],
            ['idPaymentType' => 4, 'description' => 'QR']
        ];

        $concepts = [
            ['idConcept' => 1, 'description' => 'Mensualidad'],
            ['idConcept' => 2, 'description' => 'Materiales'],
            ['idConcept' => 3, 'description' => 'Eventos'],
            ['idConcept' => 4, 'description' => 'Donaciones']
        ];

        $users = [
            ['idUser' => 1, 'login' => 'admin', 'email' => 'admin@test.com'],
            ['idUser' => 2, 'login' => 'usuario1', 'email' => 'usuario1@test.com'],
            ['idUser' => 3, 'login' => 'usuario2', 'email' => 'usuario2@test.com']
        ];

        // Prepare view data
        $viewData = [
            'paymentTypes' => $paymentTypes,
            'concepts' => $concepts,
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/create',
            'roleId' => $roleId
        ];

        // Add error message if exists
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }

        $this->view('movement/create', $viewData);
    }

    /**
     * Store new movement (mock)
     */
    public function store(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('movement/create');
            return;
        }

        // Mock validation - in real app would save to database
        $error = null;

        // Basic validation
        if (empty($_POST['description'])) {
            $error = "La descripción es requerida";
        } elseif (empty($_POST['amount']) || !is_numeric($_POST['amount']) || (float)$_POST['amount'] <= 0) {
            $error = "El monto debe ser un número válido mayor a 0";
        } elseif (empty($_POST['dateCreation'])) {
            $error = "La fecha de creación es requerida";
        } elseif (empty($_POST['idPaymentType'])) {
            $error = "Debe seleccionar un tipo de pago";
        } elseif (empty($_POST['idConcept'])) {
            $error = "Debe seleccionar un concepto";
        } elseif (empty($_POST['idUser'])) {
            $error = "Debe seleccionar un usuario";
        }

        if ($error) {
            $this->redirect('movement/create?error=' . urlencode($error));
            return;
        }

        // Mock success - in real app would save to database
        // Here you would save to database and redirect to list
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

        // Mock data for dropdowns
        $paymentTypes = [
            ['idPaymentType' => 1, 'description' => 'Efectivo'],
            ['idPaymentType' => 2, 'description' => 'Transferencia Bancaria'],
            ['idPaymentType' => 3, 'description' => 'Tarjeta de Crédito'],
            ['idPaymentType' => 4, 'description' => 'QR']
        ];

        $concepts = [
            ['idConcept' => 1, 'description' => 'Mensualidad'],
            ['idConcept' => 2, 'description' => 'Materiales'],
            ['idConcept' => 3, 'description' => 'Eventos'],
            ['idConcept' => 4, 'description' => 'Donaciones']
        ];

        $users = [
            ['idUser' => 1, 'login' => 'admin', 'email' => 'admin@test.com'],
            ['idUser' => 2, 'login' => 'usuario1', 'email' => 'usuario1@test.com'],
            ['idUser' => 3, 'login' => 'usuario2', 'email' => 'usuario2@test.com']
        ];

        // Prepare view data
        $viewData = [
            'movement' => $movement,
            'paymentTypes' => $paymentTypes,
            'concepts' => $concepts,
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'movement/edit',
            'roleId' => $roleId
        ];

        // Add messages if exist
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }
        if (isset($_GET['success'])) {
            $viewData['success'] = $_GET['success'];
        }
        $this->view('movement/edit', $viewData);
    }

    /**
     * Update movement (mock)
     */
    public function update(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("movement/edit/{$id}");
            return;
        }

        // Mock validation
        $error = null;

        // Basic validation
        if (empty($_POST['description'])) {
            $error = "La descripción es requerida";
        } elseif (empty($_POST['amount']) || !is_numeric($_POST['amount']) || (float)$_POST['amount'] <= 0) {
            $error = "El monto debe ser un número válido mayor a 0";
        } elseif (empty($_POST['dateCreation'])) {
            $error = "La fecha de creación es requerida";
        } elseif (empty($_POST['idPaymentType'])) {
            $error = "Debe seleccionar un tipo de pago";
        } elseif (empty($_POST['idConcept'])) {
            $error = "Debe seleccionar un concepto";
        } elseif (empty($_POST['idUser'])) {
            $error = "Debe seleccionar un usuario";
        }

        if ($error) {
            $this->redirect("movement/edit/{$id}?error=" . urlencode($error));
            return;
        }

        // Mock success - in real app would update database
        $this->redirect("movement/edit/{$id}?success=" . urlencode("Movimiento actualizado correctamente"));
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

        // Check if it's an AJAX request
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Solicitud inválida']);
            return;
        }

        // Mock data for PDF export
        $movements = [
            [
                'idMovement' => 1,
                'description' => 'Pago mensualidad enero',
                'amount' => 150.00,
                'dateCreation' => '2024-01-15 10:30:00',
                'payment_type_description' => 'Efectivo',
                'concept_description' => 'Mensualidad',
                'user_login' => 'admin'
            ],
            [
                'idMovement' => 2,
                'description' => 'Compra materiales arte',
                'amount' => 450.50,
                'dateCreation' => '2024-01-14 15:20:00',
                'payment_type_description' => 'Transferencia',
                'concept_description' => 'Materiales',
                'user_login' => 'usuario1'
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $movements
        ]);
    }
}