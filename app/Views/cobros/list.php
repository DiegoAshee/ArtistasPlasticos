<?php
$title       = 'Cobros Pagados';
$currentPath = 'cobros/list';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Cobros', 'url' => u('cobros/list')],
  ['label' => 'Pagadas', 'url' => null],
];

$rows     = $rows ?? [];
$types    = $types ?? [];
$contribs = $contribs ?? [];
$filters  = $filters ?? ['q'=>'','idPaymentType'=>'','idContribution'=>'','from'=>'','to'=>''];

$page      = (int)($page ?? 1);
$pageSize  = (int)($pageSize ?? 20);
$total     = (int)($total ?? 0);
$totalPg   = (int)($totalPages ?? 1);

// helper URL para paginación
$mkUrl = function(int $p) use ($currentPath) {
  $qs = $_GET;
  $qs['page'] = $p;
  if (!isset($qs['pageSize'])) $qs['pageSize'] = 20;
  return u(($currentPath==='cobros/debidas'?'cobros/debidas':'cobros/list') . '?' . http_build_query($qs));
};

ob_start();
?>
<style>
  /* ==== ESTILOS MEJORADOS ==== */
  #cobros-root {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2a2a2a;
  }
  
  /* Botones mejorados */
  #cobros-root a.btn, #cobros-root .btn {
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
  
  #cobros-root a.btn:hover, #cobros-root .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  #cobros-root .btn-primary {
    background: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
  }
  
  #cobros-root .btn-primary:hover {
    background: #5a6268 !important;
    border-color: #5a6268 !important;
  }
  
  #cobros-root .btn-danger {
    background: #e74c3c !important;
    border-color: #e74c3c !important;
    color: #fff !important;
  }
  
  #cobros-root .btn-danger:hover {
    background: #d62c1a !important;
    border-color: #d62c1a !important;
  }
  
  /* Badge mejorado */
  #cobros-root .badge {
    display: inline-block;
    border-radius: 12px;
    padding: 6px 12px;
    font-weight: 700;
    font-size: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  
  /* Iconos */
  #cobros-root i {
    line-height: 1;
    font-size: 14px;
  }
  
  /* Formularios mejorados */
  #cobros-root input, #cobros-root select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
  }
  
  #cobros-root input:focus, #cobros-root select:focus {
    outline: none;
    border-color: #bbae97;
    box-shadow: 0 0 0 3px rgba(187, 174, 151, 0.2);
  }
  
  #cobros-root label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 5px;
    display: block;
    color: #4a4a4a;
  }
  
  /* Toolbar mejorada */
  #cobros-root .toolbar {
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
  #cobros-root .modern-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 8px;
  }
  
  #cobros-root .modern-table th {
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
  
  #cobros-root .modern-table th:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  
  #cobros-root .modern-table th:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  
  #cobros-root .modern-table td {
    padding: 14px 16px;
    line-height: 1.4;
    vertical-align: middle;
    background: #d7cbb5;
    border: none;
  }
  
  #cobros-root .modern-table tr:nth-child(even) td {
    background: #dccaaf;
  }
  
  #cobros-root .modern-table td:first-child {
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
  }
  
  #cobros-root .modern-table td:last-child {
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
  }
  
  #cobros-root .table-container {
    background: #cfc4b0;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,.08);
    overflow: auto;
    padding: 8px;
  }
  
  /* Paginación mejorada */
  #cobros-root .pagination {
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
  
  #cobros-root .pagination-info {
    font-size: 14px;
    font-weight: 600;
    color: #4a4a4a;
  }
  
  /* Estado de pagado */
  .status-paid {
    background: #D1FADF !important;
    color: #065F46;
    padding: 6px 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  
  .status-paid:before {
    content: "✓";
    font-weight: bold;
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

<div id="cobros-root">
  <div class="toolbar">
    <form method="get" action="<?= u('cobros/list') ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
      <input type="hidden" name="page" value="1"><!-- reset al buscar -->
      <div>
        <label>Buscar</label>
        <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Nombre, CI, tipo o aportación" style="min-width:240px;">
      </div>
      <div>
        <label>Tipo de pago</label>
        <select name="idPaymentType" style="min-width:180px;">
          <option value="">— Todos —</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= (int)($t['idPaymentType'] ?? 0) ?>" <?= ($filters['idPaymentType']!==''
                && (int)$filters['idPaymentType']===(int)($t['idPaymentType']??-1))?'selected':'' ?>>
              <?= htmlspecialchars($t['label'] ?? ('Tipo #'.(int)($t['idPaymentType']??0))) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Aportación</label>
        <select name="idContribution" style="min-width:220px;">
          <option value="">— Todas —</option>
          <?php foreach ($contribs as $c): ?>
            <option value="<?= (int)($c['idContribution'] ?? 0) ?>" <?= ($filters['idContribution']!==''
                && (int)$filters['idContribution']===(int)($c['idContribution']??-1))?'selected':'' ?>>
              <?= htmlspecialchars($c['label'] ?? ('Aporte #'.(int)($c['idContribution']??0))) ?>
            </option>
          <?php endforeach; ?>
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
      <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Buscar</button>
      <a href="<?= u('cobros/list') ?>" class="btn"><i class="fas fa-eraser"></i> Limpiar</a>
    </form>

    <div style="margin-left:auto;display:flex;gap:10px;">
      <a href="<?= u('cobros/debidas') ?>" class="btn"><i class="fas fa-list-alt"></i> Ver Debidas</a>
      <a href="<?= u('cobros/create') ?>" class="btn-primary"><i class="fas fa-plus-circle"></i> Nuevo Cobro</a>
    </div>
  </div>

  <div class="table-container">
    <table class="modern-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Quién</th>
          <th>CI</th>
          <th>Tipo de pago</th>
          <th>Aportación</th>
          <th>Monto</th>
          <th>Fecha</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="9">
            <div class="no-results">
              <i class="fas fa-search" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
              No se encontraron resultados para tu búsqueda
            </div>
          </td>
        </tr>
      <?php else: foreach ($rows as $r): ?>
        <tr>
          <td><strong>#<?= (int)($r['idPayment'] ?? 0) ?></strong></td>
          <td><?= htmlspecialchars($r['partnerName'] ?? '') ?></td>
          <td><?= htmlspecialchars($r['partnerCI']   ?? '') ?></td>
          <td><?= htmlspecialchars($r['paymentTypeName'] ?: ('Tipo #'.(int)($r['idPaymentType']??0))) ?></td>
          <td><?= htmlspecialchars($r['contributionName'] ?: ('Aporte #'.(int)($r['idContribution']??0))) ?></td>
          <td><strong><?= number_format((float)($r['paidAmount'] ?? 0), 2, '.', ',') ?></strong></td>
          <td><?= !empty($r['dateCreation']) ? date('d/m/Y H:i', strtotime($r['dateCreation'])) : '-' ?></td>
          <td><span class="status-paid">Pagado</span></td>
          <td style="white-space: nowrap;">
            <a href="<?= u('cobros/edit/' . (int)($r['idPayment'] ?? 0)) ?>" title="Editar" class="btn"><i class="fas fa-edit"></i></a>
            <a href="<?= u('cobros/delete/' . (int)($r['idPayment'] ?? 0)) ?>" title="Eliminar"
               class="btn btn-danger" onclick="return confirm('¿Eliminar este cobro?');">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination">
    <form method="get" action="<?= u('cobros/list') ?>" style="display:flex;gap:10px;align-items:center;">
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
include __DIR__ . '/../layouts/app.php';
?>