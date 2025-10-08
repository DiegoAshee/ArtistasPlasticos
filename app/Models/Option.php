<?php
// app/Models/Option.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class Option
{
    private $lastError = [];
    private $db;

    public function __construct()
    {
        $this->db = Database::singleton()->getConnection();
    }

    public function getLastError(): array
    {
        return $this->lastError;
    }

    private function setError(string $message, $code = 0): void
    {
        $this->lastError = [
            'message' => $message,
            'code' => is_numeric($code) ? (int)$code : 0
        ];
    }

    /**
     * Obtener todas las opciones
     */
    public function getAll(): array
    {
        try {
            $sql = "SELECT * FROM `option` WHERE status != 0 ORDER BY idOption DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opciones: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener opción por ID
     */
    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT * FROM `option` WHERE idOption = :id AND status != 0";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener opción activa
     */
    public function getActive(): ?array
    {
        try {
            $sql = "SELECT * FROM `option` WHERE status = 1 LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opción activa: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getActive error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Crear nueva opción
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO `option` (title, imageURL, status, idUser) 
                    VALUES (:title, :imageURL, 0, :idUser)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $data['title'], \PDO::PARAM_STR);
            $stmt->bindValue(':imageURL', $data['imageURL'], \PDO::PARAM_STR);
            $stmt->bindValue(':idUser', $data['idUser'], \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->setError('Error al crear opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar opción
     */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE `option` SET title = :title, imageURL = :imageURL, idUser = :idUser 
                    WHERE idOption = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $data['title'], \PDO::PARAM_STR);
            $stmt->bindValue(':imageURL', $data['imageURL'], \PDO::PARAM_STR);
            $stmt->bindValue(':idUser', $data['idUser'], \PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->setError('Error al actualizar opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar una opción (desactiva las demás)
     */
    public function activate(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            
            // Desactivar todas las opciones
            $sqlDeactivate = "UPDATE `option` SET status = 0 WHERE status = 1";
            $stmtDeactivate = $this->db->prepare($sqlDeactivate);
            $stmtDeactivate->execute();
            
            // Activar la opción específica
            $sqlActivate = "UPDATE `option` SET status = 1 WHERE idOption = :id";
            $stmtActivate = $this->db->prepare($sqlActivate);
            $stmtActivate->bindValue(':id', $id, \PDO::PARAM_INT);
            $result = $stmtActivate->execute();
            
            $this->db->commit();
            return $result;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->setError('Error al activar opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::activate error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar opción (soft delete)
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "UPDATE `option` SET status = 0 WHERE idOption = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->setError('Error al eliminar opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Subir imagen
     */
    public function uploadImage(array $file): string
    {
        try {
            $uploadDir = p('images/gr/');
            
            // Crear directorio si no existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
            $destination = $uploadDir . $filename;
            
            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return 'images/gr/' . $filename;
            }
            
            return '';
        } catch (Exception $e) {
            error_log('Option::uploadImage error: ' . $e->getMessage());
            return '';
        }
    }
}
?>