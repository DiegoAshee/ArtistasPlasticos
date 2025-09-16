<?php
// app/Controllers/ConceptController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

class ConceptController extends BaseController
{
    /**
     * Show concepts list
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

        // Get filters from query parameters
        $filters = [
            'q' => $_GET['q'] ?? '',
            'type' => $_GET['type'] ?? '',
            'from' => $_GET['from'] ?? '',
            'to' => $_GET['to'] ?? ''
        ];

        // Pagination parameters
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(10, (int)($_GET['pageSize'] ?? 20));

        // Mock data for concepts (replace with real database queries)
        $concepts = [
            [
                'idConcept' => 1,
                'description' => 'Mensualidad',
                'type' => 'Ingreso',
                'dateCreation' => '2024-01-01 10:30:00'
            ],
            [
                'idConcept' => 2,
                'description' => 'Materiales',
                'type' => 'Egreso',
                'dateCreation' => '2024-01-02 15:20:00'
            ],
            [
                'idConcept' => 3,
                'description' => 'Eventos',
                'type' => 'Ingreso',
                'dateCreation' => '2024-01-03 09:15:00'
            ],
            [
                'idConcept' => 4,
                'description' => 'Donaciones',
                'type' => 'Ingreso',
                'dateCreation' => '2024-01-04 14:45:00'
            ]
        ];

        // Apply filters (mock implementation)
        $filteredConcepts = array_filter($concepts, function($concept) use ($filters) {
            // Filter by search term
            if (!empty($filters['q']) && 
                stripos($concept['description'], $filters['q']) === false) {
                return false;
            }
            
            // Filter by type
            if (!empty($filters['type']) && $concept['type'] !== $filters['type']) {
                return false;
            }
            
            // Filter by date range
            if (!empty($filters['from'])) {
                $fromDate = strtotime($filters['from']);
                $conceptDate = strtotime($concept['dateCreation']);
                if ($conceptDate < $fromDate) return false;
            }
            
            if (!empty($filters['to'])) {
                $toDate = strtotime($filters['to'] . ' 23:59:59');
                $conceptDate = strtotime($concept['dateCreation']);
                if ($conceptDate > $toDate) return false;
            }
            
            return true;
        });

        // Pagination logic
        $total = count($filteredConcepts);
        $totalPages = ceil($total / $pageSize);
        $offset = ($page - 1) * $pageSize;
        $paginatedConcepts = array_slice($filteredConcepts, $offset, $pageSize);

        // Prepare view data
        $viewData = [
            'rows' => $paginatedConcepts,
            'filters' => $filters,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'totalPages' => $totalPages,
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/list',
            'roleId' => $roleId
        ];

        $this->view('conceptos/list', $viewData);
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

        // Prepare view data
        $viewData = [
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/create',
            'roleId' => $roleId
        ];

        // Add error message if exists
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }

        $this->view('conceptos/create', $viewData);
    }

    /**
     * Store new concept
     */
    public function store(): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('conceptos/create');
            return;
        }

        // Validation
        $error = null;

        // Basic validation
        if (empty($_POST['description'])) {
            $error = "La descripción es requerida";
        } elseif (empty($_POST['type']) || !in_array($_POST['type'], ['Ingreso', 'Egreso'])) {
            $error = "Debe seleccionar un tipo válido (Ingreso/Egreso)";
        }

        if ($error) {
            $this->redirect('conceptos/create?error=' . urlencode($error));
            return;
        }

        // In a real application, you would save to database here
        // Example:
        // $concept = new Concept();
        // $concept->description = $_POST['description'];
        // $concept->type = $_POST['type'];
        // $concept->dateCreation = date('Y-m-d H:i:s');
        // $concept->save();

        // Redirect to list with success message
        $this->redirect('conceptos/list');
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

        // Mock concept data (replace with real database query)
        $concept = [
            'idConcept' => $id,
            'description' => 'Mensualidad',
            'type' => 'Ingreso',
            'dateCreation' => '2024-01-01 10:30:00'
        ];

        // Prepare view data
        $viewData = [
            'concept' => $concept,
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/edit',
            'roleId' => $roleId
        ];

        // Add messages if exist
        if (isset($_GET['error'])) {
            $viewData['error'] = $_GET['error'];
        }
        if (isset($_GET['success'])) {
            $viewData['success'] = $_GET['success'];
        }

        $this->view('conceptos/edit', $viewData);
    }

    /**
     * Update concept
     */
    public function update(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("conceptos/edit/{$id}");
            return;
        }

        // Validation
        $error = null;

        // Basic validation
        if (empty($_POST['description'])) {
            $error = "La descripción es requerida";
        } elseif (empty($_POST['type']) || !in_array($_POST['type'], ['Ingreso', 'Egreso'])) {
            $error = "Debe seleccionar un tipo válido (Ingreso/Egreso)";
        }

        if ($error) {
            $this->redirect("conceptos/edit/{$id}?error=" . urlencode($error));
            return;
        }

        // In a real application, you would update the database here
        // Example:
        // $concept = Concept::find($id);
        // $concept->description = $_POST['description'];
        // $concept->type = $_POST['type'];
        // $concept->save();

        // Redirect with success message
        $this->redirect("conceptos/edit/{$id}?success=" . urlencode("Concepto actualizado correctamente"));
    }

    /**
     * Delete concept
     */
    public function delete(int $id): void
    {
        $this->startSession();

        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Check if return URL is provided
        $returnUrl = $_GET['return_url'] ?? 'conceptos/list';

        // In a real application, you would delete from database here
        // Example:
        // $concept = Concept::find($id);
        // if ($concept) {
        //     $concept->delete();
        // }

        // Redirect back to the list or specified return URL
        $this->redirect($returnUrl);
    }

    /**
     * Export concepts to PDF (mock)
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
        $concepts = [
            [
                'idConcept' => 1,
                'description' => 'Mensualidad',
                'type' => 'Ingreso',
                'dateCreation' => '2024-01-01 10:30:00'
            ],
            [
                'idConcept' => 2,
                'description' => 'Materiales',
                'type' => 'Egreso',
                'dateCreation' => '2024-01-02 15:20:00'
            ]
        ];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $concepts
        ]);
    }
}