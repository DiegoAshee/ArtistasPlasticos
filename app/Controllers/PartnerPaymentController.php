<?php
// app/Controllers/PartnerPaymentController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Config/helpers.php';

class PartnerPaymentController extends BaseController
{
    public function viewPendingPayments(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $userModel = new Usuario();
        $user = $userModel->findById((int)$_SESSION['user_id']);
        $idPartner = (int)($user['idPartner'] ?? 0);
        
        if (!$idPartner) {
            $_SESSION['error'] = "No estás asociado a un socio.";
            $this->redirect('dashboard');
            return;
        }

        $paymentModel = new Payment();
        
        // Parámetros de paginación y filtros
        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = 10;
        $yearFilter = $_GET['year'] ?? null;

        // Obtener pagos pendientes con paginación
        $pendingResult = $paymentModel->getPendingByPartner($idPartner, $yearFilter, $page, $pageSize);
        $pendingPayments = $pendingResult['data'];
        $total = $pendingResult['total'];
        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;

        // Obtener años disponibles para filtro
        $availableYears = $paymentModel->getAvailableYears($idPartner);
        
        // Totales
        $totals = $paymentModel->getTotalsByPartner($idPartner);

        $success = $_SESSION['payment_success'] ?? null;
        $error = $_SESSION['payment_error'] ?? null;
        unset($_SESSION['payment_success'], $_SESSION['payment_error']);
        
        // Recuperar la URL del QR
        $qrImageUrl = $paymentModel->getQrImageUrl((int)$_SESSION['user_id']);
        
        // ============ PROCESAR PAGO INDIVIDUAL ============
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
            try {
                $idContribution = (int)($_POST['idContribution'] ?? 0);
                $amount = (float)($_POST['amount'] ?? 0);
                $methodId = 2; // Solo transferencia bancaria

                // Validaciones básicas
                if ($idContribution <= 0 || $amount <= 0) {
                    throw new \Exception("Datos de pago inválidos.");
                }

                // Validar archivo de comprobante
                $proofValidation = $this->validateUploadedFile('proof', 'comprobante');
                if ($proofValidation !== true) {
                    throw new \Exception($proofValidation);
                }

                // Obtener detalles de la contribución
                $contribution = $paymentModel->getContributionDetails($idContribution);
                if (!$contribution) {
                    throw new \Exception("Contribución no encontrada.");
                }
                
                $monthYear = $contribution['monthYear'] ?? date('Y-m');

                // Manejar subida de comprobante
                $proofUrl = $this->handleUpload('proof', $idPartner, $monthYear, 'comprobante');
                if (!$proofUrl) {
                    throw new \Exception("Error al subir el comprobante de pago.");
                }

                if ($paymentModel->processPayment($idContribution, $amount, $methodId, $idPartner, $proofUrl)) {
                    $_SESSION['payment_success'] = "Pago enviado para revisión. Será procesado en 24-48 horas.";
                    
                    // Create a notification for the new contribution
                    $notificationData = [
                        'title' => 'Nueva Pago de '. $user[''],
                        'message' => "Se ha pagado una contribución por un monto de $amount para el mes $monthYear",
                        'type' => 'info',
                        'data' => json_encode([
                            'contribution_id' => $contributionId,
                            'amount' => $amount,
                            'monthYear' => $monthYear
                        ]),
                        'idRol' => 1
                    ];

                    $notification = new \App\Models\Notification();
                    $notificationId = $notification->create($notificationData);
                    
                    if ($notificationId === false) {
                        error_log("Error: No se pudo crear la notificación para la contribución $contributionId");
                        throw new \Exception("Fallo al crear la notificación de contribución");
                    } else {
                        error_log("Notificación creada con ID: " . $notificationId);
                    }
                    $this->redirect('partner/pending-payments');
                    return;
                } else {
                    throw new \Exception("Error al procesar el pago.");
                }

            } catch (\Throwable $e) {
                error_log("Pago falló: " . $e->getMessage());
                $_SESSION['payment_error'] = $e->getMessage();
                $this->redirect('partner/pending-payments');
                return;
            }
        }
        
        // ============ PROCESAR PAGO MÚLTIPLE ============
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'payMultiple') {
            try {
                $selectedContributions = isset($_POST['selected_contributions']) ? $_POST['selected_contributions'] : [];
                $totalAmount = (float)($_POST['totalAmount'] ?? 0);
                $methodId = 2; // Transferencia bancaria

                if (empty($selectedContributions)) {
                    throw new \Exception("No se seleccionaron contribuciones.");
                }

                if ($totalAmount <= 0) {
                    throw new \Exception("El monto total debe ser mayor a 0.");
                }

                // Validar archivo de comprobante múltiple
                $proofValidation = $this->validateUploadedFile('proof', 'comprobante múltiple');
                if ($proofValidation !== true) {
                    throw new \Exception($proofValidation);
                }

                // Validar contribuciones seleccionadas
                $expectedTotal = 0;
                $validContributions = [];
                foreach ($selectedContributions as $idContribution) {
                    $idContribution = (int)$idContribution;
                    $contribution = $paymentModel->getContributionDetails($idContribution);
                    if (!$contribution) {
                        throw new \Exception("Contribución #$idContribution no encontrada.");
                    }
                    $balance = $paymentModel->getContributionBalance($idContribution, $idPartner);
                    if ($balance <= 0) {
                        throw new \Exception("La contribución #$idContribution no tiene saldo pendiente.");
                    }
                    $expectedTotal += $balance;
                    $validContributions[] = ['idContribution' => $idContribution, 'amount' => $balance];
                }

                // Verificar que el total coincida
                if (abs($expectedTotal - $totalAmount) > 0.01) {
                    throw new \Exception("El monto total no coincide con la suma de las contribuciones seleccionadas.");
                }

                // Determinar monthYear
                $monthYear = date('Y-m');
                if (!empty($validContributions)) {
                    $firstContribution = $paymentModel->getContributionDetails($validContributions[0]['idContribution']);
                    $monthYear = $firstContribution['monthYear'] ?? $monthYear;
                }

                // Subir comprobante
                $proofUrl = $this->handleUpload('proof', $idPartner, $monthYear, 'comprobante_multiple');
                if (!$proofUrl) {
                    throw new \Exception("Error al subir el comprobante de pago.");
                }

                if ($paymentModel->processMultiplePayments($validContributions, $methodId, $idPartner, $proofUrl)) {
                    $_SESSION['payment_success'] = "Pagos enviados para revisión. Serán procesados en 24-48 horas.";
                    $this->redirect('partner/pending-payments');
                    return;
                } else {
                    throw new \Exception("Error al procesar los pagos.");
                }
            } catch (\Throwable $e) {
                error_log("Pago múltiple falló: " . $e->getMessage());
                $_SESSION['payment_error'] = $e->getMessage();
                $this->redirect('partner/pending-payments');
                return;
            }
        }

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        $this->view('partner/pending-payments', [
            'pendingPayments' => $pendingPayments,
            'totals' => $totals,
            'availableYears' => $availableYears,
            'yearFilter' => $yearFilter,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'success' => $success,
            'error' => $error,
            'idPartner' => $idPartner,
            'page' => $page,
            'pageSize' => $pageSize,
            'total' => $total,
            'totalPages' => $totalPages,
            'qrImageUrl' => $qrImageUrl,
        ]);
    }

   public function viewPaymentHistory(): void
{
    $this->startSession();
    if (!isset($_SESSION['user_id'])) {
        $this->redirect('login');
        return;
    }

    $userModel = new Usuario();
    $user = $userModel->findById((int)$_SESSION['user_id']);
    $idPartner = (int)($user['idPartner'] ?? 0);
    
    if (!$idPartner) {
        $_SESSION['error'] = "No estás asociado a un socio.";
        $this->redirect('dashboard');
        return;
    }

    $paymentModel = new Payment();
    $page = max(1, (int)($_GET['page'] ?? 1));
    $pageSize = (int)($_GET['pageSize'] ?? 20);
    $yearFilter = $_GET['year'] ?? null; // Cambio: ahora filtramos por año

    // Obtener historial con filtro por año
    $historyResult = $paymentModel->getHistoryByPartner($idPartner, $yearFilter, $page, $pageSize);
    $historyPayments = $historyResult['data'];
    $total = $historyResult['total'];
    $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;

    $totals = $paymentModel->getTotalsByPartner($idPartner);

    // NUEVO: Obtener total pagado en el historial filtrado
     $totalPaidInHistory = $paymentModel->getTotalPaidInHistory($idPartner, $yearFilter);

    // Obtener años disponibles
    $availableYears = $paymentModel->getAvailableYears($idPartner);

    $success = $_SESSION['payment_success'] ?? null;
    $error = $_SESSION['payment_error'] ?? null;
    unset($_SESSION['payment_success'], $_SESSION['payment_error']);

    require_once __DIR__ . '/../Models/Competence.php';
    $roleId = (int)($_SESSION['role'] ?? 2);
    $menuOptions = (new Competence())->getByRole($roleId);

    // Definir filters para la vista (cambio: year en lugar de filter)
    $filters = array_filter([
        'year' => $yearFilter,
        'pageSize' => $pageSize
    ]);

    $this->view('partner/payment-history', [
        'historyPayments' => $historyPayments,
        'totals' => $totals,
        'totalPaidInHistory' => $totalPaidInHistory, // NUEVO
        'availableYears' => $availableYears, // NUEVO
        'yearFilter' => $yearFilter, // Cambio: pasamos yearFilter
        'filters' => $filters,
        'menuOptions' => $menuOptions,
        'roleId' => $roleId,
        'success' => $success,
        'error' => $error,
        'idPartner' => $idPartner,
        'page' => $page,
        'pageSize' => $pageSize,
        'total' => $total,
        'totalPages' => $totalPages,
    ]);
}

     /**
     * Valida un archivo subido (igual que en OnlinePartnerController)
     */
    private function validateUploadedFile(string $fieldName, string $displayName): string|true
    {
        if (!isset($_FILES[$fieldName])) {
            return "Falta el archivo de $displayName.";
        }

        $file = $_FILES[$fieldName];
        
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return "Debe seleccionar un archivo para $displayName.";
        }
        
        if ($file['error'] === UPLOAD_ERR_FORM_SIZE || $file['error'] === UPLOAD_ERR_INI_SIZE) {
            return "El archivo de $displayName excede el tamaño máximo de 2MB.";
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return "Error al subir el archivo de $displayName (Código: {$file['error']}).";
        }

        // Validar tamaño (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return "El archivo de $displayName excede el tamaño máximo de 2MB.";
        }

        // Validar que no esté vacío
        if ($file['size'] === 0) {
            return "El archivo de $displayName está vacío.";
        }

        // Validar tipo de archivo (JPG, PNG, PDF)
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes, true)) {
            return "El archivo de $displayName debe ser JPG, PNG o PDF.";
        }

        // Validar extensión del archivo también
        $fileName = strtolower($file['name']);
        $allowedExtensions = ['.jpg', '.jpeg', '.png', '.pdf'];
        $hasValidExtension = false;
        foreach ($allowedExtensions as $ext) {
            if (substr($fileName, -strlen($ext)) === $ext) {
                $hasValidExtension = true;
                break;
            }
        }

        if (!$hasValidExtension) {
            return "La extensión del archivo de $displayName no es válida. Use JPG, PNG o PDF.";
        }

        return true;
    }

    /**
     * Obtiene información básica del archivo para mantener referencia
     */
    private function getFileInfo(string $fieldName): array|null
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        return [
            'name' => $_FILES[$fieldName]['name'],
            'size' => $_FILES[$fieldName]['size'],
            'type' => $_FILES[$fieldName]['type']
        ];
    }

    /**
     * Maneja la subida de archivos de comprobantes
     */
    private function handleUpload(string $inputName, int $idPartner, string $monthYear, string $prefix = 'comprobante'): ?string
    {
        error_log("[$inputName] Attempting upload at " . date('Y-m-d H:i:s') . ": " . print_r($_FILES[$inputName] ?? 'No file', true));

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_FORM_SIZE) {
                throw new \RuntimeException("El archivo {$inputName} excede el tamaño máximo permitido de 2MB.");
            }
            error_log("[$inputName] Upload failed: Error code " . ($_FILES[$inputName]['error'] ?? 'No file uploaded'));
            throw new \RuntimeException("Error al subir el archivo {$inputName}.");
        }

        $fileTmp = $_FILES[$inputName]['tmp_name'];
        $fileName = basename((string)$_FILES[$inputName]['name']);
        $fileSize = (int)$_FILES[$inputName]['size'];

        // MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = $finfo ? finfo_file($finfo, $fileTmp) : @mime_content_type($fileTmp);
        if ($finfo) finfo_close($finfo);

        // Validaciones
        if ($fileSize > 2 * 1024 * 1024) {
            error_log("[$inputName] File size exceeds 2MB: $fileSize bytes");
            throw new \RuntimeException("El archivo {$inputName} excede los 2MB permitidos.");
        }

        // Aceptar JPG, PNG y PDF
        if (!in_array($fileType, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
            error_log("[$inputName] Invalid file type: $fileType");
            throw new \RuntimeException("El archivo {$inputName} debe ser JPG, PNG o PDF.");
        }

        $publicDir = 'images/receipts';
        $uploadDirFs = rtrim(p($publicDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        if (!is_dir($uploadDirFs)) {
            error_log("[$inputName] Directory $uploadDirFs does not exist, attempting to create...");
            if (!mkdir($uploadDirFs, 0777, true) && !is_dir($uploadDirFs)) {
                error_log("[$inputName] Failed to create directory $uploadDirFs");
                throw new \RuntimeException("No se pudo crear el directorio para guardar el archivo.");
            }
            error_log("[$inputName] Directory $uploadDirFs created successfully.");
        } elseif (!is_writable($uploadDirFs)) {
            error_log("[$inputName] Directory $uploadDirFs is not writable.");
            throw new \RuntimeException("El directorio de destino no tiene permisos de escritura.");
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        
        // Nombre único con timestamp
        $newName = "{$prefix}_{$idPartner}_" . str_replace('-', '', $monthYear) . "_" . time() . "." . $ext;
        $destPathFs = $uploadDirFs . $newName;
        error_log("[$inputName] Destination path (fs): $destPathFs");

        if (!move_uploaded_file($fileTmp, $destPathFs)) {
            error_log("[$inputName] move_uploaded_file failed for $destPathFs");
            throw new \RuntimeException("No se pudo guardar el archivo {$inputName}.");
        }
        error_log("[$inputName] File uploaded successfully to $destPathFs");

        $publicRelative = trim($publicDir, '/\\') . '/' . $newName;
        return $publicRelative;
    }
}