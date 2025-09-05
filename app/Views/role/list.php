<?php
$title = 'Roles';
$currentPath = 'role/list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Roles', 'url' => null],
];

ob_start();
?>
<style>
    .modern-table th, .modern-table td {
        padding: 10px 14px;
        line-height: 1.35;
        vertical-align: middle;
    }
    .modern-table { border-collapse: separate; border-spacing: 0 6px; }
    .modern-table thead th {
        position: sticky; top: 0;
        background: #bbae97; color: #2a2a2a;
        z-index: 2;
    }
    .modern-table tbody tr { background: #d7cbb5; }
    .modern-table tbody tr:nth-child(even) { background: #dccaaf; }
    .modern-table tbody tr td:first-child  { border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
    .modern-table tbody tr td:last-child   { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

    .table-container { background: #cfc4b0; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.06); overflow: auto; }

    .modal {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;
    }
    .modal-content {
        background: #fff; margin: 15% auto; padding: 20px; width: 300px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    .close { float: right; font-size: 20px; cursor: pointer; }
</style>

<!-- Barra de acciones -->
<div class="toolbar" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:12px; margin-bottom:18px;">
    <div class="search-container" style="position:relative; flex:1 1 320px;">
        <i class="fas fa-search search-icon" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:.6;"></i>
        <input
            type="text"
            id="searchInput"
            placeholder="Buscar por rol"
            style="width:100%; border:2px solid #e1e5e9; border-radius:12px; padding:10px 40px 10px 38px; outline:none; background:#fff; transition:border-color .2s;"
            onfocus="this.style.borderColor='var(--cream-400)';"
            onblur="this.style.borderColor='#e1e5e9';"
        />
    </div>

    <button id="openCreateModal" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; background:#bbae97; color:#fff; border:none; border-radius:12px; padding:10px 14px; text-decoration:none; font-weight:600;">
        <i class="fas fa-plus"></i> Nuevo Rol
    </button>
</div>

<!-- Tabla de roles -->
<?php if (!empty($roles) && is_array($roles)): ?>
    <div class="table-container">
        <table id="tablaRoles" class="modern-table" style="width:100%; border-collapse:separate; border-spacing:0;">
            <thead>
                <tr>
                    <th><i class="fas fa-money-bill-wave"></i> Rol</th>
                    <th><i class="fas fa-cogs"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?= htmlspecialchars($role['rol'] ?? '') ?></td>
                        <td class="actions">
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline open-update-modal" data-id="<?= (int)($role['idRol'] ?? 0) ?>" data-role="<?= htmlspecialchars($role['rol'] ?? '') ?>" title="Editar" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e1e5e9; color:#333; background:none; cursor:pointer;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- <button class="btn btn-sm btn-danger open-delete-modal" data-id="<?= (int)($role['idRol'] ?? 0) ?>" title="Eliminar" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#e74c3c; color:#fff; margin-left:6px; border:none; cursor:pointer;">
                                    <i class="fas fa-trash"></i>
                                </button> -->
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="empty-state" style="text-align:center; padding:40px 20px; background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.06);">
        <div class="empty-state-icon" style="font-size:42px; margin-bottom:10px; color:#bbae97;"><i class="fas fa-calendar"></i></div>
        <h3>No hay roles registradas</h3>
        <p>Comienza agregando tu primer rol al sistema</p>
        <button id="openCreateModal" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; background:#bbae97; color:#fff; border:none; border-radius:12px; padding:10px 14px; text-decoration:none; font-weight:600;">
            <i class="fas fa-plus"></i> Crear primer rol
        </button>
    </div>
<?php endif; ?>

<!-- Create Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Nuevo Rol</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" action="<?= u('role/list') ?>">
            <input type="hidden" name="action" value="create">
            
            <label for="role">Rol: </label>
            <input type="text" name="role" id="role" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">

            <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-top:10px;">Crear</button>
        </form>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Rol</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" action="<?= u('role/list') ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="updateId">

            <label for="updateRole">Rol:</label>
            <input type="text" name="role" id="updateRole" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">

            <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-top:10px;">Guardar</button>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Eliminar Rol</h2>
        <p>¿Estás seguro de que deseas eliminar este rol?</p>
        <form method="POST" action="<?= u('role/list') ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId">
            <button type="submit" style="background:#e74c3c; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-right:10px;">Sí, eliminar</button>
            <button type="button" class="close" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancelar</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM fully loaded and parsed');

        const input = document.getElementById('searchInput');
        const table = document.getElementById('tablaRoles');
        if (!table) {
            console.error('Table not found');
            return;
        }

        const tbody = table.querySelector('tbody');
        const allRows = Array.from(tbody.querySelectorAll('tr'));

        // Search functionality
        if (input) {
            input.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                allRows.forEach(tr => {
                    const text = tr.textContent.toLowerCase();
                    tr.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }

        // Modal functionality
        const createModal = document.getElementById('createModal');
        const updateModal = document.getElementById('updateModal');
        const deleteModal = document.getElementById('deleteModal');
        const openCreateBtn = document.getElementById('openCreateModal');
        const openUpdateBtns = document.querySelectorAll('.open-update-modal');
        const openDeleteBtns = document.querySelectorAll('.open-delete-modal');
        const closeBtns = document.getElementsByClassName('close');

        if (!openCreateBtn) console.error('Create button not found');
        if (!createModal) console.error('Create modal not found');

        if (openCreateBtn && createModal) {
            openCreateBtn.addEventListener('click', () => {
                console.log('Opening create modal');
                createModal.style.display = 'block';
            });
        }

        openUpdateBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    console.log('Opening update modal for ID:', btn.getAttribute('data-id'));
                    const id = btn.getAttribute('data-id');
                    const role = btn.getAttribute('data-role');
                    document.getElementById('updateId').value = id;
                    document.getElementById('updateRole').value = role;
                    updateModal.style.display = 'block';
                });
            }
        });

        openDeleteBtns.forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    console.log('Opening delete modal for ID:', btn.getAttribute('data-id'));
                    const id = btn.getAttribute('data-id');
                    document.getElementById('deleteId').value = id;
                    deleteModal.style.display = 'block';
                });
            }
        });

        Array.from(closeBtns).forEach(btn => {
            if (btn) {
                btn.addEventListener('click', () => {
                    console.log('Closing modal');
                    createModal.style.display = 'none';
                    updateModal.style.display = 'none';
                    deleteModal.style.display = 'none';
                });
            }
        });

        window.addEventListener('click', (event) => {
            if (event.target === createModal) {
                console.log('Clicked outside create modal');
                createModal.style.display = 'none';
            }
            if (event.target === updateModal) {
                console.log('Clicked outside update modal');
                updateModal.style.display = 'none';
            }
            if (event.target === deleteModal) {
                console.log('Clicked outside delete modal');
                deleteModal.style.display = 'none';
            }
        });
    });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';