<?php
$title = 'Recibo de Pago - Administración';
$currentPath = 'admin/payment-receipt';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Administración', 'url' => u('admin/review-payments')],
    ['label' => 'Recibo de Pago', 'url' => null],
];

$receiptData = $receiptData ?? null;
if (!$receiptData) {
    header('Location: ' . u('admin/review-payments'));
    exit;
}

ob_start();
?>
<style>
    /* Estilos para el recibo */
    .receipt-container {
        max-width: 800px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .receipt-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        text-align: center;
    }

    .receipt-header h1 {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 700;
    }

    .receipt-header .subtitle {
        opacity: 0.9;
        font-size: 16px;
        margin: 0;
    }

    .receipt-body {
        padding: 30px;
    }

    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .info-section {
        background: #f8fafc;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #667eea;
    }

    .info-section h3 {
        margin: 0 0 15px 0;
        color: #374151;
        font-size: 16px;
        font-weight: 600;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .info-label {
        font-weight: 500;
        color: #6b7280;
    }

    .info-value {
        font-weight: 600;
        color: #111827;
    }

    .payments-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payments-table th {
        background: #f3f4f6;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .payments-table td {
        padding: 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #6b7280;
    }

    .payments-table tr:last-child td {
        border-bottom: none;
    }

    .payments-table tr:hover {
        background: #fafafa;
    }

    .amount {
        font-weight: 700;
        color: #059669;
    }

    .total-section {
        background: #f0f9ff;
        border: 2px solid #0ea5e9;
        border-radius: 12px;
        padding: 25px;
        margin: 30px 0;
        text-align: center;
    }

    .total-amount {
        font-size: 32px;
        font-weight: 800;
        color: #0c4a6e;
        margin: 0;
    }

    .total-label {
        color: #0369a1;
        font-size: 18px;
        margin: 8px 0 0 0;
        font-weight: 500;
    }

    .receipt-footer {
        background: #f9fafb;
        padding: 25px 30px;
        border-top: 1px solid #e5e7eb;
        text-align: center;
        color: #6b7280;
    }

    .actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin: 30px 0;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-primary:hover {
        background: #5a67d8;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: white;
        color: #667eea;
        border-color: #667eea;
    }

    .btn-secondary:hover {
        background: #667eea;
        color: white;
    }

    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-multiple {
        background: #dbeafe;
        color: #1e40af;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        
        .receipt-container,
        .receipt-container * {
            visibility: visible;
        }
        
        .receipt-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            box-shadow: none !important;
        }
        
        .actions {
            display: none !important;
        }
        
        .receipt-footer {
            page-break-inside: avoid;
        }
    }

    @media (max-width: 768px) {
        .receipt-info {
            grid-template-columns: 1fr;
        }
        
        .actions {
            flex-direction: column;
        }
        
        .payments-table {
            font-size: 14px;
        }
        
        .payments-table th,
        .payments-table td {
            padding: 12px 8px;
        }
    }
</style>

<div class="receipt-container">
    <!-- Header del recibo -->
    <div class="receipt-header">
        <h1>RECIBO DE PAGO</h1>
        <p class="subtitle">
            <?= $receiptData['isMultiple'] ? 'Pago Múltiple Aprobado' : 'Pago Individual Aprobado' ?>
        </p>
        <?php if ($receiptData['isMultiple']): ?>
            <span class="badge badge-multiple">Múltiple</span>
        <?php endif; ?>
        <span class="badge badge-success">Aprobado</span>
    </div>

    <div class="receipt-body">
        <!-- Información general -->
        <div class="receipt-info">
            <div class="info-section">
                <h3><i class="fas fa-user"></i> Información del Socio</h3>
                <div class="info-item">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value"><?= htmlspecialchars($receiptData['partner']['name']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">CI:</span>
                    <span class="info-value"><?= htmlspecialchars($receiptData['partner']['ci'] ?: 'No especificado') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID Socio:</span>
                    <span class="info-value">#<?= (int)$receiptData['partner']['idPartner'] ?></span>
                </div>
            </div>

            <div class="info-section">
                <h3><i class="fas fa-calendar"></i> Información del Pago</h3>
                <div class="info-item">
                    <span class="info-label">Fecha de Aprobación:</span>
                    <span class="info-value"><?= date('d/m/Y H:i:s') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha del Pago:</span>
                    <span class="info-value"><?= date('d/m/Y H:i:s', strtotime($receiptData['date'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cantidad de Pagos:</span>
                    <span class="info-value"><?= count($receiptData['payments']) ?></span>
                </div>
            </div>
        </div>

        <!-- Detalle de pagos -->
        <h3><i class="fas fa-list"></i> Detalle de Pagos</h3>
        <table class="payments-table">
            <thead>
                <tr>
                    <th>ID Pago</th>
                    <th>Contribución</th>
                    <th>Período</th>
                    <th>Monto</th>
                    <th>Tipo de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($receiptData['payments'] as $payment): ?>
                <tr>
                    <td><strong>#<?= (int)$payment['idPayment'] ?></strong></td>
                    <td>
                        <?= htmlspecialchars($payment['contributionName'] ?: 'Contribución #' . $payment['idContribution']) ?>
                        <br>
                        <small style="color: #9ca3af;">ID: <?= (int)$payment['idContribution'] ?></small>
                    </td>
                    <td><?= htmlspecialchars($payment['monthYear']) ?></td>
                    <td class="amount">Bs. <?= number_format((float)$payment['paidAmount'], 2) ?></td>
                    <td><?= htmlspecialchars($payment['paymentType'] ?: 'Transferencia bancaria') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Total -->
        <div class="total-section">
            <p class="total-amount">Bs. <?= number_format($receiptData['total'], 2) ?></p>
            <p class="total-label">Total Aprobado</p>
        </div>

        <!-- Comprobante si existe -->
        <?php if (!empty($receiptData['payments'][0]['voucherImageURL'])): ?>
        <div class="info-section">
            <h3><i class="fas fa-file-image"></i> Comprobante de Pago</h3>
            <div class="info-item">
                <span class="info-label">Archivo:</span>
                <span class="info-value">
                    <a href="<?= u($receiptData['payments'][0]['voucherImageURL']) ?>" 
                       target="_blank" 
                       style="color: #667eea; text-decoration: underline;">
                        Ver comprobante
                    </a>
                </span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Acciones -->
        <div class="actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir Recibo
            </button>
            <a href="<?= u('admin/review-payments') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Revisar Pagos
            </a>
        </div>
    </div>

    <!-- Footer del recibo -->
    <div class="receipt-footer">
        <p style="margin: 0; font-size: 14px;">
            <strong>Sistema de Gestión de Pagos</strong><br>
            Recibo generado automáticamente el <?= date('d/m/Y H:i:s') ?><br>
            Este documento certifica la aprobación del pago mencionado.
        </p>
    </div>
</div>

<script>
// Auto-focus en el botón de imprimir para facilitar la acción
document.addEventListener('DOMContentLoaded', function() {
    // Opcional: Imprimir automáticamente al cargar (descomenta si lo deseas)
    // setTimeout(() => window.print(), 500);
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>