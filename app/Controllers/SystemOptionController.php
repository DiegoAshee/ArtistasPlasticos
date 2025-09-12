<?php
// app/Controllers/SystemOptionController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/SystemOption.php';

class SystemOptionController extends BaseController
{
    private $systemOptionModel;
        
    public function __construct()
    {
        parent::__construct();
        $this->systemOptionModel = new SystemOption();
    }
    
    /**
     * Listar todas las opciones del sistema
     */
    public function listAll()
    {
        // Solo administradores pueden acceder
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }
        
        $options = $this->systemOptionModel->getAllOptions();
        
        $data = [
            'title' => 'Configuración del Sistema',
            'options' => $options,
            'currentPath' => 'system/options'
        ];
        
        $this->view('system/options_list', $data);
    }
    
    /**
     * Actualizar una opción del sistema
     */
    public function updateOption()
    {
        // Solo administradores pueden acceder
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        
        if (empty($key)) {
            echo json_encode(['success' => false, 'message' => 'La clave es requerida']);
            return;
        }
        
        $success = $this->systemOptionModel->updateOption($key, $value);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Opción actualizada correctamente']);
        } else {
            $error = $this->systemOptionModel->getLastError();
            echo json_encode([
                'success' => false, 
                'message' => $error['message'] ?? 'Error al actualizar la opción'
            ]);
        }
    }
    
    /**
     * Mostrar formulario de edición para opciones del sistema
     */
    public function edit()
    {
        // Solo administradores pueden acceder
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }
        
        $headerOptions = $this->systemOptionModel->getHeaderOptions();
        
        $data = [
            'title' => 'Editar Configuración del Sistema',
            'headerOptions' => $headerOptions,
            'currentPath' => 'system/options/edit'
        ];
        
        $this->view('system/options_edit', $data);
    }
    
    /**
     * Procesar la actualización de opciones del header
     */
    public function updateHeaderOptions()
    {
        // Solo administradores pueden acceder
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('system/options');
            return;
        }
        
        $logo_url = $_POST['logo_url'] ?? '';
        $site_title = $_POST['site_title'] ?? '';
        $tagline = $_POST['tagline'] ?? '';
        
        $success = true;
        $errors = [];
        
        // Actualizar cada opción
        if (!$this->systemOptionModel->updateOption('logo_url', $logo_url)) {
            $success = false;
            $errors[] = $this->systemOptionModel->getLastError();
        }
        
        if (!$this->systemOptionModel->updateOption('site_title', $site_title)) {
            $success = false;
            $errors[] = $this->systemOptionModel->getLastError();
        }
        
        if (!$this->systemOptionModel->updateOption('tagline', $tagline)) {
            $success = false;
            $errors[] = $this->systemOptionModel->getLastError();
        }
        
        if ($success) {
            $_SESSION['success_message'] = 'Configuración actualizada correctamente';
        } else {
            $_SESSION['error_message'] = 'Error al actualizar algunas configuraciones';
            $_SESSION['errors'] = $errors;
        }
        
        $this->redirect('system/options/edit');
    }
    
    /**
     * Obtener una opción específica por AJAX
     */
    public function getOption()
    {
        $key = $_GET['key'] ?? '';
        
        if (empty($key)) {
            http_response_code(400);
            echo json_encode(['error' => 'Key es requerida']);
            return;
        }
        
        $value = $this->systemOptionModel->getOptionByKey($key);
        
        if ($value !== null) {
            echo json_encode(['success' => true, 'value' => $value]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Opción no encontrada']);
        }
    }
}
?>