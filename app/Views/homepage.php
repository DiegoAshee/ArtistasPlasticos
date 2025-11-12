<?php
$dias = [
  'Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
  'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'
];
$meses = [
  'January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril',
  'May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto',
  'September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'
];
 
$dia_ingles = date('l');
$mes_ingles = date('F');
$dia_numero = date('j');
$anio       = date('Y');
 
/**
 * WhatsApp dinámico:
 * - Toma primero $telephoneContact (inyectado por el controlador)
 * - Si no, intenta $activeOption['telephoneContact']
 * - SIN fallback duro (si no hay, se deshabilita el botón)
 */
$rawPhone = $telephoneContact
  ?? ($activeOption['telephoneContact'] ?? '')
  ?? '';
 
$digits = preg_replace('/\D+/', '', (string)$rawPhone); // solo dígitos
if ($digits !== '') {
  if (strpos($digits, '591') !== 0) {
    $digits = '591' . ltrim($digits, '0'); // agrega prefijo BO si falta
  }
}
$site  = $site_title ?? 'Asociación';
$msg   = rawurlencode("Hola $site, necesito información. Gracias.");
$waHref = $digits
  ? "https://api.whatsapp.com/send/?phone={$digits}&text={$msg}&type=phone_number&app_absent=0"
  : null;
?>
 
<?php
// app/Views/homepage.php
$title = $title ?? 'Dashboard';
$currentPath = $currentPath ?? 'homepage';
 
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
          <?php if ($waHref): ?>
            <a class="social-btn whatsapp" href="<?= $waHref ?>" target="_blank" rel="noopener">
              <span class="social-icon">💚</span>
              WhatsApp
            </a>
          <?php else: ?>
            <button class="social-btn whatsapp" type="button" disabled title="Teléfono no configurado">
              <span class="social-icon">💚</span>
              WhatsApp
            </button>
          <?php endif; ?>
 
          <a class="social-btn facebook"
             href="https://www.facebook.com/p/ABAP-Cochabamba-61577886166633/"
             target="_blank" rel="noopener">
            <span class="social-icon">📘</span>
            Facebook
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
 
<style>
.dashboard-container{max-width:1200px;margin:0 auto;padding:20px;}
.welcome-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:30px;
  background:linear-gradient(135deg,#8B6B61 0%,#A78A7F 100%);color:#fff;padding:30px;border-radius:15px;
  box-shadow:0 4px 20px rgba(139,107,97,.2);}
.welcome-text h1{margin:0 0 8px 0;font-size:28px;font-weight:600;}
.brand-name{color:#FFF8E1;text-shadow:0 1px 2px rgba(0,0,0,.1);}
.welcome-subtitle{margin:0;opacity:.9;font-size:16px;}
.date-display{text-align:right;}
.current-date{font-size:18px;font-weight:600;margin-bottom:4px;}
.current-time{font-size:24px;font-weight:700;color:#FFF8E1;}
.dashboard-content{display:flex;justify-content:center;align-items:center;min-height:400px;}
.company-card{background:#fff;padding:40px;border-radius:15px;box-shadow:0 2px 10px rgba(139,107,97,.1);
  text-align:center;max-width:500px;width:100%;border:1px solid #F5E6D3;}
.company-logo img{width:120px;height:auto;margin-bottom:20px;}
.company-info h2{margin:0 0 10px 0;color:#5D4037;font-size:24px;}
.company-description{color:#8D6E63;margin-bottom:30px;font-size:16px;line-height:1.5;}
.social-actions{display:flex;gap:15px;justify-content:center;flex-wrap:wrap;}
.social-btn{padding:12px 25px;border:none;border-radius:10px;font-weight:600;cursor:pointer;transition:all .3s ease;
  display:flex;align-items:center;gap:8px;font-size:16px;color:#fff;min-width:140px;justify-content:center;text-decoration:none;}
.social-btn.whatsapp{background:#25D366;}
.social-btn.facebook{background:#1877F2;}
.social-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(139,107,97,.3);}
.social-btn.whatsapp:hover{background:#128C7E;}
.social-btn.facebook:hover{background:#0D5FAB;}
.social-btn[disabled]{opacity:.55;cursor:not-allowed;transform:none;box-shadow:none;}
.social-icon{font-size:18px;}
@media (max-width:768px){
  .welcome-header{flex-direction:column;text-align:center;}
  .date-display{text-align:center;margin-top:15px;}
  .social-actions{flex-direction:column;align-items:center;}
  .social-btn{width:100%;max-width:200px;}
  .company-card{padding:30px 20px;}
}
</style>
 
<script>
function updateTime(){
  const now=new Date();
  const timeElement=document.getElementById('currentTime');
  timeElement.textContent = now.toLocaleTimeString('es-ES',{
    hour:'2-digit',minute:'2-digit',hour12:true
  });
}
setInterval(updateTime,1000);
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/app.php';