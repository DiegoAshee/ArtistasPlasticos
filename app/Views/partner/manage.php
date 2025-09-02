<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Registros Online</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form { display: inline; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Gestión de Registros Online</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if (!empty($registrations)): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>CI</th>
                <th>Celular</th>
                <th>Dirección</th>
                <th>Fecha Creación</th>
                <th>Fecha Nacimiento</th>
                <th>Fecha Registro</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($registrations as $reg): ?>
                <tr>
                    <td><?= htmlspecialchars($reg['idPartnerOnline']) ?></td>
                    <td><?= htmlspecialchars($reg['name']) ?></td>
                    <td><?= htmlspecialchars($reg['ci']) ?></td>
                    <td><?= htmlspecialchars($reg['cellPhoneNumber']) ?></td>
                    <td><?= htmlspecialchars($reg['address']) ?></td>
                    <td><?= htmlspecialchars($reg['dateCreation']) ?></td>
                    <td><?= htmlspecialchars($reg['birthday']) ?></td>
                    <td><?= htmlspecialchars($reg['dateRegistration']) ?></td>
                    <td class="actions">
                        <form method="POST" action="/partner/manage">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($reg['idPartnerOnline']) ?>">
                            <input type="hidden" name="action" value="accept">
                            <button type="submit">Aceptar</button>
                        </form>
                        <form method="POST" action="/partner/manage">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($reg['idPartnerOnline']) ?>">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit">Rechazar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No hay registros pendientes.</p>
    <?php endif; ?>
    <a href="/socios/list">Volver</a>
</body>
</html>