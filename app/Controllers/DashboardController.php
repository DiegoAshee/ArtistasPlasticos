<?php
// app/Controllers/DashboardController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';

class DashboardController extends BaseController
{
    // ====== DASHBOARD ======
    public function dashboard(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { $this->redirect('login'); }

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);

        $menuOptions = (new \Competence())->getByRole($roleId);

        $this->view('dashboard', [
            'menuOptions' => $menuOptions,  // ← nombre esperado por la vista
            'currentPath' => 'dashboard',
            'roleId'      => $roleId,
        ]);
    }
}
