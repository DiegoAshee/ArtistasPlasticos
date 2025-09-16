<?php
// Helper functions for URL generation
if (!function_exists('u')) {
    function u(string $path): string {
        $base = rtrim(BASE_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}
?>

<?php
// Set up variables for the layout
$title = 'Eliminar Movimiento - Asociación de Artistas';
$currentPath = 'movement/delete';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Movimientos', 'url' => u('movement/list')],
    ['label' => 'Eliminar Movimiento', 'url' => null],
];

// Start output buffering
ob_start();
?>
<style>
    .delete-container {
        background: var(--surface);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        padding: 2.5rem 3rem;
        margin: 2rem auto;
        max-width: 600px;
        width: calc(100% - 2rem);
        border: 1px solid var(--border);
        text-align: center;
    }

    .delete-icon {
        font-size: 4rem;
        color: #e74c3c;
        margin-bottom: 1.5rem;
        display: block;
    }

    .delete-title {
        color: var(--cream-900);
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 1.5rem 0;
        font-family: 'Playfair Display', serif;
    }

    .delete-message {
        color: black;
        font-size: 1.1rem;
        margin-bottom: 2rem;
        line-height: 1.6;
    }

    .movement-details {
        background: var(--cream-50);
        border-radius: 12px;
        padding: 1.5rem;
        margin: 2rem 0;
        border-left: 4px solid #e74c3c;
        text-align: left;
    }

    .movement-details h3 {
        color: var(--cream-800);
        margin: 0 0 1rem 0;
        font-size: 1.2rem;
        text-align: center;
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--cream-200);
    }

    .detail-row:last-child {
        border-bottom: none;
    }

    .detail-label {
        font-weight: 600;
        color: var(--cream-800);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .detail-value {
        font-weight: 500;
        color: var(--cream-600);
    }

    .amount-value {
        font-weight: 700;
        font-size: 1.1rem;
        color: #e74c3c;
    }

    .warning-box {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 1rem;
        margin: 1.5rem 0;
        color: #856404;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .btn-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }

    .btn-danger {
        background: #e74c3c;
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        min-width: 180px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
    }

    .btn-danger:hover {
        background: #c0392b;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        min-width: 180px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
        color: white;
        text-decoration: none;
    }

    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin: 0 0 2rem 0;
        border-left: 4px solid #dc2626;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.05rem;
    }

    @media (max-width: 768px) {
        .delete-container {
            padding: 2rem 1.5rem;
            margin: 1rem;
            width: calc(100% - 2rem);
        }
        
        .btn-group {
            flex-direction: column;
            align-items: center;
        }

        .detail-row {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="delete-container">
        <i class="fas fa-exclamation-triangle delete-icon"></i>
        <h1 class="delete-title">Confirmar Eliminación</h1>
        <p class="delete-message">
            ¿Está seguro que desea eliminar este movimiento? Esta acción no se puede deshacer.
        </p>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($movement) && is_array($movement)): ?>
            <div class="movement-details">
                <h3><i class="fas fa-file-alt"></i> Detalles del Movimiento</h3>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-file-alt"></i>
                        Descripción:
                    </span>
                    <span class="detail-value"><?= htmlspecialchars($movement['description'] ?? 'N/A') ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-dollar-sign"></i>
                        Monto:
                    </span>
                    <span class="detail-value amount-value">
                        Bs. <?= number_format((float)($movement['amount'] ?? 0), 2) ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-calendar"></i>
                        Fecha:
                    </span>
                    <span class="detail-value">
                        <?= !empty($movement['dateCreation']) ? date('d/m/Y H:i', strtotime($movement['dateCreation'])) : 'N/A' ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-credit-card"></i>
                        Tipo de Pago:
                    </span>
                    <span class="detail-value"><?= htmlspecialchars($movement['payment_type_description'] ?? 'N/A') ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-tag"></i>
                        Concepto:
                    </span>
                    <span class="detail-value"><?= htmlspecialchars($movement['concept_description'] ?? 'N/A') ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">
                        <i class="fas fa-user"></i>
                        Usuario:
                    </span>
                    <span class="detail-value"><?= htmlspecialchars($movement['user_login'] ?? 'N/A') ?></span>
                </div>
            </div>

            <div class="warning-box">
                <i class="fas fa-exclamation-triangle"></i>
                <span><strong>Advertencia:</strong> Una vez eliminado, este movimiento no podrá ser recuperado.</span>
            </div>

            <div class="btn-group">
                <a href="<?= u('movement/list') ?>" class="btn-cancel">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
                <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/movement/delete/<?= (int)($movement['idMovement'] ?? 0) ?>" style="display: inline;">
                    <button type="submit" class="btn-danger" onclick="return confirmDelete()">
                        <i class="fas fa-trash"></i>
                        Eliminar Definitivamente
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                No se encontró el movimiento especificado.
            </div>
            <div class="btn-group">
                <a href="<?= u('movement/list') ?>" class="btn-cancel">
                    <i class="fas fa-arrow-left"></i>
                    Volver a la Lista
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDelete() {
    return confirm('¿Está completamente seguro de que desea eliminar este movimiento?\n\nEsta acción NO se puede deshacer.');
}

// Add keyboard support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '<?= u("movement/list") ?>';
    }
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>