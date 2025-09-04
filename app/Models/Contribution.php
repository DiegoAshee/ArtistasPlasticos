<?php
// app/Models/Contribution.php
require_once __DIR__ . '/../Config/database.php';

class Contribution {
    private const TBL = '`contribution`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    public function getAll(): array {
        try {
            $query = "SELECT * FROM " . self::TBL . " ORDER BY dateCreation DESC";;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contribuciones: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id): ?array {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idContribution = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar contribución: " . $e->getMessage());
            return null;
        }
    }

    public function create($amount, $notes, $dateCreation, $monthYear): ?int {
        try {
            $query = "INSERT INTO " . self::TBL . " (amount, notes, dateCreation, monthYear) VALUES (:amount, :notes, :dateCreation, :monthYear)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $stmt->bindParam(':dateCreation', $dateCreation, PDO::PARAM_STR);
            $stmt->bindParam(':monthYear', $monthYear, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear contribución: " . $e->getMessage());
            return null;
        }
    }

    public function getLastMonthYear(): ?string
    {
        try {
            $query = "SELECT monthYear FROM " . self::TBL . " ORDER BY monthYear DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['monthYear'] : null;
        } catch (PDOException $e) {
            error_log("Error al obtener último monthYear: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $amount, $notes, $dateUpdate, $monthYear): bool {
        try {
            $query = "UPDATE " . self::TBL . " SET amount = :amount, notes = :notes, dateUpdate = :dateUpdate, monthYear = :monthYear WHERE idContribution = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $stmt->bindParam(':dateUpdate', $dateUpdate, PDO::PARAM_STR);
            $stmt->bindParam(':monthYear', $monthYear, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar contribución: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id): bool {
        try {
            $query = "DELETE FROM " . self::TBL . " WHERE idContribution = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar contribución: " . $e->getMessage());
            return false;
        }
    }
}