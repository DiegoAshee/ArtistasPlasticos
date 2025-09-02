<?php
// app/Models/PartnerOnline.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class PartnerOnline {
    private const TBL = '`partneronline`';
    private \PDO $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Crea la solicitud online.
     * Las fechas (dateCreation, dateRegistration) se fijan con NOW() desde SQL.
     * dateConfirmation e idUser quedan NULL hasta que se acepte/rechace.
     */
    public function create(string $name, string $ci, string $cellPhoneNumber, string $address, string $birthday, ?string $email = null) {
        try {
            $sql = "INSERT INTO " . self::TBL . " 
                    (name, CI, cellPhoneNumber, address, dateCreation, birthday, dateRegistration, dateConfirmation, idUser, email)
                    VALUES
                    (:name, :ci, :cellPhoneNumber, :address, NOW(), :birthday, NOW(), NULL, NULL, :email)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':ci', $ci);
            $stmt->bindValue(':cellPhoneNumber', $cellPhoneNumber);
            $stmt->bindValue(':address', $address);
            $stmt->bindValue(':birthday', $birthday);
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log("PartnerOnline::create error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . self::TBL . " ORDER BY idPartnerOnline DESC");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("PartnerOnline::getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . self::TBL . " WHERE idPartnerOnline = :id LIMIT 1");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            error_log("PartnerOnline::findById error: " . $e->getMessage());
            return null;
        }
    }

    public function delete(int $id): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM " . self::TBL . " WHERE idPartnerOnline = :id");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("PartnerOnline::delete error: " . $e->getMessage());
            return false;
        }
    }

    // Validaciones simples
    public function emailExists(string $email): bool {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . self::TBL . " WHERE email = :email");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("PartnerOnline::emailExists error: " . $e->getMessage());
            return false;
        }
    }

    public function ciExists(string $ci): bool {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . self::TBL . " WHERE CI = :ci");
            $stmt->bindValue(':ci', $ci);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("PartnerOnline::ciExists error: " . $e->getMessage());
            return false;
        }
    }
}
