<?php
$title = 'Editar Contribución';
$currentPath = 'contribution/edit/' . ($contribution['idContribution'] ?? 0);
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Contribuciones', 'url' => u('contribution/list')],
    ['label' => 'Editar', 'url' => null],
];

ob_start();
?>
<h1>Editar Contribución</h1>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST" action="<?= u('contribution/edit/' . ($contribution['idContribution'] ?? 0)) ?>">
    <div style="max-width:500px; margin:20px 0;">
        <label for="amount">Monto:</label>
        <input type="number" step="0.01" name="amount" id="amount" value="<?= htmlspecialchars($contribution['amount'] ?? '') ?>" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">
        
        <label for="notes">Notas:</label>
        <textarea name="notes" id="notes" style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px; height:100px;"><?= htmlspecialchars($contribution['notes'] ?? '') ?></textarea>
        
        <label for="monthYear">Mes/Año (YYYY-MM):</label>
        <input type="text" name="monthYear" id="monthYear" value="<?= htmlspecialchars($contribution['monthYear'] ?? '') ?>" pattern="[0-9]{4}-[0-9]{2}" placeholder="Ej: 2025-09" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">
        
        <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Guardar</button>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';