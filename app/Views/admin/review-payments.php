<?php
$title = 'Revisar Pagos Pendientes';
$currentPath = 'admin/review-payments';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Administración', 'url' => u('admin')],
    ['label' => 'Revisar Pagos', 'url' => null],
];

// Parámetros de filtros y paginación
$filters = $filters ?? ['partner' => '', 'status' => '1', 'dateFrom' => '', 'dateTo' => ''];
$page = (int)($page ?? 1);
$pageSize = (int)($pageSize ?? 20);
$total = (int)($total ?? 0);
$totalPages = (int)($totalPages ?? 1);
$viewMode = $viewMode ?? 'partners'; // 'partners' o 'payments'
$selectedPartner = $selectedPartner ?? null;

// Datos
$partners = $partners ?? [];
$payments = $payments ?? [];
$paymentGroups = $paymentGroups ?? [];

// Helper para URLs
$buildUrl = function(array $params = []) use ($currentPath, $filters, $page, $pageSize, $viewMode) {
    $defaultParams = [
        'partner' => $filters['partner'],
        'status' => $filters['status'],
        'dateFrom' => $filters['dateFrom'],
        'dateTo' => $filters['dateTo'],
        'page' => $page,
        'pageSize' => $pageSize,
        'mode' => $viewMode
    ];
    $urlParams = array_merge($defaultParams, $params);
    $urlParams = array_filter($urlParams, function($v) { return $v !== '' && $v !== null; });
    return u($currentPath . '?' . http_build_query($urlParams));
};

ob_start();
?>
<style>
    /* Variables CSS */
    :root {
        --primary: #bbae97;
        --primary-dark: #9d917dff;
        --success: #10b981;
        --warning: #f59e0b;
        --danger: #ef4444;
        --info: #3b82f6;
        --bg-light: #f8fafc;
        --text-dark: #1f2937;
        --text-light: #bbae97;
        --border-color: #d1d5db;
        --grid-bg: #f3f4f6;
    }

    #admin-payments-root {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-dark);
    }

    /* Botones mejorados */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: white;
        color: var(--text-dark);
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-primary { background: var(--primary); border-color: var(--primary); color: white; }
    .btn-success { background: var(--success); border-color: var(--success); color: white; }
    .btn-danger { background: var(--danger); border-color: var(--danger); color: white; }
    .btn-warning { background: var(--warning); border-color: var(--warning); color: white; }
    .btn-info { background: var(--info); border-color: var(--info); color: white; }

    .btn-sm { padding: 6px 12px; font-size: 12px; }
    .btn-lg { padding: 12px 24px; font-size: 16px; }

    /* Cards y estadísticas */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border-left: 4px solid var(--primary);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card.pending { border-left-color: var(--warning); }
    .stat-card.approved { border-left-color: var(--success); }
    .stat-card.rejected { border-left-color: var(--danger); }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--text-dark);
    }

    .stat-label {
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
    }

    /* Filtros */
    .filters-section {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }

    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        align-items: end;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 6px;
        color: var(--text-dark);
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    /* Pestañas de vista */
    .view-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 24px;
        background: var(--bg-light);
        padding: 4px;
        border-radius: 10px;
    }

    .view-tab {
        flex: 1;
        text-align: center;
        padding: 12px 16px;
        background: transparent;
        border: none;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        color: #6b7280;
        text-decoration: none;
    }

    .view-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Tablas */
    .table-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th,
    .table td {
        padding: 16px;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .table th {
        background: var(--bg-light);
        font-weight: 600;
        color: var(--text-dark);
        font-size: 14px;
        position: sticky;
        top: 0;
    }

    .table tbody tr:hover {
        background: #fafafa;
    }

    /* Badges de estado */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Información de socio */
    .partner-info {
        display: flex;
        flex-direction: column;
    }

    .partner-name {
        font-weight: 600;
        margin-bottom: 4px;
    }

    .partner-details {
        font-size: 12px;
        color: #6b7280;
    }

    /* Información de pago */
    .payment-info {
        display: flex;
        flex-direction: column;
    }

    .payment-amount {
        font-weight: 700;
        font-size: 16px;
        color: var(--text-dark);
        margin-bottom: 4px;
    }

    .payment-details {
        font-size: 12px;
        color: #6b7280;
    }

    /* Grupos de pagos */
    .payment-group {
        background: #f8fafc;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .payment-group-header {
        background: var(--primary);
        color: white;
        padding: 16px 20px;
        font-weight: 600;
        display: flex;
        justify-content: between;
        align-items: center;
    }

    .payment-group-actions {
        display: flex;
        gap: 8px;
    }

    .payment-group-body {
        padding: 0;
    }

    /* Comprobante modal/preview */
    .voucher-preview {
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .voucher-preview:hover {
        transform: scale(1.05);
    }

    .voucher-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid var(--border-color);
    }

    /* Modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        color:var(--primary-dark);
        background: white;
        margin: 2% auto;
        padding: 0;
        width: 90%;
        max-width: 800px;
        border-radius: 12px;
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
        position: relative;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-body {
        padding: 24px;
    }

    .close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #6b7280;
        transition: color 0.2s ease;
    }

    .close:hover {
        color: var(--danger);
    }

    /* Paginación */
    .pagination {
        display: flex;
        justify-content: between;
        align-items: center;
        padding: 20px 24px;
        background: white;
        border-top: 1px solid var(--border-color);
        border-radius: 0 0 12px 12px;
        margin-top: 0;
    }

    .pagination-info {
        font-size: 14px;
        color: #6b7280;
    }

    .pagination-controls {
        display: flex;
        gap: 8px;
    }

    /* Alertas */
    .alert {
        padding: 16px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-left: 4px solid var(--success);
    }

    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid var(--danger);
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border-left: 4px solid var(--info);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .filters-grid {
            grid-template-columns: 1fr;
        }
        
        .view-tabs {
            flex-direction: column;
            background-color: #837a6bff;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            min-width: 600px;
        }
    }

    /* Sin resultados */
    .no-results {
        text-align: center;
        padding: 60px 20px;
        color: #6b7280;
    }

    .no-results i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #d1c9bbff;
    }
</style>

<div id="admin-payments-root">
    <!-- Alertas -->
    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-value"><?= $stats['pending'] ?? 0 ?></div>
            <div class="stat-label">
                <i class="fas fa-clock"></i> Pagos Pendientes
            </div>
        </div>
        <div class="stat-card approved">
            <div class="stat-value"><?= $stats['approved'] ?? 0 ?></div>
            <div class="stat-label">
                <i class="fas fa-check"></i> Pagos Aprobados
            </div>
        </div>
        <div class="stat-card rejected">
            <div class="stat-value"><?= $stats['rejected'] ?? 0 ?></div>
            <div class="stat-label">
                <i class="fas fa-times"></i> Pagos Rechazados
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value">Bs. <?= number_format($stats['totalAmount'] ?? 0, 2) ?></div>
            <div class="stat-label">
                <i class="fas fa-dollar-sign"></i> Total Pendiente
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <!-- <div class="filters-section">
        <form method="get" action="<?= u($currentPath) ?>">
            <input type="hidden" name="mode" value="<?= htmlspecialchars($viewMode) ?>">
            <?php if ($selectedPartner): ?>
                <input type="hidden" name="partner" value="<?= (int)$selectedPartner['idPartner'] ?>">
            <?php endif; ?>
            
            <div class="filters-grid">
                <?php if ($viewMode === 'partners'): ?>
                <div class="form-group">
                    <label for="partner">Buscar Socio</label>
                    <input type="text" id="partner" name="partner" 
                           value="<?= htmlspecialchars($filters['partner']) ?>"
                           placeholder="Nombre o CI del socio">
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="status">Estado de Pago</label>
                    <select id="status" name="status">
                        <option value="">Todos los estados</option>
                        <option value="1" <?= $filters['status'] === '1' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="2" <?= $filters['status'] === '2' ? 'selected' : '' ?>>Aprobados</option>
                        <option value="3" <?= $filters['status'] === '3' ? 'selected' : '' ?>>Rechazados</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dateFrom">Desde</label>
                    <input type="date" id="dateFrom" name="dateFrom" 
                           value="<?= htmlspecialchars($filters['dateFrom']) ?>">
                </div>
                
                <div class="form-group">
                    <label for="dateTo">Hasta</label>
                    <input type="date" id="dateTo" name="dateTo" 
                           value="<?= htmlspecialchars($filters['dateTo']) ?>">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="<?= u($currentPath . '?mode=' . $viewMode) ?>" class="btn">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div> -->

    <!-- Pestañas de vista -->
    <div class="view-tabs">
        <a href="<?= $buildUrl(['mode' => 'partners', 'partner' => '']) ?>" 
           class="view-tab <?= $viewMode === 'partners' ? 'active' : '' ?>">
            <i class="fas fa-users"></i> Ver por Socios
        </a>
        <!-- <a href="<?= $buildUrl(['mode' => 'payments']) ?>" 
           class="view-tab <?= $viewMode === 'payments' ? 'active' : '' ?>">
            <i class="fas fa-list"></i> Ver Todos los Pagos
        </a> -->
    </div>

    <!-- Contenido principal -->
    <?php if ($viewMode === 'partners'): ?>
        <!-- Vista de socios -->
        <div class="table-container">
            <?php if ($selectedPartner): ?>
                <!-- Detalle de pagos del socio seleccionado -->
                <div style="padding: 20px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="margin: 0;">Pagos de <?= htmlspecialchars($selectedPartner['name']) ?></h3>
                        <p style="margin: 4px 0 0 0; opacity: 0.9;">CI: <?= htmlspecialchars($selectedPartner['ci']) ?></p>
                    </div>
                    <a href="<?= $buildUrl(['partner' => '']) ?>" class="btn" style="background: white; color: var(--primary);">
                        <i class="fas fa-arrow-left"></i> Volver a Socios
                    </a>
                </div>

                <!-- Grupos de pagos por comprobante -->
                <div style="padding: 20px;">
                    <?php if (empty($paymentGroups)): ?>
                        <div class="no-results">
                            <i class="fas fa-inbox"></i>
                            <h3>No hay pagos para revisar</h3>
                            <p>Este socio no tiene pagos pendientes de revisión.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($paymentGroups as $group): ?>
                            <div class="payment-group">
                                <div class="payment-group-header">
                                    <div>
                                        <strong>
                                            <?= count($group['payments']) === 1 ? 'Pago Individual' : 'Pago Múltiple (' . count($group['payments']) . ' contribuciones)' ?>
                                        </strong>
                                        <div style="font-weight: normal; font-size: 14px; margin-top: 4px;">
                                            Total: Bs. <?= number_format($group['totalAmount'], 2) ?> | 
                                            Fecha: <?= date('d/m/Y H:i', strtotime($group['paymentDate'])) ?>
                                        </div>
                                    </div>
                                    <div class="payment-group-actions">
                                        <?php if ($group['voucherImageURL']): ?>
                                            <button type="button" class="btn btn-info btn-sm voucher-btn"
                                                    data-voucher="<?= htmlspecialchars($group['voucherImageURL']) ?>">
                                                <i class="fas fa-image"></i> Ver Comprobante
                                            </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-success btn-sm approve-group-btn"
                                                data-group="<?= htmlspecialchars($group['groupId']) ?>">
                                            <i class="fas fa-check"></i> Aprobar Todos
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm reject-group-btn"
                                                data-group="<?= htmlspecialchars($group['groupId']) ?>">
                                            <i class="fas fa-times"></i> Rechazar Todos
                                        </button>
                                    </div>
                                </div>
                                <div class="payment-group-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Contribución</th>
                                                <th>Período</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                                <!-- <th>Acciones</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($group['payments'] as $payment): ?>
                                                <tr>
                                                    <td>
                                                        <div class="payment-info">
                                                            <div><?= htmlspecialchars($payment['contributionName'] ?: 'Contribución #' . $payment['idContribution']) ?></div>
                                                            <div class="payment-details">ID: <?= (int)$payment['idPayment'] ?></div>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($payment['monthYear']) ?></td>
                                                    <td>
                                                        <div class="payment-amount">Bs. <?= number_format($payment['paidAmount'], 2) ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?= $payment['paymentStatus'] == 1 ? 'pending' : ($payment['paymentStatus'] == 2 ? 'approved' : 'rejected') ?>">
                                                            <?= $payment['paymentStatus'] == 1 ? 'Pendiente' : ($payment['paymentStatus'] == 2 ? 'Aprobado' : 'Rechazado') ?>
                                                        </span>
                                                    </td>
                                                    <!-- <td>
                                                        <div style="display: flex; gap: 4px;">
                                                            <button type="button" class="btn btn-success btn-sm approve-btn"
                                                                    data-payment="<?= (int)$payment['idPayment'] ?>">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm reject-btn"
                                                                    data-payment="<?= (int)$payment['idPayment'] ?>">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </td> -->
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Lista de socios con pagos pendientes -->
                <table class="table">
                    <thead>
                        <tr>
                            <th>Socio</th>
                            <th>CI</th>
                            <th>Pagos Pendientes</th>
                            <th>Total Pendiente</th>
                            <th>Último Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($partners)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="no-results">
                                        <i class="fas fa-users"></i>
                                        <h3>No hay socios con pagos pendientes</h3>
                                        <p>Todos los pagos están al día o no hay pagos que revisar.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($partners as $partner): ?>
                                <tr>
                                    <td>
                                        <div class="partner-info">
                                            <div class="partner-name"><?= htmlspecialchars($partner['name']) ?></div>
                                            <div class="partner-details">
                                                Registrado: <?= date('d/m/Y', strtotime($partner['dateRegistration'])) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($partner['ci']) ?></td>
                                    <td>
                                        <span class="status-badge status-pending">
                                            <?= (int)$partner['pendingPayments'] ?> pago<?= $partner['pendingPayments'] != 1 ? 's' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="payment-amount">Bs. <?= number_format($partner['totalPending'], 2) ?></div>
                                    </td>
                                    <td>
                                        <?= $partner['lastPaymentDate'] ? date('d/m/Y', strtotime($partner['lastPaymentDate'])) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <a href="<?= $buildUrl(['partner' => $partner['idPartner']]) ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> Revisar Pagos
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Página <?= $page ?> de <?= $totalPages ?> (<?= number_format($total) ?> registros)
                    </div>
                    <div class="pagination-controls">
                        <a href="<?= $buildUrl(['page' => max(1, $page - 1)]) ?>" 
                           class="btn btn-sm" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                        <a href="<?= $buildUrl(['page' => min($totalPages, $page + 1)]) ?>" 
                           class="btn btn-sm" <?= $page >= $totalPages ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Vista de todos los pagos -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Socio</th>
                        <th>Contribución</th>
                        <th>Monto</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Comprobante</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="no-results">
                                    <i class="fas fa-receipt"></i>
                                    <h3>No hay pagos para mostrar</h3>
                                    <p>No se encontraron pagos con los filtros aplicados.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>#<?= (int)$payment['idPayment'] ?></td>
                                <td>
                                    <div class="partner-info">
                                        <div class="partner-name"><?= htmlspecialchars($payment['partnerName']) ?></div>
                                        <div class="partner-details">CI: <?= htmlspecialchars($payment['partnerCI']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="payment-info">
                                        <div><?= htmlspecialchars($payment['contributionName'] ?: 'Contribución #' . $payment['idContribution']) ?></div>
                                        <div class="payment-details"><?= htmlspecialchars($payment['monthYear']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="payment-amount">Bs. <?= number_format($payment['paidAmount'], 2) ?></div>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($payment['dateCreation'])) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $payment['paymentStatus'] == 1 ? 'pending' : ($payment['paymentStatus'] == 2 ? 'approved' : 'rejected') ?>">
                                        <?= $payment['paymentStatus'] == 1 ? 'Pendiente' : ($payment['paymentStatus'] == 2 ? 'Aprobado' : 'Rechazado') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($payment['voucherImageURL']): ?>
                                        <div class="voucher-preview voucher-btn" 
                                             data-voucher="<?= htmlspecialchars($payment['voucherImageURL']) ?>">
                                            <img src="<?= u($payment['voucherImageURL']) ?>" 
                                                 alt="Comprobante" class="voucher-image">
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #6b7280; font-size: 12px;">Sin comprobante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <?php if ($payment['paymentStatus'] == 1): ?>
                                            <button type="button" class="btn btn-success btn-sm approve-btn"
                                                    data-payment="<?= (int)$payment['idPayment'] ?>">
                                                <i class="fas fa-check"></i> Aprobar
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm reject-btn"
                                                    data-payment="<?= (int)$payment['idPayment'] ?>">
                                                <i class="fas fa-times"></i> Rechazar
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #6b7280; font-size: 12px;">
                                                <?= $payment['paymentStatus'] == 2 ? 'Ya aprobado' : 'Ya rechazado' ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Paginación -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Página <?= $page ?> de <?= $totalPages ?> (<?= number_format($total) ?> registros)
                    </div>
                    <div class="pagination-controls">
                        <a href="<?= $buildUrl(['page' => max(1, $page - 1)]) ?>" 
                           class="btn btn-sm" <?= $page <= 1 ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            <i class="fas fa-chevron-left"></i> Anterior
                        </a>
                        <a href="<?= $buildUrl(['page' => min($totalPages, $page + 1)]) ?>" 
                           class="btn btn-sm" <?= $page >= $totalPages ? 'style="opacity:0.5;pointer-events:none;"' : '' ?>>
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal para ver comprobante -->
<div id="voucherModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Comprobante de Pago</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="voucherContent" style="text-align: center;">
                <!-- Contenido del comprobante se insertará aquí -->
            </div>
        </div>
    </div>
</div>

<!-- Modal para confirmar acciones -->
<div id="confirmModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 id="confirmTitle">Confirmar Acción</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="confirmMessage"></div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn" id="confirmCancel">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmAction">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para enviar acciones -->
<form id="actionForm" method="post" action="<?= u($currentPath) ?>" style="display: none;">
    <input type="hidden" name="action" id="actionType">
    <input type="hidden" name="payment_id" id="paymentId">
    <input type="hidden" name="payment_ids" id="paymentIds">
    <input type="hidden" name="return_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const voucherModal = document.getElementById('voucherModal');
    const confirmModal = document.getElementById('confirmModal');
    const actionForm = document.getElementById('actionForm');
    
    // Cerrar modales
    document.querySelectorAll('.close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });
    
    // Ver comprobante
    document.querySelectorAll('.voucher-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const voucherUrl = this.dataset.voucher;
            const voucherContent = document.getElementById('voucherContent');
            const baseUrl = '<?= u("") ?>';
            const fullUrl = baseUrl + voucherUrl;
            
            const extension = voucherUrl.split('.').pop().toLowerCase();
            
            if (extension === 'pdf') {
                voucherContent.innerHTML = `
                    <embed src="${fullUrl}" type="application/pdf" width="100%" height="600px" />
                    <div style="margin-top: 15px;">
                        <a href="${fullUrl}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Abrir PDF en nueva ventana
                        </a>
                    </div>
                `;
            } else {
                voucherContent.innerHTML = `
                    <img src="${fullUrl}" alt="Comprobante" style="max-width: 100%; height: auto; border-radius: 8px;">
                    <div style="margin-top: 15px;">
                        <a href="${fullUrl}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Ver en tamaño completo
                        </a>
                    </div>
                `;
            }
            
            voucherModal.style.display = 'block';
        });
    });
    
    // Función para mostrar confirmación
    function showConfirmation(title, message, actionType, paymentId = null, paymentIds = null) {
        console.log('showConfirmation called:', { title, message, actionType, paymentId, paymentIds }); // Depuración
        const modalTitle = document.getElementById('confirmTitle');
        const modalMessage = document.getElementById('confirmMessage');
        const actionTypeField = document.getElementById('actionType');
        const paymentIdField = document.getElementById('paymentId');
        const paymentIdsField = document.getElementById('paymentIds');

        if (!modalTitle || !modalMessage || !actionTypeField || !paymentIdField || !paymentIdsField) {
            console.error('Missing modal or form elements:', { modalTitle, modalMessage, actionTypeField, paymentIdField, paymentIdsField });
            alert('Error: Elementos del modal o formulario no encontrados.');
            return;
        }

        modalTitle.textContent = title;
        modalMessage.innerHTML = message;
        actionTypeField.value = actionType || '';
        paymentIdField.value = paymentId || '';
        paymentIdsField.value = paymentIds || '';
        confirmModal.style.display = 'block';
    }
    
    // Aprobar pago individual
    document.querySelectorAll('.approve-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.dataset.payment;
            showConfirmation(
                'Aprobar Pago',
                '¿Está seguro que desea <strong style="color: var(--success);">aprobar</strong> este pago?<br><small>Esta acción no se puede deshacer.</small>',
                'approve',
                paymentId
            );
        });
    });
    
    // Rechazar pago individual
    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const paymentId = this.dataset.payment;
            showConfirmation(
                'Rechazar Pago',
                '¿Está seguro que desea <strong style="color: var(--danger);">rechazar</strong> este pago?<br><small>Esta acción no se puede deshacer.</small>',
                'reject',
                paymentId
            );
        });
    });
    
    // Aprobar grupo de pagos
    document.querySelectorAll('.approve-group-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const groupId = this.dataset.group;
            console.log('groupId:', groupId);
            const paymentIds = groupId; // El groupId contiene los IDs de los pagos
            showConfirmation(
                'Aprobar Pagos Múltiples',
                '¿Está seguro que desea <strong style="color: var(--success);">aprobar todos</strong> los pagos de este comprobante?<br><small>Esta acción no se puede deshacer.</small>',
                'approve_group',
                null,
                paymentIds
            );
        });
    });
    
    // Rechazar grupo de pagos
    document.querySelectorAll('.reject-group-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const groupId = this.dataset.group;
            const paymentIds = groupId; // El groupId contiene los IDs de los pagos
            showConfirmation(
                'Rechazar Pagos Múltiples',
                '¿Está seguro que desea <strong style="color: var(--danger);">rechazar todos</strong> los pagos de este comprobante?<br><small>Esta acción no se puede deshacer.</small>',
                'reject_group',
                null,
                paymentIds
            );
        });
    });
    
    // Confirmar acción
    document.getElementById('confirmAction').addEventListener('click', function() {
        const actionType = document.getElementById('actionType');
        const paymentIds = document.getElementById('paymentIds');
        const paymentId = document.getElementById('paymentId');

        if (!actionType || !paymentIds || !paymentId) {
            alert('Error: Uno o más campos del formulario no están disponibles.');
            console.error('Missing elements:', { actionType, paymentIds, paymentId });
            return;
        }

        const actionValue = actionType.value;
        const paymentIdsValue = paymentIds.value;
        const paymentIdValue = paymentId.value;

        if (typeof actionValue !== 'string' || actionValue === '') {
            alert('Error: Acción no definida.');
            console.error('Invalid actionValue:', actionValue);
            return;
        }

        /* alert('Acción: ' + (actionValue || 'Ninguno') + '\nPayment IDs: ' + (paymentIdsValue || 'Ninguno') + '\nPayment ID: ' + (paymentIdValue || 'Ninguno'));
        console.log('Form values before submit:', { actionValue, paymentIdsValue, paymentIdValue });
 */
        if (actionValue.includes('approve')) {
            this.className = 'btn btn-success';
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aprobando...';
        } else if (actionValue.includes('reject')) {
            this.className = 'btn btn-danger';
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rechazando...';
        } else {
            console.warn('Unknown action:', actionValue);
        }

        this.disabled = true;
        console.log('Submitting form to:', actionForm.actionType);
        actionForm.submit();
    });
    
    // Cancelar confirmación
    document.getElementById('confirmCancel').addEventListener('click', function() {
        confirmModal.style.display = 'none';
    });
    
    // Auto-refresh cada 30 segundos si hay pagos pendientes
    <?php if (($stats['pending'] ?? 0) > 0): ?>
    setTimeout(function() {
        if (!document.querySelector('.modal[style*="block"]')) {
            window.location.reload();
        }
    }, 30000);
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>