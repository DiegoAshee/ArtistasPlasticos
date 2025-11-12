<?php
// app/Controllers/HomeController.php
declare(strict_types=1);
 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Option.php';
 
class HomeController extends BaseController
{
    // ====== HOMEPAGE ======
    public function homepage(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }
 
        // Forzar cambio de password => sin acceso a homepage
        if (!empty($_SESSION['force_pw_change'] ?? null)) {
            $this->redirect('change-password');
            return;
        }
 
        // Menú según rol (lo tuyo)
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
 
        // === Traer opción ACTIVA (status = 1) ===
        $optModel     = new Option();
        $activeOption = $optModel->getActive(); // array|false
 
        // Variables de marca para la vista
        $site_title       = $activeOption['title']            ?? 'Asociación';
        $logo_url         = $activeOption['imageURL']         ?? 'assets/images/logo.png';
        $telephoneContact = $activeOption['telephoneContact'] ?? null;
 
        $this->view('homepage', [
            'menuOptions'       => $menuOptions,
            'currentPath'       => 'homepage',
            'roleId'            => $roleId,
 
            // Branding / contacto
            'activeOption'      => $activeOption,
            'site_title'        => $site_title,
            'logo_url'          => $logo_url,
            'telephoneContact'  => $telephoneContact,
        ]);
    }
}