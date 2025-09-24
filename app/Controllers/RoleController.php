<?php
// app/Controllers/RoleController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Role.php';
require_once __DIR__ . '/../Helpers/auth.php';

class RoleController extends BaseController
{
    public function list(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        requireRole([1], 'login');
        
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Role.php';
        $roleModel = new Role();
        $roles = $roleModel->getAll();

        $error = $_SESSION['role_error'] ?? null;
        unset($_SESSION['role_error']); // Clear the error after reading it

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
                    $result = $roleModel->delete($id);
                    if ($result === false) {
                        throw new \Exception("Rol no encontrado");
                    }
                }
                $db->commit();
                $this->redirect('role/list');
                return;
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Transaction failed: " . $e->getMessage());
                $_SESSION['role_error'] = $e->getMessage(); // Store error in session
            }
        }

        $this->view('role/list', [
            'roles' => $roles,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'error' => $error,
        ]);
    }
}