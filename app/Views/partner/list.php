<?php
// app/Views/partner/list.php

$title       = 'Socios';
$currentPath = 'partner/list'; // para marcar activo en el menú
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Socios', 'url' => null],
];

// Métricas simples
$totalSocios    = is_array($socios ?? null) ? count($socios) : 0;
$nuevosEsteAnio = 0;
if (!empty($socios) && is_array($socios)) {
    $anio = date('Y');
    foreach ($socios as $s) {
        $dr = $s['dateRegistration'] ?? null;
        if ($dr && date('Y', strtotime($dr)) === $anio) { $nuevosEsteAnio++; }
    }
}

// ---- Contenido específico de la página ----
ob_start();
?>
  <!-- Estilos optimizados para mejor uso del espacio -->
  <style>
    .modern-table th, .modern-table td {
      padding: 8px 12px;
      line-height: 1.3;
      vertical-align: middle;
      color: #000000;
      font-size: 0.875rem;
    }
    .modern-table { 
      border-collapse: separate; 
      border-spacing: 0 6px; 
      table-layout: fixed;
      width: 100%;
    }
    .modern-table thead th {
      position: sticky; top: 0;
      background: #bbae97; color: #2a2a2a;
      z-index: 2;
      font-weight: 600;
      font-size: 0.8rem;
    }
    .modern-table tbody tr { background:#d7cbb5; transition: all 0.2s ease; }
    .modern-table tbody tr:hover { background: #d0c4a8; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    .modern-table tbody tr:nth-child(even) { background: #dccaaf; }
    .modern-table tbody tr:nth-child(even):hover { background: #d5c9b0; }
    .modern-table tbody tr td:first-child  { border-top-left-radius:10px; border-bottom-left-radius:10px; }
    .modern-table tbody tr td:last-child   { border-top-right-radius:10px; border-bottom-right-radius:10px; }

    /* Ancho específico de columnas para optimizar espacio */
    .col-name { width: 20%; }
    .col-ci { width: 12%; }
    .col-contact { width: 25%; }
    .col-phone { width: 12%; }
    .col-images { width: 15%; }
    .col-actions { width: 16%; }

    /* contenedor de tabla */
    .table-container { 
      background:#cfc4b0;
      border-radius:16px;
      box-shadow:0 10px 30px rgba(0,0,0,.08);
      overflow: hidden;
    }
    
    .table-wrapper {
      overflow-x: auto;
      max-width: 100%;
    }
    
    /* Filas ocultas por búsqueda */
    .hidden { display: none !important; }
    
    /* Resaltar texto encontrado */
    .highlight {
      background-color: yellow;
      font-weight: bold;
      padding: 2px 4px;
      border-radius: 3px;
    }
    
    /* Info de búsqueda */
    .search-info {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 12px 16px;
      margin-bottom: 12px;
      font-size: 14px;
      color: #495057;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Optimización para contacto - mostrar en líneas múltiples */
    .contact-info {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }

    .contact-item {
      font-size: 0.75rem;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .contact-label {
      font-weight: 600;
      color: #666;
      text-transform: uppercase;
      font-size: 0.65rem;
      letter-spacing: 0.5px;
    }

    /* Botones para ver imágenes CI - más compactos */
    .ci-view-btn {
      display: inline-flex;
      align-items: center;
      gap: 3px;
      padding: 4px 8px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 0.7rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      margin-bottom: 3px;
      width: 100%;
      justify-content: center;
    }

    .ci-view-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
      color: white;
      text-decoration: none;
    }

    .ci-view-btn.front {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .ci-view-btn.back {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }

    .ci-buttons-container {
      display: flex;
      flex-direction: column;
      gap: 3px;
    }

    /* Botones de acciones reorganizados */
    .actions-container {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    .action-row {
      display: flex;
      gap: 4px;
      justify-content: center;
    }

    .action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 3px;
      padding: 6px 10px;
      border: none;
      border-radius: 6px;
      font-size: 0.7rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      min-width: 60px;
    }

    .action-btn:hover {
      transform: translateY(-1px);
      text-decoration: none;
    }

    .btn-edit {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }

    .btn-edit:hover {
      box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
      color: white;
    }

    .btn-delete {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
      color: white;
      box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
    }

    .btn-delete:hover {
      box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
      color: white;
    }

    /* Botón de detalles */
    .details-btn {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 6px 12px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 0.7rem;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
      width: 100%;
      justify-content: center;
    }

    .details-btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
      color: white;
      text-decoration: none;
    }

    /* Avatar del usuario más pequeño */
    .user-avatar-small {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--cream-200,#eee) 0%, var(--cream-300,#ddd) 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      flex-shrink: 0;
    }

    .user-cell {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .user-name {
      font-weight: 500;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .ci-badge {
      font-family: monospace; 
      background: #f8f9fa; 
      padding: 3px 6px; 
      border-radius: 4px;
      font-size: 0.75rem;
      font-weight: 600;
    }

    /* Estilo para campos sin información */
    .no-info {
      color: #6c757d;
      font-style: italic;
      font-weight: 400;
      opacity: 0.8;
    }

    .contact-item .no-info {
      font-size: 0.7rem;
    }

    /* Modal para mostrar imágenes */
    .image-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.9);
      z-index: 2000;
      justify-content: center;
      align-items: center;
    }

    .image-modal-content {
      position: relative;
      max-width: 90%;
      max-height: 90%;
      background: white;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .image-modal img {
      max-width: 100%;
      max-height: 70vh;
      object-fit: contain;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .modal-close {
      position: absolute;
      top: 10px;
      right: 15px;
      background: #ff4757;
      color: white;
      border: none;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .modal-close:hover {
      background: #ff3742;
      transform: scale(1.1);
    }

    .modal-title {
      text-align: center;
      margin-bottom: 15px;
      color: #333;
      font-size: 18px;
      font-weight: 600;
    }

    .no-image-placeholder {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 300px;
      height: 200px;
      background: #f8f9fa;
      border: 2px dashed #dee2e6;
      border-radius: 8px;
      color: #6c757d;
      font-size: 14px;
    }

    .no-image-placeholder i {
      font-size: 48px;
      margin-bottom: 10px;
      opacity: 0.5;
    }

    /* Modal de detalles del socio */
    .details-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 1500;
      justify-content: center;
      align-items: center;
      padding: 20px;
      overflow-y: auto;
    }

    .details-modal-content {
      background: white;
      border-radius: 16px;
      width: 100%;
      max-width: 900px;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      position: relative;
    }

    .details-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 24px 30px;
      border-radius: 16px 16px 0 0;
      position: relative;
    }

    .details-header h2 {
      margin: 0;
      font-size: 1.8rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .details-close {
      position: absolute;
      top: 20px;
      right: 20px;
      background: rgba(255,255,255,0.2);
      color: white;
      border: none;
      border-radius: 50%;
      width: 36px;
      height: 36px;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .details-close:hover {
      background: rgba(255,255,255,0.3);
      transform: scale(1.1);
    }

    .details-body {
      padding: 30px;
    }

    .details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .details-section {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .details-section h3 {
      color: #495057;
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0 0 15px 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .details-field {
      margin-bottom: 12px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }

    .details-field:last-child {
      margin-bottom: 0;
    }

    .details-label {
      font-weight: 600;
      color: #495057;
      min-width: 120px;
      font-size: 0.9rem;
    }

    .details-value {
      color: #212529;
      font-size: 0.9rem;
      word-break: break-word;
    }

    .details-images {
      background: #f8f9fa;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .details-images h3 {
      color: #495057;
      font-size: 1.2rem;
      font-weight: 600;
      margin: 0 0 20px 0;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .ci-images-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .ci-image-card {
      background: white;
      border-radius: 8px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .ci-image-card h4 {
      margin: 0 0 10px 0;
      color: #495057;
      font-size: 1rem;
      font-weight: 600;
    }

    .details-actions {
      background: #f8f9fa;
      border-top: 1px solid #e9ecef;
      padding: 20px 30px;
      border-radius: 0 0 16px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 15px;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: #6c757d;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      background: #5a6268;
      transform: translateY(-1px);
      color: white;
      text-decoration: none;
    }

    .btn-edit-modal {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-edit-modal:hover {
      background: #218838;
      transform: translateY(-1px);
      color: white;
      text-decoration: none;
    }

    /* Responsive design */
    @media (max-width: 1200px) {
      .modern-table th, .modern-table td {
        padding: 6px 8px;
        font-size: 0.8rem;
      }
      
      .contact-item {
        font-size: 0.7rem;
      }
      
      .action-btn {
        padding: 4px 6px;
        font-size: 0.65rem;
      }
    }

    @media (max-width: 768px) {
      .details-grid {
        grid-template-columns: 1fr;
        gap: 20px;
      }
      
      .ci-images-grid {
        grid-template-columns: 1fr;
      }
      
      .details-actions {
        flex-direction: column;
      }
      
      .details-modal-content {
        margin: 10px;
        max-height: calc(100vh - 20px);
      }
      
      .table-wrapper {
        border-radius: 16px;
      }
    }
  </style>

  <!-- Barra de acciones -->
  <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
    <div class="search-container" style="position:relative;flex:1 1 320px;">
      <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar por nombre, CI, login, email, celular..."
        style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:12px 40px 12px 38px;outline:none;background:#fff;transition:border-color .2s;"
        onfocus="this.style.borderColor='var(--cream-400)';"
        onblur="this.style.borderColor='#e1e5e9';"
      />
    </div>

    <div style="display:flex;gap:12px;">
      <button id="exportPdfBtn" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:#6c757d;color:#fff;border:none;border-radius:12px;padding:12px 16px;font-weight:600;cursor:pointer;transition: all 0.3s ease;">
        <i class="fas fa-file-pdf"></i> Exportar PDF
      </button>
      
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:12px 16px;text-decoration:none;font-weight:600;transition: all 0.3s ease;">
        <i class="fas fa-plus"></i> Nuevo Socio
      </a>
    </div>
  </div>

  <!-- Info de búsqueda -->
  <div id="searchInfo" class="search-info" style="display:none;"></div>

  <!-- Tabla de socios optimizada -->
  <?php if (!empty($socios) && is_array($socios)): ?>
    <div class="table-container">
      <div class="table-wrapper">
        <table id="tablaSocios" class="modern-table">
          <thead>
            <tr>
              <th class="col-name"><i class="fas fa-user"></i> Socio</th>
              <th class="col-ci"><i class="fas fa-id-card"></i> CI</th>
              <th class="col-contact"><i class="fas fa-address-book"></i> Contacto</th>
              <th class="col-phone"><i class="fas fa-phone"></i> Teléfono</th>
              <th class="col-images"><i class="fas fa-images"></i> CI Docs</th>
              <th class="col-actions"><i class="fas fa-cogs"></i> Acciones</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php foreach ($socios as $socio): ?>
              <tr class="socio-row" data-search="<?= htmlspecialchars(strtolower(
                ($socio['name'] ?? '') . ' ' . 
                ($socio['ci'] ?? '') . ' ' . 
                ($socio['login'] ?? '') . ' ' . 
                ($socio['email'] ?? '') . ' ' . 
                ($socio['cellPhoneNumber'] ?? '')
              )) ?>" 
              data-partner='<?= htmlspecialchars(json_encode($socio), ENT_QUOTES, 'UTF-8') ?>'>
                
                <!-- Columna Socio (Nombre + Avatar) -->
                <td class="col-name">
                  <div class="user-cell">
                    <div class="user-avatar-small">
                      <i class="fas fa-user" style="color: #666; font-size: 0.8rem;"></i>
                    </div>
                    <span class="searchable-text user-name" title="<?= htmlspecialchars($socio['name'] ?? 'Sin información') ?>">
                      <?= htmlspecialchars($socio['name'] ?? 'Sin información') ?>
                    </span>
                  </div>
                </td>
                
                <!-- Columna CI -->
                <td class="col-ci">
                  <span class="searchable-text ci-badge">
                    <?= htmlspecialchars($socio['ci'] ?? 'Sin información') ?>
                  </span>
                </td>
                
                <!-- Columna Contacto (Login + Email) -->
                <td class="col-contact">
                  <div class="contact-info">
                    <div class="contact-item">
                      <span class="contact-label">Usuario:</span>
                      <span class="searchable-text" title="<?= htmlspecialchars($socio['login'] ?? 'Sin información') ?>">
                        <?= htmlspecialchars($socio['login'] ?? 'Sin información') ?>
                      </span>
                    </div>
                    <div class="contact-item">
                      <span class="contact-label">Email:</span>
                      <span class="searchable-text" title="<?= htmlspecialchars($socio['email'] ?? 'Sin información') ?>">
                        <?= htmlspecialchars($socio['email'] ?? 'Sin información') ?>
                      </span>
                    </div>
                  </div>
                </td>
                
                <!-- Columna Teléfono -->
                <td class="col-phone">
                  <span class="searchable-text" style="font-weight: 500;">
                    <?= htmlspecialchars($socio['cellPhoneNumber'] ?? 'Sin información') ?>
                  </span>
                </td>
                
                <!-- Columna Imágenes CI -->
                <td class="col-images">
                  <div class="ci-buttons-container">
                    <button class="ci-view-btn front" 
                            onclick="showImageModal('<?= htmlspecialchars($socio['frontImageURL'] ?? '') ?>', 'CI Frente - <?= htmlspecialchars($socio['name'] ?? '') ?>')">
                      <i class="fas fa-eye"></i> Frente
                    </button>
                    <button class="ci-view-btn back" 
                            onclick="showImageModal('<?= htmlspecialchars($socio['backImageURL'] ?? '') ?>', 'CI Atrás - <?= htmlspecialchars($socio['name'] ?? '') ?>')">
                      <i class="fas fa-eye"></i> Atrás
                    </button>
                  </div>
                </td>
                
                <!-- Columna Acciones -->
                <td class="col-actions">
                  <div class="actions-container">
                    <!-- Fila superior: Editar + Eliminar -->
                    <div class="action-row">
                      <a href="<?= u('partner/edit/' . (int)($socio['idPartner'] ?? 0)) ?>" 
                        class="action-btn btn-edit" 
                        title="Editar socio">
                        <i class="fas fa-edit"></i> Editar
                      </a>

                      <a href="#"
                        class="action-btn btn-delete delete-btn"
                        title="Eliminar socio"
                        data-id="<?= (int)($socio['idPartner'] ?? 0) ?>"
                        data-name="<?= htmlspecialchars($socio['name'] ?? '') ?>">
                        <i class="fas fa-trash"></i> Eliminar
                      </a>
                    </div>
                    
                    <!-- Fila inferior: Detalles -->
                    <div class="action-row">
                      <button class="details-btn" onclick="showDetailsModal(this)" title="Ver detalles completos">
                        <i class="fas fa-info-circle"></i> Detalles
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Controles de paginación -->
      <div id="pager" style="display:flex;align-items:center;gap:12px;justify-content:space-between;padding:16px 20px;background:#f8f9fa;border-radius:0 0 16px 16px;">
        <div style="display:flex;align-items:center;gap:8px;">
          <label for="pageSize" style="font-weight: 500; color: #495057;">Por página:</label>
          <select id="pageSize" style="border:1px solid #ced4da;border-radius:6px;padding:6px 10px;background: white;">
            <option value="10">10</option>
            <option value="20" selected>20</option>
            <option value="50">50</option>
            <option value="100">100</option>
          </select>
        </div>

        <div style="display:flex;align-items:center;gap:8px;">
          <button id="firstPage" style="border:1px solid #ced4da;border-radius:6px;padding:8px 12px;background:#fff;cursor:pointer;transition: all 0.2s ease;">«</button>
          <button id="prevPage"  style="border:1px solid #ced4da;border-radius:6px;padding:8px 12px;background:#fff;cursor:pointer;transition: all 0.2s ease;">‹</button>
          <span id="pageInfo" style="min-width:200px;text-align:center;font-weight:600;color:#495057;"></span>
          <button id="nextPage"  style="border:1px solid #ced4da;border-radius:6px;padding:8px 12px;background:#fff;cursor:pointer;transition: all 0.2s ease;">›</button>
          <button id="lastPage"  style="border:1px solid #ced4da;border-radius:6px;padding:8px 12px;background:#fff;cursor:pointer;transition: all 0.2s ease;">»</button>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:60px 20px;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);">
      <div class="empty-state-icon" style="font-size:64px;margin-bottom:20px;color:var(--cream-600);"><i class="fas fa-users"></i></div>
      <h3 style="color: #495057; margin-bottom: 10px;">No hay socios registrados</h3>
      <p style="color: #6c757d; margin-bottom: 30px;">Comienza agregando tu primer socio al sistema</p>
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:12px 20px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Crear primer socio
      </a>
    </div>
  <?php endif; ?>

  <!-- Modal para mostrar imágenes del CI -->
  <div id="imageModal" class="image-modal">
    <div class="image-modal-content">
      <button class="modal-close" onclick="closeImageModal()">×</button>
      <div class="modal-title" id="modalTitle"></div>
      <div id="modalImageContainer"></div>
    </div>
  </div>

  <!-- Modal de detalles del socio -->
  <div id="detailsModal" class="details-modal">
    <div class="details-modal-content">
      <div class="details-header">
        <h2 id="detailsTitle">
          <i class="fas fa-user-circle"></i>
          Detalles del Socio
        </h2>
        <button class="details-close" onclick="closeDetailsModal()">×</button>
      </div>
      
      <div class="details-body">
        <div class="details-grid">
          <div class="details-section">
            <h3><i class="fas fa-user"></i> Información Personal</h3>
            <div class="details-field">
              <span class="details-label">Nombre:</span>
              <span class="details-value" id="detailName">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">CI:</span>
              <span class="details-value" id="detailCI">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">Teléfono:</span>
              <span class="details-value" id="detailPhone">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">Fecha Nac.:</span>
              <span class="details-value" id="detailBirthday">-</span>
            </div>
          </div>
          
          <div class="details-section">
            <h3><i class="fas fa-envelope"></i> Información de Contacto</h3>
            <div class="details-field">
              <span class="details-label">Email:</span>
              <span class="details-value" id="detailEmail">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">Login:</span>
              <span class="details-value" id="detailLogin">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">Dirección:</span>
              <span class="details-value" id="detailAddress">-</span>
            </div>
            <div class="details-field">
              <span class="details-label">Estado:</span>
              <span class="details-value" id="detailStatus">-</span>
            </div>
          </div>
        </div>
        
        <div class="details-section">
          <h3><i class="fas fa-calendar"></i> Fechas Importantes</h3>
          <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="details-field" style="flex-direction: column; align-items: flex-start;">
              <span class="details-label">Fecha de Creación:</span>
              <span class="details-value" id="detailDateCreation">-</span>
            </div>
            <div class="details-field" style="flex-direction: column; align-items: flex-start;">
              <span class="details-label">Fecha de Registro:</span>
              <span class="details-value" id="detailDateRegistration">-</span>
            </div>
            <div class="details-field" style="flex-direction: column; align-items: flex-start;">
              <span class="details-label">Fecha de Nacimiento:</span>
              <span class="details-value" id="detailBirthdayFull">-</span>
            </div>
          </div>
        </div>

        <div class="details-images">
          <h3><i class="fas fa-id-badge"></i> Imágenes del CI</h3>
          <div class="ci-images-grid">
            <div class="ci-image-card">
              <h4>CI - Lado Frontal</h4>
              <div id="detailFrontImage">
                <button class="ci-view-btn front" onclick="showImageFromDetails('front')" style="margin: 10px 0;">
                  <i class="fas fa-eye"></i> Ver Imagen Frontal
                </button>
              </div>
            </div>
            <div class="ci-image-card">
              <h4>CI - Lado Posterior</h4>
              <div id="detailBackImage">
                <button class="ci-view-btn back" onclick="showImageFromDetails('back')" style="margin: 10px 0;">
                  <i class="fas fa-eye"></i> Ver Imagen Posterior
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="details-actions">
        <button class="btn-back" onclick="closeDetailsModal()">
          <i class="fas fa-arrow-left"></i>
          Volver a la Lista
        </button>
        <div>
          <a href="#" id="detailEditLink" class="btn-edit-modal">
            <i class="fas fa-edit"></i>
            Editar Socio
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript para funcionalidad de imágenes y detalles -->
  <script>
    // Variables globales para el modal de detalles
    let currentPartnerData = null;

    // Función para mostrar modal de imagen
    function showImageModal(imagePath, title) {
      const modal = document.getElementById('imageModal');
      const modalTitle = document.getElementById('modalTitle');
      const modalContainer = document.getElementById('modalImageContainer');
      
      modalTitle.textContent = title;
      
      // Limpiar contenedor
      modalContainer.innerHTML = '';
      
      if (imagePath && imagePath.trim() !== '') {
        // Construir la URL completa de la imagen
        const fullImagePath = '<?= u("") ?>' + imagePath;
        
        const img = document.createElement('img');
        img.src = fullImagePath;
        img.alt = title;
        
        // Manejar error de carga de imagen
        img.onerror = function() {
          modalContainer.innerHTML = `
            <div class="no-image-placeholder">
              <i class="fas fa-image"></i>
              <span>Imagen no disponible</span>
              <small style="margin-top: 5px; opacity: 0.7;">Ruta: ${imagePath}</small>
            </div>
          `;
        };
        
        modalContainer.appendChild(img);
      } else {
        // Sin imagen disponible
        modalContainer.innerHTML = `
          <div class="no-image-placeholder">
            <i class="fas fa-image"></i>
            <span>Sin imagen disponible</span>
          </div>
        `;
      }
      
      modal.style.display = 'flex';
    }
    
    // Función para cerrar modal de imagen
    function closeImageModal() {
      const modal = document.getElementById('imageModal');
      modal.style.display = 'none';
    }

    // Función para mostrar modal de detalles
    function showDetailsModal(button) {
      const row = button.closest('.socio-row');
      const partnerDataStr = row.getAttribute('data-partner');
      
      try {
        currentPartnerData = JSON.parse(partnerDataStr);
        populateDetailsModal(currentPartnerData);
        
        const modal = document.getElementById('detailsModal');
        modal.style.display = 'flex';
        
        // Animar entrada del modal
        const modalContent = modal.querySelector('.details-modal-content');
        modalContent.style.transform = 'scale(0.8)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
          modalContent.style.transition = 'all 0.3s ease';
          modalContent.style.transform = 'scale(1)';
          modalContent.style.opacity = '1';
        }, 10);
        
      } catch (e) {
        console.error('Error al parsear datos del socio:', e);
        alert('Error al cargar los detalles del socio');
      }
    }

    // Función para poblar el modal de detalles
    function populateDetailsModal(partner) {
      // Función auxiliar para formatear fechas
      const formatDate = (dateString) => {
        if (!dateString) return 'Sin información';
        try {
          const date = new Date(dateString);
          return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
        } catch (e) {
          return 'Sin información';
        }
      };

      const formatDateTime = (dateString) => {
        if (!dateString) return 'Sin información';
        try {
          const date = new Date(dateString);
          return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
        } catch (e) {
          return 'Sin información';
        }
      };

      // Función auxiliar para mostrar valor o "Sin información"
      const displayValue = (value) => {
        return value && value.trim() !== '' ? value : 'Sin información';
      };

      // Actualizar título del modal
      document.getElementById('detailsTitle').innerHTML = `
        <i class="fas fa-user-circle"></i>
        Detalles del Socio: ${partner.name || 'Sin nombre'}
      `;

      // Información personal
      document.getElementById('detailName').textContent = displayValue(partner.name);
      document.getElementById('detailCI').textContent = displayValue(partner.ci);
      document.getElementById('detailPhone').textContent = displayValue(partner.cellPhoneNumber);
      document.getElementById('detailBirthday').textContent = formatDate(partner.birthday);

      // Información de contacto
      document.getElementById('detailEmail').textContent = displayValue(partner.email);
      document.getElementById('detailLogin').textContent = displayValue(partner.login);
      document.getElementById('detailAddress').textContent = displayValue(partner.address);
      
      // Estado del usuario
      const userStatus = partner.userStatus == 1 ? 'Activo' : 'Inactivo';
      document.getElementById('detailStatus').innerHTML = `
        <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500; 
                     background: ${partner.userStatus == 1 ? '#d4edda' : '#f8d7da'}; 
                     color: ${partner.userStatus == 1 ? '#155724' : '#721c24'};">
          ${userStatus}
        </span>
      `;

      // Fechas importantes
      document.getElementById('detailDateCreation').textContent = formatDateTime(partner.dateCreation);
      document.getElementById('detailDateRegistration').textContent = formatDate(partner.dateRegistration);
      document.getElementById('detailBirthdayFull').textContent = formatDate(partner.birthday);

      // Enlace de edición
      document.getElementById('detailEditLink').href = '<?= u("partner/edit/") ?>' + (partner.idPartner || 0);
    }

    // Función para mostrar imagen desde el modal de detalles
    function showImageFromDetails(type) {
      if (!currentPartnerData) return;
      
      const imagePath = type === 'front' ? currentPartnerData.frontImageURL : currentPartnerData.backImageURL;
      const title = `CI ${type === 'front' ? 'Frente' : 'Atrás'} - ${currentPartnerData.name || 'Socio'}`;
      
      showImageModal(imagePath, title);
    }

    // Función para cerrar modal de detalles
    function closeDetailsModal() {
      const modal = document.getElementById('detailsModal');
      const modalContent = modal.querySelector('.details-modal-content');
      
      // Animar salida del modal
      modalContent.style.transition = 'all 0.3s ease';
      modalContent.style.transform = 'scale(0.8)';
      modalContent.style.opacity = '0';
      
      setTimeout(() => {
        modal.style.display = 'none';
        modalContent.style.transform = 'scale(1)';
        modalContent.style.opacity = '1';
      }, 300);
    }
    
    // Cerrar modales al hacer click fuera
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeImageModal();
      }
    });

    document.getElementById('detailsModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeDetailsModal();
      }
    });
    
    // Cerrar modales con tecla Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeImageModal();
        closeDetailsModal();
      }
    });
  </script>

  <!-- Incluir SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    .swal2-popup {
      border-radius: 16px !important;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
    }
    
    .swal2-title {
      color: #2a2a2a !important;
      font-size: 1.5rem !important;
      font-weight: 600 !important;
    }
    
    .swal2-html-container {
      color: #555 !important;
      font-size: 1rem !important;
    }
    
    .swal2-confirm {
      background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 10px 24px !important;
      font-weight: 600 !important;
    }
    
    .swal2-cancel {
      background: #f1f1f1 !important;
      color: #333 !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 10px 24px !important;
      font-weight: 600 !important;
    }
    
    .swal2-icon {
      border-color: #e74c3c !important;
      color: #e74c3c !important;
    }
  </style>

  <!-- Buscador en vivo + paginación -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Variables globales
      let allRows = [];
      let filteredRows = [];
      let currentPage = 1;
      let pageSize = 20;
      
      // Elementos del DOM
      const searchInput = document.getElementById('searchInput');
      const searchInfo = document.getElementById('searchInfo');
      const tableBody = document.getElementById('tableBody');
      const pageInfo = document.getElementById('pageInfo');
      const pageSizeSelect = document.getElementById('pageSize');
      const firstPageBtn = document.getElementById('firstPage');
      const prevPageBtn = document.getElementById('prevPage');
      const nextPageBtn = document.getElementById('nextPage');
      const lastPageBtn = document.getElementById('lastPage');
      
      // Inicializar
      function init() {
        allRows = Array.from(document.querySelectorAll('.socio-row'));
        filteredRows = [...allRows];
        updatePagination();
        
        // Configurar event listeners para botones de eliminar
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            confirmDelete(id, name);
          });
        });

        // Agregar efectos hover a los botones de paginación
        [firstPageBtn, prevPageBtn, nextPageBtn, lastPageBtn].forEach(btn => {
          btn.addEventListener('mouseenter', function() {
            if (!this.disabled) {
              this.style.background = '#e9ecef';
              this.style.transform = 'translateY(-1px)';
            }
          });
          btn.addEventListener('mouseleave', function() {
            if (!this.disabled) {
              this.style.background = '#fff';
              this.style.transform = 'translateY(0)';
            }
          });
        });
      }
      
      // Función de confirmación de eliminación con SweetAlert
      function confirmDelete(id, name) {
        Swal.fire({
          title: '¿Estás seguro?',
          html: `Vas a eliminar al socio: <strong>${name}</strong><br><br>Esta acción no se puede deshacer.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e74c3c',
          cancelButtonColor: '#95a5a6',
          confirmButtonText: 'Sí, eliminar',
          cancelButtonText: 'Cancelar',
          reverseButtons: true,
          customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-html',
            confirmButton: 'custom-swal-confirm',
            cancelButton: 'custom-swal-cancel'
          },
          buttonsStyling: false,
          showLoaderOnConfirm: true,
          preConfirm: () => {
            return new Promise((resolve) => {
              // Redirigir para eliminar
              window.location.href = '<?= u("partner/delete/") ?>' + id + '?return_url=' + encodeURIComponent(window.location.pathname + window.location.search);
            });
          },
          allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
          if (result.dismiss === Swal.DismissReason.cancel) {
            Swal.fire({
              title: 'Cancelado',
              text: 'El socio no ha sido eliminado',
              icon: 'info',
              confirmButtonColor: '#3498db',
              confirmButtonText: 'Entendido'
            });
          }
        });
      }
      
      // Función de búsqueda
      function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        // Limpiar highlights previos
        clearHighlights();
        
        if (searchTerm === '') {
          // Mostrar todas las filas
          filteredRows = [...allRows];
          searchInfo.style.display = 'none';
        } else {
          // Filtrar filas
          filteredRows = allRows.filter(row => {
            const searchData = row.getAttribute('data-search');
            return searchData.includes(searchTerm);
          });
          
          // Resaltar términos encontrados
          if (filteredRows.length > 0) {
            highlightSearchTerm(searchTerm);
          }
          
          // Mostrar info de búsqueda
          searchInfo.style.display = 'block';
          searchInfo.innerHTML = `
            <i class="fas fa-search" style="margin-right: 8px;"></i>
            Se encontraron <strong>${filteredRows.length}</strong> resultado(s) para "<strong>${searchTerm}</strong>"
          `;
        }
        
        currentPage = 1;
        updatePagination();
      }
      
      // Resaltar término de búsqueda
      function highlightSearchTerm(term) {
        filteredRows.forEach(row => {
          const textElements = row.querySelectorAll('.searchable-text');
          textElements.forEach(element => {
            const text = element.textContent;
            const regex = new RegExp(`(${term})`, 'gi');
            element.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
          });
        });
      }
      
      // Limpiar highlights
      function clearHighlights() {
        allRows.forEach(row => {
          const highlightedElements = row.querySelectorAll('.highlight');
          highlightedElements.forEach(element => {
            element.outerHTML = element.textContent;
          });
        });
      }
      
      // Actualizar paginación
      function updatePagination() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / pageSize);
        
        if (totalPages === 0) {
          currentPage = 1;
        } else if (currentPage > totalPages) {
          currentPage = totalPages;
        }
        
        // Ocultar todas las filas
        allRows.forEach(row => {
          row.classList.add('hidden');
        });
        
        // Mostrar filas de la página actual
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const rowsToShow = filteredRows.slice(startIndex, endIndex);
        
        rowsToShow.forEach(row => {
          row.classList.remove('hidden');
        });
        
        // Actualizar controles de paginación
        pageInfo.textContent = `Página ${currentPage} de ${totalPages} (${totalRows} registros)`;
        
        firstPageBtn.disabled = currentPage <= 1;
        prevPageBtn.disabled = currentPage <= 1;
        nextPageBtn.disabled = currentPage >= totalPages || totalPages === 0;
        lastPageBtn.disabled = currentPage >= totalPages || totalPages === 0;
        
        // Actualizar estilos de botones deshabilitados
        [firstPageBtn, prevPageBtn, nextPageBtn, lastPageBtn].forEach(btn => {
          btn.style.opacity = btn.disabled ? '0.5' : '1';
          btn.style.cursor = btn.disabled ? 'not-allowed' : 'pointer';
        });
      }
      
      // Event listeners
      searchInput.addEventListener('input', performSearch);
      
      pageSizeSelect.addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        updatePagination();
      });
      
      firstPageBtn.addEventListener('click', function() {
        if (!this.disabled) {
          currentPage = 1;
          updatePagination();
        }
      });
      
      prevPageBtn.addEventListener('click', function() {
        if (!this.disabled) {
          currentPage--;
          updatePagination();
        }
      });
      
      nextPageBtn.addEventListener('click', function() {
        if (!this.disabled) {
          currentPage++;
          updatePagination();
        }
      });
      
      lastPageBtn.addEventListener('click', function() {
        if (!this.disabled) {
          const totalPages = Math.ceil(filteredRows.length / pageSize);
          currentPage = totalPages;
          updatePagination();
        }
      });
      
      // Inicializar la tabla
      init();
    });
  </script>

  <!-- Exportación PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Add click event to the export button
      document.getElementById('exportPdfBtn').addEventListener('click', async function() {
          // Show loading state
          const button = this;
          const originalText = button.innerHTML;
          button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando PDF...';
          button.disabled = true;
          
          try {
              // Usar los datos ya cargados en lugar de hacer fetch
              const partners = <?= json_encode($socios ?? []) ?>;
              
              if (partners.length === 0) {
                  throw new Error('No se encontraron socios para exportar');
              }
              
              // Create a new PDF document
              const { jsPDF } = window.jspdf;
              const doc = new jsPDF({
                  orientation: 'landscape'
              });
              
              // Add title and date
              doc.setFontSize(20);
              doc.text('Lista Completa de Socios', 15, 15);
              
              doc.setFontSize(10);
              doc.text('Generado el: ' + new Date().toLocaleDateString(), 15, 25);
              
              // Table headers
              const headers = ['Nombre', 'CI', 'Usuario', 'Correo', 'Teléfono', 'Dirección', 'F. Nac.', 'F. Reg.', 'F. Creación'];
              const columnPositions = [10, 50, 75, 100, 135, 170, 210, 235, 260];
              
              // Add table headers
              doc.setFontSize(8);
              doc.setFont('helvetica', 'bold');
              headers.forEach((header, i) => {
                  doc.text(header, columnPositions[i], 35);
              });
              
              // Add horizontal line
              doc.setDrawColor(0);
              doc.setLineWidth(0.5);
              doc.line(15, 37, 280, 37);
              
              // Add table rows
              doc.setFont('helvetica', 'normal');
              doc.setFontSize(7);
              
              let y = 45;
              partners.forEach((partner, index) => {
                  if (y > 180) {
                      doc.addPage();
                      y = 20;
                      
                      // Add headers to new page
                      doc.setFontSize(8);
                      doc.setFont('helvetica', 'bold');
                      headers.forEach((header, i) => {
                          doc.text(header, columnPositions[i], y);
                      });
                      doc.line(15, y + 2, 280, y + 2);
                      y = 30;
                      doc.setFont('helvetica', 'normal');
                      doc.setFontSize(7);
                  }
                  
                  const formatDate = (dateString) => {
                      if (!dateString) return 'Sin información';
                      try {
                          return new Date(dateString).toLocaleDateString();
                      } catch (e) {
                          return 'Sin información';
                      }
                  };

                  const displayValue = (value) => {
                      return value && value.trim() !== '' ? value : 'Sin información';
                  };
                  
                  const row = [
                      displayValue(partner.name),
                      displayValue(partner.ci),
                      displayValue(partner.login),
                      displayValue(partner.email),
                      displayValue(partner.cellPhoneNumber),
                      displayValue((partner.address || '').substring(0, 25)),
                      formatDate(partner.birthday),
                      formatDate(partner.dateRegistration),
                      formatDate(partner.dateCreation)
                  ];
                  
                  // Add row data
                  row.forEach((cell, i) => {
                      doc.text(String(cell), columnPositions[i], y);
                  });
                  
                  y += 7;
              });
              
              // Save the PDF
              doc.save('socios_completo_' + new Date().toISOString().split('T')[0] + '.pdf');
              
          } catch (error) {
              console.error('Error al generar el PDF:', error);
              Swal.fire({
                title: 'Error',
                text: 'Error al generar el PDF: ' + error.message,
                icon: 'error',
                confirmButtonColor: '#e74c3c'
              });
          } finally {
              // Restore button state
              button.innerHTML = originalText;
              button.disabled = false;
          }
      });
    });
  </script>
<?php
$content = ob_get_clean();

// ---- Incluir layout principal (misma forma que dashboard) ----
include __DIR__ . '/../layouts/app.php';