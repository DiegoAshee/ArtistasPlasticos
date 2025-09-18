<?php
// app/Controllers/ConceptController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Concept.php';
require_once __DIR__ . '/../Models/Competence.php';

class ConceptController extends BaseController
{
    public function list(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        $filters = [
            'q'    => $_GET['q'] ?? '',
            'type' => $_GET['type'] ?? '',
            'from' => $_GET['from'] ?? '',
            'to'   => $_GET['to'] ?? ''
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(10, (int)($_GET['pageSize'] ?? 20));

        $conceptModel = new \Concept();
        $data = $conceptModel->listAll($filters, $page, $pageSize);

        $viewData = array_merge($data, [
            'filters'     => $filters,
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/list',
            'roleId'      => $roleId
        ]);

        $this->view('conceptos/list', $viewData);
    }

    public function create(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        $viewData = [
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/create',
            'roleId'      => $roleId,
            'error'       => $_GET['error'] ?? null
        ];

        $this->view('conceptos/create', $viewData);
    }

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

        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? '';

        if ($description === '' || !in_array($type, ['Ingreso','Egreso'])) {
            $this->redirect('conceptos/create?error=' . urlencode("Datos inválidos"));
            return;
        }

        $model = new \Concept();
        $id = $model->create($description, $type);

        if ($id === false) {
            $err = $model->getLastError()['message'] ?? "Error al guardar";
            $this->redirect('conceptos/create?error=' . urlencode($err));
            return;
        }

        $this->redirect('conceptos/list');
    }

    public function edit(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        $concept = (new \Concept())->find($id);
        if (!$concept) {
            $this->redirect('conceptos/list?error=Concepto no encontrado');
            return;
        }

        $viewData = [
            'concept'     => $concept,
            'menuOptions' => $menuOptions,
            'currentPath' => 'conceptos/edit',
            'roleId'      => $roleId,
            'error'       => $_GET['error'] ?? null,
            'success'     => $_GET['success'] ?? null
        ];

        $this->view('conceptos/edit', $viewData);
    }

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

        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? '';

        if ($description === '' || !in_array($type, ['Ingreso','Egreso'])) {
            $this->redirect("conceptos/edit/{$id}?error=" . urlencode("Datos inválidos"));
            return;
        }

        $model = new \Concept();
        if (!$model->update($id, $description, $type)) {
            $err = $model->getLastError()['message'] ?? "Error al actualizar";
            $this->redirect("conceptos/edit/{$id}?error=" . urlencode($err));
            return;
        }
        $this->redirect("conceptos/list?success=" . urlencode("Concepto actualizado correctamente"));

        //$this->redirect("conceptos/edit/{$id}?success=" . urlencode("Concepto actualizado correctamente"));
    }

    public function delete(int $id): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $model = new \Concept();
    $model->delete($id);

    // Redirige SIEMPRE a la lista de conceptos
    $this->redirect('conceptos/list');
}

}
