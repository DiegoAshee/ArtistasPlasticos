<?php
// app/Models/Option.php
declare(strict_types=1);
 
require_once __DIR__ . '/../Config/database.php';
 
class Option
{
    private array $lastError = [];
    private \PDO $db;
 
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
            'code'    => is_numeric($code) ? (int)$code : 0
        ];
    }
 
    /** Listar solo status 1 (activa) y 2 (inactiva) con login del creador */
    public function getAll(): array
    {
        try {
            $sql = "SELECT o.*, u.login AS createdByName
                      FROM `option` o
                 LEFT JOIN `user` u ON u.idUser = o.idUser
                     WHERE o.status IN (1,2)
                  ORDER BY o.idOption DESC";
            $st  = $this->db->prepare($sql);
            $st->execute();
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opciones: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getAll error: ' . $e->getMessage());
            return [];
        }
    }
 
    /** Buscar por ID (solo si no está eliminada: 1 o 2) con login del creador */
    public function getById(int $id): ?array
    {
        try {
            $sql = "SELECT o.*, u.login AS createdByName
                      FROM `option` o
                 LEFT JOIN `user` u ON u.idUser = o.idUser
                     WHERE o.idOption = :id AND o.status IN (1,2)";
            $st  = $this->db->prepare($sql);
            $st->bindValue(':id', $id, \PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getById error: ' . $e->getMessage());
            return null;
        }
    }
 
    /** Activa (status = 1) con login del creador */
    public function getActive(): ?array
    {
        try {
            $sql = "SELECT o.*, u.login AS createdByName
                      FROM `option` o
                 LEFT JOIN `user` u ON u.idUser = o.idUser
                     WHERE o.status = 1
                  ORDER BY o.idOption DESC
                     LIMIT 1";
            $st  = $this->db->prepare($sql);
            $st->execute();
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            $this->setError('Error al obtener activa: ' . $e->getMessage(), $e->getCode());
            error_log('Option::getActive error: ' . $e->getMessage());
            return null;
        }
    }
 
    /** Crear (por defecto status=2) */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO `option`
                       (title, imageURL, imageURLQR, telephoneContact, status, idUser)
                    VALUES
                       (:title, :imageURL, :imageURLQR, :telephoneContact, 2, :idUser)";
            $st = $this->db->prepare($sql);
            $st->bindValue(':title',            $data['title'],            \PDO::PARAM_STR);
            $st->bindValue(':imageURL',         $data['imageURL'],         \PDO::PARAM_STR);
            $st->bindValue(':imageURLQR',       $data['imageURLQR'],       \PDO::PARAM_STR);
            $st->bindValue(':telephoneContact', $data['telephoneContact'], \PDO::PARAM_STR);
            $st->bindValue(':idUser',           $data['idUser'],           \PDO::PARAM_INT);
            return $st->execute();
        } catch (\PDOException $e) {
            $this->setError('Error al crear opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::create error: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Actualizar datos */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE `option`
                       SET title = :title,
                           imageURL = :imageURL,
                           imageURLQR = :imageURLQR,
                           telephoneContact = :telephoneContact,
                           idUser = :idUser
                     WHERE idOption = :id";
            $st = $this->db->prepare($sql);
            $st->bindValue(':title',            $data['title'],            \PDO::PARAM_STR);
            $st->bindValue(':imageURL',         $data['imageURL'],         \PDO::PARAM_STR);
            $st->bindValue(':imageURLQR',       $data['imageURLQR'],       \PDO::PARAM_STR);
            $st->bindValue(':telephoneContact', $data['telephoneContact'], \PDO::PARAM_STR);
            $st->bindValue(':idUser',           $data['idUser'],           \PDO::PARAM_INT);
            $st->bindValue(':id',               $id,                       \PDO::PARAM_INT);
            return $st->execute();
        } catch (\PDOException $e) {
            $this->setError('Error al actualizar opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::update error: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Activar una (1) y poner el resto en 2 (atómico) */
    public function activate(int $id): bool
    {
        try {
            $this->db->beginTransaction();
            $st = $this->db->prepare("
                UPDATE `option`
                   SET status = CASE WHEN idOption = :id THEN 1 ELSE 2 END
                 WHERE status IN (1,2)
            ");
            $st->bindValue(':id', $id, \PDO::PARAM_INT);
            $ok = $st->execute();
            $this->db->commit();
            return $ok;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->setError('Error al activar opción: ' . $e->getMessage(), $e->getCode());
            error_log('Option::activate error: ' . $e->getMessage());
            return false;
        }
    }
 
    /** Soft delete ⇒ status = 0 (no aparece en listados) */
    public function softDelete(int $id): bool
    {
        try {
            $st = $this->db->prepare("
                UPDATE `option`
                   SET status = 0
                 WHERE idOption = :id
                   AND status IN (1,2)
            ");
            $st->bindValue(':id', $id, \PDO::PARAM_INT);
            $st->execute();
 
            if ($st->rowCount() === 0) {
                $this->setError('Soft delete: 0 filas afectadas');
                return false;
            }
            return true;
        } catch (\PDOException $e) {
            $this->setError('Error al eliminar (soft): '.$e->getMessage(), $e->getCode());
            error_log('Option::softDelete '.$e->getMessage());
            return false;
        }
    }
 
    /** Subida de logo */
    public function uploadImage(array $file): string
    {
        try {
            $uploadDir = p('images/gr/');
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
            $name  = 'logo_' . time() . '_' . uniqid() . '.' . $ext;
            $dest  = $uploadDir . $name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return 'images/gr/' . $name;
            }
            return '';
        } catch (\Exception $e) {
            error_log('Option::uploadImage error: ' . $e->getMessage());
            return '';
        }
    }
 
    /** Subida de QR */
    public function uploadQrImage(array $file): string
    {
        try {
            $uploadDir = p('images/gr/qr/');
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
            $ext   = pathinfo($file['name'], PATHINFO_EXTENSION);
            $name  = 'qr_' . time() . '_' . uniqid() . '.' . $ext;
            $dest  = $uploadDir . $name;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return 'images/gr/qr/' . $name;
            }
            return '';
        } catch (\Exception $e) {
            error_log('Option::uploadQrImage error: ' . $e->getMessage());
            return '';
        }
    }
}