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
  <p class="tagline"><?= htmlspecialchars($tagline, ENT_QUOTES, 'UTF-8') ?></p>
</div>

<nav class="nav-menu">
  <?php if (!empty($grouped)): ?>
    <?php foreach ($grouped as $sectionName => $items): ?>
      <div class="nav-section">
        <div class="nav-section-title"><?= htmlspecialchars($sectionName, ENT_QUOTES, 'UTF-8') ?></div>

        <?php foreach ($items as $item): ?>
          <?php
            $name = (string)($item['name'] ?? 'Opción');
            $url  = (string)($item['url']  ?? '#');          // relativo
            $icon = (string)($item['icon'] ?? 'fas fa-circle');
            $href = u($url);
            $active = ($currentPath !== '' && $url !== '' && $currentPath === $url) ? ' active' : '';
          ?>
          <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>" class="nav-item<?= $active ?>">
            <i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i>
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
