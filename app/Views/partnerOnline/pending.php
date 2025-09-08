<?php
// app/Views/partnerOnline/pending.php

$title       = 'Pendientes';
$currentPath = 'partnerOnline/pending';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Pendientes', 'url' => null],
];

$registrations = $registrations ?? [];
$changes       = $changes ?? [];

// ---- Contenido ----
ob_start();
?>
  <style>
    .modern-table th, .modern-table td { padding:10px 14px; line-height:1.35; vertical-align:middle; }
    .modern-table { border-collapse:separate; border-spacing:0 6px; }
    .modern-table thead th { position:sticky; top:0; background:#bbae97; color:#2a2a2a; z-index:2; }
    .modern-table tbody tr { background:#d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background:#dccaaf; }
    .modern-table tbody tr td:first-child{ border-top-left-radius:10px;border-bottom-left-radius:10px; }
    .modern-table tbody tr td:last-child { border-top-right-radius:10px;border-bottom-right-radius:10px; }
    .table-container { background:#cfc4b0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:auto; }
    .section-title { display:flex; align-items:center; gap:8px; margin: 8px 0 12px; }
    .badge { display:inline-block; padding:4px 8px; border-radius:999px; font-size:.85rem; background:#fff; }
  </style>

  <h1 style="margin:0 0 10px;">Bandeja de pendientes</h1>

  <!-- REGISTROS NUEVOS -->
  <div class="section-title">
    <i class="fas fa-user-plus"></i><h2 style="margin:0;">Registros nuevos</h2>
    <span class="badge"><?= count($registrations) ?> pendiente(s)</span>
  </div>

  <div class="table-container" style="margin-bottom:24px;">
    <table class="modern-table" style="width:100%;">
      <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>CI</th>
            <th>Celular</th>
            <th>Dirección</th>
            <th>Nacimiento</th>
            <th>Email</th>
            <th>Frente</th>
            <th>Dorso</th>
            <th>Creado</th>
            <th>Tipo</th>
            <th>Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($registrations)): ?>
        <tr><td colspan="12" align="center">Sin registros nuevos pendientes.</td></tr>
        <?php else: foreach ($registrations as $r): ?>
        <tr>
            <td><?= (int)($r['idPartnerOnline'] ?? 0) ?></td>
            <td><?= htmlspecialchars($r['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?php if (!empty($r['frontImageURL'])): ?><a href="<?= u($r['frontImageURL']) ?>" target="_blank">Ver</a><?php endif; ?></td>
            <td><?php if (!empty($r['backImageURL'])): ?><a href="<?= u($r['backImageURL']) ?>" target="_blank">Ver</a><?php endif; ?></td>
            <td><?= htmlspecialchars($r['dateCreation'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td>Registro nuevo</td>
            <td>
            <form action="<?= u('partnerOnline/approve') ?>" method="post" style="display:inline;">
                <input type="hidden" name="id" value="<?= (int)($r['idPartnerOnline'] ?? 0) ?>">
                <button type="submit" style="background:#28a745;border:none;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;">
                <i class="fas fa-check"></i> Aceptar
                </button>
            </form>
            <form action="<?= u('partnerOnline/reject') ?>" method="post" style="display:inline;margin-left:6px;" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                <input type="hidden" name="id" value="<?= (int)($r['idPartnerOnline'] ?? 0) ?>">
                <button type="submit" style="background:#dc3545;border:none;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;">
                <i class="fas fa-times"></i> Rechazar
                </button>
            </form>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>

    </table>
  </div>

  <!-- MODIFICACIONES -->
  <div class="section-title">
    <i class="fas fa-user-edit"></i><h2 style="margin:0;">Solicitudes de modificación</h2>
    <span class="badge"><?= count($changes) ?> pendiente(s)</span>
  </div>

  <div class="table-container">
    <table class="modern-table" style="width:100%;">
      
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>CI</th>
                <th>Celular</th>
                <th>Dirección</th>
                <th>Nacimiento</th>
                <th>Email</th>
                <th>Creado</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($changes)): ?>
            <tr><td colspan="10" align="center">Sin modificaciones pendientes.</td></tr>
            <?php else: foreach ($changes as $c): ?>
            <tr>
                <td><?= (int)($c['idPartnerOnline'] ?? 0) ?></td>
                <td><?= htmlspecialchars($c['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['ci'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['cellPhoneNumber'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['birthday'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($c['dateCreation'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>Modificación</td>
                <td>
                <form action="<?= u('partnerOnline/approve') ?>" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="<?= (int)($c['idPartnerOnline'] ?? 0) ?>">
                    <button type="submit" style="background:#28a745;border:none;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;">
                    <i class="fas fa-check"></i> Aceptar
                    </button>
                </form>
                <form action="<?= u('partnerOnline/reject') ?>" method="post" style="display:inline;margin-left:6px;" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                    <input type="hidden" name="id" value="<?= (int)($c['idPartnerOnline'] ?? 0) ?>">
                    <button type="submit" style="background:#dc3545;border:none;color:#fff;padding:6px 10px;border-radius:8px;cursor:pointer;">
                    <i class="fas fa-times"></i> Rechazar
                    </button>
                </form>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>



    </table>
  </div>
<?php
$content = ob_get_clean();

// Layout principal (incluye tu sidebar dinámico desde BD)
include __DIR__ . '/../layouts/app.php';
