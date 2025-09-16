<?php
$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Nueva Configuración</h1>
        <a href="<?= URL_BASE ?>options" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver
        </a>
    </div>

    <!-- Mensajes de error -->
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información de la Configuración</h6>
        </div>
        <div class="card-body">
            <form action="<?= URL_BASE ?>options/store" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Título *</label>
                    <input type="text" class="form-control" id="title" name="title" required 
                           minlength="3" maxlength="100" placeholder="Ingrese el título de la configuración">
                    <small class="form-text text-muted">Mínimo 3 caracteres, máximo 100 caracteres.</small>
                </div>

                <div class="form-group">
                    <label for="logo">Logo</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp">
                        <label class="custom-file-label" for="logo">Seleccionar imagen (JPG, PNG, GIF, WEBP - máximo 2MB)</label>
                    </div>
                    <small class="form-text text-muted">Si no selecciona una imagen, se usará el logo por defecto.</small>
                </div>

                <div class="form-group">
                    <img id="preview" src="#" alt="Vista previa" class="img-fluid mt-2" style="max-height: 150px; display: none;">
                </div>

                <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                <a href="<?= URL_BASE ?>options" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script>
// Mostrar vista previa de la imagen seleccionada
document.getElementById('logo').addEventListener('change', function(e) {
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});

// Mostrar nombre de archivo seleccionado en el input
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Seleccionar imagen';
    this.nextElementSibling.textContent = fileName;
});
</script>