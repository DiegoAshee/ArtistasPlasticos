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

// Función para formatear monthYear (YYYY-MM) a formato legible (Mes Año)
$formatMonthYear = function($monthYear) {
    if (empty($monthYear)) {
        return 'N/A';
    }
    
    // Array de nombres de meses en español
    $meses = [
        '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
        '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
        '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
    ];
    
    // Separar año y mes (formato YYYY-MM)
    $parts = explode('-', $monthYear);
    if (count($parts) === 2) {
        $year = $parts[0];
        $month = $parts[1];
        return ($meses[$month] ?? $month) . ' ' . $year;
    }
    
    return $monthYear;
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
        --error-color: #dc3545;
        --cream-50: #f9f8f6;
        --cream-300: #d9d0c1;
        --cream-400: #cfc4b0;
        --cream-600: #9c8f7a;
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
        font-weight: 700;
        font-size: 16px;
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

    /* Barra de acciones flotante */
    .floating-actions {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: var(--success);
        padding: 20px 30px;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        z-index: 100;
        display: none;
        align-items: center;
        gap: 20px;
        animation: slideUp 0.3s ease;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .floating-actions.show {
        display: flex;
    }

    .floating-info {
        color: white;
    }

    .floating-count {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 4px;
    }

    .floating-total {
        font-size: 24px;
        font-weight: 700;
    }

    .floating-actions .btn {
        background: white !important;
        color: var(--success) !important;
        border: none !important;
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 700;
    }

    .floating-actions .btn:hover {
        background: #f0f0f0 !important;
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
        margin: 3% auto;
        padding: 28px;
        width: 90%;
        max-width: 650px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        position: relative;
        max-height: 90vh;
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
        font-weight: 600;
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

    /* Lista de contribuciones seleccionadas en modal */
    .selected-contributions-list {
        background: var(--bg-light);
        border-radius: 8px;
        padding: 16px;
        margin: 16px 0;
        max-height: 200px;
        overflow-y: auto;
    }

    .contribution-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: white;
        border-radius: 6px;
        margin-bottom: 8px;
        border: 1px solid var(--border-color);
    }

    .contribution-item:last-child {
        margin-bottom: 0;
    }

    .contribution-item-period {
        font-weight: 600;
        color: var(--text-dark);
    }

    .contribution-item-amount {
        font-weight: 700;
        color: var(--success);
    }

    .total-summary {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
        padding: 16px;
        border-radius: 8px;
        margin: 16px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .total-summary-label {
        font-size: 14px;
        opacity: 0.9;
    }

    .total-summary-amount {
        font-size: 24px;
        font-weight: 700;
    }

    /* ===== ESTILOS PARA UPLOAD DE ARCHIVOS ===== */
    .image-upload-box {
        border: 2px dashed var(--cream-400);
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: var(--cream-50);
        position: relative;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .image-upload-box:hover {
        border-color: var(--cream-600);
        background-color: #f5f2ed;
    }

    .form-group.has-error .image-upload-box {
        border-color: var(--error-color);
        background-color: #fef2f2;
    }

    .image-upload-box i {
        font-size: 2.5rem;
        color: var(--cream-600);
        margin-bottom: 0.75rem;
    }

    .form-group.has-error .image-upload-box i {
        color: var(--error-color);
    }

    .image-upload-box p {
        margin: 0;
        color: var(--cream-700);
        font-weight: 600;
        font-size: 0.95rem;
    }

    .image-upload-box small {
        display: block;
        margin-top: 0.5rem;
        color: var(--cream-600);
        font-size: 0.8rem;
    }

    .image-preview {
        margin-top: 1rem;
        max-width: 100%;
        max-height: 180px;
        object-fit: contain;
        display: none;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .file-name {
        display: block;
        margin-top: 0.75rem;
        font-size: 0.8rem;
        color: var(--cream-600);
        word-break: break-all;
        text-align: center;
        font-style: italic;
    }

    .file-status {
        display: none;
        margin-top: 0.75rem;
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 0.85rem;
        text-align: center;
        font-weight: 500;
    }

    .file-status.success {
        background: #d1fae5;
        color: #065f46;
        display: block;
    }

    .file-status.error {
        background: #fee2e2;
        color: #991b1b;
        display: block;
    }

 .field-error {
        color: var(--error-color);
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }

    .field-error i {
        font-size: 0.875rem;
    }

    /* Alerta de error más visible */
    .alert-error {
        background: #fee2e2;
        border: 2px solid #dc2626;
        color: #991b1b;
        padding: 16px 20px;
        border-radius: 8px;
        margin: 16px 0;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
        animation: shake 0.5s ease;
    }

    .alert-error i {
        font-size: 1.5rem;
        color: #dc2626;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-10px); }
        75% { transform: translateX(10px); }
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

        .floating-actions {
            left: 15px;
            right: 15px;
            bottom: 15px;
            flex-direction: column;
            padding: 16px;
        }

        .filters-section form {
            flex-direction: column;
            align-items: stretch;
        }

        .modal-content {
            width: 95%;
            padding: 20px;
            margin: 5% auto;
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
                    <th>Monto Total</th>
                    <th>Acción Individual</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingPayments)): ?>
                    <tr>
                        <td colspan="4">
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
                                       data-period="<?= htmlspecialchars($formatMonthYear($payment['monthYear'] ?? '')) ?>"
                                       class="contribution-checkbox">
                            </td>
                            <td>
                                <div class="contribution-info">
                                    <div class="contribution-period">
                                        <?= htmlspecialchars($formatMonthYear($payment['monthYear'] ?? '')) ?>
                                    </div>
                                    <?php if (!empty($payment['notes'])): ?>
                                        <div class="contribution-notes">
                                            <?= htmlspecialchars($payment['notes']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="contribution-amount">
                                    Bs. <?= number_format($payment['amount'] ?? 0, 2) ?>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary open-pay-modal" 
                                        data-id="<?= (int)($payment['idContribution'] ?? 0) ?>" 
                                        data-amount="<?= $payment['balance'] ?? 0 ?>"
                                        data-period="<?= htmlspecialchars($formatMonthYear($payment['monthYear'] ?? '')) ?>">
                                    <i class="fas fa-credit-card"></i> Pagar Solo Este
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Botón flotante para pagar seleccionados -->
    <div class="floating-actions" id="floatingActions">
        <div class="floating-info">
            <div class="floating-count" id="floatingCount">0 contribuciones</div>
            <div class="floating-total" id="floatingTotal">Bs. 0.00</div>
        </div>
        <button type="button" class="btn" id="openMultiplePayModal">
            <i class="fas fa-credit-card"></i> Proceder al Pago
        </button>
    </div>

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

<!-- Modal Unificado para Pagos (Individual y Múltiple) -->
<div id="payModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 style="margin-bottom: 25px; color: var(--text-dark);" id="modalTitle">
            <i class="fas fa-credit-card"></i> Realizar Pago por Transferencia
        </h2>
        
        <form method="POST" action="<?= u('partner/pending-payments') ?>" enctype="multipart/form-data" id="payForm">
            <input type="hidden" name="action" id="paymentAction" value="pay">
            <input type="hidden" name="idContribution" id="payId">
            
            <!-- Lista de contribuciones (solo para múltiples) -->
            <div id="multipleContributionsSection" style="display: none;">
                <div class="form-group">
                    <label>
                        <i class="fas fa-list"></i> Contribuciones Seleccionadas:
                    </label>
                    <div class="selected-contributions-list" id="contributionsList"></div>
                </div>
            </div>

            <!-- Info para pago individual -->
            <div id="singleContributionSection">
                <div class="form-group">
                    <label for="period">Período:</label>
                    <input type="text" id="period" readonly 
                           style="background-color: var(--bg-light); color: #6c757d;">
                </div>
            </div>

            <!-- Monto total -->
            <div class="total-summary">
                <div>
                    <div class="total-summary-label" id="amountLabel">Monto a Pagar:</div>
                </div>
                <div class="total-summary-amount" id="totalAmountDisplay">Bs. 0.00</div>
            </div>

            <input type="hidden" name="amount" id="amount">
            <input type="hidden" name="totalAmount" id="totalAmount">
            <div id="selectedContributionsInput"></div>
            
            <div class="form-group">
                <label style="font-size: 1rem; margin-bottom: 12px;">
                    <i class="fas fa-qrcode"></i> Escanea el QR para realizar el pago:
                </label>
                <?php if ($qrImageUrl): ?>
                    <div style="text-align: center; margin: 15px 0;">
                        <img src="<?= htmlspecialchars($qrImageUrl) ?>" alt="QR Pago" 
                             style="width: 200px; height: 200px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <small style="color: #6c757d; display: block; margin-top: 8px;">
                            Usa este QR para completar el pago por transferencia.
                        </small>
                    </div>
                <?php else: ?>
                    <p style="color: #6c757d; text-align: center; padding: 20px;">No se encontró un QR asociado.</p>
                <?php endif; ?>
            </div>

            <div class="form-group" id="proofGroup">
                <label for="proof" style="font-size: 1rem;">
                    <i class="fas fa-upload"></i> Comprobante de Transferencia *
                </label>
                <div class="image-upload-box" onclick="document.getElementById('proof').click()">
                    <i class="fas fa-file-upload"></i>
                    <p>Subir Comprobante</p>
                    <small>Haga clic para seleccionar el archivo</small>
                    <small style="margin-top: 4px;">JPG, PNG, PDF - Máx. 2MB</small>
                    <input type="file" name="proof" id="proof" 
                           accept="image/jpeg,image/png,application/pdf" 
                           required 
                           style="display: none"
                           onchange="handleFileSelect(this, 'proofPreview', 'proofFileName', 'proofStatus', 'proofGroup')">
                    <img id="proofPreview" class="image-preview" alt="Vista previa del comprobante">
                    <span id="proofFileName" class="file-name"></span>
                    <div id="proofStatus" class="file-status"></div>
                </div>
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
// Función mejorada para manejo de archivos con previsualización
function handleFileSelect(input, previewId, fileNameId, statusId, groupId) {
    const preview = document.getElementById(previewId);
    const fileNameElement = document.getElementById(fileNameId);
    const statusElement = document.getElementById(statusId);
    const file = input.files[0];
    const group = document.getElementById(groupId);

    // Limpiar estados previos
    if (group) {
        group.classList.remove('has-error');
    }
    statusElement.className = 'file-status';
    statusElement.style.display = 'none';
    
    // Limpiar error previo si existe
    const existingError = group ? group.querySelector('.field-error') : null;
    if (existingError) {
        existingError.remove();
    }

    if (file) {
        // Validar tamaño (2MB = 2,097,152 bytes exactos)
        if (file.size > 2 * 1024 * 1024) {
            showError(group, statusElement, `Archivo muy grande: ${formatFileSize(file.size)}. Máximo: 2MB`);
            clearFile(input, preview, fileNameElement);
            return;
        }

        // Validar que no esté vacío
        if (file.size === 0) {
            showError(group, statusElement, 'El archivo está vacío');
            clearFile(input, preview, fileNameElement);
            return;
        }

        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        const allowedExtensions = ['.jpg', '.jpeg', '.png', '.pdf'];
        const fileExtension = file.name.toLowerCase().substring(file.name.lastIndexOf('.'));
        
        if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
            showError(group, statusElement, 'Formato no válido. Use JPG, PNG o PDF');
            clearFile(input, preview, fileNameElement);
            return;
        }

        // Archivo válido - mostrar confirmación
        fileNameElement.textContent = file.name;
        statusElement.textContent = `✓ Archivo válido (${formatFileSize(file.size)})`;
        statusElement.className = 'file-status success';

        // Mostrar preview para imágenes
        if (file.type.startsWith('image/') || ['.jpg', '.jpeg', '.png'].includes(fileExtension)) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else if (fileExtension === '.pdf') {
            preview.style.display = 'none';
        }
    } else {
        clearFile(input, preview, fileNameElement, statusElement);
    }
}

function showError(group, statusElement, message) {
    if (group) {
        group.classList.add('has-error');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        group.appendChild(errorDiv);
    }
    
    statusElement.textContent = `⚠ ${message}`;
    statusElement.className = 'file-status error';
    statusElement.style.display = 'block';
}

function clearFile(input, preview, fileNameElement, statusElement) {
    input.value = '';
    preview.style.display = 'none';
    preview.src = '';
    fileNameElement.textContent = '';
    if (statusElement) {
        statusElement.style.display = 'none';
        statusElement.textContent = '';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos
    const modal = document.getElementById('payModal');
    const payForm = document.getElementById('payForm');
    const checkboxes = document.querySelectorAll('.contribution-checkbox');
    const selectAllCheckbox = document.getElementById('selectAll');
    const floatingActions = document.getElementById('floatingActions');
    const floatingCount = document.getElementById('floatingCount');
    const floatingTotal = document.getElementById('floatingTotal');
    const openMultiplePayModalBtn = document.getElementById('openMultiplePayModal');

    // Variables para datos seleccionados
    let selectedContributions = [];

    // Función para actualizar contribuciones seleccionadas
    function updateSelectedContributions() {
        selectedContributions = [];
        let total = 0;
        let count = 0;

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

        // Actualizar botón flotante
        if (count > 0) {
            floatingActions.classList.add('show');
            floatingCount.textContent = count + ' contribución' + (count !== 1 ? 'es' : '') + ' seleccionada' + (count !== 1 ? 's' : '');
            floatingTotal.textContent = 'Bs. ' + total.toFixed(2);
        } else {
            floatingActions.classList.remove('show');
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
        checkbox.addEventListener('change', updateSelectedContributions);
    });

    // Event listener para "Seleccionar todos"
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateSelectedContributions();
        });
    }

    // Abrir modal para pagos múltiples
    if (openMultiplePayModalBtn) {
        openMultiplePayModalBtn.addEventListener('click', function() {
            if (selectedContributions.length === 0) {
                alert('Por favor seleccione al menos una contribución.');
                return;
            }

            openPaymentModal(true, selectedContributions);
        });
    }

    // Abrir modal para pago individual
    document.querySelectorAll('.open-pay-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id') || 0;
            const amount = this.getAttribute('data-amount') || 0;
            const period = this.getAttribute('data-period') || '';
            
            openPaymentModal(false, [{
                id: id,
                amount: parseFloat(amount),
                period: period
            }]);
        });
    });

    // Función para abrir el modal
    function openPaymentModal(isMultiple, contributions) {
        const modalTitle = document.getElementById('modalTitle');
        const paymentAction = document.getElementById('paymentAction');
        const singleSection = document.getElementById('singleContributionSection');
        const multipleSection = document.getElementById('multipleContributionsSection');
        const contributionsList = document.getElementById('contributionsList');
        const periodInput = document.getElementById('period');
        const amountLabel = document.getElementById('amountLabel');
        const totalAmountDisplay = document.getElementById('totalAmountDisplay');
        const amountInput = document.getElementById('amount');
        const totalAmountInput = document.getElementById('totalAmount');
        const selectedContributionsInput = document.getElementById('selectedContributionsInput');

        // Limpiar el formulario
        const proofInput = document.getElementById('proof');
        const preview = document.getElementById('proofPreview');
        const fileName = document.getElementById('proofFileName');
        const status = document.getElementById('proofStatus');
        clearFile(proofInput, preview, fileName, status);

        // Calcular total
        let total = 0;
        contributions.forEach(c => total += c.amount);

        if (isMultiple) {
            // Configurar para pagos múltiples
            modalTitle.innerHTML = '<i class="fas fa-credit-card"></i> Realizar Pago Múltiple por Transferencia';
            paymentAction.value = 'payMultiple';
            singleSection.style.display = 'none';
            multipleSection.style.display = 'block';
            amountLabel.textContent = 'Total a Pagar:';

            // Generar lista de contribuciones
            contributionsList.innerHTML = '';
            contributions.forEach(contribution => {
                const item = document.createElement('div');
                item.className = 'contribution-item';
                item.innerHTML = `
                    <span class="contribution-item-period">${contribution.period}</span>
                    <span class="contribution-item-amount">Bs. ${contribution.amount.toFixed(2)}</span>
                `;
                contributionsList.appendChild(item);
            });

            // Agregar inputs hidden para las contribuciones seleccionadas
            selectedContributionsInput.innerHTML = '';
            contributions.forEach(contribution => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_contributions[]';
                input.value = contribution.id;
                selectedContributionsInput.appendChild(input);
            });

            totalAmountInput.value = total.toFixed(2);
            amountInput.value = '';
        } else {
            // Configurar para pago individual
            modalTitle.innerHTML = '<i class="fas fa-credit-card"></i> Realizar Pago Individual por Transferencia';
            paymentAction.value = 'pay';
            singleSection.style.display = 'block';
            multipleSection.style.display = 'none';
            amountLabel.textContent = 'Monto a Pagar:';

            const contribution = contributions[0];
            document.getElementById('payId').value = contribution.id;
            periodInput.value = contribution.period;
            amountInput.value = contribution.amount.toFixed(2);
            totalAmountInput.value = '';
            selectedContributionsInput.innerHTML = '';
        }

        totalAmountDisplay.textContent = 'Bs. ' + total.toFixed(2);
        modal.style.display = 'block';
    }

    // Cerrar modal
    document.querySelector('.close').addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Validación del formulario
    if (payForm) {
        payForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('proof');
            const action = document.getElementById('paymentAction').value;
            
            // Validar que se haya seleccionado un archivo
            if (!fileInput.files.length) {
                e.preventDefault();
                
                // Resaltar el campo de archivo
                const proofGroup = document.getElementById('proofGroup');
                if (proofGroup) {
                    proofGroup.classList.add('has-error');
                    
                    // Agregar mensaje de error si no existe
                    const existingError = proofGroup.querySelector('.field-error');
                    if (!existingError) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'field-error';
                        errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Debe subir un comprobante de pago para continuar';
                        proofGroup.appendChild(errorDiv);
                    }
                    
                    // Scroll al campo de archivo
                    proofGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                alert('⚠️ Comprobante Requerido\n\nDebe subir un comprobante de pago (imagen o PDF) para procesar su solicitud.');
                return;
            }

            // Validar que el archivo sea válido
            const file = fileInput.files[0];
            if (file.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('⚠️ Archivo muy grande\n\nEl comprobante no puede superar los 2MB. Por favor seleccione un archivo más pequeño.');
                return;
            }

            if (file.size === 0) {
                e.preventDefault();
                alert('⚠️ Archivo inválido\n\nEl archivo seleccionado está vacío. Por favor seleccione un comprobante válido.');
                return;
            }

            if (action === 'payMultiple') {
                // Contar solo los inputs hidden generados en el modal (no los checkboxes de la tabla)
                const selectedInputs = document.querySelectorAll('#selectedContributionsInput input[name="selected_contributions[]"]');
                const totalAmount = parseFloat(document.getElementById('totalAmount').value);
                
                if (selectedInputs.length === 0) {
                    e.preventDefault();
                    alert('Error: No se seleccionaron contribuciones.');
                    return;
                }
                
                if (!totalAmount || totalAmount <= 0) {
                    e.preventDefault();
                    alert('El monto total debe ser mayor a 0.');
                    return;
                }
                
                const contributionsText = selectedInputs.length === 1 ? 'contribución' : 'contribuciones';
                if (!confirm(`¿Está seguro de enviar el pago de ${selectedInputs.length} ${contributionsText} por un total de Bs. ${totalAmount.toFixed(2)} para revisión?`)) {
                    e.preventDefault();
                }
            } else {
                const amount = document.getElementById('amount').value;
                
                if (!amount || parseFloat(amount) <= 0) {
                    e.preventDefault();
                    alert('El monto debe ser mayor a 0.');
                    return;
                }
                
                if (!confirm('¿Está seguro de enviar este pago para revisión?')) {
                    e.preventDefault();
                }
            }
        });
    }

    // Inicializar
    updateSelectedContributions();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>