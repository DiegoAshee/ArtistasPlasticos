<?php
// ===== Helpers portables (si ya existen globales, elimina esto) =====
if (!function_exists('u')) {
  function u(string $path = ''): string {
    $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
    return $base . '/' . ltrim($path, '/');
  }
}
if (!function_exists('asset')) {
  function asset(string $path): string { return u($path); }
}

// ===== Datos de sesión =====
$sessionUser  = (string)($_SESSION['username'] ?? 'Usuario');
$sessionEmail = (string)($_SESSION['email']    ?? '');
$roleId       = (int)   ($_SESSION['role']     ?? 2); // 1=Admin, 2=Partner

// ===== Variables de vista =====
$pageTitle   = isset($title) ? (string)$title : 'Panel';
$currentPath = isset($currentPath) ? (string)$currentPath : '';  // p.ej. 'dashboard'
$breadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) ? $breadcrumbs : [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => $pageTitle, 'url' => null],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> - Asociación de Artistas</title>
  <meta name="description" content="Panel de control - Asociación de Artistas" />
  <meta name="theme-color" content="#9c8f7a" />

  <!-- Fuentes / Iconos / CSS -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <link rel="icon" type="image/x-icon" href="<?= asset('assets/images/favicon.ico') ?>" />
  <link rel="stylesheet" href="<?= asset('assets/css/dashboard.css') ?>" />

  <!-- Estilos mínimos para dropdown y capas -->
  <style>
    body:not(.loaded){visibility:hidden;}
    .top-bar{position:relative;z-index:10040;overflow:visible;}
    #overlay{pointer-events:none;}
    #overlay.show{pointer-events:auto;}

    /* Usuario + dropdown */
    .user-menu{position:relative;overflow:visible !important;display:flex;align-items:center;gap:12px;cursor:pointer;z-index:10060;}
    .user-info{display:flex;flex-direction:column;line-height:1.1;}
    .user-name{font-weight:600;}
    .user-role{font-size:.85rem;opacity:.8;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:#9c8f7a;color:#fff;font-weight:700;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,.08);}
    .user-dropdown{display:none;position:absolute;right:0;top:calc(100% + 10px);min-width:240px;padding:10px;border-radius:16px;background:rgba(214,194,160,.92);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.35);box-shadow:0 10px 30px rgba(33,24,13,.18);z-index:10080;}
    .user-menu.open>.user-dropdown{display:block !important;}
    .dropdown-header{padding:8px 12px 12px;border-bottom:1px solid rgba(255,255,255,.25);margin-bottom:6px;}
    .dropdown-user-name{color:#fff;font-weight:700;line-height:1.2;}
    .dropdown-user-email{color:#fff;opacity:.9;font-size:.9rem;}
    .dropdown-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:#2f2a24;}
    .dropdown-item:hover{background:rgba(255,255,255,.25);}
    .dropdown-divider{height:1px;background:rgba(255,255,255,.28);margin:8px 0;}
    .dropdown-item.logout{color:#e74c3c;font-weight:600;}
    .dropdown-item.logout:hover{background:rgba(231,76,60,.12);}

    /* Logo sidebar */
    .logo img{max-width:64px;max-height:64px;margin-bottom:8px;border-radius:12px;box-shadow:0 2px 8px #e1e5e9;background:#fff;padding:6px;}
  </style>

  <script>document.addEventListener('DOMContentLoaded',()=>document.body.classList.add('loaded'));</script>
</head>
<body class="cream-theme">
  <!-- Overlay para sidebar móvil -->
  <div class="overlay" id="overlay"></div>

  <!-- Sidebar reutilizable -->
  <aside class="sidebar" id="sidebar">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>
  </aside>

  <!-- Área principal -->
  <main class="main-content">
    <!-- Topbar reutilizable -->
    <header class="top-bar">
      <?php include __DIR__ . '/../partials/topbar.php'; ?>
    </header>

    <!-- Contenido específico de cada página -->
    <div class="content-wrapper">
      <?= $content ?? '' ?>
    </div>
  </main>

  <!-- Toasts -->
  <div id="toastContainer" style="position:fixed;top:20px;right:20px;z-index:10001;"></div>

  <!-- Scripts comunes -->
  <script>
    // Toggle dropdown usuario (robusto y reutilizable)
    document.addEventListener('DOMContentLoaded', function () {
      const userMenu = document.getElementById('userMenu');
      const drop     = document.getElementById('userDropdown');
      if (!userMenu || !drop) return;

      const open  = () => { userMenu.classList.add('open'); drop.style.display='block'; drop.setAttribute('aria-expanded','true');  drop.setAttribute('aria-hidden','false'); };
      const close = () => { userMenu.classList.remove('open'); drop.style.display='none';  drop.setAttribute('aria-expanded','false'); drop.setAttribute('aria-hidden','true'); };
      const toggle = (e) => { e && e.stopPropagation(); userMenu.classList.contains('open') ? close() : open(); };

      // Click en avatar/nombre/rol => toggle
      userMenu.addEventListener('click', (e)=>{ if (e.target.closest('.user-dropdown')) return; toggle(e); });

      // Cierra al clicar enlaces del dropdown
      drop.addEventListener('click', (e)=>{ if (e.target.closest('a')) close(); });

      // Cierra al clicar fuera
      document.addEventListener('click', (e)=>{ if (!userMenu.contains(e.target)) close(); });

      // Cierra con ESC
      document.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') close(); });

      // Accesible con teclado
      userMenu.addEventListener('keydown', (e)=>{ if (e.key === 'Enter' || e.key === ' ') toggle(e); });

      // Estado inicial SIEMPRE cerrado
      close();
    });

    // (Opcional) Toggle sidebar móvil si no lo tienes ya en dashboard.js
    document.addEventListener('DOMContentLoaded', function(){
      const btn = document.getElementById('menuToggle');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('overlay');
      if (!btn || !sidebar || !overlay) return;

      btn.addEventListener('click', function(){
        sidebar.classList.toggle('open');
        overlay.classList.toggle('show');
      });
      overlay.addEventListener('click', function(){
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
      });
    });

    function logout(){ window.location.href = "<?= u('logout') ?>"; }
  </script>

  <script src="<?= asset('assets/js/dashboard.js') ?>" defer></script>
</body>
</html>
