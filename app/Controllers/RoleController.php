<?php
// app/Controllers/RoleController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Role.php';

class RoleController extends BaseController
{
    public function list(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Role.php';
        $roleModel = new Role();
        $roles = $roleModel->getAll();

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = \Database::singleton()->getConnection();
            $db->beginTransaction();

            try {
                if (isset($_POST['action']) && $_POST['action'] === 'create') {
                    $role = trim((string)($_POST['role'] ?? ''));
                    if (empty($role)) {
                        throw new \Exception("El rol no puede estar vacío");
                    }
                    $roleId = $roleModel->create($role);
                    if (!$roleId) {
                        throw new \Exception("Fallo al crear el rol");
                    }
                } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                    $id = (int)($_POST['id'] ?? 0);
                    $role = trim((string)($_POST['role'] ?? ''));
                    if (empty($role)) {
                        throw new \Exception("El rol no puede estar vacío");
                    }
                    if (!$roleModel->update($id, $role)) {
                        throw new \Exception("Fallo al actualizar el rol");
                    }
                } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    $role = $roleModel->findById($id);
                    if (!$role || !$roleModel->delete($id)) {
                        throw new \Exception("Rol no encontrado o no se pudo eliminar");
                    }
                }
                $db->commit();
                $this->redirect('role/list');
                return;
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Transaction failed: " . $e->getMessage());
                $error = "Error: " . $e->getMessage();
            }
        }

        $this->view('role/list', [
            'roles' => $roles, // Changed to lowercase for consistency
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'error' => $error,
        ]);
    }
}