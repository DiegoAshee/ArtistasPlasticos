<?php
// app/Views/system/options_edit.php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
        
        <main class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-cog"></i> <?= htmlspecialchars($title ?? 'Configuración del Sistema', ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8') ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> Configuración del Encabezado
                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?= u('system/options/update-header') ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="logo_url">
                                        <i class="fas fa-image"></i> URL del Logo
                                    </label>
                                    <input type="url" 
                                           class="form-control" 
                                           id="logo_url" 
                                           name="logo_url" 
                                           value="<?= htmlspecialchars($headerOptions['logo_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="https://ejemplo.com/logo.png">
                                    <small class="form-text text-muted">
                                        URL completa de la imagen del logo (formato: PNG, JPG, SVG)
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vista previa del logo</label>
                                    <div class="border rounded p-3 text-center" style="min-height: 100px;">
                                        <img id="logo_preview" 
                                             src="<?= htmlspecialchars($headerOptions['logo_url'] ?? 'assets/images/logo.png', ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="Logo Preview" 
                                             style="max-height: 80px; max-width: 200px;"
                                             onerror="this.src='<?= u('assets/images/default-logo.png') ?>'; this.alt='Logo no disponible';">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="site_title">
                                        <i class="fas fa-heading"></i> Título del Sitio
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="site_title" 
                                           name="site_title" 
                                           value="<?= htmlspecialchars($headerOptions['site_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="Nombre de tu sitio web"
                                           maxlength="100">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tagline">
                                        <i class="fas fa-tag"></i> Eslogan/Tagline
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tagline" 
                                           name="tagline" 
                                           value="<?= htmlspecialchars($headerOptions['tagline'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                           placeholder="Descripción corta o eslogan"
                                           maxlength="150">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-eye"></i> Vista Previa del Encabezado
                                    </h6>
                                    <div class="preview-header d-flex align-items-center">
                                        <img id="header_logo_preview" 
                                             src="<?= htmlspecialchars($headerOptions['logo_url'] ?? 'assets/images/logo.png', ENT_QUOTES, 'UTF-8') ?>" 
                                             alt="Logo" 
                                             style="height: 40px; margin-right: 10px;"
                                             onerror="this.src='<?= u('assets/images/default-logo.png') ?>';">
                                        <div>
                                            <strong id="title_preview"><?= htmlspecialchars($headerOptions['site_title'] ?? 'Sistema MVC', ENT_QUOTES, 'UTF-8') ?></strong>
                                            <br>
                                            <small id="tagline_preview" class="text-muted"><?= htmlspecialchars($headerOptions['tagline'] ?? 'Panel de Administración', ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right">
                            <a href="<?= u('system/options') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
// Actualizar vista previa en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const logoUrlInput = document.getElementById('logo_url');
    const siteTitleInput = document.getElementById('site_title');
    const taglineInput = document.getElementById('tagline');
    
    const logoPreview = document.getElementById('logo_preview');
    const headerLogoPreview = document.getElementById('header_logo_preview');
    const titlePreview = document.getElementById('title_preview');
    const taglinePreview = document.getElementById('tagline_preview');
    
    // Actualizar logo
    logoUrlInput.addEventListener('input', function() {
        const newUrl = this.value || '<?= u("assets/images/default-logo.png") ?>';
        logoPreview.src = newUrl;
        headerLogoPreview.src = newUrl;
    });
    
    // Actualizar título
    siteTitleInput.addEventListener('input', function() {
        titlePreview.textContent = this.value || 'Sistema MVC';
    });
    
    // Actualizar tagline
    taglineInput.addEventListener('input', function() {
        taglinePreview.textContent = this.value || 'Panel de Administración';
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>