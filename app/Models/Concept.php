<?php
// app/Models/Concept.php

require_once __DIR__ . '/../Config/database.php';

class Concept {
    private const TBL = '`concept`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Obtiene todos los conceptos
     * @return array Lista de conceptos
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM " . self::TBL . " ORDER BY name ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Concept::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un concepto por su ID
     * @param int $id ID del concepto
     * @return array|false Datos del concepto o false si no se encuentra
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idConcept = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en Concept::getById(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo concepto
     * @param array $data Datos del concepto
     * @return int|false ID del concepto creado o false en caso de error
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . self::TBL . " (name, description, type, dateCreation) 
                     VALUES (:name, :description, :type, NOW())";
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en Concept::create(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un concepto existente
     * @param int $id ID del concepto a actualizar
     * @param array $data Nuevos datos del concepto
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE " . self::TBL . " SET 
                     name = :name, 
                     description = :description, 
                     type = :type 
                     WHERE idConcept = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
            $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
            $stmt->bindParam(':type', $data['type'], PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Concept::update(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un concepto
     * @param int $id ID del concepto a eliminar
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . self::TBL . " WHERE idConcept = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en Concept::delete(): " . $e->getMessage());
            return false;
        }
    }
}
