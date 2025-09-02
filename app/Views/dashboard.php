<?php
// app/Views/dashboard.php

$title       = 'Dashboard';
$currentPath = 'dashboard'; // para marcar activo en el menú
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Dashboard', 'url' => null],
];

// ---- Contenido específico de la página ----
ob_start();
?>
  <div class="dashboard-cards">
    <div class="card">
      <div class="card-header">
        <div class="card-icon success"><i class="fas fa-users"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Total Socios</div>
        <div class="card-value" id="totalSocios">0</div>
        <div class="card-change positive">
          <i class="fas fa-arrow-up"></i>
          <span id="totalSociosChange">Cargando...</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon primary"><i class="fas fa-check-circle"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Al Día</div>
        <div class="card-value" id="sociosAlDia">0</div>
        <div class="card-change positive">
          <i class="fas fa-arrow-up"></i>
          <span id="sociosAlDiaChange">Cargando...</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon error"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">En Deuda</div>
        <div class="card-value" id="sociosEnDeuda">0</div>
        <div class="card-change negative">
          <i class="fas fa-arrow-down"></i>
          <span id="sociosEnDeudaChange">Cargando...</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <div class="card-icon warning"><i class="fas fa-calendar-alt"></i></div>
        <div class="card-menu"><i class="fas fa-ellipsis-v"></i></div>
      </div>
      <div class="card-content">
        <div class="card-title">Pendientes Hoy</div>
        <div class="card-value" id="pendientesHoy">0</div>
        <div class="card-change">
          <i class="fas fa-clock"></i>
          <span id="pendientesHoyChange">Cargando...</span>
        </div>
      </div>
    </div>
  </div>

  <div class="recent-activity">
    <div class="activity-header">
      <h2>Actividad Reciente</h2>
      <button class="menu-toggle" onclick="loadRecentActivities()" title="Actualizar actividades">
        <i class="fas fa-sync-alt"></i>
      </button>
    </div>
    <div class="activity-list" id="activityList">
      <div class="activity-item">
        <div class="activity-icon"><div class="loading"></div></div>
        <div class="activity-details">
          <div class="activity-text">Cargando actividades recientes...</div>
          <div class="activity-time">Un momento por favor</div>
        </div>
      </div>
    </div>
    <div style="text-align:center;margin-top:24px;padding-top:24px;border-top:1px solid var(--border);">
      <a href="<?= u('actividades') ?>" class="dropdown-item" style="display:inline-flex;padding:12px 24px;border-radius:12px;background:var(--surface-elevated);color:var(--accent-primary);text-decoration:none;font-weight:500;">
        <i class="fas fa-external-link-alt"></i>
        <span style="margin-left:8px;">Ver todas las actividades</span>
      </a>
    </div>
  </div>
<?php
$content = ob_get_clean();

// ---- Incluir layout principal ----
include __DIR__ . '/layouts/app.php';
