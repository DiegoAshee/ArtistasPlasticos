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
    content: "⚙️";
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
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 1.5rem;
    align-items: center;
    transition: all .3s ease;
  }

  .uploader:hover {
    background: var(--bg-soft);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: var(--shadow-soft);
  }
 
  .thumb-box {
    position: relative;
    width: 150px;
    min-width: 150px;
    height: 150px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,.1);
  }

  .thumb-placeholder, .thumb {
    position: absolute;
    inset: 0;
    border-radius: 12px;
  }

  .thumb-placeholder {
    display: grid;
    place-items: center;
    text-align: center;
    color: var(--text-secondary);
    background: white;
    border: 2px dashed var(--border-light);
    font-size: .9rem;
    font-weight: 600;
    padding: 1rem;
  }

  .thumb {
    object-fit: cover;
    width: 100%;
    height: 100%;
    background: white;
    border: none;
  }
 
  .uploader-controls .custom-file-label {
    border-radius: 10px;
    border: 2px solid var(--border-light);
    font-weight: 500;
    transition: all .3s ease;
    cursor: pointer;
  }

  .uploader-controls .custom-file-label:hover {
    border-color: var(--primary);
    background: var(--bg-soft);
  }

  .uploader-controls .custom-file-input:focus ~ .custom-file-label {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(201, 180, 138, .1);
  }

  .file-hint { 
    margin-top: 0.75rem;
  }

  .file-name { 
    font-size: .875rem;
    color: var(--text-primary);
    margin-top: 0.75rem;
    word-break: break-all;
    font-weight: 600;
    color: var(--primary);
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

  .btn-outline-secondary {
    background: var(--text-secondary) !important;
    border: 2px solid var(--text-secondary) !important;
    border-radius: 12px;
    padding: 0.875rem 2rem;
    font-weight: 600;
    font-size: 1rem;
    color: white !important;
    transition: all .3s ease;
  }

  .btn-outline-secondary:hover {
    background: var(--text-primary) !important;
    border-color: var(--text-primary) !important;
    color: white !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
  }

  .btn-secondary {
    background: var(--text-secondary);
    border: none;
    border-radius: 10px;
    padding: 0.625rem 1.5rem;
    font-weight: 600;
    transition: all .3s ease;
  }

  .btn-secondary:hover {
    background: var(--text-primary);
    transform: translateY(-2px);
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

  /* —— Responsive —— */
  @media (max-width: 768px) {
    .card-body {
      padding: 1.5rem;
    }

    .uploader {
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .thumb-box {
      width: 100%;
      max-width: 200px;
      margin: 0 auto;
    }

    .tel-grid {
      grid-template-columns: 1fr;
    }
  }
</style>
 
<div class="container-fluid">
  <!-- Header -->
  <div class="d-sm-flex align-items-center justify-content-between mb-4 page-header">
    <h1>✨ Nueva Configuración</h1>
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
 
  <div class="card card-soft shadow-sm mb-4">
    <div class="card-header py-3">
      <h6>Información de la Configuración</h6>
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
                     required minlength="3" maxlength="100"
                     placeholder="Ej. Asociación Boliviana de Artistas Plásticos">
              <small class="help">📝 Entre 3 y 100 caracteres</small>
            </div>
 
            <!-- Teléfono: código + número => se envían concatenados a telephoneContact -->
            <div class="form-group">
              <label class="section-title d-block mb-1">Teléfono de contacto</label>
              <div class="tel-grid">
                <div>
                  <select id="telCode" class="form-control tel-code">
                    <option value="+591" selected>🇧🇴 +591</option>
                    <option value="+54">🇦🇷 +54</option>
                    <option value="+55">🇧🇷 +55</option>
                    <option value="+56">🇨🇱 +56</option>
                    <option value="+57">🇨🇴 +57</option>
                    <option value="+58">🇻🇪 +58</option>
                    <option value="+34">🇪🇸 +34</option>
                    <option value="+1">🇺🇸 +1</option>
                  </select>
                </div>
                <div class="inp-wrap">
                  <span class="inp-icon"><i class="fas fa-phone-alt"></i></span>
                  <input type="text" class="form-control" id="telNumber"
                         inputmode="numeric" autocomplete="tel"
                         placeholder="77778888" maxlength="15">
                </div>
              </div>
              <input type="hidden" name="telephoneContact" id="telephoneContact" value="">
              <div id="telPreview" class="tel-preview"></div>
              <small class="help">📞 Formato: <code>+código</code> + número. Ejemplo: <strong>+59177778888</strong></small>
            </div>
          </div>
 
          <div class="col-lg-6">
            <!-- LOGO -->
            <div class="mb-4">
              <div class="section-title">Logo Institucional</div>
              <div class="uploader">
                <div class="thumb-box">
                  <div class="thumb-placeholder" id="logoPlaceholder">🖼️<br>Vista Previa</div>
                  <img id="logoPreview" class="thumb d-none" alt="Logo">
                </div>
                <div class="uploader-controls">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logo" name="logo"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <label class="custom-file-label" for="logo">📁 Seleccionar imagen (máx 2MB)</label>
                  </div>
                  <div class="file-name" id="logoName"></div>
                  <div class="help file-hint">✅ Formatos: JPG, PNG, GIF, WEBP · Máx. 2MB</div>
                </div>
              </div>
            </div>
 
            <!-- QR -->
            <div>
              <div class="section-title">Logo/QR para Pagos</div>
              <div class="uploader">
                <div class="thumb-box">
                  <div class="thumb-placeholder" id="qrPlaceholder">📱<br>Vista QR</div>
                  <img id="qrPreview" class="thumb d-none" alt="QR">
                </div>
                <div class="uploader-controls">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" id="logoQR" name="logoQR"
                           accept="image/jpeg,image/png,image/gif,image/webp">
                    <label class="custom-file-label" for="logoQR">📁 Seleccionar imagen (máx 2MB)</label>
                  </div>
                  <div class="file-name" id="qrName"></div>
                  <div class="help file-hint">💳 Se mostrará en pagos y comprobantes · Máx. 2MB</div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- row -->
 
        <div class="mt-5 d-flex gap-2" style="gap: 1rem;">
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-save mr-2"></i> Guardar Configuración
          </button>
          <a href="<?= u('options') ?>" class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-times mr-2"></i> Cancelar
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
 
<script>
(function () {
  const MAX_MB = 2;
  const ALLOWED = ['image/jpeg','image/png','image/gif','image/webp'];
 
  const $ = (id) => document.getElementById(id);
  const telCode   = $('telCode');
  const telNumber = $('telNumber');
  const telHidden = $('telephoneContact');
  const telPrev   = $('telPreview');
 
  // Normaliza número: solo dígitos, sin espacios/guiones
  function sanitizeDigits(v){ return (v || '').replace(/\D+/g,''); }
 
  function updatePhone() {
    const code = telCode.value.trim();               // ej. +591
    const num  = sanitizeDigits(telNumber.value);    // solo dígitos
    if (!num) {
      telHidden.value = '';
      telPrev.innerHTML = '<span class="bad">❌ Sin número</span>';
      return;
    }
    telHidden.value = code + num;
    telPrev.innerHTML = '✅ Se guardará como: <strong class="ok">' + telHidden.value + '</strong>';
  }
 
  telCode.addEventListener('change', updatePhone);
  telNumber.addEventListener('input', (e) => {
    const clean = sanitizeDigits(e.target.value);
    if (e.target.value !== clean) e.target.value = clean;
    updatePhone();
  });
 
  // Validación antes de enviar
  $('optionCreateForm').addEventListener('submit', function(e){
    const title = $('title').value.trim();
    if (title.length < 3) { e.preventDefault(); alert('El título debe tener al menos 3 caracteres.'); return; }
 
    updatePhone();
    const phone = telHidden.value;
    if (phone) {
      // Validar formato: +código (2-4 dígitos) + 6-12 dígitos
      if (!/^\+\d{1,4}\d{6,12}$/.test(phone)) {
        e.preventDefault();
        alert('Teléfono inválido. Revisa el código y el número.');
        return;
      }
    }
  });
 
  // ====== Previews de imágenes ======
  function handlePreview(inputId, imgId, placeholderId, nameId) {
    const input = $(inputId);
    const img   = $(imgId);
    const ph    = $(placeholderId);
    const name  = $(nameId);
 
    input.addEventListener('change', function(e){
      const f = e.target.files[0];
      if (!f) { img.classList.add('d-none'); ph.classList.remove('d-none'); name.textContent = ''; return; }
      if (!ALLOWED.includes(f.type)) { alert('Formato no permitido. Usa JPG, PNG, GIF o WEBP.');
        input.value = ''; img.classList.add('d-none'); ph.classList.remove('d-none'); name.textContent = ''; return; }
      if (f.size > MAX_MB * 1024 * 1024) { alert('La imagen supera 2MB.');
        input.value = ''; img.classList.add('d-none'); ph.classList.remove('d-none'); name.textContent = ''; return; }
 
      name.textContent = '📎 ' + f.name;
      const r = new FileReader();
      r.onload = ev => { img.src = ev.target.result; img.classList.remove('d-none'); ph.classList.add('d-none'); };
      r.readAsDataURL(f);
    });
  }
  handlePreview('logo',   'logoPreview', 'logoPlaceholder', 'logoName');
  handlePreview('logoQR', 'qrPreview',   'qrPlaceholder',   'qrName');
})();
</script>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';