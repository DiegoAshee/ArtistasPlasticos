<?php
// Usa $pageTitle, $breadcrumbs, $sessionUser, $sessionEmail, $roleId
?>
<div class="menu-section">
  <button id="menuToggle" class="menu-toggle" aria-label="Alternar menú">
    <i class="fas fa-bars"></i>
  </button>
  <div>
    <h1 class="page-title"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <nav class="breadcrumb">
      <?php if (!empty($breadcrumbs)): ?>
        <?php foreach ($breadcrumbs as $i => $bc): ?>
          <?php if ($i > 0): ?><span class="breadcrumb-separator">/</span><?php endif; ?>
          <?php if (!empty($bc['url'])): ?>
            <a href="<?= htmlspecialchars((string)$bc['url'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars((string)$bc['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          <?php else: ?>
            <span><?= htmlspecialchars((string)$bc['label'], ENT_QUOTES, 'UTF-8') ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </nav>
  </div>
</div>

<div class="user-menu" id="userMenu" role="button" tabindex="0" aria-haspopup="true" aria-controls="userDropdown">
  <div class="user-info">
    <div class="user-name"><?= htmlspecialchars($sessionUser, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="user-role"><?= ($roleId === 1 ? 'Administrador' : 'Socio') ?></div>
  </div>
  <div class="user-avatar"><?= strtoupper(substr($sessionUser, 0, 2)) ?></div>

  <div class="user-dropdown" id="userDropdown" aria-expanded="false" aria-hidden="true">
    <div class="dropdown-header">
      <div class="dropdown-user-name"><?= htmlspecialchars($sessionUser, ENT_QUOTES, 'UTF-8') ?></div>
      <div class="dropdown-user-email"><?= htmlspecialchars($sessionEmail, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <a href="<?= u('users/profile') ?>" class="dropdown-item">
      <i class="fas fa-user"></i> Mi Perfil
    </a>
    <div class="dropdown-divider"></div>
    <a href="<?= u('logout') ?>" class="dropdown-item logout">
      <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
    </a>
  </div>
</div>
