<?php
$title = 'Nueva Contribución';
$currentPath = 'contribution/create';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Contribuciones', 'url' => u('contribution/list')],
    ['label' => 'Nueva', 'url' => null],
];

ob_start();
?>
<h1>Nueva Contribución</h1>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST" action="<?= u('contribution/create') ?>">
    <div style="max-width:500px; margin:20px 0;">
        <label>Mes/Año: <span style="color:#666; font-weight:bold;"><?= htmlspecialchars($defaultMonthYear) ?></span> (Automático)</label>
        <input type="hidden" name="monthYear" value="<?= htmlspecialchars($defaultMonthYear) ?>">

        <label>Notas: <span style="color:#666; font-weight:bold;"><?= htmlspecialchars($defaultNotes) ?></span> (Automático)</label>
        <input type="hidden" name="notes" value="<?= htmlspecialchars($defaultNotes) ?>">

        <label for="amount">Monto (predeterminado: <?= htmlspecialchars(number_format($defaultAmount, 2)) ?>):</label>
        <input type="number" step="0.01" name="amount" id="amount" value="<?= htmlspecialchars(number_format($defaultAmount, 2)) ?>" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">

        <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Crear</button>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';