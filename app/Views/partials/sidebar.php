<?php
// Helpers defensivos (si ya existen globalmente, puedes quitarlos)
if (!function_exists('u')) {
    function u(string $path=''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}

/**
 * Mapear nombres de menú a iconos por código (sin tocar la base de datos).
 * Devuelve cadena vacía si no debe mostrarse icono.
 */
if (!function_exists('mapMenuNameToIcon')) {
  function mapMenuNameToIcon(string $name): string {
    $n = mb_strtolower(trim($name), 'UTF-8');
    switch ($n) {
      case 'dashboard':
        return 'fas fa-home';
      case 'analytics':
      case 'analitica':
      case 'analíticas':
      case 'analiticas':
        return 'fas fa-chart-line';
      case 'socios':
      case 'partners':
        return 'fas fa-users';
      case 'socio':
        // Caso especial: si el nombre es "socio" no mostramos icono
        return '';
      case 'nuevo socio':
      case 'alta socio':
        return 'fas fa-user-plus';
      case 'usuarios':
      case 'users':
        return 'fas fa-users';
      case 'contribución':
      case 'contribution':
        return 'fas fa-users';
      case 'permisos':
      case 'permissions':
        return 'fas fa-user-lock';
      case 'roles':
        return 'fas fa-user-shield';
      case 'historial pagos':
      case 'historial de pagos':
        return 'fas fa-history';
      case 'reportes':
      case 'reporting':
        return 'fas fa-file-invoice';
      case 'configuracion':
      case 'configuración':
      case 'ajustes':
      case 'settings':
        return 'fas fa-cog';
      case 'backup':
      case 'respaldo':
        return 'fas fa-database';
      case 'mi perfil':
      case 'perfil':
      case 'profile':
        return 'fas fa-user';
      case 'ayuda':
      case 'help':
        return 'fas fa-question-circle';
      case 'mis pagos':
      case 'pagos':
        return 'fas fa-file-invoice';
      case 'pagos pendientes':
        return 'fas fa-file-invoice';
      case 'competencias':
        return 'fas fa-cogs';
      case 'cobros':
      case 'recibos':
      case 'ingresos':
        return 'fas fa-receipt';
      case 'conceptos':
        return 'fas fa-receipt';
      case 'opciones':
      case 'options':
        return 'fas fa-cog';
      default:
        return 'fas fa-circle';
    }
  }
}

// Normaliza datos que deberían venir del controlador
$menuOptions = $menuOptions ?? [];
$currentPath = $currentPath ?? '';

/** Agrupa por sección */
$grouped = [];
foreach ($menuOptions as $it) {
    $section = (string)($it['section'] ?? 'Menú');
    $grouped[$section][] = $it;
}
?>

<div class="logo">
  <img src="<?= asset($logo_url) ?>" alt="Logo <?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?>">
  <h2><?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?></h2>
  
</div>

<nav class="nav-menu">
  <?php if (!empty($grouped)): ?>
    <?php foreach ($grouped as $sectionName => $items): ?>
      <div class="nav-section">
        <div class="nav-section-title"><?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') ?></div>

        <?php foreach ($items as $item): ?>
          <?php
            $name = (string)($item['name'] ?? 'Opción');
            // Priorizar urlOption (guardada en la tabla `competence`) si existe,
            // luego fallback a 'url' (antiguo comportamiento), y por último '#'.
            $rawUrl = (string)($item['urlOption'] ?? $item['url'] ?? '');
      // Icono provisto por la BD (puede ser 'none' o cadena vacía para ocultarlo)
      $iconRaw = $item['icon'] ?? null;
      if (is_string($iconRaw)) { $iconRaw = trim($iconRaw); }
      if ($iconRaw === null || $iconRaw === '') {
        // Derivar icono por nombre (mapeo en código)
        $icon = mapMenuNameToIcon($name);
      } elseif (strtolower((string)$iconRaw) === 'none') {
        // Token especial para ocultar icono
        $icon = '';
      } else {
        $icon = (string)$iconRaw;
      }

            // Normalizar y decidir href:
            // - Si es vacío o '#', usar '#'.
            // - Si es absoluta (http:// o https://) usarla tal cual.
            // - Si es relativa (p.ej '/dashboard' o 'dashboard'), pasar por u() después de limpiar la barra.
            if ($rawUrl === '' || $rawUrl === '#') {
                $href = '#';
                $urlForActive = '';
            } elseif (preg_match('#^https?://#i', $rawUrl)) {
                $href = $rawUrl; // URL absoluta
                // Para la detección de activo, extraemos la ruta relativa si es del mismo host
                $urlForActive = ltrim(parse_url($rawUrl, PHP_URL_PATH) ?: '', '/');
            } else {
                // ruta relativa: eliminar slash inicial para que u() la normalice correctamente
                $urlForActive = ltrim($rawUrl, '/');
                $href = u($urlForActive);
            }

            $active = ($currentPath !== '' && $urlForActive !== '' && $currentPath === $urlForActive) ? ' active' : '';
          ?>
          <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="nav-item<?= $active ?>">
            <?php if ($icon !== ''): ?>
              <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
            <?php endif; ?>
            <span><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <!-- Fallback visual mínimo -->
    <div class="nav-section">
      <div class="nav-section-title">Principal</div>
      <a href="<?= u('dashboard') ?>" class="nav-item<?= ($currentPath==='dashboard'?' active':'') ?>">
        <i class="fas fa-home"></i><span>Dashboard</span>
      </a>
    </div>
  <?php endif; ?>
</nav>
