<?php
// app/Views/partner/list.php

$title       = 'Socios';
$currentPath = 'partner/list'; // para marcar activo en el menú
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Socios', 'url' => null],
];

// Métricas simples
$totalSocios    = is_array($socios ?? null) ? count($socios) : 0;
$nuevosEsteAnio = 0;
if (!empty($socios) && is_array($socios)) {
    $anio = date('Y');
    foreach ($socios as $s) {
        $dr = $s['dateRegistration'] ?? null;
        if ($dr && date('Y', strtotime($dr)) === $anio) { $nuevosEsteAnio++; }
    }
}

// ---- Contenido específico de la página ----
ob_start();
?>
  <!-- Estilos para dar aire y encabezado pegajoso -->
  <style>
    .modern-table th, .modern-table td {
      padding: 10px 14px;
      line-height: 1.35;
      vertical-align: middle;
    }
    .modern-table { border-collapse: separate; border-spacing: 0 6px; }
    .modern-table thead th {
      position: sticky; top: 0;
      background: #bbae97; color: #2a2a2a;
      z-index: 2;
    }
    .modern-table tbody tr { background:#d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background: #dccaaf; }
    .modern-table tbody tr td:first-child  { border-top-left-radius:10px; border-bottom-left-radius:10px; }
    .modern-table tbody tr td:last-child   { border-top-right-radius:10px; border-bottom-right-radius:10px; }

    /* contenedor de tabla */
    .table-container { background:#cfc4b0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);overflow:auto; }
  </style>

  <!-- Barra de acciones -->
  <div class="toolbar" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;">
    <div class="search-container" style="position:relative;flex:1 1 320px;">
      <i class="fas fa-search search-icon" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.6;"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Buscar por nombre, CI, login, email, celular..."
        style="width:100%;border:2px solid #e1e5e9;border-radius:12px;padding:10px 40px 10px 38px;outline:none;background:#fff;transition:border-color .2s;"
        onfocus="this.style.borderColor='var(--cream-400)';"
        onblur="this.style.borderColor='#e1e5e9';"
      />
    </div>

    <div style="display:flex;gap:12px;">
      <button id="exportPdfBtn" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:#6c757d;color:#fff;border:none;border-radius:12px;padding:10px 14px;font-weight:600;cursor:pointer;">
        <i class="fas fa-file-pdf"></i> Exportar PDF
      </button>
      
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Nuevo Socio
      </a>
    </div>
  </div>

  <!-- Tabla de socios -->
  <?php if (!empty($socios) && is_array($socios)): ?>
    <div class="table-container">
      <table id="tablaSocios" class="modern-table" style="width:100%;border-collapse:separate;border-spacing:0;">
        <thead>
          <tr>
            <th><i class="fas fa-user"></i> Nombre</th>
            <th><i class="fas fa-id-card"></i> CI</th>
            <th><i class="fas fa-user-tag"></i> Login</th>
            <th><i class="fas fa-envelope"></i> Email</th>
            <th><i class="fas fa-phone"></i> Celular</th>
            <th><i class="fas fa-map-marker-alt"></i> Dirección</th>
            <th><i class="fas fa-calendar-plus"></i> F. Creación</th>
            <th><i class="fas fa-birthday-cake"></i> F. Nacimiento</th>
            <th><i class="fas fa-calendar-check"></i> F. Registro</th>
            <th><i class="fas fa-cogs"></i> Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($socios as $socio): ?>
            <tr>
              <td>
                <div class="user-cell" style="display:flex;align-items:center;gap:10px;">
                  <div class="user-avatar-small" style="width:28px;height:28px;border-radius:50%;background:var(--cream-200,#eee);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-user"></i>
                  </div>
                  <span><?= htmlspecialchars($socio['name'] ?? '') ?></span>
                </div>
              </td>
              <td><?= htmlspecialchars($socio['ci'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['login'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['email'] ?? '') ?></td>
              <td><?= htmlspecialchars($socio['cellPhoneNumber'] ?? '') ?></td>
              <td class="address-cell" title="<?= htmlspecialchars($socio['address'] ?? '') ?>">
                <?php
                  $addr = (string)($socio['address'] ?? '');
                  $addr = htmlspecialchars($addr, ENT_QUOTES, 'UTF-8');
                  echo (mb_strlen($addr,'UTF-8') > 30) ? mb_substr($addr,0,30,'UTF-8').'…' : $addr;
                ?>
              </td>
              <td><span class="date-badge"><?= !empty($socio['dateCreation'])     ? date('d/m/Y', strtotime($socio['dateCreation']))     : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['birthday'])         ? date('d/m/Y', strtotime($socio['birthday']))         : '-' ?></span></td>
              <td><span class="date-badge"><?= !empty($socio['dateRegistration']) ? date('d/m/Y', strtotime($socio['dateRegistration'])) : '-' ?></span></td>
              <td class="actions">
                <div class="action-buttons">
                  <a href="<?= u('partner/edit/' . (int)($socio['idPartner'] ?? 0)) ?>" class="btn btn-sm btn-outline" title="Editar" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid #e1e5e9;color:#333;text-decoration:none;">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="<?= u('partner/delete/' . (int)($socio['idPartner'] ?? 0)) ?>"
                     class="btn btn-sm btn-danger"
                     title="Eliminar"
                     onclick="return confirm('¿Seguro que desea eliminar este socio?');"
                     style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#e74c3c;color:#fff;text-decoration:none;margin-left:6px;">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Controles de paginación -->
      <div id="pager" style="display:flex;align-items:center;gap:8px;justify-content:flex-end;padding:12px;">
        <label for="pageSize">Por página:</label>
        <select id="pageSize" style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 8px;">
          <option value="10">10</option>
          <option value="20" selected>20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>

        <button id="firstPage" style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">«</button>
        <button id="prevPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">‹</button>
        <span id="pageInfo" style="min-width:180px;text-align:center;font-weight:600;"></span>
        <button id="nextPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">›</button>
        <button id="lastPage"  style="border:1px solid #cfcfcf;border-radius:8px;padding:6px 10px;background:#fff;">»</button>
      </div>
    </div>
  <?php else: ?>
    <div class="empty-state" style="text-align:center;padding:40px 20px;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);">
      <div class="empty-state-icon" style="font-size:42px;margin-bottom:10px;color:var(--cream-600);"><i class="fas fa-users"></i></div>
      <h3>No hay socios registrados</h3>
      <p>Comienza agregando tu primer socio al sistema</p>
      <a href="<?= u('partner/create') ?>" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;background:var(--cream-600);color:#fff;border:none;border-radius:12px;padding:10px 14px;text-decoration:none;font-weight:600;">
        <i class="fas fa-plus"></i> Crear primer socio
      </a>
    </div>
  <?php endif; ?>

  <!-- Buscador en vivo + paginación -->
  <script>
    // (tu script de búsqueda y paginación va aquí, sin cambios)
  </script>

  <!-- Exportación PDF -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script>
    // (tu script de exportación PDF va aquí, sin cambios)
  </script>
<?php
$content = ob_get_clean();

// ---- Incluir layout principal (misma forma que dashboard) ----
include __DIR__ . '/../layouts/app.php';
