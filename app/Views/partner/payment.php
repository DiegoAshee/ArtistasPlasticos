<?php
$title = 'Mis Pagos';
$currentPath = 'partner/payment';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Mis Pagos', 'url' => null],
];

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
        --danger:rgb(239, 216, 68);
        --border-color: #cbd5e0;
        --grid-bg: #a49884;
    }

    .modern-table th, .modern-table td { 
        padding: 12px 16px; 
        line-height: 1.5; 
        vertical-align: middle;
        border-bottom: 1px solid var(--border-color);
    }
    
    .modern-table { 
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--grid-bg);
        border-radius: 12px;
        overflow: hidden;
        color: #2d3748;
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
        color: #2d3748;
    }
    
    .modern-table tbody tr:hover {
        background-color: #b8ac98;
    }
    
    .modern-table tbody tr:last-child td {
        border-bottom: none;
    }

    .table-container, .cards-container { 
        background: #a49884;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 24px;
        padding: 24px;
        color: #2d3748;
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

    .filter-container { 
        display: flex; 
        gap: 12px; 
        align-items: center; 
        margin-bottom: 20px;
        padding: 0 8px;
    }
    
    .filter-select { 
        padding: 10px 16px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .filter-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    .pay-btn { 
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: #fff;
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
    
    .pending-item { 
        background:#dccaaf;
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

    .modal { 
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.7);
        z-index: 1000;
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
    
    .error-message { 
        color: #b91c1c;
        background: #fee2e2;
        border-left: 4px solid #dc2626;
        padding: 12px 16px;
        border-radius: 6px;
        margin: 16px 0;
    }
    
    .success-message { 
        color: #166534;
        background: #dcfce7;
        border-left: 4px solid #16a34a;
        padding: 12px 16px;
        border-radius: 6px;
        margin: 16px 0;
    }

    @media (max-width: 768px) {
        .summary-cards { flex-direction: column; }
        .modern-table { font-size: 14px; }
        .pending-item { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>

<?php if ($success): ?>
    <div class="success-message"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Resumen en Cards -->
<div class="summary-cards">
    <div class="summary-card pending">
        <h3>Total Pendiente</h3>
        <p style="font-size: 24px; font-weight: bold; color: #e74c3c;">$<?= number_format($totals['pending'], 2) ?></p>
    </div>
    <div class="summary-card paid">
        <h3>Total Pagado</h3>
        <p style="font-size: 24px; font-weight: bold; color: #27ae60;">$<?= number_format($totals['paid'], 2) ?></p>
    </div>
</div>

<!-- Sección Pagos Pendientes -->
<div class="table-container">
    <h2><i class="fas fa-clock"></i> Pagos Pendientes</h2>
    <?php if (!empty($pendingPayments)): ?>
        <?php foreach ($pendingPayments as $payment): ?>
            <div class="pending-item">
                <div>
                    <strong>Monto: $<?= number_format($payment['amount'], 2) ?></strong><br>
                    Mes/Año: <?= htmlspecialchars($payment['monthYear'] ?? '') ?><br>
                    Fecha: <?= date('d/m/Y', strtotime($payment['contrib_date'])) ?><br>
                    Notas: <?= htmlspecialchars($payment['notes'] ?? '') ?>
                </div>
                <button class="pay-btn open-pay-modal" data-id="<?= (int)($payment['idPayment']) ?>" data-amount="<?= $payment['amount'] ?>">
                    <i class="fas fa-credit-card"></i> Pagar Ahora
                </button>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #fff; border-radius: 12px;">
            <i class="fas fa-check-circle" style="font-size: 48px; color: #27ae60; margin-bottom: 10px;"></i>
            <h3>No hay pagos pendientes</h3>
            <p>¡Estás al día con tus contribuciones!</p>
        </div>
    <?php endif; ?>
</div>

<!-- Sección Historial -->
<div class="table-container">
    <div class="filter-container">
        <label for="monthFilter">Filtrar por Mes/Año:</label>
        <select id="monthFilter" class="filter-select" onchange="applyFilter()">
            <option value="">Todos</option>
            <?php 
            // Obtener meses únicos (agregar lógica en modelo si es necesario)
            $uniqueMonths = array_unique(array_column($historyPayments, 'monthYear'));
            foreach ($uniqueMonths as $month): 
            ?>
                <option value="<?= htmlspecialchars($month) ?>" <?= ($monthYearFilter === $month) ? 'selected' : '' ?>><?= htmlspecialchars($month) ?></option>
            <?php endforeach; ?>
        </select>
        <button onclick="exportPDF()" style="background: #bbae97; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
            <i class="fas fa-download"></i> Exportar PDF
        </button>
    </div>

    <?php if (!empty($historyPayments)): ?>
    <table class="modern-table">
        <thead>
            <tr>
                <th><i class="fas fa-calendar"></i> Fecha</th>
                <th><i class="fas fa-dollar-sign"></i> Monto Pagado</th>
                <th><i class="fas fa-tag"></i> Tipo de Pago</th>
                <th><i class="fas fa-sticky-note"></i> Mes/Año</th>
                <th><i class="fas fa-info-circle"></i> Notas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($historyPayments as $payment): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($payment['payment_date'])) ?></td>
                    <td>$<?= number_format($payment['paidAmount'], 2) ?></td>
                    <td><?= htmlspecialchars($payment['payment_type'] ?? '') ?></td>
                    <td><?= htmlspecialchars($payment['monthYear'] ?? '') ?></td>
                    <td><?= htmlspecialchars($payment['notes'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div style="text-align: center; padding: 40px; background: #fff; border-radius: 12px;">
        <i class="fas fa-history" style="font-size: 48px; color: #bbae97; margin-bottom: 10px;"></i>
        <h3>No hay historial de pagos</h3>
        <p>Realiza tu primer pago para ver el historial aquí.</p>
    </div>
<?php endif; ?>
</div>

<!-- Modal para Realizar Pago -->
<div id="payModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 style="color: #000;"><i class="fas fa-credit-card"></i> Realizar Pago</h2>
        <form method="POST" action="<?= u('partner/payments') ?>" id="payForm">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="idPayment" id="payId">
            <input type="hidden" name="idContribution" id="payId">
            <label for="amount" style="color: #000;">Monto a Pagar:</label>
            <input type="number" name="amount" id="amount" step="0.01" required style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;" readonly>

            <label for="paymentType" style="color: #000;">Método de Pago:</label>
            <select name="paymentType" id="paymentType" required style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;">
                <!-- Opciones de paymenttype, cargar dinámicamente si es necesario -->
                
                <option value="2">Transferencia Bancaria</option>
                <option value="3">Pago Efectivo</option>
            </select>

            <!-- Aquí integrar pasarela real, e.g., formulario Stripe -->
            <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; color: #000;">
                <p style="color: #000;"><strong style="color: #000;">Nota:</strong> Este es un simulador. Integra tu pasarela de pagos aquí.</p>
            </div>

            <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 10px; font-weight: 500; transition: all 0.2s ease;">
                <i class="fas fa-lock"></i> Confirmar Pago
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Abrir modal de pago
        document.querySelectorAll('.open-pay-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const amount = btn.getAttribute('data-amount');
                document.getElementById('payId').value = id;
                document.getElementById('amount').value = amount;
                document.getElementById('payModal').style.display = 'block';
            });
        });

        // Cerrar modales
        document.querySelectorAll('.close').forEach(close => {
            close.addEventListener('click', () => {
                document.getElementById('payModal').style.display = 'none';
            });
        });

        window.addEventListener('click', (event) => {
            const modal = document.getElementById('payModal');
            if (event.target === modal) modal.style.display = 'none';
        });

        // Filtro por mes (recarga página)
        function applyFilter() {
            const filter = document.getElementById('monthFilter').value;
            window.location.href = `<?= u('partner/payments') ?>?filter=${encodeURIComponent(filter)}`;
        }

        // Export PDF (simular, llamar a ruta /partner/export-pdf-payments)
        function exportPDF() {
            window.location.href = `<?= u('partner/export-pdf-payments') ?>?idPartner=<?= $idPartner ?>`;
        }

        // Búsqueda en historial (opcional, agregar input si se necesita)
    });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';  // Asumir layout como en ejemplo
?>