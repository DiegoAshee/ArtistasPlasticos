<?php
// app/Controllers/OptionController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Option.php';

class OptionController extends BaseController
{
    private $optionModel;

    public function __construct()
    {
        parent::__construct();
        $this->optionModel = new Option();
    }

    /**
     * Listar todas las opciones
     */
    public function index(): void
    {
        try {
            $options = $this->optionModel->getAll();
            $activeOption = $this->optionModel->getActive();
            
            $this->view('admin/options/index', [
                'title' => 'Configuración del Sitio',
                'options' => $options,
                'activeOption' => $activeOption,
                'success' => $_SESSION['success'] ?? null,
                'error' => $_SESSION['error'] ?? null
            ]);
            
            // Limpiar mensajes de sesión
            unset($_SESSION['success'], $_SESSION['error']);
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::index error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar las opciones';
            $this->redirect('dashboard');
        }
    }

    /**
     * Mostrar formulario para crear nueva opción
     */
    public function create(): void
    {
        $this->view('admin/options/create', [
            'title' => 'Nueva Configuración',
            'error' => $_SESSION['error'] ?? null
        ]);
        
        unset($_SESSION['error']);
    }

    /**
     * Procesar creación de nueva opción
     */
    public function store(): void
    {
        try {
            // Validar datos
            $errors = $this->validateOptionData($_POST, $_FILES);
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $this->redirect('options/create');
                return;
            }
            
            $data = [
                'title' => trim($_POST['title']),
                'imageURL' => '',
                'idUser' => $_SESSION['user_id'] ?? 1
            ];
            
            // Subir imagen si se proporcionó
            if (!empty($_FILES['logo']['name'])) {
                $uploadedPath = $this->optionModel->uploadImage($_FILES['logo']);
                if (empty($uploadedPath)) {
                    $_SESSION['error'] = 'Error al subir la imagen';
                    $this->redirect('options/create');
                    return;
                }
                $data['imageURL'] = $uploadedPath;
            } else {
                $data['imageURL'] = 'assets/images/logo.png'; // Logo por defecto
            }
            
            if ($this->optionModel->create($data)) {
                $_SESSION['success'] = 'Configuración creada correctamente';
                $this->redirect('options');
            } else {
                $_SESSION['error'] = 'Error al crear la configuración';
                $this->redirect('options/create');
            }
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options/create');
        }
    }

    /**
     * Mostrar formulario para editar opción
     */
    public function edit(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de opción inválido';
                $this->redirect('options');
                return;
            }
            
            $option = $this->optionModel->getById($id);
            
            if (!$option) {
                $_SESSION['error'] = 'Opción no encontrada';
                $this->redirect('options');
                return;
            }
            
            $this->view('admin/options/edit', [
                'title' => 'Editar Configuración',
                'option' => $option,
                'error' => $_SESSION['error'] ?? null
            ]);
            
            unset($_SESSION['error']);
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::edit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la opción';
            $this->redirect('options');
        }
    }

    /**
     * Procesar actualización de opción
     */
    public function update(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de opción inválido';
                $this->redirect('options');
                return;
            }
            
            // Obtener opción actual
            $currentOption = $this->optionModel->getById($id);
            if (!$currentOption) {
                $_SESSION['error'] = 'Opción no encontrada';
                $this->redirect('options');
                return;
            }
            
            // Validar datos
            $errors = $this->validateOptionData($_POST, $_FILES, $id);
            
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $this->redirect('options/edit?id=' . $id);
                return;
            }
            
            $data = [
                'title' => trim($_POST['title']),
                'imageURL' => $currentOption['imageURL'], // Mantener imagen actual por defecto
                'idUser' => $_SESSION['user_id'] ?? 1
            ];
            
            // Actualizar imagen si se subió una nueva
            if (!empty($_FILES['logo']['name'])) {
                $uploadedPath = $this->optionModel->uploadImage($_FILES['logo']);
                if (!empty($uploadedPath)) {
                    $data['imageURL'] = $uploadedPath;
                    // Opcional: eliminar imagen anterior
                    if ($currentOption['imageURL'] && file_exists($currentOption['imageURL'])) {
                        unlink($currentOption['imageURL']);
                    }
                }
            }
            
            if ($this->optionModel->update($id, $data)) {
                $_SESSION['success'] = 'Configuración actualizada correctamente';
                $this->redirect('options');
            } else {
                $_SESSION['error'] = 'Error al actualizar la configuración';
                $this->redirect('options/edit?id=' . $id);
            }
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }

    /**
     * Activar una opción específica
     */
    public function activate(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de opción inválido';
                $this->redirect('options');
                return;
            }
            
            if ($this->optionModel->activate($id)) {
                $_SESSION['success'] = 'Configuración activada correctamente';
            } else {
                $_SESSION['error'] = 'Error al activar la configuración';
            }
            
            $this->redirect('options');
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::activate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }

    /**
     * Eliminar opción (soft delete)
     */
    public function delete(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['error'] = 'ID de opción inválido';
                $this->redirect('options');
                return;
            }
            
            if ($this->optionModel->delete($id)) {
                $_SESSION['success'] = 'Configuración eliminada correctamente';
            } else {
                $_SESSION['error'] = 'Error al eliminar la configuración';
            }
            
            $this->redirect('options');
            
        } catch (Exception $e) {
            $this->addDebug('OptionController::delete error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }

    /**
     * Validar datos de opción
     */
    private function validateOptionData(array $data, array $files, int $id = null): array
    {
        $errors = [];
        
        // Validar título
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            $errors[] = 'El título debe tener al menos 3 caracteres';
        }
        
        if (strlen(trim($data['title'])) > 100) {
            $errors[] = 'El título no puede exceder 100 caracteres';
        }
        
        // Validar imagen si se subió
        if (!empty($files['logo']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($files['logo']['type'], $allowedTypes)) {
                $errors[] = 'Tipo de imagen no permitido (solo JPG, PNG, GIF, WEBP)';
            }
            
            if ($files['logo']['size'] > $maxSize) {
                $errors[] = 'La imagen no puede superar 2MB';
            }
            
            if ($files['logo']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'Error al subir la imagen';
            }
        }
        
        return $errors;
    }
}
?>