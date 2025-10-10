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

    /**
     * Devuelve columnas de notifications y datos de lectura (Notification_Us) para el usuario actual.
     * @param int $userId
     * @param int $roleId
     * @return array
     */
    public function getNotificationsForUser(int $userId, int $roleId)
    {
     * @return bool
     */
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
     * Marca todas las notificaciones del rol (o globales) como leídas para un usuario: inserta filas faltantes en Notification_Us.
     * @param array $data keys: idRol (int), title, message, type, data
     * @return int|false
     */
    public function create(array $data)
    {
                VALUES (:title, :message, :type, :data, 0, NOW(), NOW(), :idRol)";

        $stmt = $this->db->prepare($sql);

        $payload = [
            'title'   => isset($data['title']) ? $data['title'] : '',
            'message' => isset($data['message']) ? $data['message'] : '',
            'type'    => isset($data['type']) ? $data['type'] : null,
            'data'    => isset($data['data']) ? json_encode($data['data']) : null,
            // permitir idRol NULL para notificación global
            'idRol'   => array_key_exists('idRol', $data) && $data['idRol'] !== '' ? (is_null($data['idRol']) ? null : (int)$data['idRol']) : null,
        ];

        try {
            if ($stmt->execute($payload)) {
                return (int)$this->db->lastInsertId();
            }
        } catch (\Throwable $e) {
            error_log('Notification::create error: ' . $e->getMessage());
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