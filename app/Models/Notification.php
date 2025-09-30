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

    public function getNotificationsForUser($userId)
    {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id OR user_id = 0 ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function markNotificationAsRead($notificationId, $userId)
    {
        // Solo marca como leída si la notificación es para el usuario o para todos
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = :id AND (user_id = :user_id OR user_id = 0)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $notificationId, 'user_id' => $userId]);
    }

    public function markAllNotificationsAsRead($userId)
    {
        // Marca todas las notificaciones del usuario y las globales como leídas
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id OR user_id = 0";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['user_id' => $userId]);
    }
}