<?php
// Helper functions for URL generation
if (!function_exists('u')) {
    function u(string $path): string {
        $base = rtrim(BASE_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string { 
        return u($path); 
    }
}

// Set up variables for the layout
$title = 'Editar Movimiento';
$currentPath = 'movement/edit';
$formAction = u('movement/update/' . $movement['idMovement']);

// Start output buffering
ob_start();
?>

<div class="content-wrapper">
    <div class="form-container">
        <div class="form-header">
            <h1>Editar Movimiento #<?= htmlspecialchars($movement['idMovement']) ?></h1>
            <p>Actualice los datos del movimiento</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fca5a5;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success" style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #86efac;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= $formAction ?>" method="post" class="movement-form">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Descripción</label>
                    <input type="text" id="description" name="description" required 
                           value="<?= htmlspecialchars($movement['description'] ?? '') ?>"
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="amount" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Monto</label>
                    <input type="number" id="amount" name="amount" step="0.01" required 
                           value="<?= htmlspecialchars($movement['amount'] ?? '') ?>"
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label for="dateCreation" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Fecha y Hora</label>
                    <?php 
                    // Formatear la fecha para el input datetime-local
                    $dateValue = '';
                    if (!empty($movement['dateCreation'])) {
                        $date = new DateTime($movement['dateCreation']);
                        $dateValue = $date->format('Y-m-d\TH:i');
                    }
                    ?>
                    <input type="datetime-local" id="dateCreation" name="dateCreation" required 
                           value="<?= htmlspecialchars($dateValue) ?>"
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem;">
                </div>
                
                <div class="form-group">
                    <label for="idConcept" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Concepto</label>
                    <select id="idConcept" name="idConcept" required 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; background-color: white;">
                        <option value="">Seleccione un concepto</option>
                        <?php foreach ($concepts as $concept): ?>
                            <option value="<?= $concept['idConcept'] ?>" <?= (isset($movement['idConcept']) && $movement['idConcept'] == $concept['idConcept']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($concept['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="idPaymentType" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Tipo de Pago</label>
                    <select id="idPaymentType" name="idPaymentType" required 
                            style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; background-color: white;">
                        <option value="">Seleccione tipo de pago</option>
                        <?php foreach ($paymentTypes as $paymentType): ?>
                            <option value="<?= $paymentType['idPaymentType'] ?>" <?= (isset($movement['idPaymentType']) && $movement['idPaymentType'] == $paymentType['idPaymentType']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($paymentType['description']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <?php if (isset($users) && !empty($users) && (isset($_SESSION['role']) && $_SESSION['role'] == 1)): ?>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="idUser" style="display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4b5563;">Usuario</label>
                <select id="idUser" name="idUser" 
                        style="width: 50%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 1rem; background-color: white;">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['idUser'] ?>" <?= (isset($movement['idUser']) && $user['idUser'] == $movement['idUser']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['login']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="idUser" value="<?= $movement['idUser'] ?? '' ?>">
            <?php endif; ?>
            
            <div class="form-actions" style="display: flex; justify-content: space-between; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
                <div>
                    <a href="<?= u('movement/delete/' . $movement['idMovement']) ?>" class="btn-delete" style="padding: 0.75rem 1.5rem; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; border-radius: 8px; font-weight: 500; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;"
                       onclick="return confirm('¿Está seguro de que desea eliminar este movimiento? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <a href="<?= u('movement/list') ?>" style="padding: 0.75rem 1.5rem; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px; font-weight: 500; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" style="padding: 0.75rem 1.5rem; background: #d1a679; color: white; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.form-container {
    max-width: 900px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}

.form-header {
    text-align: center;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.form-header h1 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin: 0 0 0.5rem 0;
    font-weight: 600;
}

.form-header p {
    color: #6b7280;
    margin: 0;
    font-size: 1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

alert-danger {
    background-color: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fca5a5;
}

alert-success {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.movement-form input[type="text"],
.movement-form input[type="number"],
.movement-form input[type="datetime-local"],
.movement-form select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.movement-form input[type="text"]:focus,
.movement-form input[type="number"]:focus,
.movement-form input[type="datetime-local"]:focus,
.movement-form select:focus {
    outline: none;
    border-color: #d1a679;
    box-shadow: 0 0 0 3px rgba(209, 166, 121, 0.2);
}

.btn-delete:hover {
    background-color: #fee2e2 !important;
    border-color: #fca5a5 !important;
}

.btn-delete i {
    transition: transform 0.2s;
}

.btn-delete:hover i {
    transform: scale(1.1);
}
</style>

<?php
// End output buffering and get the content
$content = ob_get_clean();

// Include the layout
include __DIR__ . '/../layouts/app.php';
?>
