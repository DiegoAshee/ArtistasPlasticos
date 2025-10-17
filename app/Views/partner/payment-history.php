<?php
// partner/payment-history.php
$title = 'Historial de Pagos';
$currentPath = 'partner/payment-history';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Historial de Pagos', 'url' => null],
];
// URL base actual
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Función para construir URL de paginación/filtros
$mkUrl = function (int $p) use ($currentPath, $filters, $pageSize) {
    $params = $filters + ['page' => $p, 'pageSize' => $pageSize];
    return u($currentPath . '?' . http_build_query($params));
};

// Función para formatear fechas
function formatDate($dateString) {
    if (!$dateString) return 'N/A';
    $date = strtotime($dateString);
    return date('d/m/Y', $date);
}

function formatDateTime($dateString) {
    if (!$dateString) return 'N/A';
    $date = strtotime($dateString);
    return date('d/m/Y H:i', $date);
}

ob_start();
?>
<style>
    :root {
        --primary: #a49884;
        --primary-dark: #8a7d6b;
        --bg-light: #f8fafc;
        --text-dark: #2d3748;
        --text-light: #ffffff;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --border-color: #cbd5e0;
        --grid-bg: #dccaaf;
        --card-bg: #dccaaf;
    }

    * {
        box-sizing: border-box;
    }

    .history-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        margin-bottom: 30px;
    }

    .page-header h1 {
        color: var(--text-dark);
        font-size: 28px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    /* Filtros mejorados */
    .filters-section {
        background: var(--card-bg);
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 24px;
    }

    .filters-section h3 {
        margin: 0 0 16px 0;
        color: var(--text-dark);
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filters-form {
        display: grid;
        grid-template-columns: 1fr 1fr auto auto;
        gap: 16px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group label {
        font-weight: 500;
        color: var(--text-dark);
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .form-group input,
    .form-group select {
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        transition: all 0.2s ease;
        width: 100%;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(164, 152, 132, 0.1);
    }

    .filter-actions {
        display: flex;
        gap: 8px;
        grid-column: span 2;
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--text-light);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(164, 152, 132, 0.3);
    }

    .btn-secondary {
        background: #fff;
        color: var(--text-dark);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: var(--bg-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    /* Tabla responsiva */
    .table-container {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }

    .table-header h2 {
        margin: 0;
        color: var(--text-dark);
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-info {
        font-size: 14px;
        color: #6c757d;
    }

    .table-wrapper {
        overflow-x: auto;
        padding: 24px;
    }

    .modern-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .modern-table thead {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .modern-table th {
        padding: 14px 12px;
        text-align: left;
        color: var(--text-light);
        font-weight: 500;
        font-size: 13px;
        white-space: nowrap;
    }

    .modern-table td {
        padding: 14px 12px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-dark);
        font-size: 14px;
    }

    .modern-table tbody tr {
        background: var(--grid-bg);
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: #c9baa3;
    }

    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badges de estado */
    .status-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
        white-space: nowrap;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-approved {
        background: #dcfce7;
        color: #166534;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Enlaces y botones */
    .voucher-link {
        color: var(--primary);
        text-decoration: none;
        padding: 4px 10px;
        border: 1px solid var(--primary);
        border-radius: 6px;
        font-size: 12px;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    .voucher-link:hover {
        background: var(--primary);
        color: var(--text-light);
    }

    /* Paginación */
    .pagination-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        margin-top: 24px;
    }

    .pagination {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination a,
    .pagination span {
        padding: 8px 14px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
        min-width: 40px;
        text-align: center;
    }

    .pagination a:hover {
        background: var(--bg-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    .pagination span.active {
        background: var(--primary);
        color: var(--text-light);
        border-color: var(--primary);
    }

    .pagination span.dots {
        border: none;
        color: #6c757d;
    }

    .pagination-info {
        text-align: center;
        color: #6c757d;
        font-size: 14px;
    }

    /* Estado vacío */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 48px;
        color: #cbd5e0;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        margin: 0 0 8px 0;
        color: var(--text-dark);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .history-container {
            padding: 12px;
        }

        .page-header h1 {
            font-size: 22px;
        }

        .filters-form {
            grid-template-columns: 1fr;
        }

        .filter-actions {
            grid-column: 1;
            width: 100%;
        }

        .btn {
            flex: 1;
            justify-content: center;
        }

        .table-wrapper {
            padding: 12px;
        }

        .modern-table {
            font-size: 13px;
            min-width: 600px;
        }

        .modern-table th,
        .modern-table td {
            padding: 10px 8px;
        }

        .table-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .pagination a,
        .pagination span {
            padding: 6px 10px;
            font-size: 13px;
            min-width: 36px;
        }
    }

    @media (max-width: 480px) {
        .page-header h1 {
            font-size: 20px;
        }

        .modern-table {
            min-width: 500px;
        }
    }

    /* Utilidades */
    .text-success {
        color: var(--success);
        font-weight: 600;
    }

    .text-muted {
        color: #6c757d;
        font-style: italic;
    }

    .amount {
        font-weight: 600;
        white-space: nowrap;
    }
</style>

<div class="history-container">
    <!-- Encabezado -->
    <div class="page-header">
        <h1>
            <i class="fas fa-history"></i> 
            Historial de Pagos
        </h1>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <h3>
            <i class="fas fa-filter"></i>
            Filtros de búsqueda
        </h3>
        <form method="get" action="<?= htmlspecialchars($currentPath) ?>" class="filters-form">
            <div class="form-group">
                <label for="year">
                    <i class="fas fa-calendar-alt"></i>
                    Filtrar por Año:
                </label>
                <select id="year" name="year">
                    <option value="">Todos los años</option>
                    <?php
                    // Obtener años disponibles desde 2000 hasta el año actual
                    $currentYear = date('Y');
                    $startYear = 2000;
                    for ($y = $currentYear; $y >= $startYear; $y--): ?>
                        <option value="<?= $y ?>" <?= (isset($filters['year']) && $filters['year'] == $y) ? 'selected' : '' ?>>
                            <?= $y ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="pageSize">
                    <i class="fas fa-list"></i>
                    Registros por página:
                </label>
                <select id="pageSize" name="pageSize">
                    <option value="10" <?= $pageSize == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $pageSize == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $pageSize == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $pageSize == 100 ? 'selected' : '' ?>>100</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <button class="btn btn-primary">
                    <a href="<?= u($currentPath) ?>" >
                    <i class="fas fa-times"></i> Limpiar
                </a>
                </button>
                
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-container">
        <div class="table-header">
            <h2>
                <i class="fas fa-table"></i> 
                Registros de Pagos
            </h2>
            <span class="table-info">
                <?= $total ?> registro<?= $total != 1 ? 's' : '' ?> encontrado<?= $total != 1 ? 's' : '' ?>
            </span>
        </div>
        
        <?php if (empty($historyPayments)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No se encontraron registros</h3>
                <p>No hay pagos registrados con los filtros aplicados.</p>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar-check"></i> Período</th>
                            <th><i class="fas fa-money-bill-wave"></i> Monto Aporte</th>
                            <th><i class="fas fa-hand-holding-usd"></i> Monto Pagado</th>
                            <th><i class="fas fa-credit-card"></i> Método</th>
                            <th><i class="fas fa-calendar"></i> Fecha de Pago</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-receipt"></i> Comprobante</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historyPayments as $payment): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($payment['monthYear']) ?></strong>
                                </td>
                                <td class="amount">
                                    <?= number_format($payment['amount'], 2) ?> Bs
                                </td>
                                <td class="amount text-success">
                                    <?= number_format($payment['paidAmount'], 2) ?> Bs
                                </td>
                                <td><?= htmlspecialchars($payment['payment_type'] ?? 'N/A') ?></td>
                                <td>
                                    <?= formatDateTime($payment['payment_date']) ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = '';
                                    switch ($payment['paymentStatus'] ?? 1) {
                                        case 0:
                                            $statusClass = 'status-approved';
                                            break;
                                        case 1:
                                            $statusClass = 'status-pending';
                                            break;
                                        case 2:
                                            $statusClass = 'status-rejected';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                    }
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars($payment['status_text'] ?? 'Pendiente') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($payment['voucherImageURL'])): ?>
                                        <a href="<?= u(htmlspecialchars($payment['voucherImageURL'])) ?>" 
                                           target="_blank" 
                                           class="voucher-link"
                                           title="Ver comprobante">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Sin comprobante</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination-wrapper">
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= $mkUrl(1) ?>" title="Primera página">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="<?= $mkUrl($page - 1) ?>" title="Página anterior">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($totalPages, $page + 2);
            ?>
            
            <?php if ($start > 1): ?>
                <a href="<?= $mkUrl(1) ?>">1</a>
                <?php if ($start > 2): ?>
                    <span class="dots">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $mkUrl($i) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?>
                    <span class="dots">...</span>
                <?php endif; ?>
                <a href="<?= $mkUrl($totalPages) ?>"><?= $totalPages ?></a>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= $mkUrl($page + 1) ?>" title="Página siguiente">
                    <i class="fas fa-chevron-right"></i>
                </a>
                <a href="<?= $mkUrl($totalPages) ?>" title="Última página">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="pagination-info">
            Mostrando <?= (($page - 1) * $pageSize) + 1 ?> - <?= min($page * $pageSize, $total) ?> 
            de <?= $total ?> registro<?= $total != 1 ? 's' : '' ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>