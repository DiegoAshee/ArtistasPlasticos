<?php
// app/Views/users/list.php

// === Configuración inicial ===
if (!function_exists('u')) {
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

$title = 'Lista de Administradores';
$currentPath = 'users'; // ← ESTA ES LA CLAVE: debe coincidir con la URL del menú
$breadcrumbs = [
    ['label' => 'Principal', 'url' => u('dashboard')],
    ['label' => 'Administradores', 'url' => null],
];

// Datos de sesión
$sessionUser = $_SESSION['username'] ?? 'Usuario';
$sessionEmail = $_SESSION['email'] ?? '';
$roleId = (int)($_SESSION['role'] ?? 0);

// Métricas
$totalUsuarios = is_array($users ?? []) ? count($users) : 0;
$usuariosActivos = 0;

if (!empty($users) && is_array($users)) {
    foreach ($users as $u) {
        if (!isset($u['active']) || !empty($u['active'])) {
            $usuariosActivos++;
        }
    }
}

// === Contenido de la página ===
ob_start();
?>

<style>
    .modern-table th, .modern-table td { 
        padding: 12px 16px; 
        line-height: 1.4; 
        vertical-align: middle; 
    }
    
    .modern-table { 
        border-collapse: separate; 
        border-spacing: 0; 
        width: 100%; 
    }
    
    .modern-table thead th { 
        position: sticky; 
        top: 0; 
        background: var(--cream-500, #bbae97); 
        color: #2a2a2a; 
        z-index: 2; 
        font-weight: 700; 
        border-bottom: 2px solid rgba(255,255,255,.3); 
    }
    
    .modern-table tbody tr { 
        background: var(--cream-200, #d7cbb5); 
        transition: all 0.2s ease;
    }
    
    .modern-table tbody tr:nth-child(even) { 
        background: var(--cream-300, #dccaaf); 
    }
    
    .modern-table tbody tr:hover { 
        background: var(--cream-400, #e8dcc0); 
        transform: translateY(-1px); 
    }
    
    .modern-table tbody tr td:first-child { 
        border-top-left-radius: 8px; 
        border-bottom-left-radius: 8px; 
    }
    
    .modern-table tbody tr td:last-child { 
        border-top-right-radius: 8px; 
        border-bottom-right-radius: 8px; 
    }
    
    .table-container { 
        background: var(--cream-100, #cfc4b0); 
        border-radius: 16px; 
        box-shadow: 0 10px 30px rgba(0,0,0,.08); 
        overflow: hidden; 
        border: 1px solid rgba(255,255,255,.2); 
        margin-top: 20px;
    }
    
    .search-input {
        width: 100%;
        border: 2px solid #e1e5e9;
        border-radius: 12px;
        padding: 12px 40px 12px 38px;
        outline: none;
        background: #fff;
        transition: border-color .2s, box-shadow .2s;
    }
    
    .search-input:focus {
        border-color: var(--cream-400);
        box-shadow: 0 0 0 3px rgba(184,155,99,.1);
    }
    
    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--cream-600);
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        text-decoration: none;
        font-weight: 600;
        transition: background .2s;
    }
    
    .btn-primary:hover {
        background: var(--cream-700);
    }
    
    .user-avatar-small {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--cream-600) 0%, var(--cream-500) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
        font-size: 14px;
    }
    
    .admin-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
    }
    
    .date-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 6px;
        background: rgba(255,255,255,.4);
        font-size: 12px;
    }
    
    .status-badge.active {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 12px;
        background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);
        color: #fff;
        font-size: 12px;
        font-weight: 600;
    }
    
    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        justify-content: space-between;
        padding: 16px 20px;
        background: rgba(255,255,255,.3);
        border-top: 1px solid rgba(255,255,255,.5);
    }
    
    .page-btn {
        border: 1px solid #cfcfcf;
        border-radius: 6px;
        padding: 8px 12px;
        background: #fff;
        cursor: pointer;
    }
    
    .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<!-- Barra de herramientas -->
<div class="toolbar" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px;">
    <div class="search-container" style="position: relative; flex: 1 1 320px;">
        <i class="fas fa-search search-icon" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); opacity: .6;"></i>
        <input id="searchInput" type="text" placeholder="Buscar por login, email..." class="search-input">
    </div>

    <div style="display: flex; gap: 10px;">
        <a href="<?= u('users/create') ?>" class="btn-primary">
            <i class="fas fa-plus"></i> Nuevo Usuario
        </a>
        <a href="<?= u('users/list') ?>" class="btn-primary" style="background: #6c757d;">
            <i class="fas fa-sync"></i> Actualizar
        </a>
    </div>
</div>

<!-- Métricas -->
<!-- <div class="dashboard-cards" style="margin-bottom: 20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-icon primary"><i class="fas fa-user-shield"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Total Usuarios</div>
            <div class="card-value" id="totalUsuarios"><?= (int)$totalUsuarios ?></div>
            <div class="card-change positive">
                <i class="fas fa-arrow-up"></i>
                <span>Administradores</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon success"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Usuarios Activos</div>
            <div class="card-value"><?= (int)$usuariosActivos ?></div>
            <div class="card-change">
                <i class="fas fa-users"></i>
                <span>En sistema</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon warning"><i class="fas fa-tools"></i></div>
        </div>
        <div class="card-content">
            <div class="card-title">Accesos rápidos</div>
            <div class="card-value">
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <a href="<?= u('dashboard') ?>" class="dropdown-item" style="padding: 6px 10px; border-radius: 8px; background: rgba(255,255,255,.3); text-decoration: none; font-size: 12px;">
                        <i class="fas fa-chart-pie"></i> Ir al Dashboard
                    </a>
                    <a href="<?= u('partner/list') ?>" class="dropdown-item" style="padding: 6px 10px; border-radius: 8px; background: rgba(255,255,255,.3); text-decoration: none; font-size: 12px;">
                        <i class="fas fa-users"></i> Ver Socios
                    </a>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Tabla de usuarios -->
<?php if (!empty($users) && is_array($users)): ?>
    <div class="table-container">
        <table id="tablaUsuarios" class="modern-table">
            <thead>
                <tr>
                    <th><i class="fas fa-user"></i> Usuario</th>
                    <th><i class="fas fa-envelope"></i> Email</th>
                    <th><i class="fas fa-shield-alt"></i> Rol</th>
                    <th><i class="fas fa-calendar-plus"></i> Fecha Creación</th>
                    <th><i class="fas fa-signal"></i> Estado</th>
                    <th><i class="fas fa-cogs"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $usuario): ?>
                    <tr>
                        <td>
                            <div class="user-cell" style="display: flex; align-items: center; gap: 12px;">
                                <div class="user-avatar-small">
                                    <?= strtoupper(substr($usuario['login'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight: 600; margin-bottom: 2px;"><?= htmlspecialchars($usuario['login'] ?? '') ?></div>
                                    <div class="admin-badge">ADMIN</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="user-badge" style="display: inline-flex; align-items: center; gap: 8px; padding: 4px 8px; border-radius: 6px; background: rgba(255,255,255,.3); font-size: 13px; font-weight: 500;">
                                <i class="fas fa-envelope" style="opacity: .7;"></i>
                                <?= htmlspecialchars($usuario['email'] ?? '') ?>
                            </span>
                        </td>
                        <td>
                            <span class="role-badge">
                                <i class="fas fa-crown"></i> Administrador
                            </span>
                        </td>
                        <td>
                            <span class="date-badge">
                                <i class="fas fa-calendar"></i>
                                <?php 
                                    $dc = $usuario['dateCreated'] ?? $usuario['created_at'] ?? null;
                                    echo $dc ? date('d/m/Y', strtotime($dc)) : '-';
                                ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge active">
                                <i class="fas fa-circle" style="font-size: 6px;"></i> Activo
                            </span>
                        </td>
                        <td class="actions">
                            <div class="action-buttons" style="display: flex; gap: 8px;">
                                <a href="<?= u('users/edit/' . urlencode((string)($usuario['idUser'] ?? ''))) ?>" 
                                   class="btn btn-sm btn-outline" 
                                   title="Editar" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e1e5e9; color: #333; text-decoration: none; background: #fff;">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= u('users/view/' . urlencode((string)($usuario['idUser'] ?? ''))) ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="Ver" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: #3498db; color: #fff; text-decoration: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= u('users/delete/' . urlencode((string)($usuario['idUser'] ?? ''))) ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Eliminar" 
                                   onclick="return confirm('¿Seguro que desea eliminar este usuario?');" 
                                   style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; background: #e74c3c; color: #fff; text-decoration: none;">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Paginación -->
        <div class="pagination-controls">
            <div style="display: flex; align-items: center; gap: 10px;">
                <label for="pageSize" style="font-weight: 600; color: #555;">Por página:</label>
                <select id="pageSize" style="border: 1px solid #cfcfcf; border-radius: 8px; padding: 6px 10px; background: #fff;">
                    <option value="10">10</option>
                    <option value="20" selected>20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div id="pageInfo" style="font-weight: 600; color: #555; text-align: center; min-width: 200px;"></div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <button id="firstPage" class="page-btn">«</button>
                <button id="prevPage" class="page-btn">‹</button>
                <button id="nextPage" class="page-btn">›</button>
                <button id="lastPage" class="page-btn">»</button>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state" style="text-align: center; padding: 60px 20px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.06);">
        <div class="empty-state-icon" style="font-size: 48px; margin-bottom: 15px; color: var(--cream-600);">
            <i class="fas fa-user-shield"></i>
        </div>
        <h3 style="margin: 0 0 10px 0; color: #555;">No hay usuarios administradores</h3>
        <p style="margin: 0 0 25px 0; color: #777;">Comienza agregando tu primer usuario administrador</p>
        <a href="<?= u('users/create') ?>" class="btn-primary">
            <i class="fas fa-plus"></i> Crear Primer Usuario
        </a>
    </div>
<?php endif; ?>

<!-- JavaScript para búsqueda y paginación -->
<script>
    (function(){
        const table = document.getElementById('tablaUsuarios');
        const input = document.getElementById('searchInput');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const allRows = Array.from(tbody.querySelectorAll('tr'));

        let currentPage = 1;
        const pageSizeSelect = document.getElementById('pageSize');
        let pageSize = parseInt(pageSizeSelect ? pageSizeSelect.value : '20', 10);

        allRows.forEach(tr => tr.dataset.match = '1');

        function filteredRows(){ 
            return allRows.filter(tr => tr.dataset.match !== '0'); 
        }
        
        function totalPages(){ 
            const t = filteredRows().length; 
            return Math.max(1, Math.ceil(t / pageSize)); 
        }
        
        function clamp(){ 
            const tp = totalPages(); 
            if (currentPage > tp) currentPage = tp; 
            if (currentPage < 1) currentPage = 1; 
        }

        function render(){
            clamp();
            const fr = filteredRows();
            const start = (currentPage - 1) * pageSize;
            const end   = start + pageSize;

            allRows.forEach(tr => tr.style.display = 'none');
            fr.slice(start, end).forEach(tr => tr.style.display = '');

            const totalEl = document.getElementById('totalUsuarios'); 
            if (totalEl) totalEl.textContent = String(fr.length);
            
            const pageInfo = document.getElementById('pageInfo'); 
            if (pageInfo) pageInfo.textContent = `Página ${currentPage} de ${totalPages()} (${fr.length} registros)`;

            const btnFirst = document.getElementById('firstPage');
            const btnPrev  = document.getElementById('prevPage');
            const btnNext  = document.getElementById('nextPage');
            const btnLast  = document.getElementById('lastPage');

            if (btnFirst) btnFirst.disabled = (currentPage === 1);
            if (btnPrev)  btnPrev.disabled  = (currentPage === 1);
            if (btnNext)  btnNext.disabled  = (currentPage === totalPages());
            if (btnLast)  btnLast.disabled  = (currentPage === totalPages());
        }

        if (input) {
            input.addEventListener('input', function(){
                const term = this.value.trim().toLowerCase();
                allRows.forEach(tr => {
                    tr.dataset.match = tr.textContent.toLowerCase().includes(term) ? '1' : '0';
                });
                currentPage = 1; 
                render();
            });
        }

        const btnFirst = document.getElementById('firstPage');
        const btnPrev  = document.getElementById('prevPage');
        const btnNext  = document.getElementById('nextPage');
        const btnLast  = document.getElementById('lastPage');

        if (btnFirst) btnFirst.addEventListener('click', () => { currentPage = 1; render(); });
        if (btnPrev)  btnPrev.addEventListener('click',  () => { currentPage -= 1; render(); });
        if (btnNext)  btnNext.addEventListener('click',  () => { currentPage += 1; render(); });
        if (btnLast)  btnLast.addEventListener('click',  () => { currentPage = totalPages(); render(); });

        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function(){
                pageSize = parseInt(this.value, 10) || 20;
                currentPage = 1; 
                render();
            });
        }

        render();
    })();
</script>

<?php
$content = ob_get_clean();

// === Incluir layout principal ===
include __DIR__ . '/../layouts/app.php';