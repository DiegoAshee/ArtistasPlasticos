<?php
// app/Views/movement/list.php

$title = 'Movimientos';
$currentPath = 'movement/list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Movimientos', 'url' => null],
];

// Calcular métricas
$totalMovimientos = is_array($movements ?? []) ? count($movements) : 0;
$montoTotal = 0;
$movimientosHoy = 0;
$hoy = date('Y-m-d');

if (!empty($movements) && is_array($movements)) {
    foreach ($movements as $m) {
        $montoTotal += (float)($m['amount'] ?? 0);
        $fechaMovimiento = $m['dateMovement'] ?? '';
        if ($fechaMovimiento && date('Y-m-d', strtotime($fechaMovimiento)) === $hoy) {
            $movimientosHoy++;
        }
    }
}

ob_start();
?>

<!-- Estilos para la tabla de movimientos -->
<style>
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
      <div class="metric-label">Monto Total</div>
    </div>
    <div class="metric-card">
      <div class="metric-value"><?= $movimientosHoy ?></div>
      <div class="metric-label">Movimientos Hoy</div>
    </div>
  </div>

  <!-- Barra de acciones -->
  <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
    <div class="search-container" style="position:relative;flex:1 1 320px;">
      <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar por descripción, monto, usuario, concepto, tipo de pago..."
        style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:10px 40px 10px 38px;outline:none;background:#fff;transition:border-color .2s;"
        onfocus="this.style.borderColor='var(--cream-400)';"
        onblur="this.style.borderColor='#e1e5e9';"
      />
    </div>

    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
      <div style="display:flex;gap:8px;align-items:center;">
        <label for="startDate" style="font-size:0.85rem;color:#555;">Desde:</label>
        <input type="date" id="startDate" style="border:2px solid #e1e5e9;border-radius:12px;padding:8px 10px;" />
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <label for="endDate" style="font-size:0.85rem;color:#555;">Hasta:</label>
        <input type="date" id="endDate" style="border:2px solid #e1e5e9;border-radius:12px;padding:8px 10px;" />
      </div>
      <button id="exportPdfBtn" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:#6c757d;color:#fff;border:none;border-radius:12px;padding:10px 14px;font-weight:600;cursor:pointer;">
        <i class="fas fa-file-pdf"></i> Exportar PDF
      </button>
    </div>
  </div>

  <!-- Tabla de movimientos -->
  <?php if (!empty($movements) && is_array($movements)): ?>
    <div class="table-container">
      <table id="tablaMovimientos" class="modern-table" style="width:100%;border-collapse:separate;border-spacing:0;">
        <thead>
          <tr>
            <th><i class="fas fa-file-alt"></i> Descripción</th>
            <th><i class="fas fa-dollar-sign"></i> Monto</th>
            <th><i class="fas fa-calendar"></i> Fecha</th>
            <th><i class="fas fa-credit-card"></i> Tipo de Pago</th>
            <th><i class="fas fa-tag"></i> Concepto</th>
            <th><i class="fas fa-user"></i> Usuario</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($movements as $movement): ?>
            <?php
              $dateAttr = !empty($movement['dateCreation']) ? date('Y-m-d', strtotime($movement['dateCreation'])) : '';
            ?>
            <tr data-date="<?= htmlspecialchars($dateAttr) ?>">
              <td title="<?= htmlspecialchars($movement['description'] ?? '') ?>">
                <?php
                  $desc = (string)($movement['description'] ?? '');
                  $desc = htmlspecialchars($desc, ENT_QUOTES, 'UTF-8');
                  echo (mb_strlen($desc,'UTF-8') > 40) ? mb_substr($desc,0,40,'UTF-8').'…' : $desc;
                ?>
              </td>
              <td class="amount-cell <?= (float)($movement['amount'] ?? 0) >= 0 ? 'amount-positive' : 'amount-negative' ?>">
                Bs. <?= number_format((float)($movement['amount'] ?? 0), 2) ?>
              </td>
              <td>
                <span class="date-badge">
                  <?= !empty($movement['dateCreation']) ? date('d/m/Y H:i', strtotime($movement['dateCreation'])) : '-' ?>
                </span>
              </td>
              <td><?= htmlspecialchars($movement['payment_type_description'] ?? 'N/A') ?></td>
              <td><?= htmlspecialchars($movement['concept_description'] ?? 'N/A') ?></td>
              <td>
                <div class="user-cell" style="display:flex;align-items:center;gap:8px;">
                  <div class="user-avatar-small" style="width:24px;height:24px;border-radius:50%;background:var(--cream-200,#eee);display:flex;align-items:center;justify-content:center;font-size:10px;">
                    <i class="fas fa-user"></i>
                  </div>
                  <span><?= htmlspecialchars($movement['user_login'] ?? 'N/A') ?></span>
                </div>
              </td>
              <td class="actions">
                <div class="action-buttons">
                  <!-- <a href="<?= u('movement/edit/' . (int)($movement['idMovement'] ?? 0)) ?>" 
                    class="btn btn-sm btn-outline" 
                    title="Editar" 
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e1e5e9;color:#333;text-decoration:none;">
                    <i class="fas fa-edit"></i>
                  </a> -->

                  <button onclick="showDeleteModal(<?= (int)($movement['idMovement'] ?? 0) ?>, '<?= htmlspecialchars(addslashes($movement['description'] ?? ''), ENT_QUOTES) ?>')"
                    class="btn btn-sm btn-danger"
                    title="Eliminar"
                    style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e74c3c;color:#fff;border:none;margin-left:6px;cursor:pointer;">
                    <i class="fas fa-trash"></i>
                  </button>
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
      <h3>No hay movimientos registrados</h3>
      <p>Comienza agregando tu primer movimiento al sistema</p>
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

    if (searchInput) searchInput.addEventListener('input', applyFilters);
    if (startDateInput) startDateInput.addEventListener('change', applyFilters);
    if (endDateInput) endDateInput.addEventListener('change', applyFilters);
  });
  </script>

  <!-- Exportación PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      document.getElementById('exportPdfBtn').addEventListener('click', async function() {
        const button = this;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando PDF...';
        button.disabled = true;
        
        try {
          const response = await fetch('export-pdf', {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
          });
          
          const result = await response.json();
          
          if (!result.success) {
            throw new Error('Error al generar el PDF: ' + (result.error || 'Error desconocido'));
          }
          
          const movements = result.data || [];
          // Aplicar filtro por rango de fechas al PDF si el usuario lo configuró
          const start = document.getElementById('startDate')?.value || '';
          const end = document.getElementById('endDate')?.value || '';
          const data = (start || end)
            ? movements.filter(m => {
                if (!m.dateCreation) return false;
                try {
                  const dStr = new Date(m.dateCreation).toISOString().slice(0,10); // YYYY-MM-DD
                  if (start && dStr < start) return false;
                  if (end && dStr > end) return false;
                  return true;
                } catch (_) {
                  return false;
                }
              })
            : movements;
          
          if (data.length === 0) {
            throw new Error('No se encontraron movimientos para exportar');
          }
          
          const { jsPDF } = window.jspdf;
          const doc = new jsPDF({ orientation: 'landscape' });
          
          // Título y fecha
          doc.setFontSize(20);
          doc.text('Lista  de Movimientos', 15, 15);
          
          doc.setFontSize(10);
          doc.text('Generado el: ' + new Date().toLocaleDateString(), 15, 25);
          
          // Cabeceras de tabla
          const headers = ['Descripción', 'Monto', 'Fecha', 'Tipo Pago', 'Concepto', 'Usuario'];
          // Posiciones de columnas (x) y configuración de anchos
          const columnPositions = [15, 130, 170, 200, 235, 270];
          const descMaxWidth = 110; // Ancho máximo para ajuste de texto en descripción
          
          doc.setFontSize(8);
          doc.setFont('helvetica', 'bold');
          headers.forEach((header, i) => {
            doc.text(header, columnPositions[i], 35);
          });
          
          doc.line(15, 37, 280, 37);
          
          // Filas de datos
          doc.setFont('helvetica', 'normal');
          doc.setFontSize(7);
          
          let y = 45;
          const topMargin = 20;
          const headerY = 35;
          const bottomMargin = 15;
          const pageHeight = doc.internal.pageSize.height || doc.internal.pageSize.getHeight();
          const lineHeight = 5.5; // altura por línea
          data.forEach((movement, index) => {
            // Salto de página si no hay espacio suficiente para al menos una línea
            if (y > (pageHeight - bottomMargin)) {
              doc.addPage();
              y = topMargin;
              
              // Cabeceras en nueva página
              doc.setFontSize(8);
              doc.setFont('helvetica', 'bold');
              headers.forEach((header, i) => {
                doc.text(header, columnPositions[i], y + 15);
              });
              doc.line(15, y + 17, 280, y + 17);
              y = y + 25;
              doc.setFont('helvetica', 'normal');
              doc.setFontSize(7);
            }
            
            const formatDate = (dateString) => {
              if (!dateString) return 'N/A';
              try {
                const date = new Date(dateString);
                return isNaN(date.getTime()) ? 'N/A' : date.toLocaleDateString();
              } catch (e) {
                return 'N/A';
              }
            };
            
            // Preparar valores de la fila
            const fullDesc = movement.description || 'N/A';
            const wrappedDesc = doc.splitTextToSize(fullDesc, descMaxWidth);
            const amountText = 'Bs. ' + parseFloat(movement.amount || 0).toFixed(2);
            const dateText = formatDate(movement.dateCreation);
            const payTypeText = (movement.payment_type_description || 'N/A');
            const conceptText = (movement.concept_description || 'N/A');
            const userText = (movement.user_login || 'N/A');

            // Si no hay espacio suficiente para todas las líneas de la descripción, saltar de página
            const neededHeight = lineHeight * (Array.isArray(wrappedDesc) ? wrappedDesc.length : 1);
            if (y + neededHeight > (pageHeight - bottomMargin)) {
              doc.addPage();
              y = topMargin;
              doc.setFontSize(8);
              doc.setFont('helvetica', 'bold');
              headers.forEach((header, i) => {
                doc.text(header, columnPositions[i], y + 15);
              });
              doc.line(15, y + 17, 280, y + 17);
              y = y + 25;
              doc.setFont('helvetica', 'normal');
              doc.setFontSize(7);
            }

            // Pintar la descripción en múltiples líneas si es necesario
            doc.text(wrappedDesc, columnPositions[0], y);
            // Pintar el resto de columnas alineadas con la primera línea
            doc.text(amountText, columnPositions[1], y);
            doc.text(dateText, columnPositions[2], y);
            doc.text(payTypeText, columnPositions[3], y);
            doc.text(conceptText, columnPositions[4], y);
            doc.text(userText, columnPositions[5], y);
            
            // Avanzar Y según líneas ocupadas por la descripción
            y += neededHeight + 2;
          });
          
          // Números de página
          const pageCount = doc.internal.getNumberOfPages();
          for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(
              `Página ${i} de ${pageCount}`,
              doc.internal.pageSize.width - 30,
              doc.internal.pageSize.height - 10
            );
          }
          
          doc.save('movimientos_' + new Date().toISOString().split('T')[0] + '.pdf');
          
        } catch (error) {
          console.error('Error al generar el PDF:', error);
          alert('Error al generar el PDF. Por favor, intente nuevamente.');
        } finally {
          button.innerHTML = originalText;
          button.disabled = false;
        }
      });
    });
  </script>
<?php
$content = ob_get_clean();

// ---- Incluir layout principal ----
include __DIR__ . '/../layouts/app.php';
?>