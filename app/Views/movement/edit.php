<?php
// Helper functions for URL generation
if (!function_exists('u')) {
    function u(string $path): string {
        $base = rtrim(BASE_URL, '/');
        return $base . '/' . ltrim($path, '/');
    }
}
if (!function_exists('asset')) {
    function asset(string $path): string { return u($path); }
}

// Set up variables for the layout
$title = 'Editar Movimiento';
$currentPath = 'movement/edit';
$formAction = u('movement/update/' . $movement['idMovement']);
$breadcrumbs = [
    ['label' => 'Inicio', 'url' => u('dashboard')],
    ['label' => 'Movimientos', 'url' => u('movement/list')],
    ['label' => 'Editar Movimiento', 'url' => null],
];

// Start output buffering
ob_start();
?>

<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
        background: #fff;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .form-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .form-header h1 {
        color: #2c3e50;
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
        width: 100px;
        height: 4px;
        background: var(--cream-600);
        border-radius: 2px;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.6rem;
        font-weight: 600;
        color: var(--cream-800);
        font-size: 0.95rem;
        font-family: 'Inter', sans-serif;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.85rem 1.25rem;
        border: 1px solid var(--cream-300);
        border-radius: 8px;
        font-size: 1rem;
        transition: var(--transition);
        background-color: var(--cream-50);
        font-family: 'Inter', sans-serif;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--cream-600);
        box-shadow: 0 0 0 3px rgba(156, 143, 122, 0.1);
        background-color: var(--surface);
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.75rem;
        margin-bottom: 1.5rem;
    }

    .form-section {
        margin-bottom: 2.5rem;
    }

    .section-title {
        font-size: 1.4rem;
        color: var(--cream-800);
        margin: 0 0 1.5rem 0;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid var(--cream-200);
        font-family: 'Playfair Display', serif;
    }

    .btn-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin: 2rem 0 0;
    }

    .btn-submit {
        background: var(--cream-600);
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        min-width: 180px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
    }

    .btn-submit:hover {
        background: var(--cream-700);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: var(--transition);
        min-width: 180px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin: 0 0 2.5rem 0;
        border-left: 4px solid #dc2626;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.05rem;
    }

    .success-message {
        background: #d1fae5;
        color: #065f46;
        padding: 1.25rem 1.5rem;
        border-radius: 8px;
        margin: 0 0 2.5rem 0;
        border-left: 4px solid #10b981;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.05rem;
    }

    .amount-input {
        font-weight: 600;
        font-size: 1.1rem;
    }

    @media (max-width: 1200px) {
        .edit-container {
            padding: 2rem;
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    }

    @media (max-width: 768px) {
        .edit-container {
            padding: 1.5rem;
            margin: 0.5rem;
            width: calc(100% - 1rem);
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .edit-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
        }

        .btn-group {
            flex-direction: column;
            align-items: center;
        }
    }
</style>

<div class="content-wrapper">
    <div class="edit-container">
        <h1 class="edit-title">Editar Movimiento</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/movement/edit/<?= (int)($movement['idMovement'] ?? 0) ?>">
            <div class="form-section">
                <h2 class="section-title">Información del Movimiento</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <input type="text" name="description" id="description" 
                               value="<?= htmlspecialchars($movement['description'] ?? '') ?>" 
                               placeholder="Descripción del movimiento" maxlength="255" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Monto (Bs.)</label>
                        <input type="number" name="amount" id="amount" class="amount-input" 
                               value="<?= htmlspecialchars($movement['amount'] ?? '') ?>"
                               step="0.01" min="0" max="999999.99" placeholder="0.00" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dateCreation">Fecha de Creación</label>
                        <input type="datetime-local" name="dateCreation" id="dateCreation" 
                               value="<?= !empty($movement['dateCreation']) ? date('Y-m-d\TH:i', strtotime($movement['dateCreation'])) : '' ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="idPaymentType">Tipo de Pago</label>
                        <select name="idPaymentType" id="idPaymentType" required>
                            <option value="">Seleccione un tipo de pago</option>
                            <?php if (isset($paymentTypes) && is_array($paymentTypes)): ?>
                                <?php foreach ($paymentTypes as $paymentType): ?>
                                    <option value="<?= htmlspecialchars($paymentType['idPaymentType']) ?>"
                                            <?= ($paymentType['idPaymentType'] == ($movement['idPaymentType'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($paymentType['description']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="idConcept">Concepto</label>
                        <select name="idConcept" id="idConcept" required>
                            <option value="">Seleccione un concepto</option>
                            <?php if (isset($concepts) && is_array($concepts)): ?>
                                <?php foreach ($concepts as $concept): ?>
                                    <option value="<?= htmlspecialchars($concept['idConcept']) ?>"
                                            <?= ($concept['idConcept'] == ($movement['idConcept'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($concept['description']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="idUser">Usuario</label>
                        <select name="idUser" id="idUser" required>
                            <option value="">Seleccione un usuario</option>
                            <?php if (isset($users) && is_array($users)): ?>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['idUser']) ?>"
                                            <?= ($user['idUser'] == ($movement['idUser'] ?? '')) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['login']) ?> - <?= htmlspecialchars($user['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="btn-group">
                <a href="<?= u('movement/list') ?>" class="btn-cancel">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Actualizar Movimiento
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format amount input
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function() {
        let value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(2);
        }
    });
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let hasErrors = false;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#e74c3c';
                hasErrors = true;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            alert('Por favor, complete todos los campos requeridos.');
        }
    });
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout file
include __DIR__ . '/../layouts/app.php';
?>