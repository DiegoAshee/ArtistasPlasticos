<?php
// app/Controllers/OptionController.php
declare(strict_types=1);
 
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Option.php';
 
class OptionController extends BaseController
{
    private Option $optionModel;
 
    public function __construct()
    {
        parent::__construct();
        $this->optionModel = new Option();
    }
 
    /** Listar (solo status 1 y 2) */
    public function index(): void
    {
        try {
            $options      = $this->optionModel->getAll();     // solo 1 y 2
            $activeOption = $this->optionModel->getActive();  // 1
 
            $this->view('options/index', [
                'title'        => 'Configuración del Sitio',
                'options'      => $options,
                'activeOption' => $activeOption,
                'success'      => $_SESSION['success'] ?? null,
                'error'        => $_SESSION['error'] ?? null
            ]);
 
            unset($_SESSION['success'], $_SESSION['error']);
        } catch (\Exception $e) {
            error_log('OptionController::index error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar las opciones';
            $this->redirect('dashboard');
        }
    }
 
    /** Form crear */
    public function create(): void
    {
        $this->view('options/create', [
            'title' => 'Nueva Configuración',
            'error' => $_SESSION['error'] ?? null
        ]);
        unset($_SESSION['error']);
    }
 
    /** Guardar nueva (por defecto status=2 inactiva) */
    public function store(): void
    {
        try {
            $errors = $this->validateOptionData($_POST, $_FILES);
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $this->redirect('options/create');
                return;
            }
 
            $data = [
                'title'            => trim($_POST['title']),
                'imageURL'         => 'assets/images/logo.png',
                'imageURLQR'       => 'assets/images/logo_qr.png',
                'telephoneContact' => trim($_POST['telephoneContact'] ?? ''),
                'idUser'           => $_SESSION['user_id'] ?? 1
            ];
 
            if (!empty($_FILES['logo']['name'])) {
                $up = $this->optionModel->uploadImage($_FILES['logo']);
                if (empty($up)) {
                    $_SESSION['error'] = 'Error al subir la imagen';
                    $this->redirect('options/create'); return;
                }
                $data['imageURL'] = $up;
            }
            if (!empty($_FILES['logoQR']['name'])) {
                $upQ = $this->optionModel->uploadQrImage($_FILES['logoQR']);
                if (empty($upQ)) {
                    $_SESSION['error'] = 'Error al subir la imagen QR';
                    $this->redirect('options/create'); return;
                }
                $data['imageURLQR'] = $upQ;
            }
 
            if ($this->optionModel->create($data)) {
                $_SESSION['success'] = 'Configuración creada (inactiva).';
                $this->redirect('options');
            } else {
                $err = $this->optionModel->getLastError();
                $_SESSION['error'] = 'Error al crear: ' . ($err['message'] ?? 'desconocido');
                $this->redirect('options/create');
            }
        } catch (\Exception $e) {
            error_log('OptionController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options/create');
        }
    }
 
    /** Form editar (solo si status 1 o 2) */
    public function edit(): void
    {
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) { $_SESSION['error'] = 'ID inválido'; $this->redirect('options'); return; }
 
            $option = $this->optionModel->getById($id); // solo 1 y 2
            if (!$option) { $_SESSION['error'] = 'Opción no encontrada'; $this->redirect('options'); return; }
 
            $this->view('options/edit', [
                'title'  => 'Editar Configuración',
                'option' => $option,
                'error'  => $_SESSION['error'] ?? null
            ]);
            unset($_SESSION['error']);
        } catch (\Exception $e) {
            error_log('OptionController::edit error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la opción';
            $this->redirect('options');
        }
    }
 
    /** Actualizar */
    public function update(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { $_SESSION['error'] = 'ID inválido'; $this->redirect('options'); return; }
 
            $current = $this->optionModel->getById($id); // solo 1 y 2
            if (!$current) { $_SESSION['error'] = 'Opción no encontrada'; $this->redirect('options'); return; }
 
            $errors = $this->validateOptionData($_POST, $_FILES, $id);
            if (!empty($errors)) {
                $_SESSION['error'] = implode(', ', $errors);
                $this->redirect('options/edit?id=' . $id); return;
            }
 
            $data = [
                'title'            => trim($_POST['title']),
                'imageURL'         => $current['imageURL']   ?? 'assets/images/logo.png',
                'imageURLQR'       => $current['imageURLQR'] ?? 'assets/images/logo_qr.png',
                'telephoneContact' => trim($_POST['telephoneContact'] ?? ($current['telephoneContact'] ?? '')),
                'idUser'           => $_SESSION['user_id'] ?? 1
            ];
 
            if (!empty($_FILES['logo']['name'])) {
                $up = $this->optionModel->uploadImage($_FILES['logo']);
                if (!empty($up)) {
                    $data['imageURL'] = $up;
                    if (!empty($current['imageURL']) && $current['imageURL'] !== 'assets/images/logo.png' && file_exists($current['imageURL'])) {
                        @unlink($current['imageURL']);
                    }
                }
            }
            if (!empty($_FILES['logoQR']['name'])) {
                $upQ = $this->optionModel->uploadQrImage($_FILES['logoQR']);
                if (!empty($upQ)) {
                    $data['imageURLQR'] = $upQ;
                    if (!empty($current['imageURLQR']) && $current['imageURLQR'] !== 'assets/images/logo_qr.png' && file_exists($current['imageURLQR'])) {
                        @unlink($current['imageURLQR']);
                    }
                }
            }
 
            if ($this->optionModel->update($id, $data)) {
                $_SESSION['success'] = 'Configuración actualizada.';
                $this->redirect('options');
            } else {
                $err = $this->optionModel->getLastError();
                $_SESSION['error'] = 'No se pudo actualizar: ' . ($err['message'] ?? 'desconocido');
                $this->redirect('options/edit?id=' . $id);
            }
        } catch (\Exception $e) {
            error_log('OptionController::update error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }
 
    /** Activar (1) y desactivar otras a 2 */
    public function activate(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { $_SESSION['error'] = 'ID inválido'; $this->redirect('options'); return; }
 
            $option = $this->optionModel->getById($id); // solo 1 y 2
            if (!$option) { $_SESSION['error'] = 'Opción no encontrada'; $this->redirect('options'); return; }
 
            if ($this->optionModel->activate($id)) {
                $_SESSION['success'] = 'Configuración activada.';
            } else {
                $err = $this->optionModel->getLastError();
                $_SESSION['error'] = 'No se pudo activar: ' . ($err['message'] ?? 'desconocido');
            }
            $this->redirect('options');
        } catch (\Exception $e) {
            error_log('OptionController::activate error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }
 
    /** Eliminar (soft delete ⇒ status = 0, NO se muestra en index) */
    public function delete(): void
    {
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { $_SESSION['error'] = 'ID inválido'; $this->redirect('options'); return; }
 
            $option = $this->optionModel->getById($id); // getById ya filtra IN (1,2)
            if (!$option) { $_SESSION['error'] = 'Opción no encontrada o ya eliminada'; $this->redirect('options'); return; }
 
            if ((int)$option['status'] === 1) {
                $_SESSION['error'] = 'No se puede eliminar la configuración activa.';
                $this->redirect('options'); return;
            }
 
            $ok = $this->optionModel->softDelete($id);
            if ($ok === true) {
                $_SESSION['success'] = 'Configuración eliminada (status=0).';
            } else {
                $err = $this->optionModel->getLastError();
                $_SESSION['error'] = 'No se actualizó ninguna fila. ' .
                                     (!empty($err['message']) ? $err['message'] : '');
            }
            $this->redirect('options');
        } catch (\Throwable $e) {
            error_log('OptionController::delete error: ' . $e->getMessage());
            $_SESSION['error'] = 'Error interno del servidor';
            $this->redirect('options');
        }
    }
 
    /** Validación */
    private function validateOptionData(array $data, array $files, int $id = null): array
    {
        $errors = [];
 
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            $errors[] = 'El título debe tener al menos 3 caracteres';
        }
        if (strlen(trim($data['title'] ?? '')) > 100) {
            $errors[] = 'El título no puede exceder 100 caracteres';
        }
 
        if (isset($data['telephoneContact']) && strlen(trim($data['telephoneContact'])) > 0) {
            $tel = preg_replace('/\s+/', '', $data['telephoneContact']);
            if (!preg_match('/^[0-9+\-]{6,20}$/', $tel)) {
                $errors[] = 'Teléfono de contacto inválido';
            }
        }
 
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $maxSize = 2 * 1024 * 1024;
 
        if (!empty($files['logo']['name'])) {
            if (!in_array($files['logo']['type'], $allowed)) $errors[] = 'Logo: tipo no permitido';
            if ((int)$files['logo']['size'] > $maxSize)      $errors[] = 'Logo: más de 2MB';
            if ((int)$files['logo']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Error al subir el logo';
        }
 
        if (!empty($files['logoQR']['name'])) {
            if (!in_array($files['logoQR']['type'], $allowed)) $errors[] = 'QR: tipo no permitido';
            if ((int)$files['logoQR']['size'] > $maxSize)      $errors[] = 'QR: más de 2MB';
            if ((int)$files['logoQR']['error'] !== UPLOAD_ERR_OK) $errors[] = 'Error al subir el QR';
        }
 
        return $errors;
    }
}