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
  <!-- Estilos para dar aire y encabezado pegajoso -->
  <style>
    .modern-table th, .modern-table td {
      padding: 10px 14px;
      line-height: 1.35;
      vertical-align: middle;
      color: #000000;
    }
    .modern-table { border-collapse: separate; border-spacing: 0 6px; }
    .modern-table thead th {
      position: sticky; top: 0;
      background: #bbae97; color: #2a2a2a;
      z-index: 2;
    }
    .modern-table tbody tr { background:#d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background: #dccaaf; }
    .modern-table tbody tr td:first-child  { border-top-left-radius:10px; border-bottom-left-radius:10px; }
    .modern-table tbody tr td:last-child   { border-top-right-radius:10px; border-bottom-right-radius:10px; }

    /* contenedor de tabla */
    .table-container { background:#cfc4b0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:auto; }
    
    /* Filas ocultas por búsqueda */
    .hidden { display: none !important; }
    
    /* Resaltar texto encontrado */
    .highlight {
      background-color: yellow;
      font-weight: bold;
    }
    
    /* Info de búsqueda */
    .search-info {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 8px 12px;
      margin-bottom: 10px;
      font-size: 14px;
      color: #495057;
    }

    /* Botones para ver imágenes CI */
    .ci-view-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .ci-view-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
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
      gap: 8px;
      flex-wrap: wrap;
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
  </style>

  <!-- Barra de acciones -->
  <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
    <div class="search-container" style="position:relative;flex:1 1 320px;">
      <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar por nombre, CI, login, email, celular..."
        style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:10px 40px 10px 38px;outline:none;background:#fff;transition:border-color .2s;"
        onfocus="this.style.borderColor='var(--cream-400)';"
        onblur="this.style.borderColor='#e1e5e9';"
      />
    </div>

    <div style="display:flex;gap:12px;">
      <button id="exportPdfBtn" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:#6c757d;color:#fff;border:none;border-radius:12px;padding:10px 14px;font-weight:600;cursor:pointer;">
        <i class="fas fa-file-pdf"></i> Exportar PDF
      </button>
      
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Nuevo Socio
      </a>
    </div>
  </div>

  <!-- Info de búsqueda -->
  <div id="searchInfo" class="search-info" style="display:none;"></div>

  <!-- Tabla de socios -->
  <?php if (!empty($socios) && is_array($socios)): ?>
    <div class="table-container">
      <table id="tablaSocios" class="modern-table" style="width:100%;border-collapse:separate;border-spacing:0;">
        <thead>
          <tr>
            <th><i class="fas fa-user"></i> Nombre</th>
            <th><i class="fas fa-id-card"></i> CI</th>
            <th><i class="fas fa-user-tag"></i> Login</th>
            <th><i class="fas fa-envelope"></i> Email</th>
            <th><i class="fas fa-phone"></i> Celular</th>
            <th><i class="fas fa-map-marker-alt"></i> Dirección</th>
            <th><i class="fas fa-id-badge"></i> CI Frente</th>
            <th><i class="fas fa-id-badge"></i> CI Atrás</th>
            <th><i class="fas fa-calendar-plus"></i> F. Creación</th>
            <th><i class="fas fa-birthday-cake"></i> F. Nacimiento</th>
            <th><i class="fas fa-calendar-check"></i> F. Registro</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody id="tableBody">
          <?php foreach ($socios as $socio): ?>
            <tr class="socio-row" data-search="<?= htmlspecialchars(strtolower(
              ($socio['name'] ?? '') . ' ' . 
              ($socio['ci'] ?? '') . ' ' . 
              ($socio['login'] ?? '') . ' ' . 
              ($socio['email'] ?? '') . ' ' . 
              ($socio['cellPhoneNumber'] ?? '') . ' ' . 
              ($socio['address'] ?? '')
            )) ?>">
              <td>
                <div class="user-cell" style="display:flex;align-items:center;gap:10px;">
                  <div class="user-avatar-small" style="width:28px;height:28px;border-radius:50%;background:var(--cream-200,#eee);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user"></i>
                  </div>
                  <span class="searchable-text"><?= htmlspecialchars($socio['name'] ?? '') ?></span>
                </div>
              </td>
              <td><span class="searchable-text"><?= htmlspecialchars($socio['ci'] ?? '') ?></span></td>
              <td><span class="searchable-text"><?= htmlspecialchars($socio['login'] ?? '') ?></span></td>
              <td><span class="searchable-text"><?= htmlspecialchars($socio['email'] ?? '') ?></span></td>
              <td><span class="searchable-text"><?= htmlspecialchars($socio['cellPhoneNumber'] ?? '') ?></span></td>
              <td class="address-cell" title="<?= htmlspecialchars($socio['address'] ?? '') ?>">
                <span class="searchable-text">
                <?php
                  $addr = (string)($socio['address'] ?? '');
                  $addr = htmlspecialchars($addr, ENT_QUOTES, 'UTF-8');
                  echo (mb_strlen($addr,'UTF-8') > 30) ? mb_substr($addr,0,30,'UTF-8').'…' : $addr;
                ?>
                </span>
              </td>
              <!-- CI Frente -->
              <td>
                <button class="ci-view-btn front" 
                        onclick="showImageModal('<?= htmlspecialchars($socio['frontImageURL'] ?? '') ?>', 'CI Frente - <?= htmlspecialchars($socio['name'] ?? '') ?>')">
                  <i class="fas fa-eye"></i>
                  Ver Frente
                </button>
              </td>
              <!-- CI Atrás -->
              <td>
                <button class="ci-view-btn back" 
                        onclick="showImageModal('<?= htmlspecialchars($socio['backImageURL'] ?? '') ?>', 'CI Atrás - <?= htmlspecialchars($socio['name'] ?? '') ?>')">
                  <i class="fas fa-eye"></i>
                  Ver Atrás
                </button>
              </td>
              <td><span class="date-badge"><?= !empty($socio['dateCreation'])     ? date('d/m/Y', strtotime($socio['dateCreation']))     : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['birthday'])         ? date('d/m/Y', strtotime($socio['birthday']))         : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['dateRegistration']) ? date('d/m/Y', strtotime($socio['dateRegistration'])) : '-' ?></span></td>
              <td class="actions">
                <div class="action-buttons">
                  <a href="<?= u('partner/edit/' . (int)($socio['idPartner'] ?? 0)) ?>" 
                    class="btn btn-sm btn-outline" 
                    title="Editar" 
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e1e5e9;color:#333;text-decoration:none;">
                    <i class="fas fa-edit"></i>
                  </a>

                  <a href="#"
                    class="btn btn-sm btn-danger delete-btn"
                    title="Eliminar"
                    data-id="<?= (int)($socio['idPartner'] ?? 0) ?>"
                    data-name="<?= htmlspecialchars($socio['name'] ?? '') ?>"
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e74c3c;color:#fff;text-decoration:none;margin-left:6px;">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Controles de paginación -->
      <div id="pager" style="display:flex;align-items:center;gap:8px;justify-content:flex-end;padding:12px;">
        <label for="pageSize">Por página:</label>
        <select id="pageSize" style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 8px;">
          <option value="10">10</option>
          <option value="20" selected>20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>

        <button id="firstPage" style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">«</button>
        <button id="prevPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">‹</button>
        <span id="pageInfo" style="min-width:180px;text-align:center;font-weight:600;"></span>
        <button id="nextPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">›</button>
        <button id="lastPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">»</button>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:40px 20px;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);">
      <div class="empty-state-icon" style="font-size:42px;margin-bottom:10px;color:var(--cream-600);"><i class="fas fa-users"></i></div>
      <h3>No hay socios registrados</h3>
      <p>Comienza agregando tu primer socio al sistema</p>
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
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

  <!-- Modal de confirmación de eliminación -->
  <div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
    <div style="background:white;padding:25px;border-radius:12px;max-width:400px;width:90%;box-shadow:0 5px 15px rgba(0,0,0,0.3);">
      <h3 style="margin-top:0;color:#2a2a2a;">Confirmar eliminación</h3>
      <p>¿Estás seguro de que deseas eliminar a <strong id="deleteItemName"></strong>?</p>
      <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
        <button id="cancelDelete" class="btn" style="background:#f1f1f1;padding:8px 16px;border:none;border-radius:8px;cursor:pointer;">Cancelar</button>
        <button id="confirmDelete" class="btn" style="background:#e74c3c;color:white;padding:8px 16px;border:none;border-radius:8px;cursor:pointer;">Eliminar</button>
      </div>
    </div>
  </div>

  <!-- JavaScript para funcionalidad de imágenes -->
  <script>
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
    
    // Cerrar modal al hacer click fuera de la imagen
    document.getElementById('imageModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeImageModal();
      }
    });
    
    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        closeImageModal();
      }
    });
  </script>

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
          searchInfo.textContent = `Se encontraron ${filteredRows.length} resultado(s) para "${searchTerm}"`;
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
        nextPageBtn.disabled = currentPage >= totalPages;
        lastPageBtn.disabled = currentPage >= totalPages;
        
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
      
      // Modal de eliminación
      const deleteModal = document.getElementById('deleteModal');
      const deleteButtons = document.querySelectorAll('.delete-btn');
      const deleteItemName = document.getElementById('deleteItemName');
      const cancelBtn = document.getElementById('cancelDelete');
      const confirmBtn = document.getElementById('confirmDelete');
      
      let deleteId = '';
      
      deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          deleteId = this.getAttribute('data-id');
          const name = this.getAttribute('data-name');
          deleteItemName.textContent = name;
          deleteModal.style.display = 'flex';
        });
      });
      
      cancelBtn.addEventListener('click', function() {
        deleteModal.style.display = 'none';
      });
      
      confirmBtn.addEventListener('click', function() {
        if (deleteId) {
          window.location.href = '<?= u("partner/delete/") ?>' + deleteId + '?return_url=' + encodeURIComponent(window.location.pathname + window.location.search);
        }
      });
      
      // Cerrar modal al hacer click fuera
      deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
          deleteModal.style.display = 'none';
        }
      });
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
                      if (!dateString) return 'N/A';
                      try {
                          return new Date(dateString).toLocaleDateString();
                      } catch (e) {
                          return 'N/A';
                      }
                  };
                  
                  const row = [
                      partner.name || 'N/A',
                      partner.ci || 'N/A',
                      partner.login || 'N/A',
                      partner.email || 'N/A',
                      partner.cellPhoneNumber || 'N/A',
                      (partner.address || 'N/A').substring(0, 25),
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
              alert('Error al generar el PDF: ' + error.message);
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
?>