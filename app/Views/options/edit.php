<?php
// app/Views/options/edit.php
$title = 'Editar Configuración';
$currentPath = 'options';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Opciones', 'url' => u('options')],
  ['label' => 'Editar', 'url' => null],
];
 
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
 
ob_start();
?>
<div class="container-fluid">
  <div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Editar Configuración</h1>
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
 
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Información de la Configuración</h6>
    </div>
    <div class="card-body">
      <form action="<?= u('options/update/' . (int)$option['idOption']) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
 
        <div class="form-group">
          <label for="title">Título *</label>
          <input type="text" class="form-control" id="title" name="title" required minlength="3" maxlength="100"
                 value="<?= htmlspecialchars($option['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
 
        <div class="form-group">
          <label for="telephoneContact">Teléfono de contacto</label>
          <input type="text" class="form-control" id="telephoneContact" name="telephoneContact"
                 maxlength="20"
                 value="<?= htmlspecialchars($option['telephoneContact'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                 placeholder="77778888 o +59177778888">
        </div>
 
        <div class="form-group">
          <label>Imagen Actual</label><br>
          <img src="<?= u($option['imageURL'] ?? 'assets/images/logo.png') ?>" alt="Logo actual"
               class="img-fluid mb-2" style="max-height:150px;">
        </div>
 
        <div class="form-group">
          <label>QR Actual</label><br>
          <img src="<?= u($option['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>"
               alt="QR actual" class="img-fluid mb-2" style="max-height:150px;">
        </div>
 
        <div class="form-group">
          <label for="logo">Nuevo Logo (opcional)</label>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp">
            <label class="custom-file-label" for="logo">Seleccionar nueva imagen (máx 2MB)</label>
          </div>
        </div>
 
        <div class="form-group">
          <label for="logoQR">Nuevo QR (opcional)</label>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="logoQR" name="logoQR" accept="image/jpeg,image/png,image/gif,image/webp">
            <label class="custom-file-label" for="logoQR">Seleccionar nueva imagen (máx 2MB)</label>
          </div>
        </div>
 
        <div class="form-group">
          <img id="preview" src="#" alt="Vista previa" class="img-fluid mt-2" style="max-height:150px; display:none;">
        </div>
 
        <button type="submit" class="btn btn-primary">Actualizar Configuración</button>
        <a href="<?= u('options') ?>" class="btn btn-secondary">Cancelar</a>
      </form>
    </div>
  </div>
</div>
 
<script>
document.getElementById('logo').addEventListener('change', function(e){
  const preview = document.getElementById('preview');
  const f = e.target.files[0];
  if (f) {
    const r = new FileReader();
    r.onload = ev => { preview.src = ev.target.result; preview.style.display='block'; };
    r.readAsDataURL(f);
  } else { preview.style.display = 'none'; }
});
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';