<?php
// Verificar si hay mensajes de éxito o error
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

// Limpiar mensajes después de mostrarlos
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Configuración del Sitio</h1>
        <a href="<?= URL_BASE ?>options/create" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Nueva Configuración
        </a>
    </div>

    <!-- Mensajes de éxito/error -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $success ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $error ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Panel de Configuración Activa -->
    <?php if ($activeOption): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Configuración Activa</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <img src="<?= URL_BASE . $activeOption['imageURL'] ?>" alt="Logo" class="img-fluid" style="max-height: 100px;">
                </div>
                <div class="col-md-10">
                    <h4><?= htmlspecialchars($activeOption['title']) ?></h4>
                    <p class="text-muted">ID: <?= $activeOption['idOption'] ?> | Creado por: <?= $activeOption['idUser'] ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Configuraciones -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Todas las Configuraciones</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Imagen</th>
                            <th>Estado</th>
                            <th>Creado por</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($options as $option): ?>
                        <tr>
                            <td><?= $option['idOption'] ?></td>
                            <td><?= htmlspecialchars($option['title']) ?></td>
                            <td>
                                <img src="<?= URL_BASE . $option['imageURL'] ?>" alt="Logo" style="max-height: 50px;">
                            </td>
                            <td>
                                <?php if ($option['status'] == 1): ?>
                                    <span class="badge badge-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $option['idUser'] ?></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?php if ($option['status'] != 1): ?>
                                        <form action="<?= URL_BASE ?>options/activate" method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $option['idOption'] ?>">
                                            <button type="submit" class="btn btn-success btn-sm" title="Activar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="<?= URL_BASE ?>options/edit?id=<?= $option['idOption'] ?>" class="btn btn-primary btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <form action="<?= URL_BASE ?>options/delete" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $option['idOption'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta configuración?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>