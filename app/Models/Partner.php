<?php
// app/Models/Partner.php

require_once __DIR__ . '/../Config/database.php';

class Partner {
    private const TBL = '`partner`';
    private const TBL2 = '`user`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    // app/Models/Partner.php
    public function create($name, $ci, $cellPhoneNumber, $address, $dateCreation, $birthday, $dateRegistration) {
        try {
            $query = "INSERT INTO " . self::TBL . " (name, CI, cellPhoneNumber, address, dateCreation, birthday, dateRegistration) VALUES (:name, :ci, :cellPhoneNumber, :address, :dateCreation, :birthday, :dateRegistration)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':dateCreation', $dateCreation, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':dateRegistration', $dateRegistration, PDO::PARAM_STR);
            $stmt->execute();
            $partnerId = $this->db->lastInsertId();
            error_log("Inserted Partner ID: $partnerId");
            return $partnerId;
        } catch (PDOException $e) {
            error_log("Error al crear socio: " . $e->getMessage());
            return false;
        }
    }
    public function getAllSocios() {
        try {
            $query = "SELECT 
                        p.idPartner,
                        p.name,
                        p.CI,
                        p.cellPhoneNumber,
                        p.address,
                        DATE_FORMAT(p.dateCreation, '%Y-%m-%d %H:%i:%s') as dateCreation,
                        DATE_FORMAT(p.birthday, '%Y-%m-%d') as birthday,
                        DATE_FORMAT(p.dateRegistration, '%Y-%m-%d') as dateRegistration,
                        u.login,
                        u.email
                      FROM " . self::TBL . " p 
                      JOIN " . self::TBL2 . " u ON p.idPartner = u.idPartner 
                      WHERE u.idRol = 2";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log the result for debugging
            error_log('Socios data: ' . print_r($result, true));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener socios: " . $e->getMessage());
            return [];
        }
    }
    public function findById($id) {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idPartner = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar socio: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $name, $ci, $cellPhoneNumber, $address, $birthday, $dateRegistration) {
        try {
            $query = "UPDATE " . self::TBL . " SET name = :name, CI = :ci, cellPhoneNumber = :cellPhoneNumber, address = :address, birthday = :birthday, dateRegistration = :dateRegistration WHERE idPartner = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':dateRegistration', $dateRegistration, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar socio: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $query = "UPDATE " . self::TBL . " SET status = :status WHERE idPartner = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':status', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar socio: " . $e->getMessage());
            return false;
        }
    }
}