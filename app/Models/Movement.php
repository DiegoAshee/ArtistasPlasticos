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
                        m.nameDestination,
                        c.description AS concept_description,
                        c.type AS concept_type,
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
            if (isset($data['nameDestination'])) {
                $updates[] = 'nameDestination = :nameDestination';
                $params[':nameDestination'] = $data['nameDestination'];
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
                     (description, amount, dateCreation, idPaymentType, idConcept, idUser, nameDestination) 
                     VALUES 
                     (:description, :amount, :dateCreation, :idPaymentType, :idConcept, :idUser, :nameDestination)";
            
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([
                ':description' => $data['description'],
                ':amount' => $data['amount'],
                ':dateCreation' => $data['dateCreation'],
                ':idPaymentType' => $data['idPaymentType'],
                ':idConcept' => $data['idConcept'],
                ':idUser' => $data['idUser'],
                ':nameDestination' => $data['nameDestination'] ?? ''
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
    /**
     * Obtiene los datos para generar un recibo de movimiento
     * @param int $idMovement ID del movimiento
     * @return array|false Datos para el recibo o false si hay error
     */
    public function getReceiptData($idMovement) {
        error_log("=== INICIO DE getReceiptData ===");
        error_log("Buscando movimiento con ID: " . $idMovement);
        
        try {
            $query = "SELECT 
                    m.idMovement,
                    m.amount,
                    m.dateCreation,
                    m.description,
                    m.nameDestination,
                    c.description AS concept_description,
                    c.type AS concept_type,
                    u.login AS user_login,
                    pt.description AS payment_type_description,
                    m.idUser
                FROM " . self::TBL . " m
                LEFT JOIN `concept` c ON m.idConcept = c.idConcept
                LEFT JOIN `user` u ON m.idUser = u.idUser
                LEFT JOIN `paymenttype` pt ON m.idPaymentType = pt.idPaymentType
                WHERE m.idMovement = :idMovement";
            
            error_log("Consulta SQL: " . $query);
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idMovement', $idMovement, PDO::PARAM_INT);
            
            $executed = $stmt->execute();
            error_log("Ejecución de consulta: " . ($executed ? "éxito" : "falló"));
            
            $movement = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Datos del movimiento: " . print_r($movement, true));
            
            if (!$movement) {
                error_log("No se encontró el movimiento con ID: " . $idMovement);
                return false;
            }
            
            // Formatear los datos para el recibo
            $receiptData = [
                'receiptNumber' => 'M-' . str_pad($movement['idMovement'], 6, '0', STR_PAD_LEFT),
                'issueDate' => date('Y-m-d', strtotime($movement['dateCreation'])),
                'movement' => $movement,
                'user' => [
                    'name' => $movement['user_login'],
                    'login' => $movement['user_login']
                ]
            ];
            
            error_log("Datos del recibo preparados: " . print_r($receiptData, true));
            return $receiptData;
            
        } catch (PDOException $e) {
            $error = "Error en getReceiptData: " . $e->getMessage() . "\n" . $e->getTraceAsString();
            error_log($error);
            return false;
        }
    }
}
