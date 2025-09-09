<?php
// app/Views/cobros/create.php

$title       = 'Registrar Cobro';
$currentPath = 'cobros/create';
$breadcrumbs = [
  ['label' => 'Inicio',  'url' => u('dashboard')],
  ['label' => 'Cobros',  'url' => u('cobros/list')],
  ['label' => 'Nuevo',   'url' => null],
];

$partners = $partners ?? [];
$types    = $types ?? [];
// Ya no necesitamos $contribs como fallback porque solo mostraremos deudas

// Utilidad para etiquetar catálogos
$label = function(array $r, string $pref, string $idKey) {
  foreach (['label','name','title','description','notes','concept'] as $k) {
    if (!empty($r[$k])) return (string)$r[$k];
  }
  return $pref.' #'.(int)($r[$idKey] ?? 0);
};

ob_start();
?>
<style>
  #cobros-root a.btn, #cobros-root .btn{
    display:inline-flex; align-items:center; gap:8px;
    padding:10px 14px; border-radius:12px;
    border:1px solid #cfcfcf !important;
    background:#ffffff !important;
    color:#2a2a2a !important;
    text-decoration:none; line-height:1.2; font-weight:600; cursor:pointer;
  }
  #cobros-root .btn-primary{
    background:#6c757d !important; border-color:#6c757d !important; color:#fff !important;
  }
  #cobros-root .btn:hover{ filter:brightness(.98); }

  #cobros-root .field-help { font-size:12px; color:#555; margin-top:6px; }
  #cobros-root .error-text { color:#b91c1c; font-size:12px; margin-top:6px; display:none; }

  #cobros-root .card {
    background:#fff;border-radius:16px;padding:18px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);max-width:820px;
  }

  #cobros-root .select-disabled {
    background-color: #f8f9fa !important;
    color: #6c757d !important;
  }
</style>

<div id="cobros-root">
  <?php if (!empty($error)): ?>
    <div style="background:#f8d7da;color:#842029;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <form id="createCobroForm" action="<?= u('cobros/create') ?>" method="post" class="card">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">

      <!-- Socio (buscador) -->
      <div style="grid-column:1 / span 2;">
        <label for="partnerSearch">Quién paga (Socio)</label>
        <input id="partnerSearch" list="partnersList"
               placeholder="Escribe nombre o CI y elige de la lista…"
               autocomplete="off"
               style="width:100%;padding:10px;border-radius:10px;border:1px solid #ddd;">
        <datalist id="partnersList">
          <?php foreach ($partners as $s):
            $pid = (int)($s['idPartner'] ?? 0);
            $nm  = trim((string)($s['name'] ?? ''));
            $ci  = trim((string)($s['CI']   ?? ''));
            $display = $nm . ($ci !== '' ? (' — CI ' . $ci) : '');
          ?>
            <option value="<?= htmlspecialchars($display, ENT_QUOTES, 'UTF-8') ?>"></option>
          <?php endforeach; ?>
        </datalist>
        <input type="hidden" name="idPartner" id="idPartner">
        <div id="partnerHelp" class="field-help">
          Selecciona una opción del listado para fijar el socio. Esto habilitará la selección de tipo de pago.
        </div>
        <div id="partnerError" class="error-text">Debes seleccionar un socio válido de la lista.</div>
      </div>

      <!-- Tipo de pago -->
      <div>
        <label>Tipo de pago</label>
        <select name="idPaymentType" id="idPaymentType" required disabled 
                class="select-disabled"
                style="width:100%;padding:10px;border-radius:10px;border:1px solid #ddd;">
          <option value="">— Primero selecciona un socio —</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= (int)($t['idPaymentType'] ?? 0) ?>">
              <?= htmlspecialchars($label($t,'Tipo','idPaymentType'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="field-help">Al cambiar el tipo, se recalculan las deudas del socio.</div>
      </div>

      <!-- Aportación (SOLO deudas, nunca fallback) -->
      <div>
        <label>Aportación (solo deudas del socio)</label>
        <select name="idContribution" id="idContribution" required disabled
                class="select-disabled"
                style="width:100%;padding:10px;border-radius:10px;border:1px solid #ddd;">
          <option value="">— Primero selecciona socio y tipo —</option>
        </select>
        <div id="debtsMsg" class="field-help">Selecciona socio y tipo de pago para cargar solo sus deudas.</div>
      </div>

      <!-- Monto -->
      <div>
        <label>Monto</label>
        <input id="paidAmount" type="number" name="paidAmount" step="0.01" min="0.01" required
               style="width:100%;padding:10px;border-radius:10px;border:1px solid #ddd;">
      </div>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;">
      <button type="submit" class="btn-primary" id="submitBtn" disabled>
        <i class="fas fa-save"></i> Guardar
      </button>
      <a href="<?= u('cobros/list') ?>" class="btn">Cancelar</a>
    </div>
  </form>
</div>

<script>
  // Catálogo para resolver ID de socio a partir del datalist
  const PARTNERS = <?php
    $out = [];
    foreach ($partners as $s) {
      $pid = (int)($s['idPartner'] ?? 0);
      $nm  = trim((string)($s['name'] ?? ''));
      $ci  = trim((string)($s['CI']   ?? ''));
      $display = $nm . ($ci !== '' ? (' — CI ' . $ci) : '');
      $out[] = ['id' => $pid, 'display' => $display];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>;

  const mapByDisplay = new Map(PARTNERS.map(p => [p.display, p.id]));

  const input       = document.getElementById('partnerSearch');
  const hiddenId    = document.getElementById('idPartner');
  const err         = document.getElementById('partnerError');
  const typeSel     = document.getElementById('idPaymentType');
  const contrSel    = document.getElementById('idContribution');
  const amountInput = document.getElementById('paidAmount');
  const debtsMsg    = document.getElementById('debtsMsg');
  const submitBtn   = document.getElementById('submitBtn');
  
  // URL de la API usando tu sistema de rutas
  const DEBTS_API = "<?= u('cobros/debts-api') ?>";
  
  console.log('API URL:', DEBTS_API); // Para debug

  function updateFormState() {
    const hasPartner = Boolean(hiddenId.value);
    const hasType = Boolean(typeSel.value);
    const hasContribution = Boolean(contrSel.value);

    // Habilitar/deshabilitar tipo de pago
    typeSel.disabled = !hasPartner;
    if (hasPartner) {
      typeSel.classList.remove('select-disabled');
      if (!typeSel.value) {
        typeSel.querySelector('option[value=""]').textContent = '— Seleccionar tipo —';
      }
    } else {
      typeSel.classList.add('select-disabled');
      typeSel.value = '';
      typeSel.querySelector('option[value=""]').textContent = '— Primero selecciona un socio —';
    }

    // Habilitar/deshabilitar aportación
    contrSel.disabled = !hasPartner || !hasType;
    if (hasPartner && hasType) {
      contrSel.classList.remove('select-disabled');
    } else {
      contrSel.classList.add('select-disabled');
      contrSel.innerHTML = '<option value="">— Primero selecciona socio y tipo —</option>';
      amountInput.value = '';
    }

    // Habilitar botón de guardar
    submitBtn.disabled = !hasPartner || !hasType || !hasContribution;

    // Actualizar mensaje de ayuda
    if (!hasPartner) {
      debtsMsg.textContent = 'Selecciona socio y tipo de pago para cargar solo sus deudas.';
    } else if (!hasType) {
      debtsMsg.textContent = 'Selecciona el tipo de pago para cargar las deudas correspondientes.';
    }
  }

  function tryResolveSelection() {
    const val = (input.value || '').trim();
    const resolved = mapByDisplay.get(val);
    if (resolved) {
      hiddenId.value = String(resolved);
      err.style.display = 'none';
      input.setCustomValidity('');
      updateFormState();
      // Si ya hay tipo seleccionado, cargar deudas inmediatamente
      if (typeSel.value) {
        refreshDebts();
      }
    } else {
      hiddenId.value = '';
      updateFormState();
    }
  }

  async function refreshDebts() {
    if (!hiddenId.value || !typeSel.value) {
      updateFormState();
      return;
    }

    contrSel.innerHTML = '<option value="">Cargando deudas…</option>';
    contrSel.disabled = true;
    debtsMsg.textContent = 'Cargando deudas del socio...';

    // Construir URL con parámetros
    const url = new URL(DEBTS_API, window.location.origin);
    url.searchParams.set('idPartner', hiddenId.value);
    url.searchParams.set('idPaymentType', typeSel.value);

    console.log('Requesting:', url.toString()); // Para debug

    try {
      const res = await fetch(url.toString(), { 
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });

      console.log('Response status:', res.status); // Para debug
      
      if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${res.statusText}`);
      }

      const text = await res.text();
      console.log('Response text:', text.substring(0, 200)); // Para debug

      let json;
      try {
        json = JSON.parse(text);
      } catch (parseError) {
        console.error('JSON parse error:', parseError);
        console.error('Response was:', text);
        throw new Error('La respuesta del servidor no es JSON válido');
      }

      if (!json.success) {
        throw new Error(json.error || 'Error de servidor');
      }

      const list = json.data || [];
      contrSel.innerHTML = '';
      
      if (list.length === 0) {
        contrSel.insertAdjacentHTML('beforeend','<option value="">— Sin deudas pendientes —</option>');
        debtsMsg.textContent = 'Este socio no tiene deudas pendientes para este tipo de pago.';
        debtsMsg.style.color = '#28a745'; // Verde para indicar que está al día
        amountInput.value = '';
      } else {
        contrSel.insertAdjacentHTML('beforeend','<option value="">— Seleccionar deuda a pagar —</option>');
        list.forEach(d => {
          const id   = d.idContribution;
          const txt  = (d.notes || ('Aporte #' + id))
                     + (d.monthYear ? (' (' + d.monthYear + ')') : '')
                     + (d.amount != null ? (' — Bs ' + Number(d.amount).toFixed(2)) : '');
          const opt = document.createElement('option');
          opt.value = id;
          opt.textContent = txt;
          if (d.amount != null) opt.dataset.amount = d.amount;
          contrSel.appendChild(opt);
        });
        debtsMsg.textContent = `Mostrando ${list.length} deuda(s) pendiente(s) para este socio y tipo de pago.`;
        debtsMsg.style.color = '#555'; // Color normal
      }
    } catch (e) {
      console.error('Error cargando deudas:', e);
      contrSel.innerHTML = '<option value="">— Error al cargar deudas —</option>';
      debtsMsg.textContent = 'Error: ' + e.message;
      debtsMsg.style.color = '#dc3545'; // Rojo para error
    }

    updateFormState();
  }

  // Autorrellenar monto al elegir aportación (si viene amount)
  contrSel.addEventListener('change', () => {
    const opt = contrSel.selectedOptions[0];
    if (opt && opt.dataset.amount && opt.value) {
      amountInput.value = Number(opt.dataset.amount).toFixed(2);
    }
    updateFormState();
  });

  input.addEventListener('change', tryResolveSelection);
  input.addEventListener('blur',  tryResolveSelection);
  input.addEventListener('input', () => { 
    if (!input.value) {
      hiddenId.value = '';
      updateFormState();
    }
  });

  typeSel.addEventListener('change', () => { 
    contrSel.value = ''; // Reset contribution selection
    amountInput.value = ''; // Reset amount
    if (hiddenId.value && typeSel.value) {
      refreshDebts();
    } else {
      updateFormState();
    }
  });

  document.getElementById('createCobroForm').addEventListener('submit', function(e){
    if (!hiddenId.value) {
      e.preventDefault();
      err.style.display = 'block';
      input.focus();
      input.setCustomValidity('Selecciona un socio válido de la lista');
      input.reportValidity();
      return;
    }
    if (!contrSel.value) {
      e.preventDefault();
      debtsMsg.textContent = 'Debes seleccionar una deuda pendiente para proceder con el pago.';
      debtsMsg.style.color = '#dc3545';
      contrSel.focus();
      return;
    }
    if (!amountInput.value || parseFloat(amountInput.value) <= 0) {
      e.preventDefault();
      amountInput.focus();
      return;
    }
  });

  // Inicializar estado del formulario
  updateFormState();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>