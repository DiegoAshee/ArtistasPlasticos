<?php
$title       = 'Editar Cobro';
$currentPath = 'cobros/list';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Cobros', 'url' => u('cobros/list')],
  ['label' => 'Editar', 'url' => null],
];

$row = $row ?? [];
$partners=$partners??[]; $types=$types??[]; $contribs=$contribs??[];

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
</style>

<div id="cobros-root">
  <?php if (!empty($error)): ?>
    <div style="background:#f8d7da;color:#842029;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
      <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
  <?php endif; ?>

  <form action="<?= u('cobros/edit/' . (int)($row['idPayment'] ?? 0)) ?>" method="post"
        style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.06);max-width:740px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
      <div>
        <label>Quién paga (Socio)</label>
        <select name="idPartner" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #ddd;">
          <?php foreach ($partners as $s): ?>
            <option value="<?= (int)($s['idPartner'] ?? 0) ?>"
              <?= ((int)($row['idPartner'] ?? 0) === (int)($s['idPartner'] ?? -1)) ? 'selected' : '' ?>>
              <?= htmlspecialchars(($s['name'] ?? '') . (isset($s['CI']) ? ' — CI '.$s['CI'] : ''), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Tipo de pago</label>
        <select name="idPaymentType" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #ddd;">
          <?php foreach ($types as $t): ?>
            <option value="<?= (int)($t['idPaymentType'] ?? 0) ?>"
              <?= ((int)($row['idPaymentType'] ?? 0) === (int)($t['idPaymentType'] ?? -1)) ? 'selected' : '' ?>>
              <?= htmlspecialchars($label($t,'Tipo','idPaymentType'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Aportación</label>
        <select name="idContribution" required style="width:100%;padding:8px;border-radius:8px;border:1px solid #ddd;">
          <?php foreach ($contribs as $c): ?>
            <option value="<?= (int)($c['idContribution'] ?? 0) ?>"
              <?= ((int)($row['idContribution'] ?? 0) === (int)($c['idContribution'] ?? -1)) ? 'selected' : '' ?>>
              <?= htmlspecialchars($label($c,'Aporte','idContribution'), ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label>Monto</label>
        <input type="number" name="paidAmount" step="0.01" min="0.01" required
               value="<?= htmlspecialchars((string)($row['paidAmount'] ?? '0.00')) ?>"
               style="width:100%;padding:8px;border-radius:8px;border:1px solid #ddd;">
      </div>
    </div>

    <div style="margin-top:16px;display:flex;gap:10px;">
      <button type="submit" class="btn-primary">
        <i class="fas fa-save"></i> Guardar cambios
      </button>
      <a href="<?= u('cobros/list') ?>" class="btn">Cancelar</a>
    </div>
  </form>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
