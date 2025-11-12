<?php
$title       = 'Conceptos';
$currentPath = 'conceptos/list';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Conceptos', 'url' => null],
];

$rows     = $rows ?? [];
$filters  = $filters ?? ['q'=>'','type'=>'','from'=>'','to'=>''];

$page      = (int)($page ?? 1);
$pageSize  = (int)($pageSize ?? 20);
$total     = (int)($total ?? 0);
$totalPg   = (int)($totalPages ?? 1);

// Obtener parámetros actuales para mantenerlos en la URL
$currentQueryParams = $_GET;
$currentUrl = u('conceptos/list?' . http_build_query($currentQueryParams));

// helper URL para paginación
$mkUrl = function(int $p) use ($currentPath, $currentQueryParams) {
  $qs = $currentQueryParams;
  $qs['page'] = $p;
  if (!isset($qs['pageSize'])) $qs['pageSize'] = 20;
  return u('conceptos/list?' . http_build_query($qs));
};

ob_start();
?>
<style>
  /* ==== ESTILOS MEJORADOS ==== */
  #conceptos-root {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2a2a2a;
  }
  
  /* Botones mejorados */
  #conceptos-root a.btn, #conceptos-root .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 12px;
    border: 1px solid #cfcfcf !important;
    background: #ffffff !important;
    color: #2a2a2a !important;
    text-decoration: none;
    line-height: 1.2;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  
  #conceptos-root a.btn:hover, #conceptos-root .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  #conceptos-root .btn-primary {
    background: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
  }
  
  #conceptos-root .btn-primary:hover {
    background: #5a6268 !important;
    border-color: #5a6268 !important;
  }
  
  #conceptos-root .btn-danger {
    background: #e74c3c !important;
    border-color: #e74c3c !important;
    color: #fff !important;
  }
  
  #conceptos-root .btn-danger:hover {
    background: #d62c1a !important;
    border-color: #d62c1a !important;
  }
  
  #conceptos-root .btn-success {
    background: #28a745 !important;
    border-color: #28a745 !important;
    color: #fff !important;
  }
  
  #conceptos-root .btn-success:hover {
    background: #218838 !important;
    border-color: #218838 !important;
  }
  
  /* Iconos */
  #conceptos-root i {
    line-height: 1;
    font-size: 14px;
  }
  
  /* Formularios mejorados */
  #conceptos-root input, #conceptos-root select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
  }
  
  #conceptos-root input:focus, #conceptos-root select:focus {
    outline: none;
    border-color: #bbae97;
    box-shadow: 0 0 0 3px rgba(187, 174, 151, 0.2);
  }
  
  #conceptos-root label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 5px;
    display: block;
    color: #4a4a4a;
  }
  
  /* Toolbar mejorada */
  #conceptos-root .toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: end;
    gap: 16px;
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f6f2;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  
  /* Tabla mejorada */
  #conceptos-root .modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
  }
  
  #conceptos-root .modern-table th {
    position: sticky;
    top: 0;
    background: #bbae97;
    color: #2a2a2a;
    z-index: 2;
    padding: 14px 16px;
    font-weight: 700;
    font-size: 14px;
    text-align: left;
    border: none;
  }
  
  #conceptos-root .modern-table th:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  
  #conceptos-root .modern-table th:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  
  #conceptos-root .modern-table td {
    padding: 14px 16px;
    line-height: 1.4;
    vertical-align: middle;
    background: #d7cbb5;
    border: none;
  }
  
  #conceptos-root .modern-table tr:nth-child(even) td {
    background: #dccaaf;
  }
  
  #conceptos-root .modern-table td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  
  #conceptos-root .modern-table td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  
  #conceptos-root .table-container {
    background: #cfc4b0;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    overflow: auto;
    padding: 8px;
  }
  
  /* Paginación mejorada */
  #conceptos-root .pagination {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding: 16px;
    background: #f8f6f2;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  
  #conceptos-root .pagination-info {
    font-size: 14px;
    font-weight: 600;
    color: #4a4a4a;
  }
  
  /* Badge para tipos de concepto */
  .badge-type {
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  
  .badge-income {
    background: #D1FADF !important;
    color: #065F46;
  }
  
  .badge-expense {
    background: #FEE4E2 !important;
    color: #D92D20;
  }
  
  /* Mensaje sin resultados */
  .no-results {
    text-align: center;
    padding: 40px;
    font-style: italic;
    color: #6c757d;
    background: #f8f6f2;
    border-radius: 12px;
    margin: 10px 0;
  }
</style>

<div id="conceptos-root">
  <div class="toolbar">
    <form method="get" action="<?= u('conceptos/list') ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
      <input type="hidden" name="page" value="1"><!-- reset al buscar -->
      
      <div>
        <label>Descripción</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Buscar concepto...">
      </div>
      
      <div>
        <label>Tipo</label>
        <select name="type" style="min-width:180px;">
          <option value="">— Todos —</option>
          <option value="Ingreso" <?= $filters['type']==='Ingreso'?'selected':'' ?>>Ingreso</option>
          <option value="Egreso" <?= $filters['type']==='Egreso'?'selected':'' ?>>Egreso</option>
        </select>
      </div>
      
      <div>
        <label>Desde</label>
        <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>">
      </div>
      <div>
        <label>Hasta</label>
        <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>">
      </div>
      <button type="submit" class="btn"><i class="fas fa-search"></i> Buscar</button>
      
    </form>

    <div style="margin-left:auto;display:flex;gap:10px;">
      <a href="<?= u('conceptos/create') ?>" class="btn btn-success">
        <i class="fas fa-plus"></i> Nuevo Concepto
      </a>
    </div>
  </div>

  <div class="table-container">
    <table class="modern-table">
      <thead>
        <tr>
          <!--<th>ID</th>-->
          <th>Descripción</th>
          <th>Tipo</th>
          <th>Fecha Creación</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="5">
            <div class="no-results">
              <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
              No se encontraron conceptos
            </div>
          </td>
        </tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <!--<td><strong>#<?= (int)($r['idConcept'] ?? 0) ?></strong></td>-->
          <td><?= htmlspecialchars($r['description'] ?? '') ?></td>
          <td>
            <span class="badge-type <?= ($r['type'] ?? '') === 'ingreso'  ? 'badge-income' : 'badge-expense' ?>">
              <?= htmlspecialchars($r['type'] ?? '') ?>
            </span>
          </td>
          <td><?= !empty($r['dateCreation']) ? date('d/m/Y H:i', strtotime($r['dateCreation'])) : '-' ?></td>
          <td style="white-space: nowrap;">
            <a href="<?= u('conceptos/edit/' . (int)($r['idConcept'] ?? 0) . '?return_url=' . urlencode($currentUrl)) ?>" 
               title="Editar" class="btn"><i class="fas fa-edit"></i></a>
            <a href="#" title="Eliminar"
               class="btn btn-danger delete-btn" 
               data-id="<?= (int)($r['idConcept'] ?? 0) ?>"
               data-name="<?= htmlspecialchars($r['description'] ?? 'este concepto') ?>"
               data-return-url="<?= urlencode($currentUrl) ?>">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <form method="get" action="<?= u('conceptos/list') ?>" style="display:flex;gap:10px;align-items:center;">
      <?php foreach (($_GET ?? []) as $k=>$v): if ($k==='pageSize') continue; ?>
        <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars(is_array($v)?reset($v):$v) ?>">
      <?php endforeach; ?>
      <label>Por página:</label>
      <select name="pageSize" onchange="this.form.submit()">
        <option value="10"  <?= $pageSize===10?'selected':'' ?>>10</option>
        <option value="20"  <?= $pageSize===20?'selected':'' ?>>20</option>
        <option value="50"  <?= $pageSize===50?'selected':'' ?>>50</option>
        <option value="100" <?= $pageSize===100?'selected':'' ?>>100</option>
      </select>
    </form>

    <div class="pagination-info">Página <?= $page ?> de <?= $totalPg ?> (<?= number_format($total) ?> registros)</div>
    
    <div style="display: flex; gap: 6px;">
      <a class="btn" href="<?= $mkUrl(1) ?>" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>><i class="fas fa-angle-double-left"></i></a>
      <a class="btn" href="<?= $mkUrl(max(1,$page-1)) ?>" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>><i class="fas fa-angle-left"></i></a>
      <a class="btn" href="<?= $mkUrl(min($totalPg,$page+1)) ?>" <?= $page >= $totalPg ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>><i class="fas fa-angle-right"></i></a>
      <a class="btn" href="<?= $mkUrl($totalPg) ?>" <?= $page >= $totalPg ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>><i class="fas fa-angle-double-right"></i></a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();

// Add delete confirmation modal
ob_start();
?>
<div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
  <div style="background:white;padding:25px;border-radius:12px;max-width:400px;width:90%;box-shadow:0 5px 15px rgba(0,0,0,0.3);">
    <h3 style="margin-top:0;color:#2a2a2a;">Confirmar eliminación</h3>
    <p>¿Estás seguro de que deseas eliminar el concepto "<span id="deleteItemName"></span>"?</p>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
      <button id="cancelDelete" class="btn" style="background:#f1f1f1;">Cancelar</button>
      <button id="confirmDelete" class="btn btn-danger">Eliminar</button>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('deleteModal');
  const deleteButtons = document.querySelectorAll('.delete-btn');
  const deleteItemName = document.getElementById('deleteItemName');
  const cancelBtn = document.getElementById('cancelDelete');
  const confirmBtn = document.getElementById('confirmDelete');
  
  let deleteUrl = '';
  let returnUrl = '';
  
  deleteButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.getAttribute('data-id');
      const name = this.getAttribute('data-name');
      returnUrl = this.getAttribute('data-return-url');
      deleteUrl = '<?= u('conceptos/delete/') ?>' + id + '?return_url=' + encodeURIComponent(returnUrl);
      deleteItemName.textContent = name;
      modal.style.display = 'flex';
    });
  });
  
  cancelBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });
  
  confirmBtn.addEventListener('click', function() {
    window.location.href = deleteUrl;
  });
  
  // Close modal when clicking outside
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});
</script>

<style>
#deleteModal {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

#deleteModal h3 {
  font-size: 1.3em;
  margin-bottom: 15px;
}

#deleteModal p {
  margin: 10px 0 20px;
  color: #4a4a4a;
  line-height: 1.5;
}

#deleteModal .btn {
  padding: 8px 16px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

#deleteModal .btn:hover {
  filter: brightness(0.95);
}

#deleteModal .btn-danger {
  background: #e74c3c;
  border: 1px solid #e74c3c;
  color: white;
}
</style>
<?php
$modalContent = ob_get_clean();
$content .= $modalContent;

include __DIR__ . '/../layouts/app.php';