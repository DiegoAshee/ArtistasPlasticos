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
  .card-soft{border:1px solid rgba(0,0,0,.06);border-radius:14px;}
  .media-wrap{display:grid;grid-template-columns:200px 1fr;gap:20px;align-items:flex-start;}
  .thumb-box{background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:12px;padding:10px;width:180px;min-width:180px;}
  .thumb-box .label{display:inline-block;font-size:.75rem;font-weight:700;letter-spacing:.02em;color:#6b5b3f;background:#f3eee5;border:1px solid #e6dccb;padding:2px 8px;border-radius:999px;margin-bottom:8px;}
  .thumb{width:100%;height:160px;object-fit:contain;border-radius:8px;background:#fafafa;border:1px dashed rgba(0,0,0,.12);}
  .thumb-qr{height:120px;}
  .tbl-thumb{width:56px;height:56px;object-fit:contain;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:8px;padding:4px;}
  .badge-state{font-size:.8rem;}
  .btn-primary{background:#c9b48a;border-color:#c9b48a;}
  .btn-primary:hover{background:#b9a476;border-color:#b9a476;}
 
  /* ===== SCROLL INTERNO ===== */
  .table-scroll{max-height:70vh;overflow:auto;border:1px solid #e9ecef;border-radius:10px;background:#fff;
    scrollbar-width:thin;scrollbar-color:#bfc3c9 #2b2b2b;}
  .table-scroll::-webkit-scrollbar{width:8px;height:8px}
  .table-scroll::-webkit-scrollbar-track{background:#2b2b2b;border-radius:8px}
  .table-scroll::-webkit-scrollbar-thumb{background:#bfc3c9;border-radius:8px}
  .table-scroll::-webkit-scrollbar-thumb:hover{background:#aeb3b9}
 
  /* ===== TABLA ===== */
  #dataTable{min-width:1100px;border-collapse:separate;border-spacing:0;table-layout:auto;}
  #dataTable thead th{position:sticky;top:0;z-index:2;background:#f8f9fc;border-bottom:2px solid #e5e7eb!important;text-align:center;}
  #dataTable th + th,#dataTable td + td{border-left:2px solid #f0f2f5!important;}
  #dataTable td,#dataTable th{border-top:1px solid #edf0f3!important;vertical-align:middle!important;text-align:center;}
  #dataTable tbody tr:nth-child(odd){background:#fff;}
  #dataTable tbody tr:nth-child(even){background:#fbfbfd;}
  #dataTable tbody tr:hover{background:#fff9e9;transition:background-color .15s ease;}
 
  .col-id{white-space:nowrap;width:70px;}
  .col-logo,.col-qr{width:90px;}
  .col-tel{width:140px;white-space:nowrap;}
  .col-estado{width:110px;white-space:nowrap;}
  .col-user{width:160px;}
  .col-acc{width:120px;text-align:center;}
  .title-trunc{max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;}
 
  .btn-sm-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:30px;padding:0;}
  #dataTable button:focus{outline:2px solid #b9a476;outline-offset:1px;}
 
  /* ===== MODAL PROPIO (sin Bootstrap) ===== */
  .ovl{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:saturate(120%) blur(2px);display:none;align-items:center;justify-content:center;z-index:1050;}
  .ovl.show{display:flex;}
  .ovl-card{width:min(520px,92vw);background:#fff;border:1px solid #eee;border-radius:16px;box-shadow:0 10px 25px rgba(0,0,0,.18);overflow:hidden;transform:translateY(-8px);opacity:0;transition:all .15s ease;}
  .ovl.show .ovl-card{transform:translateY(0);opacity:1;}
  .ovl-hd{display:flex;align-items:center;gap:10px;background:#fff8eb;padding:14px 18px;border-bottom:1px solid #f2e7d3;}
  .ovl-ico{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:#fff1d6;color:#b7791f;font-size:22px;}
  .ovl-tt{margin:0;font-weight:800;color:#8a6d3b;letter-spacing:.02em;}
  .ovl-bd{padding:18px;color:#4b5563;}
  .ovl-ft{display:flex;gap:10px;justify-content:flex-end;padding:14px 18px;background:#fafafa;border-top:1px solid #eee;}
  .btn-ghost{border:1px solid #d1d5db;background:#fff;color:#374151;border-radius:10px;padding:8px 14px;}
  .btn-ghost:hover{background:#f3f4f6}
  .btn-danger2{border:1px solid #dc3545;background:#dc3545;color:#fff;border-radius:10px;padding:8px 14px;}
</style>
 
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Configuración del Sitio</h1>
    <a href="<?= u('options/create') ?>" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
      <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Configuración
    </a>
  </div>
 
  <?php if (!empty($success)): ?>
    <div class="alert alert-success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>
 
  <?php if (!empty($activeOption)): ?>
    <div class="card card-soft shadow-sm mb-4">
      <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Configuración Activa</h6></div>
      <div class="card-body">
        <div class="media-wrap">
          <div>
            <div class="thumb-box mb-3">
              <span class="label">LOGO</span>
              <img src="<?= u($activeOption['imageURL'] ?? 'assets/images/logo.png') ?>" alt="Logo activo" class="thumb" loading="lazy">
            </div>
            <div class="thumb-box">
              <span class="label">QR</span>
              <img src="<?= u($activeOption['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>" alt="QR activo" class="thumb thumb-qr" loading="lazy">
            </div>
          </div>
          <div style="text-align:center;">
            <h4 class="mb-2"><?= htmlspecialchars($activeOption['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted mb-2">
              ID: <?= (int)($activeOption['idOption'] ?? 0) ?> |
              Creado por: <?= htmlspecialchars($activeOption['createdByName'] ?? (string)($activeOption['idUser'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </p>
            <p class="mb-3"><strong>Teléfono:</strong> <?= htmlspecialchars($activeOption['telephoneContact'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
 
  <div class="card card-soft shadow-sm mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Todas las Configuraciones</h6>
    </div>
    <div class="card-body">
      <div class="table-scroll">
        <table class="table table-bordered align-middle" id="dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th class="col-id">ID</th>
              <th>Título</th>
              <th class="col-logo">Logo</th>
              <th class="col-qr">QR</th>
              <th class="col-tel">Teléfono</th>
              <th class="col-estado">Estado</th>
              <th class="col-user">Creado por</th>
              <th class="col-acc">Activar</th>
              <th class="col-acc">Editar</th>
              <th class="col-acc">Borrar</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!empty($options)): ?>
            <?php foreach ($options as $option): ?>
              <tr>
                <td class="col-id"><?= (int)$option['idOption'] ?></td>
                <td><span class="title-trunc" title="<?= htmlspecialchars($option['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars($option['title'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td class="col-logo">
                  <img src="<?= u($option['imageURL'] ?? 'assets/images/logo.png') ?>" alt="Logo" class="tbl-thumb" loading="lazy">
                </td>
                <td class="col-qr">
                  <img src="<?= u($option['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>" alt="QR" class="tbl-thumb" loading="lazy">
                </td>
                <td class="col-tel"><?= htmlspecialchars($option['telephoneContact'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="col-estado">
                  <?php if ((int)($option['status'] ?? 0) === 1): ?>
                    <span class="badge badge-success badge-state">Activa</span>
                  <?php else: ?>
                    <span class="badge badge-secondary badge-state">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td class="col-user"><?= htmlspecialchars($option['createdByName'] ?? (string)($option['idUser'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
 
                <!-- Activar -->
                <td class="col-acc">
                  <?php if ((int)($option['status'] ?? 0) !== 1): ?>
                    <form action="<?= u('options/activate') ?>" method="POST" class="d-inline">
                      <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                      <button type="submit" class="btn btn-success btn-sm btn-sm-icon" title="Activar">
                        <i class="fas fa-check"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted" title="Ya activa">—</span>
                  <?php endif; ?>
                </td>
 
                <!-- Editar -->
                <td class="col-acc">
                  <form action="<?= u('options/edit') ?>" method="GET" class="d-inline">
                    <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                    <button type="submit" class="btn btn-primary btn-sm btn-sm-icon" title="Editar">
                      <i class="fas fa-edit"></i>
                    </button>
                  </form>
                </td>
 
                <!-- Borrar -> abre modal propio -->
                <td class="col-acc">
                  <form action="<?= u('options/delete') ?>" method="POST" class="d-inline delete-form" data-item="<?= htmlspecialchars($option['title'] ?? ('#'.(int)$option['idOption']), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
                    <button type="button" class="btn btn-danger btn-sm btn-sm-icon btn-delete" title="Borrar">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="10" class="text-center">No hay configuraciones registradas.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
 
<!-- ===== MODAL PROPIO ===== -->
<div id="ovl-del" class="ovl" aria-hidden="true">
  <div class="ovl-card" role="dialog" aria-modal="true" aria-labelledby="ovl-tt">
    <div class="ovl-hd">
      <div class="ovl-ico"><i class="fas fa-exclamation-triangle"></i></div>
      <h5 id="ovl-tt" class="ovl-tt">¿Eliminar configuración?</h5>
    </div>
    <div class="ovl-bd">
      <p>Esta acción es permanente. ¿Seguro que deseas eliminar <strong id="ovl-item">este registro</strong>?</p>
    </div>
    <div class="ovl-ft">
      <button type="button" class="btn-ghost" id="ovl-cancel">Cancelar</button>
      <button type="button" class="btn-danger2" id="ovl-ok">Sí, eliminar</button>
    </div>
  </div>
</div>
 
<script>
(function(){
  // Referencias del modal propio
  const ovl = document.getElementById('ovl-del');
  const ovlItem = document.getElementById('ovl-item');
  const btnOk = document.getElementById('ovl-ok');
  const btnCancel = document.getElementById('ovl-cancel');
 
  let formToSubmit = null;
 
  // Quitar cualquier confirm nativo previo (por si quedó en otra versión)
  document.querySelectorAll('form.delete-form').forEach(f => {
    f.addEventListener('submit', function(ev){
      // No dejamos que otro listener dispare confirm nativo
      // (si existiera código legado), pero aquí no cancelamos:
      // este handler solo existe para capturar si otro script intenta interceptar.
    }, true);
  });
 
  // Abrir modal al pulsar borrar
  document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', e => {
      const form = e.currentTarget.closest('form.delete-form');
      formToSubmit = form;
      const name = form.getAttribute('data-item') || 'este registro';
      ovlItem.textContent = name;
      ovl.classList.add('show');
      ovl.setAttribute('aria-hidden','false');
    });
  });
 
  // Cerrar modal (cancelar)
  btnCancel.addEventListener('click', () => {
    ovl.classList.remove('show');
    ovl.setAttribute('aria-hidden','true');
    formToSubmit = null;
  });
 
  // Confirmar borrado
  btnOk.addEventListener('click', () => {
    if (formToSubmit) {
      // Enviamos el form sin diálogos nativos
      formToSubmit.submit();
      formToSubmit = null;
    }
    ovl.classList.remove('show');
    ovl.setAttribute('aria-hidden','true');
  });
 
  // Cerrar al hacer clic fuera de la tarjeta
  ovl.addEventListener('click', (e) => {
    if (e.target === ovl) {
      ovl.classList.remove('show');
      ovl.setAttribute('aria-hidden','true');
      formToSubmit = null;
    }
  });
 
  // Esc para cerrar
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && ovl.classList.contains('show')) {
      ovl.classList.remove('show');
      ovl.setAttribute('aria-hidden','true');
      formToSubmit = null;
    }
  });
})();
</script>
 
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';