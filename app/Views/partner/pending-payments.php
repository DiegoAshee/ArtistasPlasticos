<?php
$title = 'Pagos Pendientes';
$currentPath = 'partner/pending-payments';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Pagos Pendientes', 'url' => null],
];

// URL builder para paginación
$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$buildUrl = function(int $p) use ($currentUrl, $yearFilter, $pageSize) {
    $params = array_filter([
        'page' => $p,
        'year' => $yearFilter,
        'pageSize' => $pageSize
    ]);
    return u($currentUrl . '?' . http_build_query($params));
};

// Función para formatear fecha
$formatDate = function($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return 'No registrada';
    }
    return date('d/m/Y', strtotime($date));
};

// Función segura para obtener valores del array
$getSafe = function($array, $key, $default = '') {
    return isset($array[$key]) ? $array[$key] : $default;
};

ob_start();
?>
<style>
    /* ==== ESTILOS MEJORADOS ==== */
    #pagos-root {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #2a2a2a;
    }

    /* Variables CSS */
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

    /* Tarjetas de resumen */
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

    .summary-card.pending {
        border-top: 4px solid var(--warning);
    }

    .summary-card.paid {
        border-top: 4px solid var(--success);
    }

    /* Botones mejorados */
    #pagos-root a.btn, #pagos-root .btn {
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

    #pagos-root a.btn:hover, #pagos-root .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    #pagos-root .btn-primary {
        background: var(--primary) !important;
        border-color: var(--primary) !important;
        color: #fff !important;
    }

    #pagos-root .btn-primary:hover {
        background: var(--primary-dark) !important;
        border-color: var(--primary-dark) !important;
    }

    #pagos-root .btn-success {
        background: var(--success) !important;
        border-color: var(--success) !important;
        color: #fff !important;
    }

    #pagos-root .btn-success:hover {
        background: #059669 !important;
        border-color: #059669 !important;
    }

    /* Formularios mejorados */
    #pagos-root input, #pagos-root select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: white;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
    }

    #pagos-root input:focus, #pagos-root select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(164, 152, 132, 0.2);
    }

    #pagos-root label {
        font-weight: 600;
        font-size: 13px;
        margin-bottom: 5px;
        display: block;
        color: #4a4a4a;
    }

    /* Checkbox personalizado */
    #pagos-root input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: var(--success);
        cursor: pointer;
    }

    /* Sección de filtros */
    .filters-section {
        background: var(--primary);
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

    .filters-section select,
    .filters-section button {
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .filters-section select:focus,
    .filters-section button:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    .filters-section button {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--text-light);
        border: none;
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
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        transition: all 0.2s ease;
    }

    .filters-section a:hover {
        color: var(--primary);
        border-color: var(--primary);
    }

    /* Tabla mejorada */
    #pagos-root .modern-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    #pagos-root .modern-table th {
        position: sticky;
        top: 0;
        background: var(--primary);
        color: var(--text-light);
        z-index: 2;
        padding: 14px 16px;
        font-weight: 700;
        font-size: 14px;
        text-align: left;
        border: none;
    }

    #pagos-root .modern-table th:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    #pagos-root .modern-table th:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    #pagos-root .modern-table td {
        padding: 14px 16px;
        line-height: 1.4;
        vertical-align: middle;
        background: #d7cbb5;
        border: none;
    }

    #pagos-root .modern-table tr:nth-child(even) td {
        background: #dccaaf;
    }

    #pagos-root .modern-table td:first-child {
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
    }

    #pagos-root .modern-table td:last-child {
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    #pagos-root .table-container {
        background: #cfc4b0;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,.08);
        overflow: auto;
        padding: 8px;
    }

    /* Estado pendiente */
    .status-pending {
        background: #FEF3C7 !important;
        color: #92400E;
        padding: 6px 12px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .status-pending:before {
        content: "⏳";
        font-size: 14px;
    }

    /* Información de contribución */
    .contribution-info {
        display: flex;
        flex-direction: column;
    }

    .contribution-period {
        font-weight: 600;
        margin-bottom: 4px;
        color: var(--text-dark);
    }

    .contribution-date {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .contribution-notes {
        font-size: 12px;
        color: #6c757d;
        font-style: italic;
    }

    .contribution-amount {
        font-weight: 700;
        color: var(--warning);
        margin-top: 4px;
        font-size: 16px;
    }

    .balance-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .paid-amount {
        font-size: 12px;
        color: var(--success);
        font-weight: 500;
    }

    .pending-balance {
        font-weight: 700;
        color: #dc3545;
        font-size: 14px;
    }

    /* Checkbox cell */
    .checkbox-cell {
        text-align: center;
        width: 50px;
    }

    /* Barra de resumen de pago */
    .payment-summary {
        position: sticky;
        bottom: 0;
        background: var(--bg-light);
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
        z-index: 10;
        border: 2px solid var(--success);
    }

    .total-amount {
        font-size: 20px;
        font-weight: 700;
        color: var(--success);
    }

    .selected-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .selected-count {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    /* Paginación mejorada */
    #pagos-root .pagination {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
        padding: 16px;
        background: var(--bg-light);
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }

    #pagos-root .pagination-info {
        font-size: 14px;
        font-weight: 600;
        color: #4a4a4a;
    }

    #pagos-root .pagination a,
    #pagos-root .pagination span {
        padding: 12px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        color: var(--text-dark);
        font-weight: 500;
        transition: all 0.2s ease;
    }

    #pagos-root .pagination a:hover {
        background: var(--bg-light);
        border-color: var(--primary);
        color: var(--primary);
    }

    #pagos-root .pagination span.active {
        background: var(--primary);
        color: var(--text-light);
        border-color: var(--primary);
    }

    /* Mensaje sin resultados */
    .no-results {
        text-align: center;
        padding: 40px;
        font-style: italic;
        color: #6c757d;
        background: var(--bg-light);
        border-radius: 12px;
        margin: 10px 0;
    }

    /* Modal estilos */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(4px);
    }

    .modal-content {
        background: white;
        margin: 10% auto;
        padding: 28px;
        width: 90%;
        max-width: 480px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        position: relative;
        max-height: 80vh;
        overflow-y: auto;
    }

    .close {
        position: absolute;
        top: 16px;
        right: 20px;
        font-size: 24px;
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.2s ease;
    }

    .close:hover {
        color: var(--primary);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    .success-message,
    .error-message {
        padding: 12px 16px;
        border-radius: 6px;
        margin: 16px 0;
    }

    .success-message {
        color: #166534;
        background: #dcfce7;
        border-left: 4px solid #16a34a;
    }

    .error-message {
        color: #b91c1c;
        background: #fee2e2;
        border-left: 4px solid #dc2626;
    }

    @media (max-width: 768px) {
        .summary-cards {
            flex-direction: column;
        }

        .payment-summary {
            flex-direction: column;
            gap: 15px;
            text-align: center;
        }

        .filters-section form {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div id="pagos-root">
    <?php if ($success): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de resumen -->
    <!-- <div class="summary-cards">
        <div class="summary-card pending">
            <h3 style="margin: 0 0 10px 0; color: var(--warning);">
                <i class="fas fa-exclamation-circle"></i> Total Pendiente
            </h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: var(--text-dark);">
                <?= number_format($totals['pending'] ?? 0, 2) ?> Bs
            </p>
        </div>
        <div class="summary-card paid">
            <h3 style="margin: 0 0 10px 0; color: var(--success);">
                <i class="fas fa-check-circle"></i> Total Pagado
            </h3>
            <p style="font-size: 24px; font-weight: bold; margin: 0; color: var(--text-dark);">
                <?= number_format($totals['paid'] ?? 0, 2) ?> Bs
            </p>
        </div>
    </div> -->

    <!-- Filtros -->
    <!-- <div class="filters-section">
        <form method="get" action="<?= htmlspecialchars($currentUrl) ?>">
            <label for="year">Filtrar por Año:</label>
            <select id="year" name="year">
                <option value="">Todos los años</option>
                <?php foreach ($availableYears as $year): ?>
                    <option value="<?= $year ?>" <?= $yearFilter == $year ? 'selected' : '' ?>>
                        <?= $year ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">
                <i class="fas fa-filter"></i> Aplicar Filtro
            </button>
            <a href="<?= u($currentUrl) ?>" style="margin-left: 10px; color: var(--text-light);">
                <i class="fas fa-times"></i> Limpiar
            </a>
        </form>
    </div> -->

    <!-- Formulario para pagos múltiples -->
    <form id="paymentForm" action="<?= u('partner/pending-payments') ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="payMultiple">
        
        <!-- Tabla de contribuciones pendientes -->
        <div class="table-container">
            <div style="padding: 20px; border-bottom: 1px solid var(--border-color); background: var(--grid-bg);">
                <h2 style="margin: 0; color: var(--text-light);">
                    <i class="fas fa-exclamation-circle"></i> 
                    Contribuciones Pendientes (<?= $total ?> registros)
                </h2>
            </div>

            <table class="modern-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell">
                            <input type="checkbox" id="selectAll" title="Seleccionar todos">
                        </th>
                        <th>Período</th>
                        <th>Fecha</th>
                        <th>Monto Total</th>
                        <!-- <th>Pagado</th> -->
                        <!-- <th>Saldo Pendiente</th> -->
                        <!-- <th>Estado</th> -->
                        <th>Acción Individual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingPayments)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="no-results">
                                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px; display: block; color: var(--success);"></i>
                                    ¡Excelente! No hay pagos pendientes<br>
                                    <small style="color: #6c757d; margin-top: 10px;">
                                        Estás al día con todas tus contribuciones.
                                    </small>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingPayments as $payment): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" 
                                           name="selected_contributions[]" 
                                           value="<?= (int)($payment['idContribution'] ?? 0) ?>"
                                           data-amount="<?= $payment['balance'] ?? 0 ?>"
                                           data-period="<?= htmlspecialchars($payment['monthYear'] ?? '') ?>"
                                           class="contribution-checkbox">
                                </td>
                                <td>
                                    <div class="contribution-info">
                                        <div class="contribution-period">
                                            <?= htmlspecialchars($payment['monthYear'] ?? '') ?>
                                        </div>
                                        <?php if (!empty($payment['notes'])): ?>
                                            <div class="contribution-notes">
                                                <?= htmlspecialchars($payment['notes']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="contribution-date">
                                        <?= isset($payment['contrib_date']) && $payment['contrib_date'] 
                                            ? $formatDate($payment['contrib_date'])
                                            : 'N/A' ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="contribution-amount">
                                        Bs. <?= number_format($payment['amount'] ?? 0, 2) ?>
                                    </div>
                                </td>
                                <!-- <td>
                                    <div class="balance-info">
                                        <?php if (($payment['paidAmount'] ?? 0) > 0): ?>
                                            <div class="paid-amount">
                                                Bs. <?= number_format($payment['paidAmount'], 2) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: #6c757d; font-size: 12px;">Sin pagos</span>
                                        <?php endif; ?>
                                    </div>
                                </td> -->
                                <!-- <td>
                                    <div class="pending-balance">
                                        <strong>Bs. <?= number_format($payment['balance'] ?? 0, 2) ?></strong>
                                    </div>
                                </td> -->
                                <!-- <td>
                                    <span class="status-pending">Pendiente</span>
                                </td> -->
                                <td>
                                    <button type="button" class="btn btn-primary open-pay-modal" 
                                            data-id="<?= (int)($payment['idContribution'] ?? 0) ?>" 
                                            data-amount="<?= $payment['balance'] ?? 0 ?>"
                                            data-period="<?= htmlspecialchars($payment['monthYear'] ?? '') ?>">
                                        <i class="fas fa-credit-card"></i> Pagar Solo Este
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Barra de resumen para pagos múltiples -->
        <div class="payment-summary" id="paymentSummary" style="display: none;">
            <div class="selected-info">
                <div class="total-amount" id="totalAmount">Bs. 0.00</div>
                <div class="selected-count" id="selectedCount">0 contribuciones seleccionadas</div>
            </div>
            <div>
                <input type="hidden" name="totalAmount" id="totalAmountInput" value="0">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-credit-card"></i> Pagar Seleccionados
                </button>
            </div>
        </div>

        <!-- Campo para comprobante múltiple (se mostrará cuando se seleccionen items) -->
        <div id="multipleProofSection" style="display: none; margin-top: 20px; padding: 20px; background: #f8f9fa; border-radius: 12px;">
            <div class="form-group">
                <label>Escanea el QR para realizar el pago:</label>
                <?php if ($qrImageUrl): ?>
                    <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="QR Pago" style="width: 200px; height: 200px; display: block; margin: 0 auto;">
                    <small style="color: #6c757d; display: block; text-align: center; margin-top: 5px;">
                        Usa este QR para completar el pago por transferencia.
                    </small>
                <?php else: ?>
                    <p style="color: #6c757d; text-align: center;">No se encontró un QR asociado.</p>
                <?php endif; ?>
            </div>
        <div class="form-group">
                <label for="proofMultiple">
                    <i class="fas fa-upload"></i> Comprobante de Transferencia (Para pagos múltiples) *
                </label>
                <input type="file" name="proof" id="proofMultiple" 
                       accept="image/jpeg,image/png,application/pdf">
                <small style="color: #6c757d; display: block; margin-top: 5px;">
                    Formatos permitidos: JPG, PNG, PDF. Máximo 2MB.
                </small>
            </div>
        </div>
    </form>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">Página <?= $page ?> de <?= $totalPages ?> (<?= number_format($total) ?> registros)</div>
        
        <?php if ($page > 1): ?>
            <a href="<?= $buildUrl($page - 1) ?>">
                <i class="fas fa-chevron-left"></i> Anterior
            </a>
        <?php endif; ?>
        
        <?php
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        ?>
        
        <?php if ($start > 1): ?>
            <a href="<?= $buildUrl(1) ?>">1</a>
            <?php if ($start > 2): ?>
                <span>...</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start; $i <= $end; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="<?= $buildUrl($i) ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
                <span>...</span>
            <?php endif; ?>
            <a href="<?= $buildUrl($totalPages) ?>"><?= $totalPages ?></a>
        <?php endif; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="<?= $buildUrl($page + 1) ?>">
                Siguiente <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para Pago Individual -->
<div id="payModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 style="margin-bottom: 25px; color: var(--text-dark);">
            <i class="fas fa-credit-card"></i> Realizar Pago Individual por Transferencia
        </h2>
        
        <form method="POST" action="<?= u('partner/pending-payments') ?>" enctype="multipart/form-data" id="payForm">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="idContribution" id="payId">
            
            <div class="form-group">
                <label>Escanea el QR para realizar el pago:</label>
                <?php if ($qrImageUrl): ?>
                    <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="QR Pago" style="width: 200px; height: 200px; display: block; margin: 0 auto;">
                    <small style="color: #6c757d; display: block; text-align: center; margin-top: 5px;">
                        Usa este QR para completar el pago por transferencia.
                    </small>
                <?php else: ?>
                    <p style="color: #6c757d; text-align: center;">No se encontró un QR asociado.</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="amount">Monto a Pagar:</label>
                <input type="number" name="amount" id="amount" step="0.01" required readonly
                       style="background-color: var(--bg-light); font-weight: bold; color: var(--text-dark);">
                <small style="color: #6c757d;">Este es el saldo pendiente de la contribución seleccionada</small>
            </div>
            
            <div class="form-group">
                <label for="period">Período:</label>
                <input type="text" id="period" readonly 
                       style="background-color: var(--bg-light); color: #6c757d;">
            </div>

            <div class="form-group">
                <label for="proof">
                    <i class="fas fa-upload"></i> Comprobante de Transferencia *
                </label>
                <input type="file" name="proof" id="proof" 
                       accept="image/jpeg,image/png,application/pdf" required>
                <small style="color: #6c757d; display: block; margin-top: 5px;">
                    Formatos permitidos: JPG, PNG, PDF. Máximo 2MB.
                </small>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid var(--warning);">
                <p style="margin: 0; color: #856404;">
                    <i class="fas fa-info-circle"></i>
                    <strong>Importante:</strong> Su pago será revisado por el administrador en un plazo de 24-48 horas. 
                    Asegúrese de subir un comprobante legible con todos los datos de la transferencia.
                </p>
            </div>

            <button type="submit" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: var(--text-light); border: none; padding: 15px 30px; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; font-weight: 500;">
                <i class="fas fa-paper-plane"></i> Enviar Pago para Revisión
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos
    const modal = document.getElementById('payModal');
    const payForm = document.getElementById('payForm');
    const paymentForm = document.getElementById('paymentForm');
    const checkboxes = document.querySelectorAll('.contribution-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const paymentSummary = document.getElementById('paymentSummary');
    const totalAmount = document.getElementById('totalAmount');
    const totalAmountInput = document.getElementById('totalAmountInput');
    const selectedCount = document.getElementById('selectedCount');
    const multipleProofSection = document.getElementById('multipleProofSection');

    // Función para actualizar el resumen de pagos múltiples
    function updatePaymentSummary() {
        let total = 0;
        let count = 0;
        const selectedContributions = [];

        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const amount = parseFloat(checkbox.dataset.amount) || 0;
                total += amount;
                count++;
                selectedContributions.push({
                    id: checkbox.value,
                    amount: amount,
                    period: checkbox.dataset.period
                });
            }
        });

        totalAmount.textContent = 'Bs. ' + total.toFixed(2);
        totalAmountInput.value = total.toFixed(2);
        selectedCount.textContent = count + ' contribución' + (count !== 1 ? 'es' : '') + ' seleccionada' + (count !== 1 ? 's' : '');

        if (count > 0) {
            paymentSummary.style.display = 'flex';
            multipleProofSection.style.display = 'block';
            // Hacer requerido el campo de comprobante para pagos múltiples
            document.getElementById('proofMultiple').required = true;
        } else {
            paymentSummary.style.display = 'none';
            multipleProofSection.style.display = 'none';
            document.getElementById('proofMultiple').required = false;
        }

        // Actualizar estado del checkbox "Seleccionar todos"
        if (count === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (count === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
        }
    }

    // Event listeners para checkboxes individuales
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePaymentSummary);
    });

    // Event listener para "Seleccionar todos"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updatePaymentSummary();
        });
    }

    // Abrir modal para pago individual
    document.querySelectorAll('.open-pay-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id') || 0;
            const amount = this.getAttribute('data-amount') || 0;
            const period = this.getAttribute('data-period') || '';
            
            document.getElementById('payId').value = id;
            document.getElementById('amount').value = parseFloat(amount).toFixed(2);
            document.getElementById('period').value = period;
            
            modal.style.display = 'block';
        });
    });

    // Cerrar modal
    document.querySelector('.close').addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Validación del formulario de pago individual
    if (payForm) {
        payForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('proof');
            const amount = document.getElementById('amount').value;
            
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Por favor seleccione un comprobante de pago.');
                return;
            }
            
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                alert('El monto debe ser mayor a 0.');
                return;
            }
            
            // Confirmar envío
            if (!confirm('¿Está seguro de enviar este pago para revisión?')) {
                e.preventDefault();
            }
        });
    }

    // Validación del formulario de pagos múltiples
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(e) {
            const selectedCheckboxes = document.querySelectorAll('.contribution-checkbox:checked');
            const multipleProofInput = document.getElementById('proofMultiple');
            
            if (selectedCheckboxes.length === 0) {
                e.preventDefault();
                alert('Por favor seleccione al menos una contribución para pagar.');
                return;
            }
            
            if (!multipleProofInput.files.length) {
                e.preventDefault();
                alert('Por favor seleccione un comprobante de pago para las contribuciones seleccionadas.');
                return;
            }
            
            const totalAmount = parseFloat(document.getElementById('totalAmountInput').value);
            if (!totalAmount || totalAmount <= 0) {
                e.preventDefault();
                alert('El monto total debe ser mayor a 0.');
                return;
            }
            
            // Confirmar envío
            const contributionsText = selectedCheckboxes.length === 1 ? 'contribución' : 'contribuciones';
            if (!confirm(`¿Está seguro de enviar el pago de ${selectedCheckboxes.length} ${contributionsText} por un total de Bs. ${totalAmount.toFixed(2)} para revisión?`)) {
                e.preventDefault();
            }
        });
    }

    // Inicializar el resumen
    updatePaymentSummary();

    // Mostrar QR en modal si existe
    <?php if ($qrImageUrl): ?>
    console.log('QR disponible para pagos');
    <?php endif; ?>
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>