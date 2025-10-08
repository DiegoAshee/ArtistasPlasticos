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
$title = 'Crear Movimiento';
$currentPath = 'movement/create';
$formAction = u('movement/store');

// Start output buffering
ob_start();
?>

<style>
    .form-container {
        max-width: 800px;
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
        width: auto;
        min-width: 220px;
        margin: 2rem auto 0;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: 'Inter', sans-serif;
    }

    .btn-submit:hover {
        background: var(--cream-700);
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

    .amount-input {
        font-weight: 600;
        font-size: 1.1rem;
    }

    @media (max-width: 1200px) {
        .create-container {
            padding: 2rem;
            margin: 1rem;
            width: calc(100% - 2rem);
        }
    }

    @media (max-width: 768px) {
        .create-container {
            padding: 1.5rem;
            margin: 0.5rem;
            width: calc(100% - 1rem);
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .create-title {
            font-size: 1.75rem;
            margin-bottom: 2rem;
        }
    }
</style>

<div class="content-wrapper">
    <div class="create-container">
        <h1 class="create-title">Crear Movimiento</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?= rtrim(BASE_URL,'/') ?>/movement/create">
            <div class="form-section">
                <h2 class="section-title">Información del Movimiento</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="description">Descripción</label>
                        <input type="text" name="description" id="description" placeholder="Descripción del movimiento" maxlength="255" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Monto (Bs.)</label>
                        <input type="number" name="amount" id="amount" class="amount-input" step="0.01" min="0" max="999999.99" placeholder="0.00" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="dateCreation">Fecha de Creación</label>
                        <input type="datetime-local" name="dateCreation" id="dateCreation" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="idPaymentType">Tipo de Pago</label>
                        <select name="idPaymentType" id="idPaymentType" required>
                            <option value="">Seleccione un tipo de pago</option>
                            <?php if (isset($paymentTypes) && is_array($paymentTypes)): ?>
                                <?php foreach ($paymentTypes as $paymentType): ?>
                                    <option value="<?= htmlspecialchars($paymentType['idPaymentType']) ?>">
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
                                    <option value="<?= htmlspecialchars($concept['idConcept']) ?>">
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
                                    <option value="<?= htmlspecialchars($user['idUser']) ?>">
                                        <?= htmlspecialchars($user['login']) ?> - <?= htmlspecialchars($user['email']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-plus-circle"></i> Crear Movimiento
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set current datetime as default
    const dateCreationInput = document.getElementById('dateCreation');
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    dateCreationInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Format amount input
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('input', function() {
        let value = parseFloat(this.value);
        if (!isNaN(value)) {
            this.value = value.toFixed(2);
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