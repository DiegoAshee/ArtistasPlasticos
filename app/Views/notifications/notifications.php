<?php
// app/Views/notifications/notifications.php

// Configuración de la página
$title = 'Notificaciones';
$currentPath = 'notifications';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Notificaciones', 'url' => null]
];

// Verificar si hay notificaciones
if (!isset($notifications) || !is_array($notifications)) {
    $notifications = [];
    $error = 'No se pudieron cargar las notificaciones';
}

// Contar notificaciones no leídas
$unreadCount = 0;
foreach ($notifications as $n) {
    if (empty($n['user_is_read'])) {
        $unreadCount++;
    }
}

// Iniciar el buffer de salida
ob_start();
?>

<!-- Contenido de la página -->
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1" style="color:rgba(190, 179, 127, 0.86);">
                <i class="fas fa-bell text-primary me-2"></i>
                <?= htmlspecialchars($title) ?>
                <?php if ($unreadCount > 0): ?>
                    <span class="badge bg-primary ms-2"><?= $unreadCount ?> sin leer</span>
                <?php endif; ?>
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <?php foreach ($breadcrumbs as $index => $item): ?>
                        <?php if (isset($item['url'])): ?>
                            <li class="breadcrumb-item"><a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['label']) ?></a></li>
                        <?php else: ?>
                            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($item['label']) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <button id="markAllReadBtn" class="btn btn-primary" <?= $unreadCount === 0 ? 'disabled' : '' ?>>
                <i class="fas fa-check-double me-2"></i>
                Marcar todas como leídas
            </button>
            <a href="<?= u('notifications') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-2"></i>
                Actualizar
            </a>
        </div>
    </div>

    <div class="notifications-wrapper px-3">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($notifications)): ?>
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="fas fa-bell-slash text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
                <h4 class="text-muted mb-2">No hay notificaciones</h4>
                <p class="text-muted">No tienes notificaciones pendientes en este momento.</p>
            </div>
            <?php else: ?>
                <div class="notifications-container">
                    <?php foreach ($notifications as $n): ?>
                        <?php 
                        $isRead = !empty($n['user_is_read']);
                        $notificationClass = $isRead ? 'read' : 'unread';
                        
                        // Formatear fecha y hora
                        $date = new DateTime($n['created_at']);
                        $now = new DateTime();
                        $interval = $now->diff($date);
                        
                        // Formato de hora
                        $timeString = $date->format('h:i A');
                        
                        // Formato de fecha relativa
                        if ($interval->d == 0) {
                            $dateString = 'Hoy';
                        } elseif ($interval->d == 1) {
                            $dateString = 'Ayer';
                        } elseif ($interval->d < 7) {
                            $days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                            $dateString = $days[$date->format('w')];
                        } else {
                            $dateString = $date->format('d M Y');
                        }
                        ?>
                        <div class="notification-card <?= $notificationClass ?>" data-id="<?= htmlspecialchars($n['id']) ?>">
                            <div class="notification-content">
                                <div class="notification-header">
                                    <h4 class="notification-title"><?= htmlspecialchars($n['title']) ?></h4>
                                    <span class="notification-time">
                                        <?= $timeString ?>
                                        <?php if ($isRead): ?>
                                            <i class="fas fa-check-double read-status" title="Leído"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <p class="notification-message"><?= htmlspecialchars($n['message']) ?></p>
                                <div class="notification-footer">
                                    <span class="notification-date"><?= $dateString ?></span>
                                    <div class="notification-actions">
                                        <?php if (!$isRead): ?>
                                            <button type="button" 
                                                    class="btn-action btn-mark-read" 
                                                    onclick="markAsRead('<?= $n['id'] ?>', this)" 
                                                    title="Marcar como leída">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" 
                                                class="btn-action btn-delete" 
                                                onclick="deleteNotification('<?= $n['id'] ?>', this)" 
                                                title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
            </div>
        </div>
        </div>
    </div>
</div>

<style>
    /* Estilo de notificaciones al estilo iPhone */
    .notifications-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 1rem;
    }
    
    .notification-card {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #007AFF;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .notification-card.unread {
        background: #f8f9fa;
        border-left-color: #007AFF;
        position: relative;
    }
    
    .notification-card.unread::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        width: 8px;
        height: 8px;
        background-color: #007AFF;
        border-radius: 50%;
    }
    
    .notification-card.read {
        opacity: 0.8;
        border-left-color: #e5e5ea;
    }
    
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .notification-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        color: #000;
        flex: 1;
    }
    
    .notification-time {
        font-size: 0.75rem;
        color: #8e8e93;
        margin-left: 0.5rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    
    .read-status {
        color: #007AFF;
        font-size: 0.9em;
    }
    
    .notification-message {
        font-size: 0.9rem;
        color: #48484a;
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
    }
    
    .notification-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
        color: #8e8e93;
        border-top: 1px solid #f0f0f0;
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .notification-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-action {
        background: none;
        border: none;
        color: #8e8e93;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .btn-mark-read:hover {
        background: rgba(0, 122, 255, 0.1);
        color: #007AFF;
    }
    
    .btn-delete:hover {
        background: rgba(255, 59, 48, 0.1);
        color: #FF3B30;
    }
    
    .notification-card.unread .notification-title {
        font-weight: 700;
    }
    
    .notification-card.unread .notification-message {
        color: #000;
    }
    
    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .notification-card {
            padding: 0.75rem;
        }
        
        .notification-title {
            font-size: 0.95rem;
        }
        
        .notification-message {
            font-size: 0.85rem;
        }
    }
</style>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toastContainer" class="toast-container"></div>
</div>

<script>
// Mostrar notificación toast
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    const icon = icons[type] || 'info-circle';
    
    toast.className = `toast show align-items-center text-white bg-${type} border-0 mb-2`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;
    
    // Agregar el toast al contenedor
    toastContainer.appendChild(toast);
    
    // Eliminar el toast después de 5 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Marcar notificación como leída
function markAsRead(id, element) {
    fetch('<?= u('notifications/markRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = element.closest('tr');
            
            // Actualizar el estado visual
            row.classList.remove('table-primary');
            
            // Actualizar el estado en la tabla
                    const statusCell = row.querySelector('td:nth-child(5)');
                    if (statusCell) {
                        statusCell.innerHTML = `
                            <i class="fas fa-check-circle me-1"></i>
                            Leída
                        `;
                        statusCell.className = 'text-success';
                    }
                    
                    // Eliminar el botón de marcar como leída
                    element.closest('.btn-group').innerHTML = `
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger delete-btn" 
                                data-id="${id}"
                                title="Eliminar notificación">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                    
                    // Agregar evento al nuevo botón de eliminar
                    const deleteBtn = row.querySelector('.delete-btn');
                    if (deleteBtn) {
                        deleteBtn.addEventListener('click', function() {
                            deleteNotification(id, this);
                        });
                    }
                    
                    showToast('Notificación marcada como leída', 'success');
                } else {
                    showToast('Error al marcar la notificación como leída', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al procesar la solicitud', 'danger');
            });
        }
        
        // Eliminar notificación
        function deleteNotification(id, element) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta notificación?')) {
                return;
            }
            
            fetch('<?= u('notifications/delete') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = element.closest('tr');
                    // Animación de desvanecimiento
                    row.style.transition = 'opacity 0.3s';
                    row.style.opacity = '0';
                    
                    setTimeout(() => {
                        row.remove();
                        
                        // Verificar si no hay más notificaciones
                        const tbody = document.querySelector('tbody');
                        if (tbody && tbody.children.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="7" class="text-center p-5">
                                        <div class="mb-4">
                                            <i class="fas fa-bell-slash text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                        </div>
                                        <h4 class="text-muted">No hay notificaciones</h4>
                                        <p class="text-muted">No tienes notificaciones pendientes en este momento.</p>
                                    </td>
                                </tr>
                            `;
                        }
                        
                        showToast('Notificación eliminada correctamente', 'success');
                    }, 300);
                } else {
                    showToast('Error al eliminar la notificación', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al procesar la solicitud', 'danger');
            });
        }

        // Inicialización cuando el DOM está listo
        document.addEventListener('DOMContentLoaded', function() {
            // Marcar todas como leídas
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function() {
                    if (confirm('¿Marcar todas las notificaciones como leídas?')) {
                        fetch('<?= u('notifications/markAllRead') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar la interfaz
                                document.querySelectorAll('tr.table-primary').forEach(row => {
                                    row.classList.remove('table-primary');
                                    
                                    const statusCell = row.querySelector('td:nth-child(5)');
                                    if (statusCell) {
                                        statusCell.innerHTML = `
                                            <i class="fas fa-check-circle me-1"></i>
                                            Leída
                                        `;
                                        statusCell.className = 'text-success';
                                    }
                                    
                                    // Reemplazar botones
                                    const btnGroup = row.querySelector('.btn-group');
                                    if (btnGroup) {
                                        const id = row.getAttribute('data-id');
                                        btnGroup.innerHTML = `
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-id="${id}"
                                                    title="Eliminar notificación">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        `;
                                        
                                        // Agregar evento al nuevo botón
                                        const deleteBtn = btnGroup.querySelector('.delete-btn');
                                        if (deleteBtn) {
                                            deleteBtn.addEventListener('click', function() {
                                                deleteNotification(id, this);
                                            });
                                        }
                                    }
                                });
                                
                                showToast('Todas las notificaciones han sido marcadas como leídas', 'success');
                            } else {
                                showToast('Error al marcar las notificaciones como leídas', 'danger');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('Error al procesar la solicitud', 'danger');
                        });
                    }
                });
            }
            
            // Agregar eventos a los botones de marcar como leída
            document.querySelectorAll('.mark-read-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    markAsRead(id, this);
                });
            });
            
            // Agregar eventos a los botones de eliminar
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    deleteNotification(id, this);
                });
            });
        });
    </script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>

<!-- Scripts adicionales -->
<script>
// Función para marcar notificación como leída
function markAsRead(notificationId, button) {
    fetch('<?= u('notifications/markAsRead') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItem = button.closest('.notification-item');
            notificationItem.classList.remove('bg-light');
            notificationItem.classList.add('fade-in');
            
            // Actualizar el contador de no leídas
            const unreadCount = document.querySelector('.badge.bg-primary');
            if (unreadCount) {
                const count = parseInt(unreadCount.textContent) - 1;
                if (count > 0) {
                    unreadCount.textContent = count;
                } else {
                    unreadCount.remove();
                }
            }
            
            // Cambiar el botón
            button.outerHTML = '<span class="badge bg-light text-dark">Leída</span>';
            
            showToast('Notificación marcada como leída', 'success');
        } else {
            showToast('Error al actualizar la notificación', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    });
}

// Función para eliminar notificación
function deleteNotification(notificationId, button) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta notificación?')) {
        return;
    }
    
    fetch('<?= u('notifications/delete') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationItem = button.closest('.notification-item');
            notificationItem.style.opacity = '0';
            setTimeout(() => {
                notificationItem.remove();
                
                // Actualizar contadores
                const unreadCount = document.querySelector('.badge.bg-primary');
                const isUnread = notificationItem.getAttribute('data-read') === '0';
                
                if (isUnread && unreadCount) {
                    const count = parseInt(unreadCount.textContent) - 1;
                    if (count > 0) {
                        unreadCount.textContent = count;
                    } else {
                        unreadCount.remove();
                    }
                }
                
                // Si no hay más notificaciones, mostrar mensaje
                if (document.querySelectorAll('.notification-item').length === 0) {
                    const container = document.querySelector('.card-body');
                    container.innerHTML = `
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-bell-slash fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No hay notificaciones</h5>
                            <p class="text-muted">Cuando tengas notificaciones nuevas, aparecerán aquí.</p>
                        </div>
                    `;
                }
            }, 300);
            
            showToast('Notificación eliminada', 'success');
        } else {
            showToast('Error al eliminar la notificación', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    });
}

// Función para marcar todas como leídas
document.getElementById('markAllReadBtn')?.addEventListener('click', function() {
    fetch('<?= u('notifications/markAllRead') ?>', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar la interfaz
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('bg-light');
                const readBadge = item.querySelector('.mark-as-read');
                if (readBadge) {
                    readBadge.outerHTML = '<span class="badge bg-light text-dark">Leída</span>';
                }
                item.setAttribute('data-read', '1');
            });
            
            // Actualizar contador
            const unreadBadge = document.querySelector('.badge.bg-primary');
            if (unreadBadge) {
                unreadBadge.remove();
            }
            
            // Deshabilitar botón
            this.disabled = true;
            
            showToast('Todas las notificaciones han sido marcadas como leídas', 'success');
        } else {
            showToast('Error al marcar las notificaciones como leídas', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error de conexión', 'error');
    });
});

// Función para seleccionar/deseleccionar todas las notificaciones
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.notification-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Función para mostrar notificaciones toast
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    
    const icon = icons[type] || 'info-circle';
    
    toast.className = `toast show align-items-center text-white bg-${type} border-0 mb-2`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center">
                <i class="fas fa-${icon} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>`;
    
    // Agregar el toast al contenedor
    toastContainer.appendChild(toast);
    
    // Inicializar el toast de Bootstrap
    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
    bsToast.show();
    
    // Eliminar el toast después de que se oculte
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Filtrar notificaciones por búsqueda
document.getElementById('searchInput')?.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        const text = notification.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            notification.style.display = '';
        } else {
            notification.style.display = 'none';
        }
    });
});

// Filtrar por estado (leídas/no leídas)
document.getElementById('filterStatus')?.addEventListener('change', function() {
    const status = this.value;
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
        const isRead = notification.getAttribute('data-read') === '1';
        
        if (status === 'all' || 
            (status === 'read' && isRead) || 
            (status === 'unread' && !isRead)) {
            notification.style.display = '';
        } else {
            notification.style.display = 'none';
        }
    });
});
</script>

<!-- Estilos adicionales para las notificaciones -->
<style>
    /* Estilos para la tabla de notificaciones */
    .table {
        --bs-table-bg: transparent;
        --bs-table-striped-bg: rgba(0, 0, 0, 0.02);
        --bs-table-hover-bg: rgba(0, 0, 0, 0.03);
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    
    .table thead th {
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 12px 16px;
        background-color: #f8f9fa;
    }
    
    .table tbody tr {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
    }
    
    .table tbody td {
        vertical-align: middle;
        padding: 16px;
        border-top: 1px solid #f0f0f0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .table tbody td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
        border-left: 1px solid #f0f0f0;
    }
    
    .table tbody td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
        border-right: 1px solid #f0f0f0;
    }
    
    .table.table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge {
        font-weight: 500;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        text-transform: none;
        letter-spacing: 0.3px;
    }
    
    .btn-outline-success, .btn-outline-danger {
        transition: all 0.2s ease;
        border-width: 1.5px;
    }
    
    .btn-outline-success:hover, .btn-outline-danger:hover {
        transform: translateY(-1px);
    }
    
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #6c757d;
        border-top: none;
        border-bottom: 1px solid #e9ecef;
    }
    
    .table > :not(:first-child) {
        border-top: none;
    }
    
    .notification-row {
        transition: background-color 0.2s ease;
    }
    
    .notification-row:hover {
        background-color: rgba(0, 0, 0, 0.01) !important;
    }
    
    /* Estilos para los botones de acción */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    /* Estilos para los badges */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }
    
    /* Estilos para el contenedor de toast */
    .toast-container {
        min-width: 300px;
    }
    
    .toast {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>
