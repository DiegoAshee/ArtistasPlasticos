<?php
// app/Views/options/index.php
 
$title = $title ?? 'Configuración del Sitio';
$currentPath = 'options';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Opciones', 'url' => null],
];
 
$success = $success ?? null;
$error   = $error   ?? null;
 
ob_start();
?>
<style>
  .card-soft { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; }
  .section-title { font-weight: 700; font-size: .95rem; margin-bottom: 8px; }
  .media-wrap { display: grid; grid-template-columns: 200px 1fr; gap: 20px; align-items: flex-start; }
  .thumb-box {
    background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 12px;
    padding: 10px; width: 180px; min-width: 180px;
  }
  .thumb-box .label {
    display: inline-block; font-size: .75rem; font-weight: 700; letter-spacing: .02em;
    color: #6b5b3f; background: #f3eee5; border: 1px solid #e6dccb;
    padding: 2px 8px; border-radius: 999px; margin-bottom: 8px;
  }
  .thumb {
    width: 100%; height: 160px; object-fit: contain; border-radius: 8px;
    background: #fafafa; border: 1px dashed rgba(0,0,0,.12);
  }
  .thumb-qr { height: 120px; }
 
  /* table thumbs */
  .tbl-thumb { width: 56px; height: 56px; object-fit: contain; background:#fff;
    border:1px solid rgba(0,0,0,.08); border-radius:8px; padding:4px; }
 
  .badge-state { font-size: .8rem; }
  .btn-primary { background:#c9b48a; border-color:#c9b48a; }
  .btn-primary:hover { background:#b9a476; border-color:#b9a476; }
</style>
 
<div class="container-fluid">
  <!-- Encabezado -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Configuración del Sitio</h1>
    <a href="<?= u('options/create') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
      <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Configuración
    </a>
  </div>
 
  <!-- Mensajes (sin botón de cerrar para evitar la “x”) -->
  <?php if (!empty($success)): ?>
    <div class="alert alert-success" role="alert">
      <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>
 
  <!-- Configuración activa -->
  <?php if (!empty($activeOption)): ?>
    <div class="card card-soft shadow-sm mb-4">
      <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Configuración Activa</h6>
      </div>
      <div class="card-body">
        <div class="media-wrap">
          <!-- Columna izquierda: Logo y QR claramente diferenciados -->
          <div>
            <div class="thumb-box mb-3">
              <span class="label">LOGO</span>
              <img
                src="<?= u($activeOption['imageURL'] ?? 'assets/images/logo.png') ?>"
                alt="Logo activo" class="thumb">
            </div>
            <div class="thumb-box">
              <span class="label">QR</span>
              <img
                src="<?= u($activeOption['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>"
                alt="QR activo" class="thumb thumb-qr">
            </div>
          </div>
 
          <!-- Columna derecha: datos -->
          <div>
            <h4 class="mb-2"><?= htmlspecialchars($activeOption['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted mb-2">
              ID: <?= (int)($activeOption['idOption'] ?? 0) ?> |
              Creado por:
              <?= htmlspecialchars($activeOption['createdByName'] ?? (string)($activeOption['idUser'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="mb-3"><strong>Teléfono:</strong>
              <?= htmlspecialchars($activeOption['telephoneContact'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>
 
            <!-- (Opcional) puedes añadir más metadata aquí -->
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
 
  <!-- Tabla -->
  <div class="card card-soft shadow-sm mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Todas las Configuraciones</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th style="width:70px">ID</th>
              <th>Título</th>
              <th style="width:90px">Logo</th>
              <th style="width:90px">QR</th>
              <th style="width:140px">Teléfono</th>
              <th style="width:110px">Estado</th>
              <th style="width:160px">Creado por</th>
              <th style="width:160px">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($options)): ?>
            <?php foreach ($options as $option): ?>
              <tr>
                <td><?= (int)$option['idOption'] ?></td>
                <td><?= htmlspecialchars($option['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <img src="<?= u($option['imageURL'] ?? 'assets/images/logo.png') ?>"
                       alt="Logo" class="tbl-thumb">
                </td>
                <td>
                  <img src="<?= u($option['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>"
                       alt="QR" class="tbl-thumb">
                </td>
                <td><?= htmlspecialchars($option['telephoneContact'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if ((int)($option['status'] ?? 0) === 1): ?>
                    <span class="badge badge-success badge-state">Activa</span>
                  <?php else: ?>
                    <span class="badge badge-secondary badge-state">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($option['createdByName'] ?? (string)($option['idUser'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <div class="btn-group" role="group" aria-label="Acciones">
                    <?php if ((int)($option['status'] ?? 0) !== 1): ?>
                      <form action="<?= u('options/activate') ?>" method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                        <button type="submit" class="btn btn-success btn-sm" title="Activar">
                          <i class="fas fa-check"></i>
                        </button>
                      </form>
                    <?php endif; ?>
 
                    <form action="<?= u('options/edit') ?>" method="GET" class="d-inline">
                      <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                      <button type="submit" class="btn btn-primary btn-sm" title="Editar">
                        <i class="fas fa-edit"></i>
                      </button>
                    </form>
 
                    <form action="<?= u('options/delete') ?>" method="POST" class="d-inline"
                          onsubmit="return confirm('¿Seguro de eliminar esta configuración?');">
                      <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center">No hay configuraciones registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
 