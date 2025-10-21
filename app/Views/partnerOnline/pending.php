<?php
// app/Views/partnerOnline/pending.php

$title       = 'Pendientes';
$currentPath = 'partnerOnline/pending';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Pendientes', 'url' => null],
];

$registrations = $registrations ?? [];
$changes       = $changes ?? [];

// Obtener mensajes flash de la sesión
$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
// Limpiar los mensajes flash después de obtenerlos
unset($_SESSION['success'], $_SESSION['error']);

// ---- Contenido ----
ob_start();
?>
  <style>
    /* Table Styles */
    .modern-table {
      border-collapse: separate;
      border-spacing: 0 8px;
      width: 100%;
      margin: 0;
    }
    .modern-table th, .modern-table td { 
      padding: 12px 15px;
      line-height: 1.5;
      vertical-align: middle;
      white-space: nowrap;
    }
    .modern-table thead th { 
      position: sticky;
      top: 0;
      background: #bbae97;
      color: #2a2a2a;
      font-weight: 600;
      font-size: 0.9rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      z-index: 2;
    }
    .modern-table tbody tr { 
      background: #d7cbb5;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .modern-table tbody tr:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .modern-table tbody tr:nth-child(even) { 
      background: #dccaaf;
    }
    .modern-table tbody tr td:first-child{ 
      border-top-left-radius: 10px;
      border-bottom-left-radius: 10px;
    }
    .modern-table tbody tr td:last-child { 
      border-top-right-radius: 10px;
      border-bottom-right-radius: 10px;
    }
    
    /* Table Container */
    .table-container { 
      background: #cfc4b0;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.08);
      overflow: auto;
      margin-bottom: 30px;
    }
    
    /* Section Titles */
    .section-title { 
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 24px 0 16px;
      padding: 10px 0;
      border-bottom: 2px solid #bbae97;
    }
    .section-title h2 {
      margin: 0;
      font-size: 1.4rem;
      color: #2a2a2a;
    }
    .section-title i {
      color: #2a2a2a;
      font-size: 1.3rem;
    }
    
    /* Badge */
    .badge { 
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 28px;
      height: 28px;
      padding: 0 10px;
      border-radius: 14px;
      font-size: 0.8rem;
      font-weight: 600;
      background: #2a2a2a;
      color: #fff;
    }
    
    /* Action Buttons */
    .action-buttons {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .btn-action { 
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      border: none;
      color: #fff;
      padding: 8px 14px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
      font-weight: 500;
      transition: all 0.2s ease;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-action i {
      font-size: 0.9em;
    }
    .btn-approve { 
      background: #28a745;
    }
    .btn-approve:hover { 
      background: #218838;
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .btn-reject { 
      background: #dc3545;
    }
    .btn-reject:hover { 
      background: #c82333;
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    /* Empty State */
    .empty-state {
      padding: 30px 20px;
      text-align: center;
      color: #6c757d;
      font-style: italic;
    }

    /* Comparison Section */
    .comparison-row {
      background: #f8f9fa !important;
      border-top: 2px solid #bbae97;
    }
    
    .comparison-container {
      padding: 20px;
    }
    
    .comparison-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-top: 10px;
    }
    
    .comparison-card {
      background: white;
      border-radius: 8px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .comparison-card h4 {
      margin: 0 0 15px 0;
      padding-bottom: 10px;
      border-bottom: 2px solid #e9ecef;
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 1rem;
    }
    
    .comparison-card.original h4 {
      color: #6c757d;
    }
    
    .comparison-card.modified h4 {
      color: #28a745;
    }
    
    .comparison-field {
      margin-bottom: 12px;
      padding: 8px;
      border-radius: 4px;
      background: #f8f9fa;
    }
    
    .comparison-field.changed {
      background: #fff3cd;
      border-left: 3px solid #ffc107;
    }
    
    .comparison-field-label {
      font-weight: 600;
      color: #495057;
      font-size: 0.85rem;
      margin-bottom: 4px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .comparison-field-value {
      color: #212529;
      font-size: 0.95rem;
    }
    
    .comparison-field.changed .comparison-field-value {
      font-weight: 600;
      color: #d63384;
    }
    
    .change-indicator {
      display: inline-block;
      margin-left: 8px;
      padding: 2px 8px;
      background: #ffc107;
      color: #000;
      border-radius: 12px;
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    /* Responsive Table */
    @media (max-width: 1200px) {
      .table-container {
        overflow-x: auto;
      }
      .modern-table {
        min-width: 1000px;
      }
      .comparison-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <!-- Incluir SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <!-- REGISTROS NUEVOS -->
  <div class="section-title">
    <i class="fas fa-user-plus"></i><h2 style="margin:0;">Registros nuevos</h2>
    <span class="badge"><?= count($registrations) ?> pendiente(s)</span>
  </div>

  <div class="table-container" style="margin-bottom:24px;">
    <table class="modern-table" style="width:100%;">
      <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>CI</th>
            <th>Celular</th>
            <th>Dirección</th>
            <th>Nacimiento</th>
            <th>Email</th>
            <th>Frente</th>
            <th>Dorso</th>
            <th>Creado</th>
            <th>Tipo</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($registrations)): ?>
        <tr><td colspan="12" class="empty-state">No hay registros nuevos pendientes</td></tr>
        <?php else: foreach ($registrations as $r): ?>
        <tr>
            <td><?= (int)($r['idPartnerOnline'] ?? 0) ?></td>
            <td><?= htmlspecialchars($r['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?php if (!empty($r['frontImageURL'])): ?><a href="<?= u($r['frontImageURL']) ?>" target="_blank" class="btn-action" style="background: #6c757d; text-decoration: none;"><i class="fas fa-eye"></i> Ver</a><?php endif; ?></td>
            <td><?php if (!empty($r['backImageURL'])): ?><a href="<?= u($r['backImageURL']) ?>" target="_blank" class="btn-action" style="background: #6c757d; text-decoration: none;"><i class="fas fa-eye"></i> Ver</a><?php endif; ?></td>
            <td><?= htmlspecialchars($r['dateCreation'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td>Registro nuevo</td>
            <td class="action-cell">
            <div class="action-buttons">
                <form action="<?= u('partnerOnline/approve') ?>" method="post" class="approve-form">
                    <input type="hidden" name="id" value="<?= (int)($r['idPartnerOnline'] ?? 0) ?>">
                    <button type="submit" class="btn-action btn-approve" title="Aprobar y crear socio/usuario">
                        <i class="fas fa-check"></i> Aceptar
                    </button>
                </form>
                <form action="<?= u('partnerOnline/disapprove') ?>" method="post" class="reject-form">
                    <input type="hidden" name="id" value="<?= (int)($r['idPartnerOnline'] ?? 0) ?>">
                    <button type="submit" class="btn-action btn-reject" title="Rechazar solicitud">
                        <i class="fas fa-times"></i> Rechazar
                    </button>
                </form>
            </div>
        </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
  </div>
  
  <!-- MODIFICACIONES -->
  <div class="section-title">
    <i class="fas fa-user-edit"></i><h2 style="margin:0;">Solicitudes de modificación</h2>
    <span class="badge"><?= count($changes) ?> pendiente(s)</span>
  </div>

  <div class="table-container">
    <table class="modern-table" style="width:100%;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Fecha Solicitud</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($changes)): ?>
        <tr><td colspan="4" class="empty-state">No hay modificaciones pendientes</td></tr>
        <?php else: 
            require_once __DIR__ . '/../../Models/Partner.php';
            $partnerModel = new Partner();
            
            foreach ($changes as $c): 
                $idUser = (int)($c['idUser'] ?? 0);
                $originalData = null;
                
                // Obtener datos originales del socio
                if ($idUser > 0) {
                    require_once __DIR__ . '/../../Models/Usuario.php';
                    $userModel = new Usuario();
                    $user = $userModel->findById($idUser);
                    
                    if ($user && !empty($user['idPartner'])) {
                        $originalData = $partnerModel->findById((int)$user['idPartner']);
                    }
                }
        ?>
        <!-- Fila principal con resumen -->
        <tr>
            <td><?= (int)($c['idPartnerOnline'] ?? 0) ?></td>
            <td>
                <strong><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong><br>
                <small style="color: #6c757d;">CI: <?= htmlspecialchars($c['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
            </td>
            <td><?= htmlspecialchars($c['dateCreation'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td class="action-cell">
                <div class="action-buttons">
                    <form action="<?= u('partnerOnline/approveChanges') ?>" method="post" class="approve-form">
                        <input type="hidden" name="id" value="<?= (int)($c['idPartnerOnline'] ?? 0) ?>">
                        <button type="submit" class="btn-action btn-approve" title="Aprobar modificación de datos">
                            <i class="fas fa-check"></i> Aceptar Cambios
                        </button>
                    </form>
                    <form action="<?= u('partnerOnline/reject') ?>" method="post" class="reject-form">
                        <input type="hidden" name="id" value="<?= (int)($c['idPartnerOnline'] ?? 0) ?>">
                        <button type="submit" class="btn-action btn-reject" title="Rechazar modificación">
                            <i class="fas fa-times"></i> Rechazar
                        </button>
                    </form>
                </div>
            </td>
        </tr>
        
        <!-- Fila de comparación -->
        <tr class="comparison-row">
            <td colspan="4">
                <div class="comparison-container">
                    <h3 style="margin: 0 0 15px 0; color: #495057; font-size: 1.1rem;">
                        <i class="fas fa-exchange-alt"></i> Comparación de Cambios Solicitados
                    </h3>
                    
                    <?php if ($originalData): ?>
                    <div class="comparison-grid">
                        <!-- DATOS ORIGINALES -->
                        <div class="comparison-card original">
                            <h4>
                                <i class="fas fa-database"></i>
                                Datos Actuales
                            </h4>
                            
                            <div class="comparison-field <?= ($originalData['name'] !== $c['name']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Nombre Completo
                                    <?= ($originalData['name'] !== $c['name']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($originalData['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['ci'] !== $c['ci']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Cédula de Identidad
                                    <?= ($originalData['ci'] !== $c['ci']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($originalData['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['cellPhoneNumber'] !== $c['cellPhoneNumber']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Número de Celular
                                    <?= ($originalData['cellPhoneNumber'] !== $c['cellPhoneNumber']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($originalData['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['address'] !== $c['address']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Dirección
                                    <?= ($originalData['address'] !== $c['address']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($originalData['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['birthday'] !== $c['birthday']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Fecha de Nacimiento
                                    <?= ($originalData['birthday'] !== $c['birthday']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($originalData['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- DATOS NUEVOS -->
                        <div class="comparison-card modified">
                            <h4>
                                <i class="fas fa-edit"></i>
                                Cambios Solicitados
                            </h4>
                            
                            <div class="comparison-field <?= ($originalData['name'] !== $c['name']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Nombre Completo
                                    <?= ($originalData['name'] !== $c['name']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['ci'] !== $c['ci']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Cédula de Identidad
                                    <?= ($originalData['ci'] !== $c['ci']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($c['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['cellPhoneNumber'] !== $c['cellPhoneNumber']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Número de Celular
                                    <?= ($originalData['cellPhoneNumber'] !== $c['cellPhoneNumber']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($c['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['address'] !== $c['address']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Dirección
                                    <?= ($originalData['address'] !== $c['address']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($c['address'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                            
                            <div class="comparison-field <?= ($originalData['birthday'] !== $c['birthday']) ? 'changed' : '' ?>">
                                <div class="comparison-field-label">
                                    Fecha de Nacimiento
                                    <?= ($originalData['birthday'] !== $c['birthday']) ? '<span class="change-indicator">Modificado</span>' : '' ?>
                                </div>
                                <div class="comparison-field-value">
                                    <?= htmlspecialchars($c['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="padding: 20px; text-align: center; color: #dc3545; background: #f8d7da; border-radius: 8px; border: 1px solid #f5c6cb;">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se pudieron cargar los datos originales del socio
                    </div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
  </div>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Interceptar formularios de aceptación
    document.querySelectorAll('.approve-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
          title: '¿Aprobar solicitud?',
          text: "Esta acción no se puede deshacer",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#28a745',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, aprobar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      });
    });

    // Interceptar formularios de rechazo
    document.querySelectorAll('.reject-form').forEach(form => {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        Swal.fire({
          title: '¿Rechazar solicitud?',
          text: "Esta acción no se puede deshacer",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, rechazar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            this.submit();
          }
        });
      });
    });

    // Mostrar alertas de éxito/error si hay mensajes flash
    <?php if (!empty($flashSuccess)): ?>
      Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: '<?= addslashes($flashSuccess) ?>',
        timer: 3000,
        showConfirmButton: false
      });
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '<?= addslashes($flashError) ?>'
      });
    <?php endif; ?>
  });
  </script>
<?php
$content = ob_get_clean();

// Layout principal (incluye tu sidebar dinámico desde BD)
include __DIR__ . '/../layouts/app.php';