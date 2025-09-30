<?php
// Usa $pageTitle, $breadcrumbs, $sessionUser, $sessionEmail, $roleId
require_once __DIR__ . '/../../Models/Notification.php';
use App\Models\Notification;

$userId = $_SESSION['user_id'] ?? 0;
$notificationModel = new Notification();
$notifications = $notificationModel->getNotificationsForUser($userId);

// Adaptar los datos para la vista (icono, url, etc.) si es necesario
foreach ($notifications as &$notif) {
  // Icono por tipo
  switch ($notif['type']) {
    case 'success': $notif['icon'] = 'fas fa-check-circle'; break;
    case 'warning': $notif['icon'] = 'fas fa-exclamation-triangle'; break;
    case 'error': $notif['icon'] = 'fas fa-times-circle'; break;
    case 'info': default: $notif['icon'] = 'fas fa-info-circle'; break;
  }
  // url opcional, si tienes campo 'data' puedes usarlo
  $notif['url'] = $notif['data'] ?? null;
  // Adaptar campo 'is_read' a 'read' para compatibilidad con la vista
  $notif['read'] = isset($notif['is_read']) ? (bool)$notif['is_read'] : false;
  // Adaptar campo 'created_at' a 'time' para compatibilidad con la vista
  $notif['time'] = $notif['created_at'] ?? '';
}
unset($notif);

$unreadCount = count(array_filter($notifications, fn($n) => !$n['read']));
?>

<div class="menu-section">
  <button id="menuToggle" class="menu-toggle" aria-label="Alternar menú">
    <i class="fas fa-bars"></i>
  </button>
  <div>
    <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <nav class="breadcrumb">
      <?php if (!empty($breadcrumbs)): ?>
        <?php foreach ($breadcrumbs as $i => $bc): ?>
          <?php if ($i > 0): ?><span class="breadcrumb-separator">/</span><?php endif; ?>
          <?php if (!empty($bc['url'])): ?>
            <a href="<?= htmlspecialchars((string)$bc['url'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars((string)$bc['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          <?php else: ?>
            <span><?= htmlspecialchars((string)$bc['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </nav>
  </div>
</div>

<div class="topbar-actions">
  <!-- Notifications Bell -->
  <div class="notification-container">
    <button class="notification-bell" id="notificationBell" aria-label="Notificaciones">
      <i class="fas fa-bell"></i>
      <?php if ($unreadCount > 0): ?>
        <span class="notification-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
      <?php endif; ?>
    </button>
    
    <div class="notification-dropdown" id="notificationDropdown">
      <div class="notification-header">
        <h3>Notificaciones</h3>
        <?php if ($unreadCount > 0): ?>
          <button class="mark-all-read" onclick="markAllAsRead()">
            <i class="fas fa-check-double"></i> Marcar todas como leídas
          </button>
        <?php endif; ?>
      </div>
      
      <div class="notification-list">
        <?php if (!empty($notifications)): ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="notification-item <?= !$notification['read'] ? 'unread' : '' ?>" 
                 data-id="<?= $notification['id'] ?>"
                 <?= $notification['url'] ? 'onclick="handleNotificationClick(' . $notification['id'] . ', \'' . htmlspecialchars($notification['url'], ENT_QUOTES) . '\')"' : '' ?>>
              <div class="notification-icon <?= $notification['type'] ?>">
                <i class="<?= $notification['icon'] ?>"></i>
              </div>
              <div class="notification-content">
                <div class="notification-title"><?= htmlspecialchars($notification['title']) ?></div>
                <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                <div class="notification-time"><?= timeAgo($notification['time']) ?></div>
              </div>
              <?php if (!$notification['read']): ?>
                <div class="unread-indicator"></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="no-notifications">
            <i class="fas fa-bell-slash"></i>
            <p>No hay notificaciones</p>
          </div>
        <?php endif; ?>
      </div>
      
      <?php if (!empty($notifications)): ?>
        <div class="notification-footer">
          <a href="/notifications/all" class="view-all-link">
            <i class="fas fa-list"></i> Ver todas las notificaciones
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- User Menu -->
  <div class="user-menu" id="userMenu" role="button" tabindex="0" aria-haspopup="true" aria-controls="userDropdown">
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($sessionUser, ENT_QUOTES, 'UTF-8') ?></div>
      <div class="user-role"><?= ($roleId === 1 ? 'Administrador' : 'Socio') ?></div>
    </div>
    <div class="user-avatar"><?= strtoupper(substr($sessionUser, 0, 2)) ?></div>

    <div class="user-dropdown" id="userDropdown" aria-expanded="false" aria-hidden="true">
      <div class="dropdown-header">
        <div class="dropdown-user-name"><?= htmlspecialchars($sessionUser, ENT_QUOTES, 'UTF-8') ?></div>
        <div class="dropdown-user-email"><?= htmlspecialchars($sessionEmail, ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <a href="<?= u('users/profile') ?>" class="dropdown-item">
        <i class="fas fa-user"></i> Mi Perfil
      </a>
      <div class="dropdown-divider"></div>
      <a href="<?= u('logout') ?>" class="dropdown-item logout">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
      </a>
    </div>
  </div>
</div>

<style>
/* Topbar Actions Container */
.topbar-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Notification Bell */
.notification-container {
  position: relative;
}

.notification-bell {
  position: relative;
  background: none;
  border: none;
  font-size: 1.2rem;
  color: var(--cream-700, #666);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
}

.notification-bell:hover {
  background-color: var(--cream-100, #f5f5f5);
  color: var(--cream-800, #333);
  transform: scale(1.05);
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #e74c3c;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.7rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 1;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

/* Notification Dropdown */
.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  width: 380px;
  max-height: 500px;
  overflow: hidden;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  z-index: 1000;
  border: 1px solid var(--cream-200, #e5e5e5);
}

.notification-dropdown.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.notification-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--cream-200, #e5e5e5);
  background: var(--cream-50, #f9f9f9);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-header h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--cream-800, #333);
}

.mark-all-read {
  background: none;
  border: none;
  color: var(--cream-600, #666);
  font-size: 0.8rem;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.mark-all-read:hover {
  background: var(--cream-100, #f0f0f0);
  color: var(--cream-800, #333);
}

.notification-list {
  max-height: 350px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--cream-100, #f0f0f0);
  cursor: pointer;
  transition: background-color 0.3s ease;
  position: relative;
}

.notification-item:hover {
  background-color: var(--cream-50, #f9f9f9);
}

.notification-item.unread {
  background-color: #f8f9ff;
  border-left: 3px solid #3498db;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.notification-icon.success {
  background: #d4edda;
  color: #155724;
}

.notification-icon.warning {
  background: #fff3cd;
  color: #856404;
}

.notification-icon.error {
  background: #f8d7da;
  color: #721c24;
}

.notification-icon.info {
  background: #d1ecf1;
  color: #0c5460;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  color: var(--cream-800, #333);
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
  line-height: 1.3;
}

.notification-message {
  color: var(--cream-600, #666);
  font-size: 0.8rem;
  line-height: 1.4;
  margin-bottom: 0.25rem;
}

.notification-time {
  color: var(--cream-500, #999);
  font-size: 0.7rem;
}

.unread-indicator {
  width: 8px;
  height: 8px;
  background: #3498db;
  border-radius: 50%;
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.no-notifications {
  text-align: center;
  padding: 2rem 1rem;
  color: var(--cream-500, #999);
}

.no-notifications i {
  font-size: 2rem;
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

.no-notifications p {
  margin: 0;
  font-size: 0.9rem;
}

.notification-footer {
  padding: 0.75rem 1.25rem;
  border-top: 1px solid var(--cream-200, #e5e5e5);
  background: var(--cream-50, #f9f9f9);
}

.view-all-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: var(--cream-600, #666);
  text-decoration: none;
  font-size: 0.85rem;
  padding: 0.25rem;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.view-all-link:hover {
  background: var(--cream-100, #f0f0f0);
  color: var(--cream-800, #333);
  text-decoration: none;
}

/* Responsive */
@media (max-width: 768px) {
  .notification-dropdown {
    width: 320px;
    right: -1rem;
  }
  
  .notification-item {
    padding: 0.75rem 1rem;
  }
  
  .notification-icon {
    width: 32px;
    height: 32px;
    font-size: 0.8rem;
  }
}
</style>

<script>
// Notification functionality
document.addEventListener('DOMContentLoaded', function() {
  const notificationBell = document.getElementById('notificationBell');
  const notificationDropdown = document.getElementById('notificationDropdown');
  
  // Toggle notification dropdown
  notificationBell.addEventListener('click', function(e) {
    e.stopPropagation();
    notificationDropdown.classList.toggle('show');
    
    // Close user menu if open
    const userDropdown = document.getElementById('userDropdown');
    userDropdown.classList.remove('show');
  });
  
  // Close dropdown when clicking outside
  document.addEventListener('click', function(e) {
    if (!notificationDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
      notificationDropdown.classList.remove('show');
    }
  });
  
  // Handle keyboard navigation
  notificationBell.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      notificationDropdown.classList.toggle('show');
    }
  });
});

// Handle notification click
function handleNotificationClick(notificationId, url) {
  // Mark as read
  markAsRead(notificationId);
  
  // Navigate to URL if provided
  if (url) {
    window.location.href = url;
  }
}

// Mark single notification as read
function markAsRead(notificationId) {
  const notificationItem = document.querySelector(`[data-id="${notificationId}"]`);
  if (notificationItem) {
    notificationItem.classList.remove('unread');
    const indicator = notificationItem.querySelector('.unread-indicator');
    if (indicator) {
      indicator.remove();
    }
    updateBadgeCount();
    // AJAX para marcar como leída en el backend
    fetch('/notifications/mark-read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: notificationId })
    });
  }
}

// Mark all notifications as read
function markAllAsRead() {
  const unreadItems = document.querySelectorAll('.notification-item.unread');
  unreadItems.forEach(item => {
    item.classList.remove('unread');
    const indicator = item.querySelector('.unread-indicator');
    if (indicator) {
      indicator.remove();
    }
  });
  // Oculta el botón de marcar todas
  const markAllButton = document.querySelector('.mark-all-read');
  if (markAllButton) {
    markAllButton.style.display = 'none';
  }
  updateBadgeCount();
  // AJAX para marcar todas como leídas en el backend
  fetch('/notifications/mark-all-read', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' }
  });
}

// Update notification badge count
function updateBadgeCount() {
  const badge = document.querySelector('.notification-badge');
  const unreadCount = document.querySelectorAll('.notification-item.unread').length;
  
  if (unreadCount === 0) {
    if (badge) {
      badge.remove();
    }
  } else {
    if (badge) {
      badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
    }
  }
}

// Add real-time notification (call this function when receiving new notifications)
function addNewNotification(notification) {
  // Add notification to the list
  // Update badge count
  // Show a toast or brief animation
  console.log('New notification received:', notification);
}
</script>

<?php
// Helper function to calculate time ago
function timeAgo($datetime) {
  $time = time() - strtotime($datetime);
  
  if ($time < 60) return 'ahora';
  if ($time < 3600) return floor($time/60) . 'm';
  if ($time < 86400) return floor($time/3600) . 'h';
  if ($time < 2592000) return floor($time/86400) . 'd';
  if ($time < 31536000) return floor($time/2592000) . 'mes';
  return floor($time/31536000) . 'año';
}
?>