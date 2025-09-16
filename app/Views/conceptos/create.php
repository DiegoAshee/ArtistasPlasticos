<?php
$title       = 'Crear Concepto';
$currentPath = 'conceptos/create';
$breadcrumbs = [
  ['label' => 'Inicio', 'url' => u('dashboard')],
  ['label' => 'Conceptos', 'url' => u('conceptos/list')],
  ['label' => 'Crear', 'url' => null],
];

ob_start();
?>
<style>
  #conceptos-create {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2a2a2a;
    max-width: 600px;
    margin: 0 auto;
  }
  
  .form-container {
    background: #f8f6f2;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  
  .form-group {
    margin-bottom: 20px;
  }
  
  .form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #4a4a4a;
  }
  
  .form-group input,
  .form-group select,
  .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
    box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);
  }
  
  .form-group input:focus,
  .form-group select:focus,
  .form-group textarea:focus {
    outline: none;
    border-color: #bbae97;
    box-shadow: 0 0 0 3px rgba(187, 174, 151, 0.2);
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
  
  .btn-primary {
    background: #6c757d;
    border-color: #6c757d;
    color: #fff;
  }
  
  .btn-primary:hover {
    background: #5a6268;
    border-color: #5a6268;
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

<div id="conceptos-create">
  <div class="form-container">
    <h2 style="margin-top: 0; margin-bottom: 25px; color: #2a2a2a;">Crear Nuevo Concepto</h2>
    
    <?php if (isset($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    
    <form method="post" action="<?= u('conceptos/store') ?>">
      <div class="form-group">
        <label for="description">Descripción *</label>
        <input type="text" id="description" name="description" required 
               placeholder="Ingrese la descripción del concepto">
      </div>
      
      <div class="form-group">
        <label for="type">Tipo *</label>
        <select id="type" name="type" required>
          <option value="">Seleccione un tipo</option>
          <option value="Ingreso">Ingreso</option>
          <option value="Egreso">Egreso</option>
        </select>
      </div>
      
      <div style="display: flex; gap: 10px; margin-top: 30px;">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Guardar Concepto
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