<?php
// partner/payment-history.php

// Variables que deben llegar desde el controlador:
// $historyPayments (array de pagos)
// $totals (['pending'=>X,'paid'=>Y])
// $filters (['filter'=> mes, 'pageSize' => size])
// $page, $pageSize, $total, $totalPages

// URL base actual
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Función para construir URL de paginación/filtros
$mkUrl = function (int $p) use ($currentPath, $filters, $pageSize) {
    $qs = $filters + ['page' => $p, 'pageSize' => $pageSize];
    return u($currentPath . '?' . http_build_query($qs));
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Pagos</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #a49884;
            --primary-dark: #a49884;
            --bg-light: #f8fafc;
            --text-dark: #2d3748;
            --text-light: #ffffff;
            --success: #10b981;
            --warning: orange;
            --danger: rgb(239, 216, 68);
            --border-color: #cbd5e0;
            --grid-bg: #a49884;
        }

        .summary-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 28px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 220px;
            background: #dccaaf;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .summary-card.paid {
            border-top: 4px solid var(--success);
        }

        .summary-card.pending {
            border-top: 4px solid var(--warning);
        }

        .filters-section {
            background: #a49884;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
            color: var(--text-dark);
        }

        .filters-section form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filters-section label {
            font-weight: 500;
            color: var(--text-dark);
        }

        .filters-section input,
        .filters-section select {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #fff;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .filters-section input:focus,
        .filters-section select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }

        .filters-section button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-light);
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filters-section button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgb(180, 168, 147);
        }

        .filters-section a {
            color: var(--text-light);
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .filters-section a:hover {
            color: var(--primary);
            border-color: var(--primary);
        }

        .table-container {
            background: #a49884;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 24px;
            padding: 24px;
            color: var(--text-dark);
        }

        .table-header {
            background: #a49884;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-header h2 {
            margin: 0;
            color: var(--text-dark);
        }

        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--grid-bg);
            border-radius: 12px;
            overflow: hidden;
            color: var(--text-dark);
        }

        .modern-table th,
        .modern-table td {
            padding: 12px 16px;
            line-height: 1.5;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .modern-table thead th {
            position: sticky;
            top: 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--text-light);
            font-weight: 500;
            text-align: left;
        }

        .modern-table tbody tr {
            background: var(--grid-bg);
            transition: all 0.2s ease;
            color: var(--text-dark);
        }

        .modern-table tbody tr:hover {
            background-color: #b8ac98;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #16a34a;
        }

        .status-rejected {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #dc2626;
        }

        .voucher-link {
            color: var(--primary);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--primary);
            border-radius: 8px;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .voucher-link:hover {
            background: var(--primary);
            color: var(--text-light);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .pagination a,
        .pagination span {
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.2s ease;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #fff;
            border-radius: 12px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            color: var(--success);
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .summary-cards {
                flex-direction: column;
            }

            .modern-table {
                font-size: 14px;
            }

            .table-container {
                overflow-x: auto;
            }

            .filters-section form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <h1 style="color: var(--text-dark); margin-bottom: 30px;">
        <i class="fas fa-history"></i> Historial de Pagos
    </h1>

    <!-- Tarjetas resumen -->
    <!-- <div class="summary-cards">
        <div class="summary-card paid">
            <h3><i class="fas fa-check-circle"></i> Total Pagado</h3>
            <p style="font-size: 24px; font-weight: bold; color: var(--success); margin: 10px 0;">
                <?= number_format($totals['paid'], 2) ?> Bs
            </p>
        </div>
        <div class="summary-card pending">
            <h3><i class="fas fa-clock"></i> Total Pendiente</h3>
            <p style="font-size: 24px; font-weight: bold; color: var(--warning); margin: 10px 0;">
                <?= number_format($totals['pending'], 2) ?> Bs
            </p>
        </div>
    </div> -->

    <!-- Filtros -->
    <div class="filters-section">
        <form method="get" action="<?= htmlspecialchars($currentPath) ?>">
            <label for="filter">
                <i class="fas fa-calendar"></i> Filtrar por Mes/Año:
            </label>
            <input type="month" id="filter" name="filter" 
                   value="<?= htmlspecialchars($filters['filter'] ?? '') ?>">
            
            <label for="pageSize">
                <i class="fas fa-list"></i> Registros por página:
            </label>
            <select id="pageSize" name="pageSize">
                <option value="10" <?= $pageSize == 10 ? 'selected' : '' ?>>10</option>
                <option value="20" <?= $pageSize == 20 ? 'selected' : '' ?>>20</option>
                <option value="50" <?= $pageSize == 50 ? 'selected' : '' ?>>50</option>
            </select>
            
            <button type="submit">
                <i class="fas fa-filter"></i> Aplicar
            </button>
            <a href="<?= u($currentPath) ?>">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div>

    <!-- Tabla -->
    <div class="table-container">
        <div class="table-header">
            <h2>
                <i class="fas fa-table"></i> 
                Registros de Pagos 
                <span style="font-size: 16px; font-weight: normal; color: #6c757d;">
                    (<?= $total ?> registros encontrados)
                </span>
            </h2>
        </div>
        
        <?php if (empty($historyPayments)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No se encontraron registros</h3>
                <p>No hay pagos registrados con los filtros aplicados.</p>
            </div>
        <?php else: ?>
            <table class="modern-table">
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> ID Contribución</th>
                        <th><i class="fas fa-money-bill"></i> Monto Contribución</th>
                        <th><i class="fas fa-credit-card"></i> Monto Pagado</th>
                        <th><i class="fas fa-university"></i> Método</th>
                        <th><i class="fas fa-calendar"></i> Fecha de Pago</th>
                        <th><i class="fas fa-calendar-alt"></i> Período</th>
                        <th><i class="fas fa-info-circle"></i> Estado</th>
                        <th><i class="fas fa-receipt"></i> Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historyPayments as $payment): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($payment['idContribution']) ?></strong>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--text-dark);">
                                    <?= number_format($payment['amount'], 2) ?> Bs
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: var(--success);">
                                    <?= number_format($payment['paidAmount'], 2) ?> Bs
                                </span>
                            </td>
                            <td><?= htmlspecialchars($payment['payment_type'] ?? 'N/A') ?></td>
                            <td>
                                <?= $payment['payment_date'] 
                                    ? date('d/m/Y H:i', strtotime($payment['payment_date']))
                                    : 'N/A' ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($payment['monthYear']) ?></strong>
                            </td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch ($payment['paymentStatus'] ?? 1) {
                                    case 1:
                                        $statusClass = 'status-pending';
                                        break;
                                    case 2:
                                        $statusClass = 'status-approved';
                                        break;
                                    case 3:
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
                                       target="_blank" class="voucher-link">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                <?php else: ?>
                                    <span style="color: #6c757d; font-style: italic;">Sin comprobante</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="<?= $mkUrl($page - 1) ?>">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
        <?php endif; ?>

        <?php
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        ?>
        
        <?php if ($start > 1): ?>
            <a href="<?= $mkUrl(1) ?>">1</a>
            <?php if ($start > 2): ?>
                <span style="border: none; color: #6c757d;">...</span>
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
                <span style="border: none; color: #6c757d;">...</span>
            <?php endif; ?>
            <a href="<?= $mkUrl($totalPages) ?>"><?= $totalPages ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="<?= $mkUrl($page + 1) ?>">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    
    <!-- Información de paginación -->
    <div style="text-align: center; margin-top: 15px; color: #6c757d; font-size: 14px;">
        Mostrando <?= (($page - 1) * $pageSize) + 1 ?> - <?= min($page * $pageSize, $total) ?> 
        de <?= $total ?> registros
    </div>
    <?php endif; ?>
</div>
</body>
</html>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>