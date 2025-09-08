<?php
// app/Models/Role.php
require_once __DIR__ . '/../Config/database.php';

class Role {
    private const TBL = '`rol`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    public function getAll(): array {
        try {
            $query = "SELECT * FROM " . self::TBL . "";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener roles: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id): ?array {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idRol = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar rol: " . $e->getMessage());
            return null;
        }
    }

    public function create($role): ?int {
        try {
            $query = "INSERT INTO " . self::TBL . " (rol) VALUES (:role)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear rol: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $role): bool {
        try {
            $query = "UPDATE " . self::TBL . " SET rol = :role WHERE idRol = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar rol: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id): bool {
        try {
            // Check if there are users with this role
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM `user` WHERE idRol = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $userCount = $stmt->fetchColumn();

            if ($userCount > 0) {
                throw new PDOException("No se puede eliminar el rol porque hay usuarios asignados. Cambie el rol de los usuarios primero.");
            }

            // Delete related permissions first
            $stmt = $this->db->prepare("DELETE FROM `permission` WHERE idRol = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Then delete the role
            $query = "DELETE FROM " . self::TBL . " WHERE idRol = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar rol: " . $e->getMessage());
            return false;
        }
    }
}