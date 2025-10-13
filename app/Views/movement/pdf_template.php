<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Libro Diario - Reporte</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18pt; }
        .header .subtitle { font-size: 12pt; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; border: 1px solid #ddd; }
        td { padding: 6px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; font-size: 9pt; color: #666; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .saldo-row { font-weight: bold; }
        .ingreso { color: green; }
        .egreso { color: red; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Libro Diario</h1>
        <div class="subtitle">
            <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Descripción</th>
                <th>Destinatario</th>
                <th>Usuario</th>
                <th class="text-right">Ingreso</th>
                <th class="text-right">Egreso</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movements as $movement): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($movement['dateCreation'])) ?></td>
                    <td><?= htmlspecialchars($movement['concept_description'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($movement['description'] ?? '') ?></td>
                    <td><?= htmlspecialchars($movement['nameDestination'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($movement['user_login'] ?? 'N/A') ?></td>
                    <?php if (strtolower($movement['concept_type'] ?? '') === 'ingreso'): ?>
                        <td class="text-right ingreso"><?= number_format((float)($movement['amount'] ?? 0), 2) ?></td>
                        <td class="text-right">-</td>
                    <?php else: ?>
                        <td class="text-right">-</td>
                        <td class="text-right egreso"><?= number_format((float)($movement['amount'] ?? 0), 2) ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
            
            <!-- Total de ingresos y egresos -->
            <tr class="total-row">
                <td colspan="5" class="text-right"><strong>Total:</strong></td>
                <td class="text-right ingreso"><strong><?= number_format($totalIngresos, 2) ?></strong></td>
                <td class="text-right egreso"><strong><?= number_format($totalEgresos, 2) ?></strong></td>
            </tr>
            
            <!-- Saldo -->
            <tr class="saldo-row">
                <td colspan="5" class="text-right">
                    <strong>Saldo <?= $saldo >= 0 ? 'a favor' : 'en contra' ?>:</strong>
                </td>
                <td colspan="2" class="text-right <?= $saldo >= 0 ? 'ingreso' : 'egreso' ?>">
                    <strong><?= number_format(abs($saldo), 2) ?></strong>
                </td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Generado el <?= date('d/m/Y H:i:s') ?> - <?= $_SESSION['user_login'] ?? 'Usuario' ?></p>
    </div>
</body>
</html>
