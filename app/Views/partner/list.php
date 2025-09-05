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

  Métricas (misma rejilla de cards que en dashboard)
  <!-- <div class="dashboard-cards" style="margin-bottom:16px;">
    <div class="card">
      <div class="card-header">
        <div class="card-icon success"><i class="fas fa-users"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Total Socios</div>
        <div class="card-value" id="totalSocios"><?= (int)$totalSocios ?></div>
        <div class="card-change positive">
          <i class="fas fa-arrow-up"></i>
          <span>Actualizado</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon primary"><i class="fas fa-calendar-check"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Nuevos este año</div>
        <div class="card-value"><?= (int)$nuevosEsteAnio ?></div>
        <div class="card-change">
          <i class="fas fa-clock"></i>
          <span><?= date('Y') ?></span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon warning"><i class="fas fa-database"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Accesos rápidos</div>
        <div class="card-value">
          <a href="<?= u('dashboard') ?>" class="dropdown-item" style="padding:6px 10px;border-radius:8px;background:var(--surface-elevated);text-decoration:none;">
            <i class="fas fa-chart-pie"></i> Ir al Dashboard
          </a>
        </div>
      </div>
    </div>
  </div> -->

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
            <th><i class="fas fa-calendar-plus"></i> F. Creación</th>
            <th><i class="fas fa-birthday-cake"></i> F. Nacimiento</th>
            <th><i class="fas fa-calendar-check"></i> F. Registro</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($socios as $socio): ?>
            <tr>
              <td>
                <div class="user-cell" style="display:flex;align-items:center;gap:10px;">
                  <div class="user-avatar-small" style="width:28px;height:28px;border-radius:50%;background:var(--cream-200,#eee);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user"></i>
                  </div>
                  <span><?= htmlspecialchars($socio['name'] ?? '') ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($socio['ci'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['login'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['cellPhoneNumber'] ?? '') ?></td>
              <td class="address-cell" title="<?= htmlspecialchars($socio['address'] ?? '') ?>">
                <?php
                  $addr = (string)($socio['address'] ?? '');
                  $addr = htmlspecialchars($addr, ENT_QUOTES, 'UTF-8');
                  echo (mb_strlen($addr,'UTF-8') > 30) ? mb_substr($addr,0,30,'UTF-8').'…' : $addr;
                ?>
              </td>
              <td><span class="date-badge"><?= !empty($socio['dateCreation'])     ? date('d/m/Y', strtotime($socio['dateCreation']))     : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['birthday'])         ? date('d/m/Y', strtotime($socio['birthday']))         : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['dateRegistration']) ? date('d/m/Y', strtotime($socio['dateRegistration'])) : '-' ?></span></td>
              <td class="actions">
                <div class="action-buttons">
                  <a href="<?= u('partner/edit/' . (int)($socio['idPartner'] ?? 0)) ?>" class="btn btn-sm btn-outline" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e1e5e9;color:#333;text-decoration:none;">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= u('partner/delete/' . (int)($socio['idPartner'] ?? 0)) ?>"
                     class="btn btn-sm btn-danger"
                     title="Eliminar"
                     onclick="return confirm('¿Seguro que desea eliminar este socio?');"
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

  <!-- Buscador en vivo + paginación -->
  <script>
    (function(){
      const input = document.getElementById('searchInput');
      const table = document.getElementById('tablaSocios');
      if (!table) return;

      const tbody = table.querySelector('tbody');
      const allRows = Array.from(tbody.querySelectorAll('tr'));

      // estado
      let currentPage = 1;
      const pageSizeSelect = document.getElementById('pageSize');
      let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '20', 10);

      // inicializa data-match para todas las filas
      allRows.forEach(tr => tr.dataset.match = '1');

      // helpers
      function filteredRows(){
        return allRows.filter(tr => tr.dataset.match !== '0');
      }

      function totalPages(){
        const total = filteredRows().length;
        return Math.max(1, Math.ceil(total / pageSize));
      }

      function clampPage(){
        const tp = totalPages();
        if (currentPage > tp) currentPage = tp;
        if (currentPage < 1)  currentPage = 1;
      }

      function render(){
        clampPage();
        const fr = filteredRows();
        const start = (currentPage - 1) * pageSize;
        const end   = start + pageSize;

        // ocultar todo
        allRows.forEach(tr => tr.style.display = 'none');
        // mostrar solo el rango visible
        fr.slice(start, end).forEach(tr => tr.style.display = '');

        // actualizar métricas
        const totalEl = document.getElementById('totalSocios');
        if (totalEl) totalEl.textContent = String(fr.length);

        const pageInfo = document.getElementById('pageInfo');
        if (pageInfo) pageInfo.textContent = `Página ${currentPage} de ${totalPages()} (${fr.length} registros)`;
      }

      // búsqueda en vivo
      if (input) {
        input.addEventListener('input', function(){
          const term = this.value.trim().toLowerCase();
          allRows.forEach(tr => {
            const ok = tr.textContent.toLowerCase().includes(term);
            tr.dataset.match = ok ? '1' : '0';
          });
          currentPage = 1; // vuelve al inicio
          render();
        });
      }

      // paginación
      function goFirst(){ currentPage = 1; render(); }
      function goPrev(){ currentPage -= 1; render(); }
      function goNext(){ currentPage += 1; render(); }
      function goLast(){ currentPage = totalPages(); render(); }

      const btnFirst = document.getElementById('firstPage');
      const btnPrev  = document.getElementById('prevPage');
      const btnNext  = document.getElementById('nextPage');
      const btnLast  = document.getElementById('lastPage');

      if (btnFirst) btnFirst.addEventListener('click', goFirst);
      if (btnPrev)  btnPrev.addEventListener('click',  goPrev);
      if (btnNext)  btnNext.addEventListener('click',  goNext);
      if (btnLast)  btnLast.addEventListener('click',  goLast);

      if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function(){
          pageSize = parseInt(this.value, 10) || 20;
          currentPage = 1;
          render();
        });
      }

      // primera renderizada
      render();
    })();
  </script>

  <!-- Add required library for PDF export -->
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

            const partners = result.data || [];
            if (partners.length === 0) {
                throw new Error('No se encontraron socios para exportar');
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'landscape' });

            doc.setFontSize(20);
            doc.text('Lista Completa de Socios', 15, 15);

            doc.setFontSize(10);
            doc.text('Generado el: ' + new Date().toLocaleDateString(), 15, 25);

            const headers = ['Nombre', 'CI', 'Usuario', 'Correo', 'Teléfono', 'Dirección', 'F. Nac.', 'F. Reg.', 'F. Creación'];
            const columnPositions = [10, 40, 70, 100, 130, 160, 200, 225, 250];

            doc.setFontSize(8);
            doc.setFont('helvetica', 'bold');
            headers.forEach((header, i) => {
                doc.text(header, columnPositions[i], 35);
            });

            doc.setDrawColor(0);
            doc.setLineWidth(0.5);
            doc.line(15, 37, 300, 37);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(6);

            const wrapText = (text, maxWidth) => {
                if (!text) return [''];
                const words = text.toString().split(' ');
                const lines = [];
                let currentLine = words[0] || '';
                for (let i = 1; i < words.length; i++) {
                    const word = words[i];
                    const width = doc.getTextWidth(currentLine + ' ' + word);
                    if (width < maxWidth) {
                        currentLine += ' ' + word;
                    } else {
                        lines.push(currentLine);
                        currentLine = word;
                    }
                }
                lines.push(currentLine);
                return lines;
            };

            let y = 45;
            partners.forEach((partner, index) => {
                if (y > 185) { // Ajusta el límite según tamaño de página
                    doc.addPage();
                    y = 20;
                    doc.setFontSize(8);
                    doc.setFont('helvetica', 'bold');
                    headers.forEach((header, i) => {
                        doc.text(header, columnPositions[i], y);
                    });
                    doc.line(15, y + 2, 300, y + 2);
                    y = 30;
                    doc.setFont('helvetica', 'normal');
                    doc.setFontSize(6);
                }

                const formatDate = (dateString) => {
                    if (!dateString) return 'N/A';
                    try {
                        const date = new Date(dateString);
                        return isNaN(date.getTime()) ? dateString : date.toLocaleDateString();
                    } catch (e) {
                        return dateString || 'N/A';
                    }
                };

                const row = [
                    partner.name || 'N/A',
                    partner.ci || 'N/A', // corregido: 'ci' minúscula
                    partner.login || 'N/A',
                    partner.email || 'N/A',
                    partner.cellPhoneNumber || 'N/A',
                    partner.address || 'N/A',
                    formatDate(partner.birthday),
                    formatDate(partner.dateRegistration),
                    formatDate(partner.dateCreation)
                ];

                let maxLines = 1;
                row.forEach((cell, i) => {
                    const columnWidth = i < columnPositions.length - 1 ?
                        columnPositions[i + 1] - columnPositions[i] - 5 :
                        30;
                    let lines = [cell];
                    if (i === 5) { // address
                        lines = wrapText(cell, columnWidth);
                    }
                    lines.forEach((line, lineIndex) => {
                        doc.text(line, columnPositions[i], y + (lineIndex * 3));
                    });
                    if (lines.length > maxLines) maxLines = lines.length;
                });

                y += maxLines * 5;

                if (index < partners.length - 1) {
                    doc.setDrawColor(200);
                    doc.setLineWidth(0.1);
                    doc.line(15, y - 2, 300, y - 2);
                    doc.setDrawColor(0);
                }
            });

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

            doc.save('socios_completo_' + new Date().toISOString().split('T')[0] + '.pdf');

        } catch (error) {
            alert('Error al generar el PDF. Por favor, intente nuevamente.');
        } finally {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
});
</script>
$content = ob_get_clean();

// ---- Incluir layout principal (misma forma que dashboard) ----
include __DIR__ . '/../layouts/app.php';
