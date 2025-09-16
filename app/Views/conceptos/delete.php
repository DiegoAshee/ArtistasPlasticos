<?php
$title       = 'Eliminar Concepto';
$currentPath = 'conceptos/delete';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Conceptos', 'url' => u('conceptos/list')],
  ['label' => 'Eliminar', 'url' => null],
];

$concept = $concept ?? [];
ob_start();
?>
<style>
  #conceptos-delete {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2a2a2a;
    max-width: 600px;
    margin: 0 auto;
  }
  
  .delete-container {
    background: #f8f6f2;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    text-align: center;
  }
  
  .warning-icon {
    font-size: 48px;
    color: #e74c3c;
    margin-bottom: 20px;
  }
  
  .concept-info {
    background: #ffffff;
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    text-align: left;
  }
  
  .concept-info p {
    margin: 8px 0;
    color: #4a4a4a;
  }
  
  .concept-info strong {
    color: #2a2a2a;
  }
  
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    border: 1px solid #cfcfcf;
    background: #ffffff;
    color: #2a2a2a;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }
  
  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  
  .btn-danger {
    background: #e74c3c;
    border-color: #e74c3c;
    color: #fff;
  }
  
  .btn-danger:hover {
    background: #d62c1a;
    border-color: #d62c1a;
  }
  
  .btn-secondary {
    background: #f8f6f2;
    border-color: #bbae97;
    color: #2a2a2a;
  }
  
  .btn-secondary:hover {
    background: #e8e4dc;
    border-color: #a89a83;
  }
  
  .alert {
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
  }
  
  .alert-danger {
    background: #FEE4E2;
    border: 1px solid #FECDCA;
    color: #D92D20;
  }
</style>

<div id="conceptos-delete">
  <div class="delete-container">
    <div class="warning-icon">
      <i class="fas fa-exclamation-triangle"></i>
    </div>
    
    <h2 style="margin-top: 0; margin-bottom: 20px; color: #2a2a2a;">
      Eliminar Concepto
    </h2>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <p style="color: #4a4a4a; margin-bottom: 25px;">
      ¿Está seguro de que desea eliminar este concepto? Esta acción no se puede deshacer.
    </p>
    
    <div class="concept-info">
      <p><strong>ID:</strong> #<?= (int)($concept['idConcept'] ?? 0) ?></p>
      <p><strong>Descripción:</strong> <?= htmlspecialchars($concept['description'] ?? '') ?></p>
      <p><strong>Tipo:</strong> <?= htmlspecialchars($concept['type'] ?? '') ?></p>
      <p><strong>Fecha de creación:</strong> <?= !empty($concept['dateCreation']) ? date('d/m/Y H:i', strtotime($concept['dateCreation'])) : '-' ?></p>
    </div>
    
    <form method="post" action="<?= u('conceptos/destroy/' . (int)($concept['idConcept'] ?? 0)) ?>">
      <div style="display: flex; gap: 10px; justify-content: center;">
        <button type="submit" class="btn btn-danger">
          <i class="fas fa-trash"></i> Sí, Eliminar
        </button>
        <a href="<?= u('conceptos/list') ?>" class="btn btn-secondary">
          <i class="fas fa-times"></i> Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';