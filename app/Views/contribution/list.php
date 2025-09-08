<?php
$title = 'Contribuciones';
$currentPath = 'contribution/list';
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Contribuciones', 'url' => null],
];

// Métricas simples
$totalContribuciones = is_array($contributions ?? null) ? count($contributions) : 0;
$nuevasEsteAnio = 0;
if (!empty($contributions) && is_array($contributions)) {
    $anio = date('Y');
    foreach ($contributions as $c) {
        $dc = $c['dateCreation'] ?? null;
        if ($dc && date('Y', strtotime($dc)) === $anio) { $nuevasEsteAnio++; }
    }
}

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
            placeholder="Buscar por mes/año, monto, notas..."
            style="width:100%; border:2px solid #e1e5e9; border-radius:12px; padding:10px 40px 10px 38px; outline:none; background:#fff; transition:border-color .2s;"
            onfocus="this.style.borderColor='var(--cream-400)';"
            onblur="this.style.borderColor='#e1e5e9';"
        />
    </div>

    <button id="openCreateModal" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; background:#bbae97; color:#fff; border:none; border-radius:12px; padding:10px 14px; text-decoration:none; font-weight:600;">
        <i class="fas fa-plus"></i> Nueva Contribución
    </button>
</div>

<!-- Métricas -->
<!-- <div class="dashboard-cards" style="margin-bottom:16px;">
    <div class="card">
        <div class="card-header">
            <div class="card-icon success"><i class="fas fa-calendar"></i></div>
            <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Total Contribuciones</div>
            <div class="card-value" id="totalContribuciones"><?= (int)$totalContribuciones ?></div>
            <div class="card-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>Actualizado</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon primary"><i class="fas fa-calendar-plus"></i></div>
            <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Nuevas este año</div>
            <div class="card-value"><?= (int)$nuevasEsteAnio ?></div>
            <div class="card-change">
                <i class="fas fa-clock"></i>
                <span><?= date('Y') ?></span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon warning"><i class="fas fa-sync-alt"></i></div>
            <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Accesos rápidos</div>
            <div class="card-value">
                <a href="<?= u('dashboard') ?>" class="dropdown-item" style="padding:6px 10px; border-radius:8px; background:#d7cbb5; text-decoration:none;">
                    <i class="fas fa-chart-pie"></i> Ir al Dashboard
                </a>
            </div>
        </div>
    </div>
</div> -->

<!-- Tabla de contribuciones -->
<?php if (!empty($contributions) && is_array($contributions)): ?>
    <div class="table-container">
        <table id="tablaContribuciones" class="modern-table" style="width:100%; border-collapse:separate; border-spacing:0;">
            <thead>
                <tr>
                    <th><i class="fas fa-calendar"></i> Mes/Año</th>
                    <th><i class="fas fa-sticky-note"></i> Notas</th>
                    <th><i class="fas fa-money-bill-wave"></i> Monto</th>
                    <th><i class="fas fa-clock"></i> F. Creación</th>
                    <th><i class="fas fa-sync-alt"></i> F. Actualización</th>
                    <th><i class="fas fa-cogs"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contributions as $contribution): ?>
                    <tr>
                        <td><span class="date-badge"><?= htmlspecialchars($contribution['monthYear'] ?? '-') ?></span></td>
                        <td title="<?= htmlspecialchars($contribution['notes'] ?? '') ?>">
                            <?php
                                $notes = (string)($contribution['notes'] ?? '');
                                $notes = htmlspecialchars($notes, ENT_QUOTES, 'UTF-8');
                                echo (mb_strlen($notes, 'UTF-8') > 30) ? mb_substr($notes, 0, 30, 'UTF-8') . '…' : $notes;
                            ?>
                        </td>
                        <td><?= htmlspecialchars(number_format($contribution['amount'], 2)) ?></td>
                        <td><span class="date-badge"><?= !empty($contribution['dateCreation']) ? date('d/m/Y', strtotime($contribution['dateCreation'])) : '-' ?></span></td>
                        <td><span class="date-badge"><?= !empty($contribution['dateUpdate']) ? date('d/m/Y', strtotime($contribution['dateUpdate'])) : '-' ?></span></td>
                        <td class="actions">
                            <div class="action-buttons">
                                <button class="btn btn-sm btn-outline open-update-modal" data-id="<?= (int)($contribution['idContribution'] ?? 0) ?>" data-amount="<?= htmlspecialchars(number_format($contribution['amount'], 2)) ?>" data-monthyear="<?= htmlspecialchars($contribution['monthYear'] ?? '') ?>" data-notes="<?= htmlspecialchars($contribution['notes'] ?? '') ?>" title="Editar" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; border:1px solid #e1e5e9; color:#333; background:none; cursor:pointer;">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- <button class="btn btn-sm btn-danger open-delete-modal" data-id="<?= (int)($contribution['idContribution'] ?? 0) ?>" title="Eliminar" style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:8px; background:#e74c3c; color:#fff; margin-left:6px; border:none; cursor:pointer;">
                                    <i class="fas fa-trash"></i>
                                </button> -->
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="pager" style="display:flex; align-items:center; gap:8px; justify-content:flex-end; padding:12px;">
            <label for="pageSize">Por página:</label>
            <select id="pageSize" style="border:1px solid #cfcfcf; border-radius:8px; padding:6px 8px;">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>

            <button id="firstPage" style="border:1px solid #cfcfcf; border-radius:8px; padding:6px 10px; background:#fff;">«</button>
            <button id="prevPage" style="border:1px solid #cfcfcf; border-radius:8px; padding:6px 10px; background:#fff;">‹</button>
            <span id="pageInfo" style="min-width:180px; text-align:center; font-weight:600;"></span>
            <button id="nextPage" style="border:1px solid #cfcfcf; border-radius:8px; padding:6px 10px; background:#fff;">›</button>
            <button id="lastPage" style="border:1px solid #cfcfcf; border-radius:8px; padding:6px 10px; background:#fff;">»</button>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state" style="text-align:center; padding:40px 20px; background:#fff; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,.06);">
        <div class="empty-state-icon" style="font-size:42px; margin-bottom:10px; color:#bbae97;"><i class="fas fa-calendar"></i></div>
        <h3>No hay contribuciones registradas</h3>
        <p>Comienza agregando tu primera contribución al sistema</p>
        <button id="openCreateModal" class="btn-primary" style="display:inline-flex; align-items:center; gap:8px; background:#bbae97; color:#fff; border:none; border-radius:12px; padding:10px 14px; text-decoration:none; font-weight:600;">
            <i class="fas fa-plus"></i> Crear primera contribución
        </button>
    </div>
<?php endif; ?>

<!-- Create Modal -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Nueva Contribución</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" action="<?= u('contribution/list') ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="monthYear" value="<?= htmlspecialchars($defaultMonthYear) ?>">
            <?php
            $monthParts = explode('-', $defaultMonthYear);
            $monthNum = isset($monthParts[1]) ? (int)$monthParts[1] : date('n');
            $year = isset($monthParts[0]) ? $monthParts[0] : date('Y');
            $monthName = $monthNames[$monthNum] ?? 'Mes inválido';
            ?>
            <input type="hidden" name="notes" value="Cuota mensual <?= htmlspecialchars($monthName) ?> <?= htmlspecialchars($year) ?>">

            <label>Mes/Año: <span style="color:#666; font-weight:bold;"><?= htmlspecialchars($defaultMonthYear) ?></span> (Automático)</label><br>
            <label>Notas: <span style="color:#666; font-weight:bold;">Cuota mensual <?= htmlspecialchars($monthName) ?> <?= htmlspecialchars($year) ?></span> (Automático)</label><br>

            <label for="amount">Monto (predeterminado: <?= htmlspecialchars(number_format($defaultAmount, 2)) ?>):</label>
            <input type="number" step="0.01" name="amount" id="amount" value="<?= htmlspecialchars(number_format($defaultAmount, 2)) ?>" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">

            <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-top:10px;">Crear</button>
        </form>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Contribución</h2>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST" action="<?= u('contribution/list') ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="updateId">

            <label>Mes/Año: <span style="color:#666; font-weight:bold;" id="updateMonthYear"></span> (Fijo)</label><br>
            <label>Notas: <span style="color:#666; font-weight:bold;" id="updateNotes"></span> (Fijo)</br>

            <label for="updateAmount">Monto:</label>
            <input type="number" step="0.01" name="amount" id="updateAmount" required style="width:100%; padding:8px; margin:5px 0; border:1px solid #ccc; border-radius:4px;">

            <button type="submit" style="background:#bbae97; color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-top:10px;">Guardar</button>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Eliminar Contribución</h2>
        <p>¿Estás seguro de que deseas eliminar esta contribución?</p>
        <form method="POST" action="<?= u('contribution/list') ?>">
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
        const table = document.getElementById('tablaContribuciones');
        if (!table) {
            console.error('Table not found');
            return;
        }

        const tbody = table.querySelector('tbody');
        const allRows = Array.from(tbody.querySelectorAll('tr'));

        let currentPage = 1;
        const pageSizeSelect = document.getElementById('pageSize');
        let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '20', 10);

        allRows.forEach(tr => tr.dataset.match = '1');

        function filteredRows() {
            return allRows.filter(tr => tr.dataset.match !== '0');
        }

        function totalPages() {
            const total = filteredRows().length;
            return Math.max(1, Math.ceil(total / pageSize));
        }

        function clampPage() {
            const tp = totalPages();
            if (currentPage > tp) currentPage = tp;
            if (currentPage < 1) currentPage = 1;
        }

        function render() {
            clampPage();
            const fr = filteredRows();
            const start = (currentPage - 1) * pageSize;
            const end = start + pageSize;

            allRows.forEach(tr => tr.style.display = 'none');
            fr.slice(start, end).forEach(tr => tr.style.display = '');

            const totalEl = document.getElementById('totalContribuciones');
            if (totalEl) totalEl.textContent = String(fr.length);

            const pageInfo = document.getElementById('pageInfo');
            if (pageInfo) pageInfo.textContent = `Página ${currentPage} de ${totalPages()} (${fr.length} registros)`;
        }

        if (input) {
            input.addEventListener('input', function() {
                const term = this.value.trim().toLowerCase();
                allRows.forEach(tr => {
                    const ok = tr.textContent.toLowerCase().includes(term);
                    tr.dataset.match = ok ? '1' : '0';
                });
                currentPage = 1;
                render();
            });
        }

        function goFirst() { currentPage = 1; render(); }
        function goPrev() { currentPage -= 1; render(); }
        function goNext() { currentPage += 1; render(); }
        function goLast() { currentPage = totalPages(); render(); }

        const btnFirst = document.getElementById('firstPage');
        const btnPrev = document.getElementById('prevPage');
        const btnNext = document.getElementById('nextPage');
        const btnLast = document.getElementById('lastPage');

        if (btnFirst) btnFirst.addEventListener('click', goFirst);
        if (btnPrev) btnPrev.addEventListener('click', goPrev);
        if (btnNext) btnNext.addEventListener('click', goNext);
        if (btnLast) btnLast.addEventListener('click', goLast);

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function() {
                pageSize = parseInt(this.value, 10) || 20;
                currentPage = 1;
                render();
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
                    const amount = btn.getAttribute('data-amount');
                    const monthYear = btn.getAttribute('data-monthyear');
                    const notes = btn.getAttribute('data-notes');
                    document.getElementById('updateId').value = id;
                    document.getElementById('updateAmount').value = amount;
                    document.getElementById('updateMonthYear').textContent = monthYear;
                    document.getElementById('updateNotes').textContent = notes;
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

        // Initial render
        render();
    });
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';