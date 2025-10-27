<?php
// app/Controllers/HomeController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';

class HomeController extends BaseController
{
    // ====== HOMEPAGE ======
    public function homepage(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { 
            $this->redirect('login'); 
        }
        
        // Si se está forzando el cambio de contraseña, impedir acceso a la homepage
        if (!empty($_SESSION['force_pw_change'] ?? null)) {
            $this->redirect('change-password');
            return;
        }

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);

        $menuOptions = (new \Competence())->getByRole($roleId);

        $this->view('homepage', [
            'menuOptions' => $menuOptions,  // ← nombre esperado por la vista
            'currentPath' => 'homepage',
            'roleId'      => $roleId,
        ]);
    }
}