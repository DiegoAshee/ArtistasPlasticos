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
    /* Estilo similar a role/list.php: Colores crema/beige, tablas modernas */
    .modern-table th, .modern-table td { padding: 10px 14px; line-height: 1.35; vertical-align: middle; }
    .modern-table { border-collapse: separate; border-spacing: 0 6px; }
    .modern-table thead th { position: sticky; top: 0; background: #bbae97; color: #2a2a2a; z-index: 2; }
    .modern-table tbody tr { background: #d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background: #dccaaf; }
    .modern-table tbody tr td:first-child { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .modern-table tbody tr td:last-child { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .table-container, .cards-container { background: #cfc4b0; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.06); overflow: auto; margin-bottom: 20px; padding: 20px; }

    .summary-cards { display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap; }
    .summary-card { flex: 1; min-width: 200px; background: #fff; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,.05); }
    .summary-card.pending { border-left: 5px solid #e74c3c; }
    .summary-card.paid { border-left: 5px solid #27ae60; }

    .filter-container { display: flex; gap: 10px; align-items: center; margin-bottom: 15px; }
    .filter-select { padding: 8px 12px; border: 2px solid #e1e5e9; border-radius: 8px; background: #fff; }

    .pay-btn { background: #27ae60; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
    .pay-btn:hover { background: #219a52; }
    .pending-item { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }

    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; }
    .modal-content { background: #fff; margin: 10% auto; padding: 20px; width: 400px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.3); }
    .close { float: right; font-size: 24px; cursor: pointer; color: #999; }
    .error-message { color: #e74c3c; margin: 10px 0; background: #f8d7da; padding: 10px; border-radius: 4px; }
    .success-message { color: #27ae60; margin: 10px 0; background: #d4edda; padding: 10px; border-radius: 4px; }

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
        <h2><i class="fas fa-credit-card"></i> Realizar Pago</h2>
        <form method="POST" action="<?= u('partner/payments') ?>" id="payForm">
            <input type="hidden" name="action" value="pay">
            <input type="hidden" name="idPayment" id="payId">
            <input type="hidden" name="idContribution" id="payId">
            <label for="amount">Monto a Pagar:</label>
            <input type="number" name="amount" id="amount" step="0.01" required style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;" readonly>

            <label for="paymentType">Método de Pago:</label>
            <select name="paymentType" id="paymentType" required style="width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px;">
                <!-- Opciones de paymenttype, cargar dinámicamente si es necesario -->
                <option value="1">Tarjeta de Crédito</option>
                <option value="2">Transferencia Bancaria</option>
                <option value="3">Efectivo</option>
            </select>

            <!-- Aquí integrar pasarela real, e.g., formulario Stripe -->
            <div style="margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                <p><strong>Nota:</strong> Este es un simulador. Integra tu pasarela de pagos aquí.</p>
            </div>

            <button type="submit" style="background: #27ae60; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; width: 100%; margin-top: 10px;">
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