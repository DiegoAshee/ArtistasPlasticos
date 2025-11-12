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
 
// ====== Prefill: partir teléfono guardado en código + número ======
$telRaw = (string)($option['telephoneContact'] ?? '');
$telCodePref = '+591';
$telNumPref  = '';
 
if ($telRaw !== '') {
  // Formatos típicos: +59177778888  | +34XXXXXXXXX
  if (preg_match('/^(\+\d{1,4})(\d{6,12})$/', preg_replace('/\s+/', '', $telRaw), $m)) {
    $telCodePref = $m[1];
    $telNumPref  = $m[2];
  } else {
    // Si no calza, intenta recuperar solo dígitos y asumir +591
    $digits = preg_replace('/\D+/', '', $telRaw);
    if ($digits !== '') {
      if (strpos($digits, '591') === 0) {
        $telCodePref = '+591';
        $telNumPref  = substr($digits, 3);
      } else {
        $telCodePref = '+591';
        $telNumPref  = $digits;
      }
    }
  }
}
?>
<?php ob_start(); ?>
<style>
  /* —— Variables de color —— */
  :root {
    --primary: #c9b48a;
    --primary-dark: #b9a476;
    --primary-light: #e5dcc8;
    --bg-soft: #fafbfc;
    --border-light: rgba(0,0,0,.08);
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --shadow-soft: 0 2px 8px rgba(0,0,0,.06);
    --shadow-hover: 0 4px 16px rgba(0,0,0,.1);
  }

  /* —— Contenedor principal —— */
  .container-fluid {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    min-height: 100vh;
    padding-bottom: 3rem;
  }

  /* —— Header mejorado —— */
  .page-header {
    background: white;
    border-radius: 16px;
    padding: 1.5rem 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-soft);
    border-left: 4px solid var(--primary);
  }

  .page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0;
  }

  /* —— Card principal —— */
  .card-soft { 
    border: none;
    border-radius: 20px;
    background: white;
    box-shadow: var(--shadow-soft);
    overflow: hidden;
    transition: box-shadow .3s ease;
  }

  .card-soft:hover {
    box-shadow: var(--shadow-hover);
  }

  .card-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: none;
    padding: 1.5rem 2rem;
  }

  .card-header h6 {
    color: white !important;
    font-size: 1.1rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .card-header h6::before {
    content: "✏️";
    font-size: 1.3rem;
  }

  .card-body {
    padding: 2.5rem;
  }

  /* —— Secciones con estilo —— */
  .section-title { 
    font-weight: 700;
    font-size: 1rem;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .section-title::before {
    content: "";
    width: 4px;
    height: 20px;
    background: var(--primary);
    border-radius: 2px;
  }

  .help { 
    font-size: .875rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
    display: block;
  }
 
  /* —— Inputs con icono mejorados —— */
  .inp-wrap { 
    position: relative;
  }

  .inp-wrap .inp-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
    font-size: 1rem;
    z-index: 10;
  }

  .inp-wrap .form-control { 
    padding-left: 48px;
    border-radius: 12px;
    border: 2px solid var(--border-light);
    transition: all .3s ease;
    font-size: .95rem;
    height: 48px;
  }

  .inp-wrap .form-control:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(201, 180, 138, .1);
    transform: translateY(-1px);
  }

  .inp-wrap .form-control::placeholder {
    color: #adb5bd;
  }
 
  /* —— Grid de teléfono mejorado —— */
  .tel-grid {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 12px;
    align-items: start;
  }

  .tel-code { 
    min-width: 140px;
    border-radius: 12px;
    border: 2px solid var(--border-light);
    height: 48px;
    font-weight: 600;
    transition: all .3s ease;
  }

  .tel-code:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(201, 180, 138, .1);
  }

  .tel-preview { 
    font-size: .9rem;
    color: var(--text-primary);
    margin-top: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--bg-soft);
    border-radius: 8px;
    border-left: 3px solid var(--primary);
  }

  .ok { 
    color: #10b981;
    font-weight: 600;
  }

  .bad { 
    color: #ef4444;
    font-weight: 600;
  }
 
  /* —— Uploader mejorado —— */
  .uploader {
    border: 2px dashed var(--border-light);
    border-radius: 16px;
    padding: 1.5rem;
    background: linear-gradient(135deg, #fafafa 0%, #ffffff 100%);
    transition: all .3s ease;
    margin-top: 1rem;
  }

  .uploader:hover {
    background: var(--bg-soft);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
  }

  .uploader label {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }

  .custom-file-label {
    border-radius: 10px;
    border: 2px solid var(--border-light);
    font-weight: 500;
    transition: all .3s ease;
    cursor: pointer;
    height: 48px;
    line-height: 2.5;
  }

  .custom-file-label:hover {
    border-color: var(--primary);
    background: var(--bg-soft);
  }

  .custom-file-input:focus ~ .custom-file-label {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(201, 180, 138, .1);
  }

  .file-hint { 
    margin-top: 0.75rem;
  }

  /* —— Imagen actual con estilo —— */
  .current-image-box {
    background: white;
    border: 2px solid var(--border-light);
    border-radius: 12px;
    padding: 1rem;
    display: inline-block;
    box-shadow: var(--shadow-soft);
    transition: all .3s ease;
  }

  .current-image-box:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
  }

  .current-image-box img {
    border-radius: 8px;
    display: block;
  }

  .image-preview {
    margin-top: 1rem;
    padding: 1rem;
    background: var(--bg-soft);
    border-radius: 12px;
    border: 2px solid var(--primary-light);
  }

  .image-preview img {
    border-radius: 8px;
    box-shadow: var(--shadow-soft);
  }
 
  /* —— Botones mejorados —— */
  .btn-primary { 
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    border: none;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    transition: all .3s ease;
    box-shadow: 0 4px 12px rgba(201, 180, 138, .3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(201, 180, 138, .4);
  }

  .btn-primary:active {
    transform: translateY(0);
  }

  .btn-secondary {
    background: var(--text-secondary) !important;
    border: 2px solid var(--text-secondary) !important;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    color: white !important;
    transition: all .3s ease;
  }

  .btn-secondary:hover {
    background: var(--text-primary) !important;
    border-color: var(--text-primary) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
  }

  /* —— Alert mejorado —— */
  .alert-danger {
    border: none;
    border-radius: 12px;
    border-left: 4px solid #ef4444;
    background: #fef2f2;
    color: #991b1b;
    padding: 1rem 1.5rem;
  }

  /* —— Espaciado —— */
  .form-group {
    margin-bottom: 2rem;
  }

  /* —— Badge para "actual" —— */
  .badge-current {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-block;
    margin-left: 0.5rem;
  }

  /* —— Responsive —— */
  @media (max-width: 768px) {
    .card-body {
      padding: 1.5rem;
    }

    .tel-grid {
      grid-template-columns: 1fr;
    }

    .btn-primary, .btn-secondary {
      width: 100%;
      margin-bottom: 0.5rem;
    }
  }
</style>
 
<div class="container-fluid">
  <!-- Header -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4 page-header">
    <h1>✏️ Editar Configuración</h1>
    <a href="<?= u('options') ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
      <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
    </a>
  </div>
 
  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>⚠️ Error:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
    </div>
  <?php endif; ?>
 
  <div class="card card-soft shadow mb-4">
    <div class="card-header py-3">
      <h6>Información de la Configuración</h6>
    </div>
 
    <div class="card-body">
      <form action="<?= u('options/update/' . (int)$option['idOption']) ?>" method="POST" enctype="multipart/form-data" id="optionEditForm">
        <input type="hidden" name="id" value="<?= (int)$option['idOption'] ?>">
 
        <!-- Título -->
        <div class="form-group inp-wrap">
          <span class="inp-icon"><i class="fas fa-tag"></i></span>
          <label for="title" class="section-title">Título *</label>
          <input type="text" class="form-control" id="title" name="title"
                 required minlength="3" maxlength="100"
                 value="<?= htmlspecialchars($option['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
          <small class="help">📝 Entre 3 y 100 caracteres</small>
        </div>
 
        <!-- Teléfono: código + número -> se concatena en hidden telephoneContact -->
        <div class="form-group">
          <label class="section-title d-block mb-1">Teléfono de contacto</label>
          <div class="tel-grid">
            <div>
              <select id="telCode" class="form-control tel-code">
                <?php
                // Lista de algunos prefijos; el que coincida con $telCodePref queda selected
                $codes = ['+591'=>'🇧🇴 +591','+54'=>'🇦🇷 +54','+55'=>'🇧🇷 +55','+56'=>'🇨🇱 +56','+57'=>'🇨🇴 +57','+58'=>'🇻🇪 +58','+34'=>'🇪🇸 +34','+1'=>'🇺🇸 +1'];
                foreach ($codes as $code => $label):
                  $cleanCode = explode(' ', $label)[1]; // Extraer solo el código
                ?>
                  <option value="<?= $cleanCode ?>" <?= $cleanCode===$telCodePref?'selected':''; ?>><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="inp-wrap">
              <span class="inp-icon"><i class="fas fa-phone-alt"></i></span>
              <input type="text" class="form-control" id="telNumber"
                     inputmode="numeric" autocomplete="tel"
                     placeholder="77778888" maxlength="15"
                     value="<?= htmlspecialchars($telNumPref, ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
          <input type="hidden" name="telephoneContact" id="telephoneContact" value="">
          <div id="telPreview" class="tel-preview"></div>
          <small class="help">📞 Formato: <code>+código</code> + número. Ejemplo: <strong>+59177778888</strong></small>
        </div>
 
        <!-- Imagen actual -->
        <div class="form-group">
          <label class="section-title d-block">
            🖼️ Logo Institucional
            <span class="badge-current">ACTUAL</span>
          </label>
          <div class="current-image-box">
            <img src="<?= u($option['imageURL'] ?? 'assets/images/logo.png') ?>" alt="Logo actual"
                 class="img-fluid" style="max-height:150px;">
          </div>
          <div class="uploader">
            <label for="logo" class="mb-1">📁 Cambiar Logo (opcional)</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp">
              <label class="custom-file-label" for="logo">Seleccionar nueva imagen (máx 2MB)</label>
            </div>
            <small class="help file-hint">✅ Formatos: JPG, PNG, GIF, WEBP · Máx. 2MB</small>
            <div class="image-preview" id="logoPreviewBox" style="display:none;">
              <img id="logoPreview" src="#" alt="Vista previa logo" class="img-fluid" style="max-height:150px;">
            </div>
          </div>
        </div>
 
        <!-- QR actual -->
        <div class="form-group">
          <label class="section-title d-block">
            📱 Logo/QR para Pagos
            <span class="badge-current">ACTUAL</span>
          </label>
          <div class="current-image-box">
            <img src="<?= u($option['imageURLQR'] ?? 'assets/images/logo_qr.png') ?>" alt="QR actual"
                 class="img-fluid" style="max-height:150px;">
          </div>
          <div class="uploader">
            <label for="logoQR" class="mb-1">📁 Cambiar QR (opcional)</label>
            <div class="custom-file">
              <input type="file" class="custom-file-input" id="logoQR" name="logoQR" accept="image/jpeg,image/png,image/gif,image/webp">
              <label class="custom-file-label" for="logoQR">Seleccionar nueva imagen (máx 2MB)</label>
            </div>
            <small class="help file-hint">💳 Se mostrará en pagos y comprobantes · Máx. 2MB</small>
            <div class="image-preview" id="qrPreviewBox" style="display:none;">
              <img id="qrPreview" src="#" alt="Vista previa QR" class="img-fluid" style="max-height:150px;">
            </div>
          </div>
        </div>
 
        <div class="mt-5 d-flex gap-2" style="gap: 1rem;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-2"></i> Actualizar Configuración
          </button>
          <a href="<?= u('options') ?>" class="btn btn-secondary">
            <i class="fas fa-times mr-2"></i> Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
 
<script>
(function(){
  const $ = (id) => document.getElementById(id);
 
  const telCode   = $('telCode');
  const telNumber = $('telNumber');
  const telHidden = $('telephoneContact');
  const telPrev   = $('telPreview');
 
  // Normaliza a dígitos
  function digits(v){ return (v||'').replace(/\D+/g,''); }
 
  function updatePhone(){
    const code = (telCode.value || '').trim(); // +NNN
    const num  = digits(telNumber.value);
    if(!num){
      telHidden.value = '';
      telPrev.innerHTML = '<span class="bad">❌ Sin número</span>';
      return;
    }
    telHidden.value = code + num;
    telPrev.innerHTML = '✅ Se guardará como: <strong class="ok">'+ telHidden.value +'</strong>';
  }
 
  telCode.addEventListener('change', updatePhone);
  telNumber.addEventListener('input', (e)=>{
    const clean = digits(e.target.value);
    if(e.target.value !== clean) e.target.value = clean;
    updatePhone();
  });
 
  // Inicializa preview del tel con valores prellenados
  updatePhone();
 
  // Validación antes de enviar
  $('optionEditForm').addEventListener('submit', function(e){
    const title = $('title').value.trim();
    if (title.length < 3) { e.preventDefault(); alert('El título debe tener al menos 3 caracteres.'); return; }
 
    updatePhone();
    const phone = telHidden.value;
    if (phone && !/^\+\d{1,4}\d{6,12}$/.test(phone)) {
      e.preventDefault();
      alert('Teléfono inválido. Revisa el código y el número.');
      return;
    }
  });
 
  // ===== Previews de imágenes =====
  function previewFile(inputId, imgId, boxId){
    const input = $(inputId), img = $(imgId), box = $(boxId);
    input.addEventListener('change', function(e){
      const f = e.target.files[0];
      if(!f){ box.style.display='none'; return; }
      const okTypes = ['image/jpeg','image/png','image/gif','image/webp'];
      if(!okTypes.includes(f.type)){ 
        alert('Formato no permitido. Usa JPG, PNG, GIF o WEBP.'); 
        input.value=''; box.style.display='none'; 
        return; 
      }
      if(f.size > 2*1024*1024){ 
        alert('La imagen supera 2MB.'); 
        input.value=''; box.style.display='none'; 
        return; 
      }
      const r = new FileReader();
      r.onload = ev => { 
        img.src = ev.target.result; 
        box.style.display='block'; 
      };
      r.readAsDataURL(f);
    });
  }
  previewFile('logo','logoPreview', 'logoPreviewBox');
  previewFile('logoQR','qrPreview', 'qrPreviewBox');
})();
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';