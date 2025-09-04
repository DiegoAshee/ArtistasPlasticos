<?php
// app/Views/payment/list.php

$title       = 'Pagos';
$currentPath = 'payment/list';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Pagos', 'url' => null],
];

// métricas simples
$totalPagos = is_array($payments ?? null) ? count($payments) : 0;
$montoTotal = 0;
if (!empty($payments) && is_array($payments)) {
    foreach ($payments as $p) {
        $montoTotal += (float)($p['paidAmount'] ?? 0);
    }
}

// ---- contenido específico ----
ob_start();
?>
  <style>
    .modern-table th, .modern-table td { padding: 10px 14px; line-height: 1.35; vertical-align: middle; }
    .modern-table { border-collapse: separate; border-spacing: 0 6px; }
    .modern-table thead th { position: sticky; top: 0; background: #bbae97; color: #2a2a2a; z-index: 2; }
    .modern-table tbody tr { background:#d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background:#dccaaf; }
    .modern-table tbody tr td:first-child  { border-top-left-radius:10px; border-bottom-left-radius:10px; }
    .modern-table tbody tr td:last-child   { border-top-right-radius:10px; border-bottom-right-radius:10px; }
    .table-container { background:#cfc4b0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:auto; }
  </style>

  <!-- Barra de acciones -->
  <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
    <div class="search-container" style="position:relative;flex:1 1 320px;">
      <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar por socio, tipo, contribución..."
        style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:10px 40px 10px 38px;outline:none;background:#fff;transition:border-color .2s;"
        onfocus="this.style.borderColor='var(--cream-400)';"
        onblur="this.style.borderColor='#e1e5e9';"
      />
    </div>

    <a href="<?= u('payment/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
      <i class="fas fa-plus"></i> Nuevo Pago
    </a>
  </div>

  <!-- Métricas -->
  <div class="dashboard-cards" style="margin-bottom:16px;">
    <div class="card">
      <div class="card-header">
        <div class="card-icon success"><i class="fas fa-money-check-alt"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Total Pagos</div>
        <div class="card-value" id="totalPagos"><?= (int)$totalPagos ?></div>
        <div class="card-change"><i class="fas fa-sync"></i> Actualizado</div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon primary"><i class="fas fa-dollar-sign"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Monto total</div>
        <div class="card-value"><?= number_format($montoTotal, 2) ?></div>
        <div class="card-change"><i class="fas fa-wallet"></i> Bs.</div>
      </div>
    </div>
  </div>

  <!-- Tabla de pagos -->
  <?php if (!empty($payments) && is_array($payments)): ?>
    <div class="table-container">
      <table id="tablaPagos" class="modern-table" style="width:100%;">
        <thead>
          <tr>
            <th><i class="fas fa-user"></i> Socio</th>
            <th><i class="fas fa-gift"></i> Contribución</th>
            <th><i class="fas fa-tags"></i> Tipo Pago</th>
            <th><i class="fas fa-coins"></i> Monto Pagado</th>
            <th><i class="fas fa-calendar-plus"></i> Fecha</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['partnerName'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['contributionDesc'] ?? '') ?></td>
              <td><?= htmlspecialchars($p['paymentTypeDesc'] ?? '') ?></td>
              <td><?= number_format((float)($p['paidAmount'] ?? 0), 2) ?></td>
              <td><span class="date-badge"><?= !empty($p['dateCreation']) ? date('d/m/Y', strtotime($p['dateCreation'])) : '-' ?></span></td>
              <td class="actions">
                <div class="action-buttons">
                  <a href="<?= u('payment/edit/' . (int)($p['idPayment'] ?? 0)) ?>" class="btn btn-sm btn-outline" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e1e5e9;color:#333;text-decoration:none;">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= u('payment/delete/' . (int)($p['idPayment'] ?? 0)) ?>"
                     class="btn btn-sm btn-danger"
                     title="Eliminar"
                     onclick="return confirm('¿Seguro que desea eliminar este pago?');"
                     style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e74c3c;color:#fff;text-decoration:none;margin-left:6px;">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- paginación -->
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
      <div class="empty-state-icon" style="font-size:42px;margin-bottom:10px;color:var(--cream-600);"><i class="fas fa-money-bill-wave"></i></div>
      <h3>No hay pagos registrados</h3>
      <p>Comienza agregando tu primer pago al sistema</p>
      <a href="<?= u('payment/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Crear primer pago
      </a>
    </div>
  <?php endif; ?>

  <script>
    (function(){
      const input = document.getElementById('searchInput');
      const table = document.getElementById('tablaPagos');
      if (!table) return;

      const tbody = table.querySelector('tbody');
      const allRows = Array.from(tbody.querySelectorAll('tr'));

      let currentPage = 1;
      const pageSizeSelect = document.getElementById('pageSize');
      let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '20', 10);

      allRows.forEach(tr => tr.dataset.match = '1');

      function filteredRows(){ return allRows.filter(tr => tr.dataset.match !== '0'); }
      function totalPages(){ return Math.max(1, Math.ceil(filteredRows().length / pageSize)); }
      function clampPage(){ const tp = totalPages(); if (currentPage > tp) currentPage = tp; if (currentPage < 1) currentPage = 1; }

      function render(){
        clampPage();
        const fr = filteredRows();
        const start = (currentPage - 1) * pageSize;
        const end   = start + pageSize;

        allRows.forEach(tr => tr.style.display = 'none');
        fr.slice(start, end).forEach(tr => tr.style.display = '');

        const totalEl = document.getElementById('totalPagos');
        if (totalEl) totalEl.textContent = String(fr.length);

        const pageInfo = document.getElementById('pageInfo');
        if (pageInfo) pageInfo.textContent = `Página ${currentPage} de ${totalPages()} (${fr.length} registros)`;
      }

      if (input) {
        input.addEventListener('input', function(){
          const term = this.value.trim().toLowerCase();
          allRows.forEach(tr => {
            const ok = tr.textContent.toLowerCase().includes(term);
            tr.dataset.match = ok ? '1' : '0';
          });
          currentPage = 1;
          render();
        });
      }

      function goFirst(){ currentPage = 1; render(); }
      function goPrev(){ currentPage -= 1; render(); }
      function goNext(){ currentPage += 1; render(); }
      function goLast(){ currentPage = totalPages(); render(); }

      document.getElementById('firstPage')?.addEventListener('click', goFirst);
      document.getElementById('prevPage')?.addEventListener('click', goPrev);
      document.getElementById('nextPage')?.addEventListener('click', goNext);
      document.getElementById('lastPage')?.addEventListener('click', goLast);

      if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function(){
          pageSize = parseInt(this.value, 10) || 20;
          currentPage = 1;
          render();
        });
      }

      render();
    })();
  </script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
