<?php
// app/Controllers/PartnerPaymentController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Usuario.php';
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
        $pageSize = 10; // Menos registros por página para pagos pendientes
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

        // Procesar pago
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
            
            try {
                $idContribution = (int)($_POST['idContribution'] ?? 0);
                $amount = (float)($_POST['amount'] ?? 0);
                $methodId = 2; // Solo transferencia bancaria

                // Validaciones
                if ($idContribution <= 0 || $amount <= 0) {
                    throw new \Exception("Datos de pago inválidos.");
                }

                // Obtener detalles de la contribución
                $contribution = $paymentModel->getContributionDetails($idContribution);
                if (!$contribution) {
                    throw new \Exception("Contribución no encontrada.");
                }
                
                $monthYear = $contribution['monthYear'] ?? date('Y-m');

                // Manejar subida de comprobante (requerido)
                $proofUrl = $this->handleUpload('proof', $idPartner, $monthYear, 'comprobante');
                if (!$proofUrl) {
                    throw new \Exception("El comprobante de pago es obligatorio.");
                }

                if ($paymentModel->processPayment($idContribution, $amount, $methodId, $idPartner, $proofUrl)) {
                    
                    $_SESSION['payment_success'] = "Pago enviado para revisión. Será procesado en 24-48 horas.";
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
        $monthYearFilter = $_GET['filter'] ?? null;

        // Obtener historial SIN array_slice adicional
        $historyResult = $paymentModel->getHistoryByPartner($idPartner, $monthYearFilter, $page, $pageSize);
        $historyPayments = $historyResult['data'];
        $total = $historyResult['total'];
        $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;

        $totals = $paymentModel->getTotalsByPartner($idPartner);

        $success = $_SESSION['payment_success'] ?? null;
        $error = $_SESSION['payment_error'] ?? null;
        unset($_SESSION['payment_success'], $_SESSION['payment_error']);

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        // Definir filters para la vista
        $filters = array_filter([
            'filter' => $monthYearFilter,
            'pageSize' => $pageSize
        ]);

        $this->view('partner/payment-history', [
            'historyPayments' => $historyPayments,
            'totals' => $totals,
            'monthYearFilter' => $monthYearFilter,
            'filters' => $filters, // IMPORTANTE: Definir filters
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

    private function handleUpload(string $inputName, int $idPartner, string $monthYear, string $prefix = 'comprobante'): ?string
    {
        error_log("[$inputName] Attempting upload at " . date('Y-m-d H:i:s') . ": " . print_r($_FILES[$inputName] ?? 'No file', true));

        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            error_log("[$inputName] Upload failed: Error code " . ($_FILES[$inputName]['error'] ?? 'No file uploaded'));
            return null;
        }

        $fileTmp = $_FILES[$inputName]['tmp_name'];
        $fileName = basename((string)$_FILES[$inputName]['name']);
        $fileSize = (int)$_FILES[$inputName]['size'];

        // MIME real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = $finfo ? finfo_file($finfo, $fileTmp) : @mime_content_type($fileTmp);
        if ($finfo) finfo_close($finfo);

        // Validations
        if ($fileSize > 2 * 1024 * 1024) {
            error_log("[$inputName] File size exceeds 2MB: $fileSize bytes");
            throw new \RuntimeException("El archivo {$inputName} excede los 2MB.");
        }
        // Aceptar JPG, PNG y PDF
        if (!in_array($fileType, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
            error_log("[$inputName] Invalid file type: $fileType");
            throw new \RuntimeException("El archivo {$inputName} debe ser JPG, PNG o PDF.");
        }

        $publicDir = 'images/receipts';
        $uploadDirFs = rtrim(p($publicDir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        if (!is_dir($uploadDirFs)) {
            if (!mkdir($uploadDirFs, 0777, true) && !is_dir($uploadDirFs)) {
                throw new \RuntimeException("No se pudo crear el directorio para guardar el archivo.");
            }
        } elseif (!is_writable($uploadDirFs)) {
            throw new \RuntimeException("El directorio de destino no tiene permisos de escritura.");
        }

        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') $ext = 'jpg';
        
        // Nombre único con timestamp
        $newName = "{$prefix}_{$idPartner}_" . str_replace('-', '', $monthYear) . "_" . time() . "." . $ext;
        $destPathFs = $uploadDirFs . $newName;

        if (!move_uploaded_file($fileTmp, $destPathFs)) {
            throw new \RuntimeException("No se pudo guardar el archivo {$inputName}.");
        }

        $publicRelative = trim($publicDir, '/\\') . '/' . $newName;
        return $publicRelative;
    }
}