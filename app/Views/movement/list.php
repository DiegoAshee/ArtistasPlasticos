<?php
// app/Views/movement/list.php

$title = 'Libro diario';
$currentPath = 'movement/list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Libro diario', 'url' => null],
];

// Calcular métricas
$totalMovimientos = is_array($movements ?? []) ? count($movements) : 0;
$montoTotal = 0;
$movimientosHoy = 0;
$hoy = date('Y-m-d');

// Calcular métricas y procesar movimientos
$totalMovimientos = is_array($movements ?? []) ? count($movements) : 0;
$montoTotal = 0;
$movimientosHoy = 0;
$hoy = date('Y-m-d');
$primerDiaMes = date('Y-m-01');

// Establecer el rango de fechas para el subtítulo
$fechaInicio = strtotime($filters['start_date'] ?? $primerDiaMes);
$fechaFin = strtotime($filters['end_date'] ?? $hoy);

// Formatear fechas para mostrar en el subtítulo
$subtitleText = 'Del ' . date('d/m/Y', $fechaInicio) . ' al ' . date('d/m/Y', $fechaFin);

// Calcular totales
foreach ($movements as $m) {
    $montoTotal += (float)($m['amount'] ?? 0);
    $fechaMovimiento = $m['dateMovement'] ?? '';
    if ($fechaMovimiento && date('Y-m-d', strtotime($fechaMovimiento)) === $hoy) {
        $movimientosHoy++;
    }
}

ob_start();
?>

<!-- Estilos para la tabla de movimientos -->
<style>
    .badge {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 75%;
        font-weight: 600;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .text-center {
        text-align: center !important;
    }
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
        margin: 0;
    }
    
    .modern-table th, 
    .modern-table td {
        padding: 12px 16px;
        vertical-align: middle;
        color: #000000;
    }
    
    .modern-table thead th {
        position: sticky;
        top: 0;
        background: #bbae97;
        color: #2a2a2a;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
    
    .modern-table tbody tr {
        background: #d7cbb5;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .modern-table tbody tr:nth-child(even) {
        background: #dccaaf;
    }
    
    .modern-table tbody tr:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .modern-table tbody tr td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    
    .modern-table tbody tr td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    
    .table-container {
        background: #cfc4b0;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-top: 24px;
    }
    
    .amount-cell {
        font-weight: 600;
        text-align: right;
    }
    
    /* Badge de estado */
    .status-badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }
    
    /* Métricas */
    .metrics {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    
    .metric-card {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      text-align: center;
    }
    
    .metric-value {
      font-size: 1.75rem;
      font-weight: 700;
      color: var(--cream-700);
      margin-bottom: 0.5rem;
    }
    
    .metric-label {
      color: #666;
      font-size: 0.875rem;
      font-weight: 500;
    }
  </style>

  <!-- Métricas -->
  <div class="metrics">
    <div class="metric-card">
      <div class="metric-value"><?= $totalMovimientos ?></div>
      <div class="metric-label">Total Movimientos</div>
    </div>
    <div class="metric-card">
      <div class="metric-value">Bs. <?= number_format($montoTotal, 2) ?></div>
      <div class="metric-label">Importe Total</div>
    </div>
    <div class="metric-card">
      <div class="metric-value"><?= $movimientosHoy ?></div>
      <div class="metric-label">Movimientos Hoy</div>
    </div>
  </div>

  <div id="subtitleRange" style="margin-top: -8px; color:#555; font-weight:600;">
    <?= htmlspecialchars($subtitleText) ?>
  </div>

  <!-- Barra de acciones -->
  <form id="filterForm" method="GET" action="" style="width: 100%;">
    <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
      <div class="search-container" style="position:relative;flex:1 1 320px;">
        <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
        <input
          type="text"
          id="searchInput"
          name="search"
          placeholder="Buscar por descripción, importe, usuario, concepto, tipo de pago..."
          style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:10px 40px 10px 38px;outline:none;background:#fff;transition:border-color .2s;"
          onfocus="this.style.borderColor='var(--cream-400)';"
          onblur="this.style.borderColor='#e1e5e9';"
          value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
        />
      </div>

      <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <div style="display:flex;gap:8px;align-items:center;">
          <label for="start_date" style="font-size:0.85rem;color:#555;">Desde:</label>
          <input 
            type="date" 
            id="startDate" 
            name="start_date" 
            value="<?= htmlspecialchars($filters['start_date'] ?? date('Y-m-01')) ?>" 
            style="border:2px solid #e1e5e9;border-radius:12px;padding:8px 10px;"
            onchange="document.getElementById('filterForm').submit()"
          />
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <label for="end_date" style="font-size:0.85rem;color:#555;">Hasta:</label>
          <input 
            type="date" 
            id="endDate" 
            name="end_date" 
            value="<?= htmlspecialchars($filters['end_date'] ?? date('Y-m-d')) ?>" 
            style="border:2px solid #e1e5e9;border-radius:12px;padding:8px 10px;"
            onchange="document.getElementById('filterForm').submit()"
          />
        </div>
        <!--   -->
        <a href="<?= u('movement/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;cursor:pointer;">
          <i class="fas fa-plus"></i> Nuevo Movimiento
        </a>
        
        <!-- <button id="exportPdfBtn" type="button" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:#6c757d;color:#fff;border:none;border-radius:12px;padding:10px 14px;font-weight:600;cursor:pointer;">
          <i class="fas fa-file-pdf"></i> Exportar PDF
        </button> -->
      </div>
    </div>
  </form>

  <!-- Tabla de movimientos -->
  <?php if (!empty($movements) && is_array($movements)): ?>
    <div class="table-container">
      <table id="tablaMovimientos" class="modern-table" style="width:100%;border-collapse:separate;border-spacing:0;">
        <thead>
          <tr>
            <th><i class="fas fa-calendar"></i> Fecha</th>
            <th><i class="fas fa-tag"></i> Concepto</th>
            <th><i class="fas fa-user-tag"></i> Destinatario</th>
            <th><i class="fas fa-file-alt"></i> Descripción</th>
            <th><i class="fas fa-credit-card"></i> Tipo de Pago</th>
            <th><i class="fas fa-user"></i> Usuario</th>
            <th class="text-center"><i class="fas fa-arrow-down text-success"></i> Ingreso</th>
            <th class="text-center"><i class="fas fa-arrow-up text-danger"></i> Egreso</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movements as $movement): ?>
            <?php
              $dateAttr = !empty($movement['dateCreation']) ? date('Y-m-d', strtotime($movement['dateCreation'])) : '';
            ?>
            <tr data-date="<?= htmlspecialchars($dateAttr) ?>">
              <td>
                <span class="date-badge">
                  <?= !empty($movement['dateCreation']) ? date('d/m/Y H:i', strtotime($movement['dateCreation'])) : '-' ?>
                </span>
              </td>
              <td>
                <div class="mt-1"><?= htmlspecialchars($movement['concept_description'] ?? 'N/A') ?></div>
              </td>
              <td><?= htmlspecialchars($movement['nameDestination'] ?? 'N/A') ?></td>
              <td title="<?= htmlspecialchars($movement['description'] ?? '') ?>">
                <?php
                  $desc = (string)($movement['description'] ?? '');
                  $desc = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
                  echo (mb_strlen($desc,'UTF-8') > 40) ? mb_substr($desc,0,40,'UTF-8').'…' : $desc;
                ?>
              </td>
              <td><?= htmlspecialchars($movement['payment_type_description'] ?? 'N/A') ?></td>
              <td>
                <div class="user-cell" style="display:flex;align-items:center;gap:8px;">
                  <div class="user-avatar-small" style="width:24px;height:24px;border-radius:50%;background:var(--cream-200,#eee);display:flex;align-items:center;justify-content:center;font-size:10px;">
                    <i class="fas fa-user"></i>
                  </div>
                  <span><?= htmlspecialchars($movement['user_login'] ?? 'N/A') ?></span>
                </div>
              </td>
              <td class="text-center">
                <?php if (strtolower($movement['concept_type'] ?? '') === 'ingreso'): ?>
                  <span class="text-success">Bs. <?= number_format((float)($movement['amount'] ?? 0), 2) ?></span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <?php if (strtolower($movement['concept_type'] ?? '') === 'egreso'): ?>
                  <span class="text-danger">Bs. <?= number_format((float)($movement['amount'] ?? 0), 2) ?></span>
                <?php endif; ?>
              </td>
              <td class="actions">
                <div class="action-buttons" style="display: flex; gap: 6px;">
                  <a href="<?= u('movement/edit/' . (int)($movement['idMovement'] ?? 0)) ?>" 
                    class="btn btn-sm btn-outline" 
                    title="Editar" 
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#3498db;color:#fff;border:none;text-decoration:none;transition:all 0.2s ease;">
                    <i class="fas fa-edit"></i>
                  </a>

                  <button onclick="showDeleteModal(<?= (int)($movement['idMovement'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($movement['description'] ?? ''), ENT_QUOTES) ?>')"
                    class="btn btn-sm btn-danger"
                    title="Eliminar"
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e74c3c;color:#fff;border:none;cursor:pointer;transition:all 0.2s ease;">
                    <i class="fas fa-trash"></i>
                  </button>
                   <!-- Botón Recibo -->
                  <a href="<?= u('movement/receipt/' . (int)($movement['idMovement'] ?? 0)) ?>" 
                    class="btn btn-sm btn-info" 
                    title="Ver recibo"
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#17a2b8;color:#fff;text-decoration:none;">
                      <i class="fas fa-receipt"></i>
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
      <div class="empty-state-icon" style="font-size:42px;margin-bottom:10px;color:var(--cream-600);"><i class="fas fa-exchange-alt"></i></div>
      <h3 style="color: #000000; margin-bottom: 10px;">No hay movimientos registrados</h3>
      <a href="<?= u('movement/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Crear primer movimiento
      </a>
    </div>
  <?php endif; ?>

  <!-- Modal de confirmación de eliminación -->
  <div id="deleteModal" class="modal" style="display:none;position:fixed;z-index:1000;left:0;top:0;width:100%;height:100%;background-color:rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color:#fefefe;margin:15% auto;padding:20px;border:none;border-radius:12px;width:90%;max-width:400px;box-shadow:0 10px 30px rgba(0,0,0,0.3);">
      <div class="modal-header" style="text-align:center;margin-bottom:20px;">
        <i class="fas fa-exclamation-triangle" style="font-size:48px;color:#f39c12;margin-bottom:15px;"></i>
        <h2 style="margin:0;color:#333;">Confirmar Eliminación</h2>
      </div>
      <div class="modal-body" style="color:black;text-align:center;margin-bottom:25px;">
        <p>¿Estás seguro que deseas eliminar el movimiento:</p>
        <p><strong id="movementToDelete"></strong></p>
        <p style="color:black;font-size:0.9rem;">Esta acción no se puede deshacer.</p>
      </div>
      <div class="modal-footer" style="text-align:center;">
        <button onclick="closeDeleteModal()" class="btn" style="background:#6c757d;color:white;border:none;padding:10px 20px;margin-right:10px;border-radius:8px;cursor:pointer;">
          Cancelar
        </button>
        <button onclick="confirmDelete()" class="btn" style="background:#e74c3c;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;">
          Eliminar
        </button>
      </div>
    </div>
  </div>

  <!-- Búsqueda en vivo y paginación -->
  <script>
    let currentMovementId = null;
    
    function showDeleteModal(movementId, description) {
      currentMovementId = movementId;
      document.getElementById('movementToDelete').textContent = description;
      document.getElementById('deleteModal').style.display = 'block';
    }
    
    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
      currentMovementId = null;
    }
    
    function confirmDelete() {
      if (currentMovementId) {
        window.location.href = '<?= u("movement/delete/") ?>' + currentMovementId;
      }
    }
    
    // Cerrar modal al hacer clic fuera de él
    window.onclick = function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target == modal) {
        closeDeleteModal();
      }
    }
    
    // Buscador en vivo + Filtro por rango de fechas
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('tablaMovimientos');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const subtitleEl = document.getElementById('subtitleRange');

    function normalize(dateStr) {
      return dateStr ? dateStr : '';
    }

    function applyFilters() {
      if (!table) return;
      const rows = table.querySelectorAll('tbody tr');
      const term = (searchInput?.value || '').toLowerCase();
      const start = normalize(startDateInput?.value || '');
      const end = normalize(endDateInput?.value || '');

      rows.forEach(row => {
        const rowDate = row.getAttribute('data-date') || '';
        // Filtrado por texto
        let matchesText = true;
        if (term) {
          matchesText = false;
          const cells = row.querySelectorAll('td');
          cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(term)) {
              matchesText = true;
            }
          });
        }

        // Filtrado por fecha (inclusive)
        let matchesDate = true;
        if (start && rowDate && rowDate < start) {
          matchesDate = false;
        }
        if (end && rowDate && rowDate > end) {
          matchesDate = false;
        }
        // Si no hay fecha en la fila y el usuario usa filtros de fecha, ocultar
        if ((start || end) && !rowDate) {
          matchesDate = false;
        }

        row.style.display = (matchesText && matchesDate) ? '' : 'none';
      });
    }

    function updateSubtitle() {
      if (!subtitleEl) return;
      const s = startDateInput?.value || '';
      const e = endDateInput?.value || '';
      if (!s && !e) return; // mantiene el subtítulo calculado por servidor si no hay filtros
      const fmt = (d) => d ? d.split('-').reverse().join('/') : '...';
      subtitleEl.textContent = `Del ${fmt(s)} al ${fmt(e)}`;
    }

    // Función para manejar el cambio de fecha
    function handleDateChange() {
      // Actualizar el subtítulo con las fechas seleccionadas
      updateSubtitle();
      
      // Aplicar los filtros sin recargar la página
      applyFilters();
      
      // Actualizar la URL sin recargar la página para mantener los filtros
      const form = document.getElementById('filterForm');
      const formData = new FormData(form);
      const params = new URLSearchParams(formData).toString();
      const newUrl = window.location.pathname + (params ? '?' + params : '');
      window.history.pushState({}, '', newUrl);
    }

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    
    // Actualizar los manejadores de eventos de los inputs de fecha
    if (startDateInput) {
      startDateInput.removeEventListener('change', null); // Eliminar cualquier manejador anterior
      startDateInput.addEventListener('change', handleDateChange);
    }
    
    if (endDateInput) {
      endDateInput.removeEventListener('change', null); // Eliminar cualquier manejador anterior
      endDateInput.addEventListener('change', handleDateChange);
    }
  });
  </script>

<!-- Librerías jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<!-- Exportación PDF (versión corregida y funcional) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.jspdf) {
    console.error('jsPDF no está cargado.');
    return;
  }
  const { jsPDF } = window.jspdf;

  // --- FUNCIÓN FIJA PARA PARSEAR NÚMEROS ---
  function parseNumberFromString(s) {
    if (!s) return 0;
    s = s.toString().trim();

    // Eliminar símbolo Bs., espacios, letras y separadores de miles
    s = s.replace(/[^\d.,-]/g, '');
    if (!s) return 0;

    // Quitar separadores de miles (comas)
    s = s.replace(/,/g, '');

    // Si usa coma como decimal, convertir a punto
    const parts = s.split(',');
    if (parts.length === 2 && parts[1].length <= 2) {
      s = parts[0] + '.' + parts[1];
    }

    const num = parseFloat(s);
    return isNaN(num) ? 0 : num;
  }

  // --- FUNCIÓN PRINCIPAL PARA EXPORTAR ---
  function exportToPdf() {
    const btn = document.getElementById('exportPdfBtn');
    const originalHtml = btn ? btn.innerHTML : '';

    try {
      if (btn) {
        btn.innerHTML = 'Generando...';
        btn.disabled = true;
      }

      const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'letter' });
      const table = document.querySelector('table');

      if (!table) {
        alert('No se encontró la tabla para exportar.');
        return;
      }

      // Obtener encabezados
      const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText.trim());

      // Buscar índices de columnas
      const ingresoColIndex = headers.findIndex(h => h.toLowerCase().includes('ingreso'));
      const egresoColIndex = headers.findIndex(h => h.toLowerCase().includes('egreso'));

      let totalIngresos = 0;
      let totalEgresos = 0;

      // Obtener filas del cuerpo
      const rows = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
        const cells = Array.from(tr.querySelectorAll('td')).map(td => td.innerText.trim());

        // Sumar si hay columnas de ingreso y egreso
        if (ingresoColIndex >= 0 && cells[ingresoColIndex]) {
          totalIngresos += parseNumberFromString(cells[ingresoColIndex]);
        }
        if (egresoColIndex >= 0 && cells[egresoColIndex]) {
          totalEgresos += parseNumberFromString(cells[egresoColIndex]);
        }
        return cells;
      });

      // Fila total
      const totalRow = headers.map((_, idx) => {
        if (idx === 0) return 'TOTAL';
        if (idx === ingresoColIndex) return `Bs. ${totalIngresos.toFixed(2)}`;
        if (idx === egresoColIndex) return `Bs. ${totalEgresos.toFixed(2)}`;
        return '';
      });
      rows.push(totalRow);

      // Fecha y pie de página
      const fecha = new Date().toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' });
      const hora = new Date().toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });
      const footerText = `Página 1 de 1 | Generado el ${fecha} ${hora}`;

      // Generar tabla PDF
      doc.setFont('helvetica', 'bold');
      doc.setFontSize(14);
      doc.text('Reporte de Movimientos', 105, 15, { align: 'center' });
      doc.setFontSize(10);

      doc.autoTable({
        head: [headers],
        body: rows,
        startY: 25,
        styles: { fontSize: 8, halign: 'center' },
        headStyles: { fillColor: [0, 102, 204], textColor: 255 },
      });

      doc.setFontSize(8);
      doc.text(footerText, 105, 280, { align: 'center' });
      doc.save('Reporte_Movimientos.pdf');
    } catch (err) {
      console.error('Error al generar el PDF:', err);
      alert('Ocurrió un error al generar el PDF. Revisa la consola para más detalles.');
    } finally {
      if (btn) {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
      }
    }
  }

  // Asignar evento al botón
  const exportBtn = document.getElementById('exportPdfBtn');
  if (exportBtn) exportBtn.addEventListener('click', exportToPdf);
});
</script>


<?php
$content = ob_get_clean();

// ---- Incluir layout principal ----
include __DIR__ . '/../layouts/app.php';
?>