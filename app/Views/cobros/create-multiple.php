<?php
$title = 'Pagar Múltiples Aportaciones';
$currentPath = 'cobros/create-multiple';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Cobros', 'url' => u('cobros/list')],
    ['label' => 'Pagar Múltiples', 'url' => null],
];

$debtsData = $debtsData ?? [];
$totalAmount = $totalAmount ?? 0;
$partnerName = $partnerName ?? '';
$types = $types ?? [];
$error = $error ?? null;
$selectedDebts = $selectedDebts ?? [];

ob_start();
?>
<style>
    .payment-container {
        max-width: 800px;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333333; /* Color de texto sólido sin transparencia */
    }
    
    .payment-header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .payment-header h2 {
        color: #2a2a2a;
        font-weight: 700;
        margin-bottom: 10px;
    }
    
    .debts-list {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        max-height: 300px;
        overflow-y: auto;
        border: 1px solid #e0e0e0;
    }
    
    .debts-list h4 {
        color: #2a2a2a;
        margin-bottom: 15px;
        font-weight: 600;
    }
    
    .debt-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        background: #ffffff;
        margin-bottom: 8px;
        border-radius: 8px;
    }
    
    .debt-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .payment-summary {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #e0e0e0;
    }
    
    .total-amount {
        font-size: 28px;
        font-weight: 800;
        color: #065F46;
        text-align: center;
        margin: 15px 0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #2a2a2a;
    }
    
    .form-group select, .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #ced4da;
        border-radius: 8px;
        font-size: 16px;
        background: #ffffff;
        color: #333333;
    }
    
    .form-group select:focus, .form-group input:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .btn-pay {
        background: #28a745;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        width: 100%;
        transition: background 0.3s ease;
        margin-top: 10px;
    }
    
    .btn-pay:hover {
        background: #218838;
    }
    
    .partner-info {
        text-align: center;
        margin-bottom: 25px;
        padding: 20px;
        background: #e9ecef;
        border-radius: 10px;
        border: 1px solid #ced4da;
    }
    
    .partner-info h3 {
        color: #2a2a2a;
        margin-bottom: 5px;
        font-weight: 700;
    }
    
    .partner-info p {
        color: #6c757d;
        font-weight: 500;
        margin: 0;
    }
    
    .back-link {
        display: block;
        text-align: center;
        margin-top: 20px;
        color: #6c757d;
        text-decoration: none;
        font-weight: 500;
    }
    
    .back-link:hover {
        color: #495057;
        text-decoration: underline;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        border: 1px solid #f5c6cb;
    }
    
    small {
        color: #6c757d;
        font-size: 14px;
    }
</style>

<div class="payment-container">
    <div class="payment-header">
        <h2><i class="fas fa-credit-card"></i> Confirmar Pago Múltiple</h2>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="partner-info">
        <h3><?= htmlspecialchars($partnerName) ?></h3>
        <p><?= count($debtsData) ?> aportación(es) seleccionada(s)</p>
    </div>

    <div class="debts-list">
        <h4>Aportaciones a pagar:</h4>
        <?php foreach ($debtsData as $debt): ?>
            <div class="debt-item">
                <div>
                    <strong><?= htmlspecialchars($debt['contributionName'] ?? 'Aporte #' . $debt['idContribution']) ?></strong>
                    <?php if (!empty($debt['monthYear'])): ?>
                        <br><small><?= htmlspecialchars($debt['monthYear']) ?></small>
                    <?php endif; ?>
                </div>
                <div style="font-weight: 600; color: #991B1B;">
                    Bs. <?= number_format($debt['amount'], 2, '.', ',') ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="payment-summary">
        <div style="text-align: center;">
            <h4>Total a Pagar</h4>
            <div class="total-amount">
                Bs. <?= number_format($totalAmount, 2, '.', ',') ?>
            </div>
        </div>
    </div>

    <form method="post" action="<?= u('cobros/create-multiple') ?>">
        <?php foreach ($selectedDebts as $debt): ?>
            <input type="hidden" name="selected_debts[]" value="<?= htmlspecialchars($debt) ?>">
        <?php endforeach; ?>
        
        <div class="form-group">
            <label for="idPaymentType">Tipo de Pago *</label>
            <select name="idPaymentType" id="idPaymentType" required>
                <option value="">— Seleccione tipo de pago —</option>
                <?php foreach ($types as $type): ?>
                    <option value="<?= (int)$type['idPaymentType'] ?>">
                        <?= htmlspecialchars($type['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="paidAmount">Monto a Pagar *</label>
            <input type="number" name="paidAmount" id="paidAmount" 
                   value="<?= number_format($totalAmount, 2, '.', '') ?>" 
                   step="0.01" min="0.01" required readonly
                   style="background: #e9ecef; color: #333333;">
            <small>El monto total se calcula automáticamente</small>
        </div>

        <button type="submit" name="confirm_payment" class="btn-pay">
            <i class="fas fa-check-circle"></i> Confirmar Pago
        </button>
        
        <a href="<?= u('cobros/debidas') ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Volver atrás
        </a>
    </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>