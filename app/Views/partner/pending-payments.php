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

ob_start();
?>
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

    .summary-card.pending {
        border-top: 4px solid var(--warning);
    }

    .summary-card.paid {
        border-top: 4px solid var(--success);
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

    .pending-items-container {
        background: #a49884;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 24px;
        padding: 24px;
        color: var(--text-dark);
    }

    .pending-item {
        background: #dccaaf;
        border-left: 4px solid var(--warning);
        padding: 18px 24px;
        border-radius: 8px;
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }

    .pending-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .pending-info {
        flex: 1;
    }

    .pending-amount {
        font-size: 18px;
        font-weight: bold;
        color: var(--text-dark);
        margin-bottom: 8px;
    }

    .pending-details {
        color: #6c757d;
        font-size: 14px;
        line-height: 1.4;
    }

    .pay-btn {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: var(--text-light);
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .pay-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgb(180, 168, 147);
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

    .empty-state {
        text-align: center;
        padding: 40px;
        background: #fff;
        border-radius: 12px;
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

        .pending-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }
    }
</style>

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

<!-- Tarjetas resumen -->
<!-- <div class="summary-cards">
    <div class="summary-card pending">
        <h3><i class="fas fa-clock"></i> Total Pendiente</h3>
        <p style="font-size: 24px; font-weight: bold; color: var(--warning);">
            <?= number_format($totals['pending'], 2) ?> Bs
        </p>
    </div>
    <div class="summary-card paid">
        <h3><i class="fas fa-check-circle"></i> Total Pagado</h3>
        <p style="font-size: 24px; font-weight: bold; color: var(--success);">
            <?= number_format($totals['paid'], 2) ?> Bs
        </p>
    </div>
</div> -->

<!-- Filtros -->
<div class="filters-section">
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
</div>

<!-- Lista de pagos pendientes -->
<div class="pending-items-container">
    <div style="padding: 20px; border-bottom: 1px solid var(--border-color); background: var(--grid-bg);">
        <h2 style="margin: 0; color: var(--text-dark);">
            <i class="fas fa-exclamation-circle"></i> 
            Contribuciones Pendientes (<?= $total ?> registros)
        </h2>
    </div>
    
    <?php if (!empty($pendingPayments)): ?>
        <?php foreach ($pendingPayments as $payment): ?>
            <div class="pending-item">
                <div class="pending-info">
                    <div class="pending-amount">
                        <?= number_format($payment['amount'] ?? 0, 2) ?> Bs
                        <?php if (($payment['paidAmount'] ?? 0) > 0): ?>
                            <span style="font-size: 14px; color: var(--warning); font-weight: normal;">
                                (Pagado: <?= number_format($payment['paidAmount'], 2) ?> Bs)
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="pending-details">
                        <strong>Período:</strong> <?= htmlspecialchars($payment['monthYear'] ?? '') ?><br>
                        <strong>Fecha:</strong> <?= isset($payment['contrib_date']) && $payment['contrib_date'] 
                            ? date('d/m/Y', strtotime($payment['contrib_date'])) 
                            : 'N/A' ?><br>
                        <?php if (!empty($payment['notes'])): ?>
                            <strong>Notas:</strong> <?= htmlspecialchars($payment['notes']) ?><br>
                        <?php endif; ?>
                        <strong>Saldo pendiente:</strong> 
                        <span style="color: var(--warning); font-weight: bold;">
                            <?= number_format($payment['balance'] ?? 0, 2) ?> Bs
                        </span>
                    </div>
                </div>
                <button class="pay-btn open-pay-modal" 
                        data-id="<?= (int)($payment['idContribution'] ?? 0) ?>" 
                        data-amount="<?= $payment['balance'] ?? 0 ?>"
                        data-period="<?= htmlspecialchars($payment['monthYear'] ?? '') ?>">
                    <i class="fas fa-credit-card"></i> Pagar Ahora
                </button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-check-circle"></i>
            <h3>¡Excelente! No hay pagos pendientes</h3>
            <p style="color: #6c757d; margin-top: 10px;">
                Estás al día con todas tus contribuciones.
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Paginación -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
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

<!-- Modal para Realizar Pago -->
<div id="payModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 style="margin-bottom: 25px; color: var(--text-dark);">
            <i class="fas fa-credit-card"></i> Realizar Pago por Transferencia
        </h2>
        
        <form method="POST" action="<?= u('partner/pending-payments') ?>" enctype="multipart/form-data" id="payForm">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="idContribution" id="payId">
            
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
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('payModal');
    const payForm = document.getElementById('payForm');
    
    // Abrir modal
    document.querySelectorAll('.open-pay-modal').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id') || 0;
            const amount = btn.getAttribute('data-amount') || 0;
            const period = btn.getAttribute('data-period') || '';
            
            document.getElementById('payId').value = id;
            document.getElementById('amount').value = parseFloat(amount).toFixed(2);
            document.getElementById('period').value = period;
            
            modal.style.display = 'block';
        });
    });

    // Cerrar modal
    document.querySelector('.close').addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // Validación del formulario
    payForm.addEventListener('submit', (e) => {
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
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>