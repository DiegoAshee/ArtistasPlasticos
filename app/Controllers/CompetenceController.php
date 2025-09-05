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
                    'url' => 'competence/list', // Esto debería ser una ruta relativa
                    'icon' => 'fas fa-list',
                    'section' => 'Administración'
                ];
            }
            
            $data = [
                'title' => 'Lista de Competencias',
                'competences' => $competences,
                'currentPath' => 'competence/list',
                'menuOptions' => $menuOptions
            ];
            
            $this->view('competence/competence_list', $data);
        } catch (Exception $e) {
            error_log('Error in CompetenceController::listAll(): ' . $e->getMessage());
            $_SESSION['error'] = 'Error al cargar la lista de competencias';
            $this->redirect('dashboard');
        }
    }

    /**
     * Crear una nueva competencia (Nuevo item en el menú)
     */
    public function create(string $name): bool
    {
        if (empty($name)) {
            return false;
        }

        $db = Database::singleton()->getConnection();

        try {
            $sql = "INSERT INTO `competence` (`menuOption`) VALUES (:name)";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('CompetenceController::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una competencia (item del menú)
     */
    public function update(int $idCompetence, string $name): bool
    {
        if (empty($name) || $idCompetence <= 0) {
            return false;
        }

        $db = Database::singleton()->getConnection();

        try {
            $sql = "UPDATE `competence` SET `menuOption` = :name WHERE `idCompetence` = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
            $stmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('CompetenceController::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una competencia (item del menú)
     */
    public function delete(int $idCompetence): bool
    {
        if ($idCompetence <= 0) {
            return false;
        }

        $db = Database::singleton()->getConnection();

        try {
            $sql = "DELETE FROM `competence` WHERE `idCompetence` = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':id', $idCompetence, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log('CompetenceController::delete error: ' . $e->getMessage());
            return false;
        }
    }
}
