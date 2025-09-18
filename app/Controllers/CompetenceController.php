<?php
// app/Controllers/CompetenceController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Competence.php';
require_once __DIR__ . '/../Config/database.php';

class CompetenceController extends BaseController
{
    private $competenceModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->competenceModel = new Competence();
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * List all competences
     */
    public function listAll()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }

        try {
            $competences = $this->competenceModel->listAll();  // Obtiene las competencias a través del modelo
            
            // Get base menu options
            $menuOptions = $this->competenceModel->getByRole($_SESSION['role'] ?? 2);
            
            // Add Competencias menu item if not already present
            $hasCompetencias = false;
            foreach ($menuOptions as $item) {
                if (strtolower($item['name']) === 'competencias') {
                    $hasCompetencias = true;
                    break;
                }
            }
            
            if (!$hasCompetencias) {
                $menuOptions[] = [
                    'name' => 'Competencias',
                    'url' => 'competence/competence_list', // Ruta actualizada para coincidir con mapNameToRouteAndIcon
                    'icon' => 'fas fa-list',
                    'section' => 'Administración'
                ];
            }
            
            $data = [
                'title' => 'Lista de Competencias',
                'competences' => $competences,
                'currentPath' => 'competence/competence_list',
                'menuOptions' => $menuOptions,
                'success' => $_SESSION['success'] ?? null
            ];
            
            // Clear the success message after displaying it
            unset($_SESSION['success']);
            
            $this->view('competence/competence_list', $data);
        } catch (Exception $e) {
            error_log('Error en CompetenceController::listAll: ' . $e->getMessage());
            $this->redirect('error/500');
        }
    }

    /**
     * Show create form and handle form submission
     */
    public function create()
    {
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            $this->redirect('dashboard');
            return;
        }

        $menuOptions = $this->competenceModel->getByRole($_SESSION['role'] ?? 2);
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $menuOption = trim($_POST['menu_option'] ?? '');
            
            // Basic validation
            $errors = [];
            if (empty($menuOption)) {
                $errors[] = 'La opción de menú es requerida';
            }
            
            if (empty($errors)) {
                try {
                    $result = $this->competenceModel->create($menuOption);
                    
                    if ($result !== false) {
                        $_SESSION['success_message'] = 'Competencia creada exitosamente';
                        $this->redirect('competence/competence_list');
                        return;
                    } else {
                        $errorInfo = $this->competenceModel->getLastError();
                        error_log('Error creating competence: ' . print_r($errorInfo, true));
                        $errors[] = 'Error al crear la competencia. ' . ($errorInfo['message'] ?? 'Por favor intente de nuevo.');
                    }
                } catch (Exception $e) {
                    error_log('Exception in CompetenceController::create: ' . $e->getMessage());
                    $errors[] = 'Error del sistema: ' . $e->getMessage();
                }
            }
            
            // If we have errors, show the form again with errors
            $data = [
                'title' => 'Crear Nueva Competencia',
                'currentPath' => 'competence/create',
                'menuOptions' => $menuOptions,
                'errors' => $errors,
                'formData' => [
                    'menu_option' => $menuOption
                ],
                'success' => $_SESSION['success'] ?? null
            ];
            
            // Clear the success message after displaying it
            unset($_SESSION['success']);
            
            $this->view('competence/create', $data);
            return;
        }
        
        // Show the form (GET request)
        $data = [
            'title' => 'Crear Nueva Competencia',
            'currentPath' => 'competence/create',
            'menuOptions' => $menuOptions,
            'formData' => [
                'name' => '',
                'menu_option' => ''
            ]
        ];
        
        $this->view('competence/create', $data);
    }

    /**
     * Actualizar una competencia (item del menú)
     * @param int $idCompetence ID de la competencia a actualizar
     * @param string|null $name (Opcional) Nuevo nombre para la competencia
     * @return bool|void Retorna true si la actualización fue exitosa, false en caso de error
     *                  Si es una petición GET, muestra el formulario de edición
     */
    public function update(int $idCompetence, string $name = null)
    {
        $db = Database::singleton()->getConnection();

        // Si es una petición GET, mostrar el formulario de edición
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            try {
                $stmt = $db->prepare("SELECT * FROM competence WHERE idCompetence = :id");
                $stmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
                $stmt->execute();
                $competence = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$competence) {
                    $_SESSION['error'] = 'La competencia solicitada no existe';
                    $this->redirect('competence/competence_list');
                    return false;
                }

                // Get menu options for the sidebar
                $menuOptions = $this->competenceModel->getByRole($_SESSION['role'] ?? 2);
                
                // Add Competencias menu item if not already present
                $hasCompetencias = false;
                foreach ($menuOptions as $item) {
                    if (strtolower($item['name']) === 'competencias') {
                        $hasCompetencias = true;
                        break;
                    }
                }
                
                if (!$hasCompetencias) {
                    $menuOptions[] = [
                        'name' => 'Competencias',
                        'url' => 'competence/competence_list',
                        'icon' => 'fas fa-list',
                        'section' => 'Menú'
                    ];
                }
                
                $data = [
                    'page_title' => 'Editar Competencia',
                    'competence' => $competence,
                    'action' => 'edit',
                    'menuOptions' => $menuOptions,
                    'currentPath' => 'competence/update/'.$idCompetence
                ];

                $this->view('competence/update', $data);
                return true;

            } catch (\PDOException $e) {
                error_log('Error al cargar la competencia para editar: ' . $e->getMessage());
                $_SESSION['error'] = 'Error al cargar la competencia para editar';
                $this->redirect('competence/competence_list');
                return false;
            }
        }
        
        // Si es una petición POST, procesar la actualización
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Obtener el nombre del formulario
                $name = $_POST['menu_option'] ?? '';

                if (empty($name) || $idCompetence <= 0) {
                    $_SESSION['error'] = 'El nombre de la competencia es requerido';
                    $this->redirect('competence/update/' . $idCompetence);
                    return false;
                }

                $sql = "UPDATE `competence` SET `menuOption` = :name WHERE `idCompetence` = :id";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
                $stmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = 'Competencia actualizada correctamente';
                    $this->redirect('competence/competence_list');
                    return true;
                } else {
                    throw new \Exception('No se pudo actualizar la competencia');
                }
                
            } catch (\PDOException $e) {
                error_log('Error al actualizar la competencia: ' . $e->getMessage());
                $_SESSION['error'] = 'Error al actualizar la competencia: ' . $e->getMessage();
                $this->redirect('competence/update/' . $idCompetence);
                return false;
            } catch (\Exception $e) {
                error_log('Error al actualizar la competencia: ' . $e->getMessage());
                $_SESSION['error'] = $e->getMessage();
                $this->redirect('competence/update/' . $idCompetence);
                return false;
            }
        }
        
        // Si no es GET ni POST, redirigir
        $this->redirect('competence/competence_list');
        return false;
    }

    /**
     * Eliminar una competencia (item del menú)
     */
    public function delete(int $idCompetence)
    {
        // Clear any previous output
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Set JSON header for AJAX response
        header('Content-Type: application/json; charset=utf-8');
        
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción',
                'debug' => 'User not authenticated or not admin'
            ], JSON_UNESCAPED_UNICODE);
            exit();
        }

        if ($idCompetence <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'ID de competencia no válido',
                'debug' => 'Invalid ID: ' . $idCompetence
            ]);
            exit();
        }

        try {
            $db = Database::singleton()->getConnection();
            
            // Begin transaction
            $db->beginTransaction();
            
            // First, check if the competence exists
            $checkSql = "SELECT idCompetence, menuOption FROM competence WHERE idCompetence = :id";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
            
            if (!$checkStmt->execute()) {
                throw new \Exception('Error al verificar la competencia');
            }
            
            $competence = $checkStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$competence) {
                $db->rollBack();
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'La competencia que intentas eliminar no existe',
                    'debug' => 'Competence not found with ID: ' . $idCompetence
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            
            // Log the deletion attempt
            error_log("Attempting to delete competence ID: " . $idCompetence . ", Menu Option: " . ($competence['menuOption'] ?? 'N/A'));
            
            // Proceed with deletion
            $deleteSql = "DELETE FROM competence WHERE idCompetence = :id";
            $deleteStmt = $db->prepare($deleteSql);
            $deleteStmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
            
            $result = $deleteStmt->execute();
            
            if (!$result) {
                $errorInfo = $deleteStmt->errorInfo();
                throw new \Exception('Database error: ' . ($errorInfo[2] ?? 'Unknown error'));
            }
            
            // Check if any row was actually deleted
            if ($deleteStmt->rowCount() === 0) {
                $db->rollBack();
                throw new \Exception('No se encontró la competencia para eliminar');
            }
            
            // Commit the transaction
            $db->commit();
            
            error_log("Successfully deleted competence ID: " . $idCompetence);
            
            echo json_encode([
                'success' => true,
                'message' => 'Competencia eliminada correctamente',
                'competenceId' => $idCompetence
            ]);
            
        } catch (\PDOException $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('CompetenceController::delete PDOException: ' . $e->getMessage());
            http_response_code(500);
            $response = [
                'success' => false,
                'message' => 'Error de base de datos al intentar eliminar la competencia',
                'debug' => $e->getMessage()
            ];
            if (ini_get('display_errors')) {
                $response['trace'] = $e->getTraceAsString();
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit();
        } catch (\Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('CompetenceController::delete Exception: ' . $e->getMessage());
            http_response_code(500);
            $response = [
                'success' => false,
                'message' => $e->getMessage(),
                'debug' => 'Failed to delete competence'
            ];
            if (ini_get('display_errors')) {
                $response['trace'] = $e->getTraceAsString();
            }
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit();
        }
        exit();
    }
}
