<?php
// Start output buffering at the beginning of the file
ob_start();

// Verify receipt data exists
if (!isset($receiptData) || !is_array($receiptData)) {
    echo '<div class="alert alert-danger">Error: No se encontraron datos para mostrar el recibo.</div>';
    return;
}

// Configure page title and breadcrumbs
$title = 'Recibo de Movimiento - ' . ($receiptData['receiptNumber'] ?? 'N/A');
$currentPath = 'movement/receipt';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Movimientos', 'url' => u('movement/list')],
    ['label' => 'Recibo ' . ($receiptData['receiptNumber'] ?? 'N/A'), 'url' => null],
];

// Add specific styles for the receipt
$styles = $styles ?? [];
$styles[] = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
?>

<!-- Main Content -->
<main id="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fas fa-receipt me-2"></i>Recibo de Movimiento
                        </h4>
                        <!-- <div class="card-actions">
                            <a href="<?= u('movement/list') ?>" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Imprimir
                            </button>
                        </div> -->
                    </div>
                    <div class="card-body p-0">

<style>
    /* Main container */
    .receipt-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0;
        background: #fff;
        font-family: Arial, sans-serif;
        color: #333;
        line-height: 1.6;
        border: 1px solid #e0e0e0;
    }
    
    /* Header styles */
    .receipt-header {
        background-color: #f8f9fa;
        padding: 20px;
        text-align: center;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .receipt-header h1 {
        color: #2c3e50;
        margin: 0 0 5px 0;
        font-size: 1.5rem;
        font-weight: 600;
    }
    
    .receipt-header .subtitle {
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0;
    }
    
    /* Content area */
    .receipt-content {
        padding: 20px;
    }
    
    /* Info sections */
    .receipt-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .info-section {
        background: #fff;
        padding: 15px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
    }
    
    .info-section h3 {
        color: #2c3e50;
        font-size: 1rem;
        margin: 0 0 15px 0;
        padding-bottom: 8px;
        border-bottom: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 0.9rem;
    }
    
    .info-label {
        color: #6c757d;
        font-weight: 500;
    }
    
    .info-value {
        color: #2c3e50;
        font-weight: 500;
        text-align: right;
    }
    
    /* Total section */
    .total-section {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        padding: 15px;
        margin: 20px 0;
        text-align: right;
        border-radius: 4px;
    }
    
    .total-amount {
        font-size: 1.4rem;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .amount {
        color: #28a745;
    }
    
    .total-label {
        display: block;
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    /* Buttons */
    .receipt-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e0e0e0;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
        border: 1px solid transparent;
    }
    
    .btn i {
        margin-right: 6px;
        font-size: 0.9em;
    }
    
    .btn-primary {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .btn-primary:hover {
        background-color: #0069d9;
        border-color: #0062cc;
    }
    
    .btn-outline-secondary {
        background-color:  #6c757d;
        color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
        border-color: #6c757d;
    }
    
    /* Alert styles */
    .alert {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Print styles */
    @media print {
        body * {
            visibility: hidden;
            margin: 0;
            padding: 0;
        }
        
        #main-content,
        #main-content * {
            visibility: visible;
        }
        
        #main-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            margin: 0;
        }
        
        .receipt-container {
            border: none;
            box-shadow: none;
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
        }
        
        .receipt-actions,
        .d-print-none,
        .btn-toolbar,
        .card,
        .card-header,
        .card-body {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        
        .receipt-actions,
        .d-print-none,
        .btn-toolbar {
            display: none !important;
        }
    }
</style>

<div class="receipt-container">
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Receipt Header -->
    <div class="receipt-header">
        <h1>RECIBO DE MOVIMIENTO</h1>
        <p class="subtitle">Comprobante de transacción</p>
    </div>

    <div class="receipt-body">
        <!-- Receipt Information -->
        <div class="receipt-info">
            <div class="info-section">
                <h3><i class="fas fa-receipt"></i> Información del Recibo</h3>
                <div class="info-item">
                    <span class="info-label">Número:</span>
                    <span class="info-value"><?= htmlspecialchars($receiptData['receiptNumber'] ?? 'N/A') ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Emisión:</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($receiptData['movement']['dateCreation'] ?? 'now')) ?></span>
                </div>
            </div>

            <div class="info-section">
                <h3><i class="fas fa-user"></i> Información del Usuario</h3>
                <div class="info-item">
                    <span class="info-label">Usuario:</span>
                    <span class="info-value"><?= htmlspecialchars($receiptData['user']['name'] ?? 'N/A') ?></span>
                </div>
                <!-- <div class="info-item">
                    <span class="info-label">Login:</span>
                    <span class="info-value"><?= htmlspecialchars($receiptData['user']['login'] ?? 'N/A') ?></span>
                </div> -->
            </div>
        </div>

        <!-- Movement Details -->
        <div class="info-section">
            <h3><i class="fas fa-info-circle"></i> Detalles del Movimiento</h3>
            <div class="info-item">
                <span class="info-label">Concepto:</span>
                <span class="info-value"><?= !empty($receiptData['movement']['concept_description']) ? htmlspecialchars($receiptData['movement']['concept_description']) : 'No especificado' ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo de Movimiento:</span>
                <span class="info-value"><?= !empty($receiptData['movement']['concept_type']) ? ucfirst(htmlspecialchars($receiptData['movement']['concept_type'])) : 'No especificado' ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Destinatario:</span>
                <span class="info-value"><?= !empty($receiptData['movement']['nameDestination']) ? htmlspecialchars($receiptData['movement']['nameDestination']) : 'No especificado' ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo de Pago:</span>
                <span class="info-value"><?= !empty($receiptData['movement']['payment_type_description']) ? htmlspecialchars($receiptData['movement']['payment_type_description']) : 'No especificado' ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Descripción:</span>
                <span class="info-value"><?= !empty($receiptData['movement']['description']) ? nl2br(htmlspecialchars($receiptData['movement']['description'])) : 'Sin descripción' ?></span>
            </div>
        </div>

        <!-- Total Amount -->
        <div class="total-section">
            <div class="total-amount">
                <span class="total-label">Monto Total:</span>
                <span class="amount">Bs. <?= isset($receiptData['movement']['amount']) ? number_format((float)$receiptData['movement']['amount'], 2, ',', '.') : '0,00' ?></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="receipt-actions d-print-none">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir Recibo
            </button>
            <a href="<?= u('movement/list') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la lista
            </a>
        </div>
    </div>
</div>

<script>
    // Auto-focus print button for better UX
    document.addEventListener('DOMContentLoaded', function() {
        const printButton = document.querySelector('.btn-primary');
        if (printButton) {
            printButton.focus();
        }
    });
</script>

                        </div><!-- End card-body -->
                    </div><!-- End card -->
                </div><!-- End col -->
            </div><!-- End row -->
        </div><!-- End container-fluid -->
    </main><!-- End main-content -->

    <?php
    // Get the buffered content
    $content = ob_get_clean();

    // Include the app layout
    require_once __DIR__ . '/../layouts/app.php';
    ?>