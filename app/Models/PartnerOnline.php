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
    public function emailExistsAnywhere(string $email): bool {
        try {
            $sql = "SELECT 
                    (EXISTS(SELECT 1 FROM partneronline WHERE email = :e1)
                    OR EXISTS(SELECT 1 FROM user          WHERE email = :e2)) AS ex";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':e1', $email);
            $stmt->bindValue(':e2', $email);
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("PartnerOnline::emailExistsAnywhere error: " . $e->getMessage());
            return false;
        }
    }

    public function ciExistsAnywhere(string $ci): bool {
        try {
            $sql = "SELECT 
                    (EXISTS(SELECT 1 FROM partneronline WHERE CI = :c1)
                    OR EXISTS(SELECT 1 FROM partner       WHERE ci = :c2)) AS ex";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':c1', $ci);
            $stmt->bindValue(':c2', $ci);
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("PartnerOnline::ciExistsAnywhere error: " . $e->getMessage());
            return false;
        }
    }



    //modulo modificación de daos por parte del socio

     // Crear una solicitud de cambio
    public function createChangeRequest($name, $ci, $cellPhoneNumber, $address, $birthday, $email, $idUser) {
        try {
            $query = "INSERT INTO " . self::TBL . " 
                (name, ci, cellPhoneNumber, address, birthday, email, dateCreation, dateRegistration, idUser)
                VALUES (:name, :ci, :cellPhoneNumber, :address, :birthday, :email, NOW(), NOW(), :idUser)";
            
            $stmt = $this->db->prepare($query);
            
            // Vincular parámetros con los valores proporcionados
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':ci', $ci);
            $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':birthday', $birthday);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':idUser', $idUser, PDO::PARAM_INT);
            
            // Ejecutar la consulta
            $stmt->execute();
            
            // Retornar el ID de la solicitud creada
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Si ocurre un error, lo registramos
            error_log("Error al crear solicitud: " . $e->getMessage());
            return false;
        }
    }







}
