<?php
// app/Models/PaymentType.php

require_once __DIR__ . '/../Config/database.php';

class PaymentType {
    private const TBL = '`paymenttype`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Obtiene todos los tipos de pago
     * @return array Lista de tipos de pago
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM " . self::TBL . " ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PaymentType::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un tipo de pago por su ID
     * @param int $id ID del tipo de pago
     * @return array|false Datos del tipo de pago o false si no se encuentra
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idPaymentType = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en PaymentType::getById(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo tipo de pago
     * @param array $data Datos del tipo de pago
     * @return int|false ID del tipo de pago creado o false en caso de error
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . self::TBL . " (name, description, dateCreation) 
                     VALUES (:name, :description, NOW())";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en PaymentType::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un tipo de pago existente
     * @param int $id ID del tipo de pago a actualizar
     * @param array $data Nuevos datos del tipo de pago
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . self::TBL . " SET 
                     name = :name, 
                     description = :description 
                     WHERE idPaymentType = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentType::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un tipo de pago
     * @param int $id ID del tipo de pago a eliminar
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . self::TBL . " WHERE idPaymentType = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en PaymentType::delete(): " . $e->getMessage());
            return false;
        }
    }
}
