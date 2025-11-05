<?php
// app/Controllers/CobroController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Competence.php';
require_once __DIR__ . '/../Models/Cobro.php';
require_once __DIR__ . '/../Helpers/auth.php';

class CobroController extends BaseController
{
    /** Helpers comunes */
    private function ensureSession(): void {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) { $this->redirect('login'); }
    }

    private function menu(): array {
        $roleId      = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);
        return [$menuOptions, $roleId];
    }

    /*private function getFiltersFromGet(): array {
        return [
            'q'              => $_GET['q']              ?? '',
            'idPaymentType'  => $_GET['idPaymentType']  ?? '',
            'idContribution' => $_GET['idContribution'] ?? '',
            'from'           => $_GET['from']           ?? '',
            'to'             => $_GET['to']             ?? '',
            'onlyLatest'     => isset($_GET['onlyLatest']) ? 1 : 0,
            'idPartner'      => $_GET['idPartner']      ?? '',   // <-- AÑADIR ESTO
        ];
    }*/



    private function getFiltersFromGet(): array {
    return [
        'q'              => $_GET['q']              ?? '',
        'idPaymentType'  => $_GET['idPaymentType']  ?? '',
        'idContribution' => $_GET['idContribution'] ?? '',
        'from'           => $_GET['from']           ?? '',
        'to'             => $_GET['to']             ?? '',
        'onlyLatest'     => isset($_GET['onlyLatest']) ? 1 : 0,
        'idPartner'      => $_GET['idPartner']      ?? '',
        'orderBy'        => $_GET['orderBy']        ?? 'date',
        'sortDir'        => $_GET['sortDir']        ?? 'desc',
    ];
}

    private function getPaging(): array {
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = max(5, (int)($_GET['pageSize'] ?? 20)); // ← por defecto 20
        return [$page, $pageSize];
    }

    /** Alias de listado principal → Pagadas */
    public function list(): void { $this->pagadas(); }

    /** Cobros pagados (filtrados + paginados) */
    public function pagadas(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m       = new \Cobro();
        $filters = $this->getFiltersFromGet();
        [$page, $pageSize] = $this->getPaging();

        $total = 0;
        $rows  = $m->searchPagadas($filters, $page, $pageSize, $total);
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        $totalPages = max(1, (int)ceil($total / $pageSize));

        $this->view('cobros/list', [
            'menuOptions' => $menuOptions,
            'currentPath' => 'cobros/list',
            'roleId'      => $roleId,

            'rows'        => $rows,
            'types'       => $types,
            'contribs'    => $contribs,
            'filters'     => $filters,

            'page'        => $page,
            'pageSize'    => $pageSize,
            'total'       => $total,
            'totalPages'  => $totalPages,
        ]);
    }

    /** Cobros debidos (socios sin pago para aportación/tipo) */
    public function debidas(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m       = new \Cobro();
        $filters = $this->getFiltersFromGet();
        [$page, $pageSize] = $this->getPaging();

        $total = 0;
        $rows  = $m->searchDebidas($filters, $page, $pageSize, $total);
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        $totalPages = max(1, (int)ceil($total / $pageSize));

        $this->view('cobros/debidas', [
            'menuOptions' => $menuOptions,
            'currentPath' => 'cobros/debidas',
            'roleId'      => $roleId,

            'rows'        => $rows,
            'types'       => $types,
            'contribs'    => $contribs,
            'filters'     => $filters,

            'page'        => $page,
            'pageSize'    => $pageSize,
            'total'       => $total,
            'totalPages'  => $totalPages,
        ]);
    }

    /** Alta */
    public function create(): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m = new \Cobro();
        $partners = $m->allPartners();
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPartner      = (int)($_POST['idPartner'] ?? 0);
            $idPaymentType  = (int)($_POST['idPaymentType'] ?? 0);
            $idContribution = (int)($_POST['idContribution'] ?? 0);
            $paidAmount     = (float)($_POST['paidAmount'] ?? 0);

            if ($idPartner<=0 || $idPaymentType<=0 || $idContribution<=0 || $paidAmount<=0) {
                $error = 'Completa todos los campos con valores válidos.';
                $this->view('cobros/create', [
                    'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
                    'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs, 'error'=>$error
                ]);
                return;
            }

            $id = $m->create($idPartner,$idPaymentType,$idContribution,$paidAmount);
            if ($id) { $_SESSION['success']='Cobro registrado.'; $this->redirect('cobros/list'); return; }

            $error = 'No se pudo registrar.';
            $this->view('cobros/create', [
                'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
                'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs, 'error'=>$error
            ]);
            return;
        }

        $this->view('cobros/create', [
            'menuOptions'=>$menuOptions, 'roleId'=>$roleId, 'currentPath'=>'cobros/create',
            'partners'=>$partners, 'types'=>$types, 'contribs'=>$contribs
        ]);
    }




    public function debtsApi(): void
{
    $this->startSession();
    header('Content-Type: application/json; charset=utf-8');

    $idPartner = (int)($_GET['idPartner'] ?? 0);
    $idPaymentType = isset($_GET['idPaymentType']) && $_GET['idPaymentType'] !== ''
        ? (int)$_GET['idPaymentType'] : null;

    if ($idPartner <= 0) {
        echo json_encode(['success' => false, 'error' => 'idPartner inválido', 'data' => []]);
        return;
    }

    require_once __DIR__ . '/../Models/Cobro.php';
    $m = new \Cobro();
    $data = $m->debtsByPartnerDetailed($idPartner, $idPaymentType);

    echo json_encode(['success' => true, 'data' => $data]);
}


/**
 * SIN DEBUG
 * @param array $file
 * @param int $partnerId
 * @return array{error: string, success: bool|array{path: string, success: bool}}
 */

public function createMultiple(): void
{
    $this->ensureSession();
    [$menuOptions, $roleId] = $this->menu();

    $m = new \Cobro();
    
    // Obtener los datos de los pagos seleccionados (vienen por POST)
    $selectedDebts = $_POST['selected_debts'] ?? [];
    $debtsData = [];
    $totalAmount = 0;
    $partnerId = null;
    $partnerName = '';

    // Si no hay deudas seleccionadas, redirigir con error
    if (empty($selectedDebts)) {
        $_SESSION['error'] = 'No se seleccionaron deudas para pagar.';
        $this->redirect('cobros/debidas');
        return;
    }

    foreach ($selectedDebts as $debtKey) {
        list($idPartner, $idContribution) = explode('-', $debtKey);
        
        // Obtener información detallada de cada deuda usando el modelo
        $debtInfo = $m->getDebtDetails((int)$idPartner, (int)$idContribution);
        
        if ($debtInfo) {
            $debtsData[] = $debtInfo;
            $totalAmount += (float)$debtInfo['amount'];
            
            // Establecer información del socio
            if ($partnerId === null) {
                $partnerId = (int)$idPartner;
                $partnerName = $debtInfo['partnerName'];
            }
        }
    }

    if (empty($debtsData)) {
        $_SESSION['error'] = 'No se encontraron deudas válidas para pagar.';
        $this->redirect('cobros/debidas');
        return;
    }

    $types = $m->allTypes();

    // Si se está confirmando el pago (segunda etapa)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
        $idPaymentType = (int)($_POST['idPaymentType'] ?? 0);
        $paidAmount = (float)($_POST['paidAmount'] ?? 0);
        
        // DEBUG: Logs para verificar qué llega
        error_log("DEBUG createMultiple - POST data: " . print_r($_POST, true));
        error_log("DEBUG createMultiple - FILES data: " . print_r($_FILES, true));
        
        if ($idPaymentType <= 0) {
            $error = 'Selecciona un tipo de pago válido.';
        } else if ($paidAmount <= 0) {
            $error = 'El monto a pagar debe ser mayor a cero.';
        } else {
            // Manejar la subida de archivo si está presente
            $voucherImageURL = null;
            
            // Verificar si el checkbox está marcado Y hay un archivo
            if (isset($_POST['upload_receipt']) && !empty($_FILES['receipt_file']['name'])) {
                error_log("DEBUG: Intentando subir archivo: " . $_FILES['receipt_file']['name']);
                
                $uploadResult = $this->handleReceiptUpload($_FILES['receipt_file'], $partnerId);
                error_log("DEBUG: Resultado upload: " . print_r($uploadResult, true));
                
                if ($uploadResult['success']) {
                    $voucherImageURL = $uploadResult['path'];
                    error_log("DEBUG: voucherImageURL set to: " . $voucherImageURL);
                } else {
                    $error = $uploadResult['error'];
                    error_log("DEBUG: Error en upload: " . $uploadResult['error']);
                }
            } else {
                error_log("DEBUG: No se subió archivo - upload_receipt: " . (isset($_POST['upload_receipt']) ? 'YES' : 'NO') . ", file name: " . ($_FILES['receipt_file']['name'] ?? 'EMPTY'));
            }
            
            if (!isset($error)) {
                // Procesar cada pago individual y almacenar los IDs
                $successCount = 0;
                $errors = [];
                $paymentIds = [];
                
                foreach ($debtsData as $debt) {
                    error_log("DEBUG: Creando pago para debt: " . print_r($debt, true));
                    error_log("DEBUG: voucherImageURL para este pago: " . ($voucherImageURL ?? 'NULL'));
                    
                    $paymentId = $m->create(
                        (int)$debt['idPartner'],
                        $idPaymentType,
                        (int)$debt['idContribution'],
                        (float)$debt['amount'],
                        $voucherImageURL // Pasar la URL del comprobante
                    );
                    
                    error_log("DEBUG: Resultado create: " . ($paymentId ? $paymentId : 'FALSE'));
                    
                    if ($paymentId) {
                        $successCount++;
                        $paymentIds[] = $paymentId;
                    } else {
                        $errors[] = "Error al procesar pago para aportación #{$debt['idContribution']}";
                    }
                }
                
                if ($successCount > 0) {
                    // Generar el recibo y redirigir
                    $_SESSION['success'] = "Se procesaron {$successCount} pagos correctamente.";
                    if ($voucherImageURL) {
                        $_SESSION['success'] .= " Comprobante guardado: {$voucherImageURL}";
                    }
                    $_SESSION['receipt_payment_ids'] = $paymentIds;
                    
                    if (!empty($errors)) {
                        $_SESSION['success'] .= " Errores: " . implode(', ', $errors);
                    }
                    
                    $this->redirect('cobros/recibo');
                    return;
                } else {
                    $error = 'No se pudo procesar ningún pago. ' . implode(', ', $errors);
                }
            }
        }
    }

    // Mostrar la vista de confirmación (primera etapa)
    $this->view('cobros/create-multiple', [
        'menuOptions' => $menuOptions,
        'currentPath' => 'cobros/create-multiple',
        'roleId' => $roleId,
        
        'debtsData' => $debtsData,
        'totalAmount' => $totalAmount,
        'partnerId' => $partnerId,
        'partnerName' => $partnerName,
        'types' => $types,
        'error' => $error ?? null,
        'selectedDebts' => $selectedDebts
    ]);
}

// Método auxiliar para manejar la subida de archivos
private function handleReceiptUpload(array $file, int $partnerId): array
{
    error_log("DEBUG handleReceiptUpload - file: " . print_r($file, true));
    error_log("DEBUG handleReceiptUpload - partnerId: " . $partnerId);
    
    // Validar que se subió un archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("DEBUG: Upload error: " . $file['error']);
        return ['success' => false, 'error' => 'Error al subir el archivo.'];
    }
    
    // Validar tamaño (máximo 5MB)
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        error_log("DEBUG: File too big: " . $file['size']);
        return ['success' => false, 'error' => 'El archivo es muy grande. Máximo 5MB permitido.'];
    }
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $fileType = $file['type'];
    
    error_log("DEBUG: File type: " . $fileType);
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo se permiten: JPG, PNG, PDF'];
    }
    
    // Determinar extensión
    $extension = '';
    switch ($fileType) {
        case 'image/jpeg':
        case 'image/jpg':
            $extension = 'jpg';
            break;
        case 'image/png':
            $extension = 'png';
            break;
        case 'application/pdf':
            $extension = 'pdf';
            break;
    }
    
    // Crear directorio si no existe
    $uploadDir = __DIR__ . '/../../public/images/receipts/';
    error_log("DEBUG: Upload dir: " . $uploadDir);
    
    if (!is_dir($uploadDir)) {
        error_log("DEBUG: Creating directory: " . $uploadDir);
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("DEBUG: Failed to create directory");
            return ['success' => false, 'error' => 'No se pudo crear el directorio de comprobantes.'];
        }
    }
    
    // Generar nombre único del archivo
    $timestamp = time();
    $fileName = "comprobante_{$partnerId}_" . date('Ym') . "_{$timestamp}.{$extension}";
    $uploadPath = $uploadDir . $fileName;
    $relativePath = "images/receipts/{$fileName}";
    
    error_log("DEBUG: fileName: " . $fileName);
    error_log("DEBUG: uploadPath: " . $uploadPath);
    error_log("DEBUG: relativePath: " . $relativePath);
    
    // Mover archivo
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        error_log("DEBUG: File moved successfully to: " . $uploadPath);
        return ['success' => true, 'path' => $relativePath];
    } else {
        error_log("DEBUG: Failed to move file from: " . $file['tmp_name'] . " to: " . $uploadPath);
        return ['success' => false, 'error' => 'Error al guardar el archivo.'];
    }
}




// Versión con debug en pantalla - Reemplazar createMultiple en CobroController.php



/**
 * CON DEBUG
 * @param array $file
 * @param int $partnerId
 * @return array{error: string, success: bool|array{path: string, success: bool}}
 */

    /** Edición */
    public function edit(int $id): void
    {
        $this->ensureSession();
        [$menuOptions, $roleId] = $this->menu();

        $m   = new \Cobro();
        $row = $m->findById((int)$id);
        if (!$row) { $this->redirect('cobros/list'); return; }

        $partners = $m->allPartners();
        $types    = $m->allTypes();
        $contribs = $m->allContributions();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idPartner      = (int)($_POST['idPartner'] ?? 0);
            $idPaymentType  = (int)($_POST['idPaymentType'] ?? 0);
            $idContribution = (int)($_POST['idContribution'] ?? 0);
            $paidAmount     = (float)($_POST['paidAmount'] ?? 0);

            if ($idPartner<=0 || $idPaymentType<=0 || $idContribution<=0 || $paidAmount<=0) {
                $error='Completa todos los campos con valores válidos.';
                $this->view('cobros/edit', [
                    'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
                    'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs,'error'=>$error
                ]);
                return;
            }

            $ok = $m->update((int)$id,$idPartner,$idPaymentType,$idContribution,$paidAmount);
            if ($ok) { $_SESSION['success']='Cobro actualizado.'; $this->redirect('cobros/list'); return; }

            $error='No se pudo actualizar.';
            $this->view('cobros/edit', [
                'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
                'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs,'error'=>$error
            ]);
            return;
        }

        $this->view('cobros/edit', [
            'menuOptions'=>$menuOptions,'roleId'=>$roleId,'currentPath'=>'cobros/list',
            'row'=>$row,'partners'=>$partners,'types'=>$types,'contribs'=>$contribs
        ]);
    }

    /** Borrado */
    public function delete(int $id): void
    {
        $this->ensureSession();
        (new \Cobro())->delete((int)$id);
        $this->redirect('cobros/list');
    }

    public function socios(): void {
    $this->ensureSession();
    [$menuOptions, $roleId] = $this->menu();

    $m = new \Cobro();
    $filters = $this->getFiltersFromGet();
    [$page, $pageSize] = $this->getPaging();

    $total = 0;
    $rows = $m->listPartnersWithTotals($filters, $page, $pageSize, $total);

    $totalPages = max(1, (int)ceil($total / $pageSize));

    $this->view('cobros/socios', [
        'menuOptions' => $menuOptions,
        'currentPath' => 'cobros/socios',
        'roleId' => $roleId,

        'rows' => $rows,
        'filters' => $filters,

        'page' => $page,
        'pageSize' => $pageSize,
        'total' => $total,
        'totalPages' => $totalPages,
    ]);
}

//para recibo
/*public function recibo(): void {
    $this->ensureSession();
    [$menuOptions, $roleId] = $this->menu();

    // Obtener los IDs de los pagos desde la sesión
    $paymentIds = $_SESSION['receipt_payment_ids'] ?? [];
    
    if (empty($paymentIds)) {
        $_SESSION['error'] = 'No se encontró información del recibo.';
        $this->redirect('cobros/socios');
        return;
    }

    // Limpiar la sesión después de obtener los datos
    unset($_SESSION['receipt_payment_ids']);

    $m = new \Cobro();
    $receiptData = $m->getReceiptData($paymentIds);
    
    if (!$receiptData) {
        $_SESSION['error'] = 'Error al generar el recibo.';
        $this->redirect('cobros/socios');
        return;
    }

    $this->view('cobros/recibo', [
        'menuOptions' => $menuOptions,
        'currentPath' => 'cobros/recibo',
        'roleId' => $roleId,
        'receiptData' => $receiptData
    ]);
}*/

//para recibo
public function recibo(): void {
    $this->ensureSession();
    [$menuOptions, $roleId] = $this->menu();

    // Obtener el ID desde GET (reimprimir) o desde sesión (nuevo pago)
    $paymentId = isset($_GET['paymentId']) ? (int)$_GET['paymentId'] : null;
    $paymentIds = [];
    
    if ($paymentId) {
        // Reimprimir un pago específico
        $paymentIds = [$paymentId];
    } else {
        // Pago nuevo desde sesión
        $paymentIds = $_SESSION['receipt_payment_ids'] ?? [];
    }
    
    if (empty($paymentIds)) {
        $_SESSION['error'] = 'No se encontró información del recibo.';
        $this->redirect('cobros/socios');
        return;
    }

    // Limpiar la sesión después de obtener los datos (solo si era de sesión)
    if (!$paymentId) {
        unset($_SESSION['receipt_payment_ids']);
    }

    $m = new \Cobro();
    $receiptData = $m->getReceiptData($paymentIds);
    
    if (!$receiptData) {
        $_SESSION['error'] = 'Error al generar el recibo.';
        $this->redirect('cobros/socios');
        return;
    }

    $this->view('cobros/recibo', [
        'menuOptions' => $menuOptions,
        'currentPath' => 'cobros/recibo',
        'roleId' => $roleId,
        'receiptData' => $receiptData
    ]);
}



}
