<?php
$title = 'Recibo de Pago';
$currentPath = 'cobros/recibo';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Cobros', 'url' => u('cobros/list')],
    ['label' => 'Recibo', 'url' => null],
];

$receiptData = $receiptData ?? null;

if (!$receiptData) {
    header('Location: ' . u('cobros/list'));
    exit;
}

$payments = $receiptData['payments'];
$partnerName = $receiptData['partnerName'];
$partnerCI = $receiptData['partnerCI'];
$paymentTypeName = $receiptData['paymentTypeName'];
$totalAmount = $receiptData['totalAmount'];
$paymentDate = $receiptData['paymentDate'];
$receiptNumber = $receiptData['receiptNumber'];

ob_start();
?>
<style>
    @media print {
        .no-print { display: none !important; }
        .receipt-container { 
            box-shadow: none !important; 
            margin: 0 !important;
            max-width: none !important;
            padding: 15px !important;
        }
        .actions { display: none !important; }
    }
    
    .receipt-container {
        max-width: 600px;
        margin: 20px auto;
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333333;
        border: 1px solid #e0e0e0;
    }
    
    .receipt-header {
        text-align: center;
        border-bottom: 2px solid #bbae97;
        padding-bottom: 20px;
        margin-bottom: 25px;
    }
    
    .receipt-number {
        font-size: 14px;
        color: #666666;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .receipt-title {
        font-size: 24px;
        font-weight: 700;
        color: #2a2a2a;
        margin: 0;
        text-transform: uppercase;
    }
    
    .receipt-date {
        font-size: 15px;
        color: #666666;
        margin-top: 8px;
        font-weight: 500;
    }
    
    .customer-info {
        background: #f8f6f2;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e9e5de;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding: 5px 0;
    }
    
    .info-label {
        font-weight: 600;
        color: #2a2a2a;
        min-width: 140px;
    }
    
    .payments-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .payments-table th,
    .payments-table td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .payments-table th {
        background: #bbae97;
        font-weight: 600;
        color: #2a2a2a;
        text-transform: uppercase;
        font-size: 13px;
    }
    
    .payments-table tr:last-child td {
        border-bottom: none;
    }
    
    .total-section {
        background: #f8f6f2;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 25px;
        border: 1px solid #e9e5de;
    }
    
    .total-label {
        font-weight: 600;
        color: #2a2a2a;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .total-amount {
        font-size: 22px;
        font-weight: 700;
        color: #065F46;
        margin: 0;
    }
    
    .actions {
        margin-top: 25px;
        text-align: center;
        display: flex;
        justify-content: center;
        gap: 15px;
        flex-wrap: wrap;
    }
    
    .btn {
        padding: 12px 20px;
        border-radius: 6px;
        border: none;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .btn-print {
        background: #6c757d;
        color: white;
    }
    
    .btn-print:hover {
        background: #5a6268;
    }
    
    .btn-back {
        background: #28a745;
        color: white;
    }
    
    .btn-back:hover {
        background: #218838;
    }
    
    .receipt-footer {
        margin-top: 25px;
        text-align: center;
        font-size: 13px;
        color: #666666;
        border-top: 1px solid #e0e0e0;
        padding-top: 15px;
    }
    
    .receipt-footer p {
        margin: 5px 0;
    }
    
    .text-right {
        text-align: right;
    }
</style>

<div class="receipt-container">
    <div class="receipt-header">
        <div class="receipt-number">Recibo N° <?= htmlspecialchars($receiptNumber) ?></div>
        <h1 class="receipt-title">RECIBO DE PAGO</h1>
        <div class="receipt-date"><?= date('d/m/Y H:i', strtotime($paymentDate)) ?></div>
    </div>
    
    <div class="customer-info">
        <div class="info-row">
            <span class="info-label">Socio:</span>
            <span><?= htmlspecialchars($partnerName) ?></span>
        </div>
        <?php if (!empty($partnerCI)): ?>
        <div class="info-row">
            <span class="info-label">CI:</span>
            <span><?= htmlspecialchars($partnerCI) ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">Tipo de Pago:</span>
            <span><?= htmlspecialchars($paymentTypeName) ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Cantidad de Aportaciones:</span>
            <span><?= count($payments) ?></span>
        </div>
    </div>
    
    <table class="payments-table">
        <thead>
            <tr>
                <th>Aportación</th>
                <th>Período</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?= htmlspecialchars($payment['contributionName']) ?></td>
                    <td><?= htmlspecialchars($payment['monthYear'] ?? 'N/A') ?></td>
                    <td class="text-right">Bs. <?= number_format($payment['paidAmount'], 2, '.', ',') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-label">TOTAL PAGADO</div>
        <div class="total-amount">Bs. <?= number_format($totalAmount, 2, '.', ',') ?></div>
    </div>
    
    <div class="receipt-footer">
        <p><strong>¡Gracias por su pago!</strong></p>
        <p>Este recibo es válido como comprobante de pago.</p>
    </div>
    
    <div class="actions no-print">
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> Imprimir Recibo
        </button>
        <a href="<?= u('cobros/socios') ?>" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> Volver a Cobros
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Opcional: Podrías añadir funcionalidad adicional aquí
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';