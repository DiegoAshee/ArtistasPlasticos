<?php
// app/Models/Movement.php

require_once __DIR__ . '/../Config/database.php';

class Movement {
    public const TBL = '`movement`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Obtiene todos los movimientos con información relacionada
     * @param array $filters Filtros opcionales (start_date, end_date, concept_id, user_id)
     * @return array Lista de movimientos con información de concepto, usuario y tipo de pago
     */
    public function getAllMovements($filters = []) {
        try {
            $query = "SELECT 
                        m.idMovement,
                        m.amount,
                        m.dateCreation AS dateCreation,
                        m.dateCreation AS dateMovement,
                        m.description,
                        m.idConcept,
                        m.idUser,
                        m.idPaymentType,
                        c.description AS concept_description,
                        u.login AS user_login,
                        pt.description AS payment_type_description
                      FROM " . self::TBL . " m
                      LEFT JOIN `concept` c ON m.idConcept = c.idConcept
                      LEFT JOIN `user` u ON m.idUser = u.idUser
                      LEFT JOIN `paymenttype` pt ON m.idPaymentType = pt.idPaymentType
                      WHERE 1=1";
            
            $params = [];
            
            // Aplicar filtros de fecha
            if (!empty($filters['start_date'])) {
                $query .= " AND m.dateCreation >= :start_date";
                $params[':start_date'] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $query .= " AND m.dateCreation <= :end_date";
                $params[':end_date'] = $filters['end_date'];
            }
            
            if (!empty($filters['concept_id'])) {
                $query .= " AND m.idConcept = :concept_id";
                $params[':concept_id'] = $filters['concept_id'];
            }
            
            if (!empty($filters['user_id'])) {
                $query .= " AND m.idUser = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            
            $query .= " ORDER BY m.dateCreation DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener movimientos: " . $e->getMessage());
            return [];
        }
    }

    

    /**
     * Obtiene un movimiento por su ID
     * @param int $idMovement ID del movimiento
     * @return array|false Datos del movimiento o false si no se encuentra
     */
    public function getById($idMovement) {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idMovement = :idMovement";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idMovement', $idMovement, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error al obtener movimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un movimiento existente
     * @param int $idMovement ID del movimiento
     * @param array $data Datos a actualizar
     * @return bool Éxito de la operación
     */
    public function update($idMovement, $data) {
        try {
            $updates = [];
            $params = [':idMovement' => $idMovement];
            
            if (isset($data['amount'])) {
                $updates[] = 'amount = :amount';
                $params[':amount'] = $data['amount'];
            }
            if (isset($data['idConcept'])) {
                $updates[] = 'idConcept = :idConcept';
                $params[':idConcept'] = $data['idConcept'];
            }
            if (isset($data['idPaymentType'])) {
                $updates[] = 'idPaymentType = :idPaymentType';
                $params[':idPaymentType'] = $data['idPaymentType'];
            }
            if (isset($data['idUser'])) {
                $updates[] = 'idUser = :idUser';
                $params[':idUser'] = $data['idUser'];
            }
            if (isset($data['description'])) {
                $updates[] = 'description = :description';
                $params[':description'] = $data['description'];
            }
            if (isset($data['dateCreation'])) {
                $updates[] = 'dateCreation = :dateCreation';
                $params[':dateCreation'] = $data['dateCreation'];
            }
            
            if (empty($updates)) {
                return false; // No hay nada que actualizar
            }
            
            $query = "UPDATE " . self::TBL . " SET " . implode(', ', $updates) . " WHERE idMovement = :idMovement";
            $stmt = $this->db->prepare($query);
            
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar movimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un movimiento
     * @param int $idMovement ID del movimiento a eliminar
     * @return bool Éxito de la operación
     */
    public function delete($idMovement) {
        try {
            $query = "DELETE FROM " . self::TBL . " WHERE idMovement = :idMovement";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idMovement', $idMovement, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error al eliminar movimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo movimiento
     * @param array $data Datos del movimiento a crear
     * @return bool|int ID del movimiento creado o false en caso de error
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . self::TBL . " 
                     (description, amount, dateCreation, idPaymentType, idConcept, idUser) 
                     VALUES 
                     (:description, :amount, :dateCreation, :idPaymentType, :idConcept, :idUser)";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                ':description' => $data['description'],
                ':amount' => $data['amount'],
                ':dateCreation' => $data['dateCreation'],
                ':idPaymentType' => $data['idPaymentType'],
                ':idConcept' => $data['idConcept'],
                ':idUser' => $data['idUser']
            ]);
            
            if ($success) {
                return $this->db->lastInsertId();
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error al crear movimiento: " . $e->getMessage());
            return false;
        }
    }
}
