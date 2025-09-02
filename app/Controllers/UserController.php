<?php
// app/Controllers/UserController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';

class UserController extends BaseController
{
    public function UserProfile(): void
    {
        $this->startSession();
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Competence.php';
        
        // Get user profile data
        $userModel = new \Usuario();
        $users = $userModel->getUserProfile((int)($_SESSION['role'] ?? 0), (int)($_SESSION['user_id'] ?? 0));
        
        // Get menu options for the sidebar
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
        
        $this->view('users/perfil', [
            'users' => $users,
            'menuOptions' => $menuOptions,
            'currentPath' => 'users/profile',
            'roleId' => $roleId
        ]);
    }

    public function listUsers(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { 
            $this->redirect('login'); 
        }

        // Menú dinámico desde BD (según rol)
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Usuarios administradores desde tu modelo
        require_once __DIR__ . '/../Models/Usuario.php';
        $userModel = new \Usuario();
        $users = $userModel->getUsersAdmin();

        // Render
        $this->view('users/list', [
            'users'       => $users,
            'menuOptions' => $menuOptions, // ← lo consumen los partials (sidebar)
            'roleId'      => $roleId,
            // 'currentPath' lo fija la propia vista como 'users'
        ]);
    }
}
