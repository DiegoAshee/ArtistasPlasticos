<?php
// app/Views/system/options_list.php
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
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group mr-2">
                        <a href="<?= u('system/options/edit') ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Editar Configuración
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list"></i> Opciones del Sistema
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($options)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    No hay opciones configuradas en el sistema.
                                    <a href="<?= u('system/options/edit') ?>" class="alert-link">Configurar ahora</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Clave</th>
                                                <th>Valor</th>
                                                <th>Tipo</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($options as $option): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($option['idOption'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                    <td>
                                                        <code><?= htmlspecialchars($option['option_key'] ?? '', ENT_QUOTES, 'UTF-8') ?></code>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $value = $option['option_value'] ?? '';
                                                        if (strlen($value) > 50) {
                                                            echo htmlspecialchars(substr($value, 0, 50), ENT_QUOTES, 'UTF-8') . '...';
                                                        } else {
                                                            echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $key = $option['option_key'] ?? '';
                                                        if (in_array($key, ['logo_url'])) {
                                                            echo '<span class="badge badge-info">Imagen</span>';
                                                        } elseif (in_array($key, ['site_title', 'tagline'])) {
                                                            echo '<span class="badge badge-success">Texto</span>';
                                                        } else {
                                                            echo '<span class="badge badge-secondary">General</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary" 
                                                                onclick="editOption('<?= htmlspecialchars($option['option_key'] ?? '', ENT_QUOTES, 'UTF-8') ?>', '<?= htmlspecialchars($option['option_value'] ?? '', ENT_QUOTES, 'UTF-8') ?>')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-info" 
                                                                onclick="viewOption('<?= htmlspecialchars($option['option_value'] ?? '', ENT_QUOTES, 'UTF-8') ?>')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal para editar opción -->
<div class="modal fade" id="editOptionModal" tabindex="-1" role="dialog" aria-labelledby="editOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editOptionModalLabel">Editar Opción</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editOptionForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="optionKey">Clave</label>
                        <input type="text" class="form-control" id="optionKey" name="key" readonly>
                    </div>
                    <div class="form-group">
                        <label for="optionValue">Valor</label>
                        <textarea class="form-control" id="optionValue" name="value" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver valor completo -->
<div class="modal fade" id="viewOptionModal" tabindex="-1" role="dialog" aria-labelledby="viewOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewOptionModalLabel">Ver Valor Completo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <pre id="optionFullValue" class="bg-light p-3 rounded"></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function editOption(key, value) {
    document.getElementById('optionKey').value = key;
    document.getElementById('optionValue').value = value;
    $('#editOptionModal').modal('show');
}

function viewOption(value) {
    document.getElementById('optionFullValue').textContent = value;
    $('#viewOptionModal').modal('show');
}

// Manejar envío del formulario de edición
document.getElementById('editOptionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= u("system/options/update") ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Opción actualizada correctamente');
            $('#editOptionModal').modal('hide');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar la opción');
    });
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>