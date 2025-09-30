<?php
namespace App\Controllers;

use App\Models\Notification;

class NotificationController extends BaseController
{
    public function getUserNotifications($userId)
    {
        $notificationModel = new Notification();
        $notifications = $notificationModel->getNotificationsForUser($userId);
        // Puedes retornar como JSON si es para AJAX
        echo json_encode($notifications);
    }

    public function markAsRead()
    {
        $notificationId = $_POST['id'] ?? null;
        $userId = $_SESSION['user_id'] ?? 0;
        $notificationModel = new \App\Models\Notification();
        $result = $notificationModel->markNotificationAsRead($notificationId, $userId);
        echo json_encode(['success' => $result]);
    }

    public function markAllAsRead()
    {
        $userId = $_SESSION['user_id'] ?? 0;
        $notificationModel = new \App\Models\Notification();
        $result = $notificationModel->markAllNotificationsAsRead($userId);
        echo json_encode(['success' => $result]);
    }
}