<?php
$dias = array(
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
);

$meses = array(
    'January' => 'enero',
    'February' => 'febrero',
    'March' => 'marzo',
    'April' => 'abril',
    'May' => 'mayo',
    'June' => 'junio',
    'July' => 'julio',
    'August' => 'agosto',
    'September' => 'septiembre',
    'October' => 'octubre',
    'November' => 'noviembre',
    'December' => 'diciembre'
);

$dia_ingles = date('l');
$mes_ingles = date('F');
$dia_numero = date('j');
$anio = date('Y');
?>
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
<div class="dashboard-container">
  <!-- Header de Bienvenida -->
  <div class="welcome-header">
    <div class="welcome-text">
      <h1>¡Bienvenido a <span class="brand-name"><?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?></span>! 👋</h1>
      <p class="welcome-subtitle">Estamos encantados de tenerte aquí</p>
    </div>
    <div class="date-display">
      <div class="current-date"><?= $dias[$dia_ingles] . ', ' . $dia_numero . ' de ' . $meses[$mes_ingles] . ' de ' . $anio ?></div>
      <div class="current-time" id="currentTime"><?= date('h:i A') ?></div>
    </div>
  </div>

  <!-- Contenido Principal -->
  <div class="dashboard-content">
    <!-- Logo y Información -->
    <div class="company-card">
      <div class="company-logo">
        <img src="<?= asset($logo_url) ?>" alt="Logo <?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="company-info">
        <h2><?= htmlspecialchars($site_title, ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="company-description">Conéctate con nosotros a través de nuestras redes sociales</p>
        <div class="social-actions">
          <button class="social-btn whatsapp" onclick="window.open('https://wa.me/59170468813', '_blank')">
            <span class="social-icon">💚</span>
            WhatsApp
          </button>
          <button class="social-btn facebook" onclick="window.open('https://www.facebook.com/p/ABAP-Cochabamba-61577886166633/', '_blank')">
            <span class="social-icon">📘</span>
            Facebook
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

/* Header de Bienvenida - Colores café crema */
.welcome-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 30px;
  background: linear-gradient(135deg, #8B6B61 0%, #A78A7F 100%);
  color: white;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(139, 107, 97, 0.2);
}

.welcome-text h1 {
  margin: 0 0 8px 0;
  font-size: 28px;
  font-weight: 600;
}

.brand-name {
  color: #FFF8E1; /* Crema claro */
  text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.welcome-subtitle {
  margin: 0;
  opacity: 0.9;
  font-size: 16px;
}

.date-display {
  text-align: right;
}

.current-date {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 4px;
}

.current-time {
  font-size: 24px;
  font-weight: 700;
  color: #FFF8E1; /* Crema claro */
}

/* Contenido Principal */
.dashboard-content {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.company-card {
  background: white;
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 2px 10px rgba(139, 107, 97, 0.1);
  text-align: center;
  max-width: 500px;
  width: 100%;
  border: 1px solid #F5E6D3; /* Borde café claro */
}

.company-logo img {
  width: 120px;
  height: auto;
  margin-bottom: 20px;
}

.company-info h2 {
  margin: 0 0 10px 0;
  color: #5D4037; /* Café oscuro */
  font-size: 24px;
}

.company-description {
  color: #8D6E63; /* Café medio */
  margin-bottom: 30px;
  font-size: 16px;
  line-height: 1.5;
}

.social-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  flex-wrap: wrap;
}

.social-btn {
  padding: 12px 25px;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  color: white;
  min-width: 140px;
  justify-content: center;
}

.social-btn.whatsapp {
  background: #25D366;
}

.social-btn.facebook {
  background: #1877F2;
}

.social-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 107, 97, 0.3);
}

.social-btn.whatsapp:hover {
  background: #128C7E;
}

.social-btn.facebook:hover {
  background: #0D5FAB;
}

.social-icon {
  font-size: 18px;
}

/* Responsive */
@media (max-width: 768px) {
  .welcome-header {
    flex-direction: column;
    text-align: center;
  }
  
  .date-display {
    text-align: center;
    margin-top: 15px;
  }
  
  .social-actions {
    flex-direction: column;
    align-items: center;
  }
  
  .social-btn {
    width: 100%;
    max-width: 200px;
  }
  
  .company-card {
    padding: 30px 20px;
  }
}
</style>

<script>
// Actualizar la hora en tiempo real
function updateTime() {
  const now = new Date();
  const timeElement = document.getElementById('currentTime');
  timeElement.textContent = now.toLocaleTimeString('es-ES', { 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: true 
  });
}

setInterval(updateTime, 1000);
</script>
<?php
$content = ob_get_clean();

// ---- Incluir layout principal ----
include __DIR__ . '/layouts/app.php';