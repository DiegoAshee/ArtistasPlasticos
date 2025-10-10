<?php
namespace App\Models;

require_once __DIR__ . '/../Config/database.php';
use Database;

class Notification
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::singleton()->getConnection();
    }

    public function getNotificationsForUser(int $userId, int $roleId): array
    {
        $sql = "SELECT n.*, 
                       COALESCE(nu.isRead, 0) as isRead,
                       nu.dateRead
                FROM notifications n
                LEFT JOIN Notification_User nu ON n.id = nu.idNotification AND nu.idUser = :userId
                WHERE n.isActive = 1 
                AND (n.idRol IS NULL OR n.idRol = :roleId)
                ORDER BY n.createdAt DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userId' => $userId, 'roleId' => $roleId]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function markNotificationAsRead(int $notificationId, int $userId): bool
    {
        // Insertar solo si no existe registro de lectura para este usuario y notificación
                $sql = "INSERT INTO Notification_User (isRead, dateRead, idNotification, idUser)
                SELECT 1, NOW(), :nid, :uid
                FROM DUAL
                                WHERE NOT EXISTS (
                                    SELECT 1 FROM Notification_User nu WHERE nu.idNotification = :nid AND nu.idUser = :uid
                                )";

        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute(['nid' => $notificationId, 'uid' => $userId]);
    }

    /**
     * Crea una nueva notificación
     * @param array $data keys: title, message, type, data, idRol (opcional)
     * @return int|false ID de la notificación creada o false en caso de error
     */
    public function create(array $data)
    {
        $sql = "INSERT INTO notifications (title, message, type, data, isActive, createdAt, updatedAt, idRol) 
                VALUES (:title, :message, :type, :data, 1, NOW(), NOW(), :idRol)";

        $stmt = $this->db->prepare($sql);

        // Preparar los datos de la notificación
        $payload = [
            'title'   => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'type'    => $data['type'] ?? 'info',
            'data'    => !empty($data['data']) ? json_encode($data['data']) : null,
            'idRol'   => isset($data['idRol']) && $data['idRol'] !== '' ? (int)$data['idRol'] : null,
        ];

        try {
            if ($stmt->execute($payload)) {
                return (int)$this->db->lastInsertId();
            }
        } catch (\PDOException $e) {
            error_log('Error al crear notificación: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            error_log('Datos: ' . print_r($payload, true));
        }

        return false;
    }

    /**
     * Elimina una notificación (solo admin) o la valida por rol (si se pasa userRole).
     */
    public function delete(int $notificationId, ?int $userRole = null): bool
    {
        if ($userRole === null) {
            $sql = "DELETE FROM notifications WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return (bool)$stmt->execute(['id' => $notificationId]);
        }

        $sql = "DELETE FROM notifications WHERE id = :id AND (idRol = :role_id OR idRol = 0 OR idRol IS NULL)";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute(['id' => $notificationId, 'role_id' => $userRole]);
    }
    
}