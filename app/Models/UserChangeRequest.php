<?php
// app/Models/UserChangeRequest.php

require_once __DIR__ . '/../Config/database.php';

class UserChangeRequest
{
    private const TABLE = 'user_change_requests';
    
    /** @var \PDO */
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Crear una nueva solicitud de cambio
     */
    public function create(int $userId, string $field, $oldValue, $newValue): bool
    {
        try {
            $sql = "INSERT INTO " . self::TABLE . " 
                    (user_id, field_name, old_value, new_value) 
                    VALUES (:user_id, :field_name, :old_value, :new_value)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':field_name', $field, \PDO::PARAM_STR);
            $stmt->bindParam(':old_value', $oldValue, \PDO::PARAM_STR);
            $stmt->bindParam(':new_value', $newValue, \PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al crear solicitud de cambio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener solicitudes pendientes por socio
     */
    public function getPendingByPartner(int $partnerId): array
    {
        try {
            $sql = "SELECT r.*, u.login as user_login 
                    FROM " . self::TABLE . " r
                    JOIN user u ON r.user_id = u.idUser
                    WHERE u.idPartner = :partner_id AND r.status = 'pending'
                    ORDER BY r.requested_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':partner_id', $partnerId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener solicitudes pendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las solicitudes pendientes
     */
    public function getAllPending(): array
    {
        try {
            $sql = "SELECT r.*, u.login as user_login, u.idPartner, p.name as partner_name
                    FROM " . self::TABLE . " r
                    JOIN user u ON r.user_id = u.idUser
                    LEFT JOIN partner p ON u.idPartner = p.idPartner
                    WHERE r.status = 'pending'
                    ORDER BY r.requested_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener todas las solicitudes pendientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Aprobar una solicitud de cambio
     */
    public function approve(int $requestId, int $adminId): bool
    {
        try {
            // Primero obtener la información de la solicitud
            $request = $this->getById($requestId);
            if (!$request) {
                return false;
            }

            // Actualizar el campo en la tabla partner
            /*$partnerModel = new Partner(); // Asumiendo que existe un modelo Partner
            $updateSuccess = $partnerModel->updateField(
                $request['user_id'], 
                $request['field_name'], 
                $request['new_value']
            );*/

            /*if ($updateSuccess) {
                // Marcar la solicitud como aprobada
                $sql = "UPDATE " . self::TABLE . " 
                        SET status = 'approved', reviewed_at = NOW(), reviewed_by = :admin_id
                        WHERE id = :request_id";
                
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':admin_id', $adminId, \PDO::PARAM_INT);
                $stmt->bindParam(':request_id', $requestId, \PDO::PARAM_INT);
                
                return $stmt->execute();
            }
            */
            return false;
        } catch (\PDOException $e) {
            error_log("Error al aprobar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechazar una solicitud de cambio
     */
    public function reject(int $requestId, int $adminId): bool
    {
        try {
            $sql = "UPDATE " . self::TABLE . " 
                    SET status = 'rejected', reviewed_at = NOW(), reviewed_by = :admin_id
                    WHERE id = :request_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':admin_id', $adminId, \PDO::PARAM_INT);
            $stmt->bindParam(':request_id', $requestId, \PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al rechazar solicitud: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener una solicitud por ID
     */
    public function getById(int $requestId): ?array
    {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :request_id LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':request_id', $requestId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al obtener solicitud por ID: " . $e->getMessage());
            return null;
        }
    }
}