<?php
$title = 'Lista de Socios';
$currentPath = 'cobros/socios';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Cobros', 'url' => u('cobros/list')],
    ['label' => 'Socios', 'url' => null],
];

$rows = $rows ?? [];
$filters = $filters ?? ['q' => ''];

$page = (int)($page ?? 1);
$pageSize = (int)($pageSize ?? 20);
$total = (int)($total ?? 0);
$totalPg = (int)($totalPages ?? 1);

// helper URL para paginación
$mkUrl = function(int $p) use ($currentPath) {
    $qs = $_GET;
    $qs['page'] = $p;
    if (!isset($qs['pageSize'])) $qs['pageSize'] = 20;
    return u('cobros/socios?' . http_build_query($qs));
};

// Función para formatear fecha
$formatDate = function($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return 'No registrada';
    }
    return date('d/m/Y', strtotime($date));
};

ob_start();
?>
<style>
    /* Estilos similares a las otras vistas de cobros */
    #cobros-root {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #2a2a2a;
    }
    
    .toolbar {
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
    
    .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    
    .modern-table th {
        background: #bbae97;
        color: #2a2a2a;
        padding: 14px 16px;
        font-weight: 700;
        font-size: 14px;
        text-align: left;
    }
    
    .modern-table td {
        padding: 14px 16px;
        background: #d7cbb5;
        vertical-align: middle;
    }
    
    .table-container {
        background: #cfc4b0;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,.08);
        overflow: auto;
        padding: 8px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #cfcfcf;
        background: #ffffff;
        color: #2a2a2a;
        text-decoration: none;
        font-weight: 600;
        font-size: 13px;
        transition: all 0.2s ease;
    }
    
    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-primary {
        background: #6c757d;
        border-color: #6c757d;
        color: #fff;
    }
    
    .btn-info {
        background: #17a2b8;
        border-color: #17a2b8;
        color: #fff;
    }
    
    .positive-amount {
        color: #065F46;
        font-weight: 700;
        font-size: 14px;
    }
    
    .negative-amount {
        color: #991B1B;
        font-weight: 700;
        font-size: 14px;
    }
    
    .registration-date {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    
    .socio-info {
        display: flex;
        flex-direction: column;
    }
    
    .socio-name {
        font-weight: 600;
        margin-bottom: 2px;
    }
    
    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .pagination {
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
    
    .pagination-info {
        font-size: 14px;
        font-weight: 600;
        color: #4a4a4a;
    }
    
    .no-results {
        text-align: center;
        padding: 40px;
        font-style: italic;
        color: #6c757d;
        background: #f8f6f2;
        border-radius: 12px;
        margin: 10px 0;
    }
    
    .stats-bar {
        display: flex;
        justify-content: space-around;
        background: #e9ecef;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-weight: 600;
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 18px;
        font-weight: 700;
    }
</style>

<div id="cobros-root">
    <div class="toolbar">
        <form method="get" action="<?= u('cobros/socios') ?>" style="display:flex;flex-wrap:wrap;gap:16px;align-items:end;">
            <input type="hidden" name="page" value="1">
            <div>
                <label>Buscar socio</label>
                <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>" placeholder="Nombre o CI" style="min-width:240px;">
            </div>
            <button type="submit" class="btn"><i class="fas fa-search"></i> Buscar</button>
            <a href="<?= u('cobros/socios') ?>" class="btn"><i class="fas fa-eraser"></i> Limpiar</a>
        </form>

        <div style="margin-left:auto;display:none;gap:10px;">
            <a href="<?= u('cobros/list') ?>" class="btn btn-primary"><i class="fas fa-money-bill-wave"></i> Pagadas</a>
            <a href="<?= u('cobros/debidas') ?>" class="btn btn-primary"><i class="fas fa-exclamation-circle"></i> Debidas</a>
        </div>
    </div>

    <?php if (!empty($rows)): ?>
    <div class="stats-bar">
        <div class="stat-item">
            <div>Total Socios</div>
            <div class="stat-value"><?= number_format($total) ?></div>
        </div>
        <div class="stat-item">
            <div>Total Pagado</div>
            <div class="stat-value positive-amount"><?= number_format(array_sum(array_column($rows, 'totalPaid')), 2, '.', ',') ?></div>
        </div>
        <div class="stat-item">
            <div>Total Deuda</div>
            <div class="stat-value negative-amount"><?= number_format(array_sum(array_column($rows, 'totalDebt')), 2, '.', ',') ?></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="table-container">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>CI</th>
                    <th>Total Pagado</th>
                    <th>Total Deuda</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="5">
                        <div class="no-results">
                            <i class="fas fa-users" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                            No se encontraron socios
                        </div>
                    </td>
                </tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <div class="socio-info">
                            <div class="socio-name"><?= htmlspecialchars($r['partnerName'] ?? '') ?></div>
                            <div class="registration-date">
                                Registrado: <?= $formatDate($r['partnerRegistrationDate'] ?? '') ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($r['partnerCI'] ?? '') ?></td>
                    <td class="positive-amount"><?= number_format((float)($r['totalPaid'] ?? 0), 2, '.', ',') ?></td>
                    <td class="negative-amount"><?= number_format((float)($r['totalDebt'] ?? 0), 2, '.', ',') ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?= u('cobros/list?idPartner=' . (int)($r['idPartner'] ?? 0)) ?>" 
                               class="btn btn-info" title="Ver pagos realizados">
                                <i class="fas fa-money-bill-wave"></i> Pagos
                            </a>
                            <a href="<?= u('cobros/debidas?idPartner=' . (int)($r['idPartner'] ?? 0)) ?>" 
                               class="btn btn-primary" title="Ver deudas pendientes">
                                <i class="fas fa-exclamation-circle"></i> Deudas
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <form method="get" action="<?= u('cobros/socios') ?>" style="display:flex;gap:10px;align-items:center;">
            <?php foreach (($_GET ?? []) as $k=>$v): if ($k==='pageSize') continue; ?>
                <input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars(is_array($v)?reset($v):$v) ?>">
            <?php endforeach; ?>
            <label>Por página:</label>
            <select name="pageSize" onchange="this.form.submit()">
                <option value="10"  <?= $pageSize===10?'selected':'' ?>>10</option>
                <option value="20"  <?= $pageSize===20?'selected':'' ?>>20</option>
                <option value="50"  <?= $pageSize===50?'selected':'' ?>>50</option>
            </select>
        </form>

        <div class="pagination-info">Página <?= $page ?> de <?= $totalPg ?> (<?= number_format($total) ?> socios)</div>
        
        <div style="display: flex; gap: 6px;">
            <a class="btn" href="<?= $mkUrl(1) ?>" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                <i class="fas fa-angle-double-left"></i>
            </a>
            <a class="btn" href="<?= $mkUrl(max(1,$page-1)) ?>" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                <i class="fas fa-angle-left"></i>
            </a>
            <a class="btn" href="<?= $mkUrl(min($totalPg,$page+1)) ?>" <?= $page >= $totalPg ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                <i class="fas fa-angle-right"></i>
            </a>
            <a class="btn" href="<?= $mkUrl($totalPg) ?>" <?= $page >= $totalPg ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                <i class="fas fa-angle-double-right"></i>
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';