<?php
// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 0) != 1) {
    header('Location: /dashboard');
    exit;
}

// Set up variables for the layout
$title = 'Gestión de Competencias - Asociación de Artistas';
$currentPath = 'competence/competence_list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Competencias', 'url' => null],
];

// Start output buffering
ob_start();
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    /* Main content styles */
    body {
        background-color: #f0f2f5;
    }
    
    .content-wrapper {
        background-color: #f0f2f5;
        padding: 25px;
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        margin-bottom: 25px;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Table styles */
    .table {
        width: 100%;
        margin-bottom: 0;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .table th,
    .table td {
        padding: 1rem 1.25rem;
        vertical-align: middle;
        border-top: 1px solid #edf2f7;
    }
    
    .table thead th {
        background-color: #f1f5f9;
        color: #3b4a5c;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #dbe4f0;
    }
    
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8fafc;
    }
    
    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Header styles */
    .header-actions {
        margin-bottom: 1.5rem;
    }
    
    .header-actions .page-title {
        color: rgb(0, 0, 0) !important;
        font-weight: 700 !important;
        margin: 0 !important;
        font-size: 1.75rem !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    /* Button styles */
    .btn-primary {
        background-color: #2563eb;
        border: none;
        padding: 0.6rem 1.75rem;
        font-weight: 500;
        border-radius: 8px;
        transition: all 0.2s;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
    }
    
    .btn-primary:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
    }
    
    /* Action buttons */
    .btn-edit, .btn-delete {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease-in-out;
        margin: 0 3px;
        border: none;
        color: white !important;
        text-shadow: 0 1px 1px rgba(0,0,0,0.2);
    }
    
    .btn-edit {
        background-color: #28a745;
        border: 1px solid #28a745;
    }
    
    .btn-edit:hover {
        background-color: #218838;
        border-color: #1e7e34;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn-delete {
        background-color: #dc3545;
        border: 1px solid #dc3545;
    }
    
    .btn-delete:hover {
        background-color: #c82333;
        border-color: #bd2130;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .btn i {
        margin-right: 5px;
        font-size: 0.9em;
        color: white !important;
    }

    /* Estilo para la tabla */
    .table {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #ddd;
    }

    .table th {
        background-color: #f1f1f1;
        font-weight: bold;
        text-align: center;
        border-bottom: 2px solid #ddd;
        color: #495057;
    }

    .table tbody tr {
        transition: background-color 0.3s ease;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table td {
        vertical-align: middle;
        text-align: center;
        border-top: 1px solid #ddd;
    }

    /* Estilo para las alertas flotantes */
    .notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 350px;
        width: 100%;
    }
    
    .notification {
        position: relative;
        padding: 16px 24px;
        border-radius: 10px;
        color: white;
        display: flex;
        align-items: flex-start;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        transform: translateX(120%);
        animation: slideIn 0.4s forwards;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }
    
    .notification.success {
        background: linear-gradient(135deg, #4CAF50, #45a049);
    }
    
    .notification.error {
        background: linear-gradient(135deg, #f44336, #d32f2f);
    }
    
    .notification .icon {
        font-size: 1.5rem;
        margin-right: 12px;
        margin-top: 2px;
    }
    
    .notification .content {
        flex: 1;
    }
    
    .notification .title {
        font-weight: 600;
        margin-bottom: 4px;
        font-size: 1.1rem;
    }
    
    .notification .message {
        font-size: 0.95rem;
        opacity: 0.9;
        line-height: 1.4;
    }
    
    .notification .close-btn {
        background: none;
        border: none;
        color: white;
        opacity: 0.7;
        cursor: pointer;
        padding: 4px;
        margin-left: 12px;
        transition: opacity 0.2s;
        font-size: 1.2rem;
    }
    
    .notification .close-btn:hover {
        opacity: 1;
    }
    
    .notification .progress-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        width: 100%;
        transform-origin: left;
        animation: progress 4s linear forwards;
    }

    @keyframes slideIn {
        to {
            transform: translateX(0);
        }
    }
    
    @keyframes progress {
        from {
            transform: scaleX(1);
        }
        to {
            transform: scaleX(0);
        }
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid">
        <div class="header-actions d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title">Lista de Competencias</h1>
            <a href="<?= u('competence/create') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-plus"></i> Nueva Competencia
            </a>
        </div>

        <!-- Contenedor principal de competencias -->
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if (empty($competences)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay competencias registradas.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Opción de Menú</th>
                                    <th style="width: 200px;" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($competences as $competence): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-tag text-muted me-2"></i>
                                                <span><?= htmlspecialchars($competence['menuOption']) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="<?= u('competence/update/' . $competence['idCompetence']) ?>" 
                                                   class="btn-edit" 
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteConfirmationModal" data-id="<?= $competence['idCompetence'] ?>" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm" method="POST" action="">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea eliminar la competencia <strong class="text-danger" id="competenceName"></strong>?</p>
                    <p class="text-muted">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        // Mostrar el modal de confirmación de eliminación
        $('#deleteConfirmationModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var idCompetence = button.data('id');
            var modal = $(this);
            var row = button.closest('tr');
            
            // Guardar la referencia a la fila para eliminarla después
            modal.data('row', row);
            
            // Actualizar el ID en el formulario
            modal.data('id', idCompetence);
            
            // Actualizar el nombre de la competencia en el mensaje
            var competenceName = row.find('td:first-child span').text().trim();
            modal.find('#competenceName').text(competenceName);
        });

        // Manejar el envío del formulario de eliminación
        $('#deleteConfirmationModal form').on('submit', function(e) {
            e.preventDefault();
            
            var modal = $('#deleteConfirmationModal');
            var idCompetence = modal.data('id');
            var row = modal.data('row');
            
            // Mostrar indicador de carga
            var submitBtn = modal.find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Eliminando...');
            
            console.log('Enviando solicitud para eliminar competencia ID:', idCompetence);
            
            // Get base URL from the current page
            var baseUrl = window.location.pathname.split('/competence/')[0];
            
            // Enviar la solicitud de eliminación
            $.ajax({
                url: baseUrl + '/competence/delete/' + idCompetence,
                type: 'POST',
                dataType: 'json',
                success: function(response, status, xhr) {
                    console.log('Respuesta del servidor (éxito):', response);
                    console.log('Estado HTTP:', xhr.status);
                    
                    if (response && response.success) {
                        showNotification('success', response.message);
                        
                        // Eliminar la fila de la tabla
                        if (row && row.length) {
                            row.fadeOut(400, function() {
                                $(this).remove();
                                
                                // Si no quedan más filas, mostrar mensaje
                                if ($('table tbody tr').length === 0) {
                                    $('table tbody').html('<tr><td colspan="2" class="text-center">No hay competencias registradas.</td></tr>');
                                }
                            });
                        }
                    } else {
                        var errorMsg = response && response.message ? response.message : 'Error desconocido al eliminar';
                        console.error('Error en la respuesta:', errorMsg);
                        showNotification('danger', errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    var errorMessage = 'Error al procesar la solicitud';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMessage = response.message;
                            if (response.debug) {
                                console.error('Debug info:', response.debug);
                                errorMessage += ' (ver consola para más detalles)';
                            }
                        }
                    } catch (e) {
                        console.error('Error al analizar la respuesta de error:', e);
                        errorMessage = 'Error en el formato de la respuesta del servidor';
                    }
                    
                    showNotification('danger', errorMessage);
                },
                complete: function(xhr) {
                    console.log('Solicitud completada. Estado:', xhr.status);
                    // Cerrar el modal
                    modal.modal('hide');
                    // Restaurar el botón
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Función para mostrar notificaciones
        function showNotification(type, message) {
            var alertClass = 'alert-' + (type === 'danger' ? 'danger' : 'success');
            var icon = type === 'danger' ? 'exclamation-triangle' : 'check-circle';
            
            var alert = $(
                '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
                '   <i class="fas fa-' + icon + ' me-2"></i>' +
                '   ' + message +
                '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>' +
                '</div>'
            );
            
            $('.notification-container').html(alert);
            
            // Eliminar la alerta después de 5 segundos
            setTimeout(function() {
                alert.fadeOut(400, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    });
</script>

<div class="notification-container"></div>
