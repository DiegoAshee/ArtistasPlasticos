<?php
// app/Views/options/create.php
 
$title = 'Nueva Configuración';
$currentPath = 'options';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Opciones', 'url' => u('options')],
  ['label' => 'Crear', 'url' => null],
];
 
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
 
ob_start();
?>
<style>
  /* —— Estética general —— */
  .card-soft { border: 1px solid rgba(0,0,0,.06); border-radius: 14px; }
  .section-title { font-weight: 700; font-size: .95rem; margin-bottom: 8px; }
  .help { font-size: .85rem; color: #6c757d; }
 
  /* —— Inputs con icono —— */
  .inp-wrap { position: relative; }
  .inp-wrap .inp-icon {
    position: absolute; left: 12px; top: 50%;
    transform: translateY(-50%); opacity: .6; font-size: .95rem;
  }
  .inp-wrap .form-control { padding-left: 40px; border-radius: 10px; }
  .inp-wrap .form-control:focus {
    border-color: #c9b48a; box-shadow: 0 0 0 .2rem rgba(201,180,138,.15);
  }
 
  /* —— Uploader: layout estable —— */
  .uploader {
    border: 2px dashed rgba(0,0,0,.15);
    border-radius: 14px; padding: 16px; background: #faf9f7;
    display: grid; grid-template-columns: 148px 1fr; gap: 16px;
    align-items: center;
  }
  .uploader:hover { background: #f5f2ed; }
 
  /* Columna de preview fija */
  .thumb-box {
    position: relative;
    width: 140px; min-width: 140px; height: 140px;
    border-radius: 10px; overflow: hidden;
  }
  .thumb-placeholder, .thumb {
    position: absolute; inset: 0;
    border-radius: 10px;
  }
  .thumb-placeholder {
    display: grid; place-items: center; text-align: center;
    color: #aaa; background: #fff; border: 1px dashed rgba(0,0,0,.2);
    font-size: .85rem; padding: 8px;
  }
  .thumb {
    object-fit: cover; width: 100%; height: 100%;
    background: #fff; border: 1px solid rgba(0,0,0,.08);
  }
 
  /* Columna de controles */
  .uploader-controls .custom-file-input { cursor: pointer; }
  .file-hint { margin-top: 6px; }
  .file-name { font-size: .85rem; color: #495057; margin-top: 6px; word-break: break-all; }
 
  .btn-primary { background: #c9b48a; border-color: #c9b48a; }
  .btn-primary:hover { background: #b9a476; border-color: #b9a476; }
</style>
 
<div class="container-fluid">
  <!-- Header -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Nueva Configuración</h1>
    <a href="<?= u('options') ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
      <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
    </a>
  </div>
 
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
    </div>
  <?php endif; ?>
 
  <div class="card card-soft shadow-sm mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Información de la Configuración</h6>
    </div>
 
    <div class="card-body">
      <form action="<?= u('options/store') ?>" method="POST" enctype="multipart/form-data" id="optionCreateForm">
        <div class="row">
          <div class="col-lg-6">
            <!-- Título -->
            <div class="form-group inp-wrap">
              <span class="inp-icon"><i class="fas fa-tag"></i></span>
              <label for="title" class="section-title">Título *</label>
              <input type="text" class="form-control" id="title" name="title"
                     required minlength="3" maxlength="100" placeholder="Ej. Asociación Boliviana de Artistas Plásticos">
              <small class="help">Entre 3 y 100 caracteres.</small>
            </div>
 
            <!-- Teléfono -->
            <div class="form-group inp-wrap">
              <span class="inp-icon"><i class="fas fa-phone-alt"></i></span>
              <label for="telephoneContact" class="section-title">Teléfono de contacto</label>
              <input type="text" class="form-control" id="telephoneContact" name="telephoneContact"
                     maxlength="20" placeholder="77778888 o +59177778888">
              <small class="help">Se mostrará en comprobantes o pantallas públicas si corresponde.</small>
            </div>
          </div>
 
          <div class="col-lg-6">
            <!-- LOGO -->
            <div class="mb-3">
              <div class="section-title">Logo</div>
              <div class="uploader">
                <div class="thumb-box">
                  <div class="thumb-placeholder" id="logoPlaceholder">Vista<br>Logo</div>
                  <img id="logoPreview" class="thumb d-none" alt="Logo">
                </div>
                <div class="uploader-controls">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logo" name="logo"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <label class="custom-file-label" for="logo">Seleccionar imagen (máx 2MB)</label>
                  </div>
                  <div class="file-name" id="logoName"></div>
                  <div class="help file-hint">Formatos: JPG, PNG, GIF, WEBP &middot; Tamaño máx. 2MB.</div>
                </div>
              </div>
            </div>
 
            <!-- QR -->
            <div>
              <div class="section-title">Logo/QR para pagos</div>
              <div class="uploader">
                <div class="thumb-box">
                  <div class="thumb-placeholder" id="qrPlaceholder">Vista<br>QR</div>
                  <img id="qrPreview" class="thumb d-none" alt="QR">
                </div>
                <div class="uploader-controls">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logoQR" name="logoQR"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <label class="custom-file-label" for="logoQR">Seleccionar imagen (máx 2MB)</label>
                  </div>
                  <div class="file-name" id="qrName"></div>
                  <div class="help file-hint">Se usará en pagos/comprobantes &middot; Tamaño máx. 2MB.</div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- row -->
 
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-lg">Guardar Configuración</button>
          <a href="<?= u('options') ?>" class="btn btn-outline-secondary btn-lg">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</div>
 
<script>
(function () {
  const MAX_MB = 2;
  const ALLOWED = ['image/jpeg','image/png','image/gif','image/webp'];
 
  function byId(id){ return document.getElementById(id); }
 
  function handlePreview(inputId, imgId, placeholderId, nameId) {
    const input = byId(inputId);
    const img   = byId(imgId);
    const ph    = byId(placeholderId);
    const name  = byId(nameId);
 
    input.addEventListener('change', function(e){
      const f = e.target.files[0];
 
      if (!f) {
        img.classList.add('d-none'); ph.classList.remove('d-none');
        name.textContent = '';
        return;
      }
      if (!ALLOWED.includes(f.type)) {
        alert('Formato no permitido. Usa JPG, PNG, GIF o WEBP.');
        input.value = ''; img.classList.add('d-none'); ph.classList.remove('d-none');
        name.textContent = '';
        return;
      }
      if (f.size > MAX_MB * 1024 * 1024) {
        alert('La imagen supera 2MB.');
        input.value = ''; img.classList.add('d-none'); ph.classList.remove('d-none');
        name.textContent = '';
        return;
      }
 
      name.textContent = f.name;
      const r = new FileReader();
      r.onload = ev => {
        img.src = ev.target.result;
        img.classList.remove('d-none');
        ph.classList.add('d-none');
      };
      r.readAsDataURL(f);
    });
  }
 
  handlePreview('logo',   'logoPreview', 'logoPlaceholder', 'logoName');
  handlePreview('logoQR', 'qrPreview',   'qrPlaceholder',   'qrName');
 
  document.getElementById('optionCreateForm').addEventListener('submit', function(e){
    const title = document.getElementById('title').value.trim();
    if (title.length < 3) { e.preventDefault(); alert('El título debe tener al menos 3 caracteres.'); }
  });
})();
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';