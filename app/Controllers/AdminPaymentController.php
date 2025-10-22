<?php
// app/Controllers/AdminPaymentController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Usuario.php';
require_once __DIR__ . '/../Config/helpers.php';

class AdminPaymentController extends BaseController
{
    public function reviewPayments(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Verificar permisos de administrador (ajusta según tu sistema de roles)
        $roleId = (int)($_SESSION['role'] ?? 0);
        if ($roleId !== 1) { // Asumiendo que 1 es admin
            $_SESSION['error'] = "No tienes permisos para acceder a esta sección.";
            $this->redirect('dashboard');
            return;
        }

        $paymentModel = new Payment();

        // Procesar acciones POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePaymentActions($paymentModel);
            return;
        }

        // Parámetros de filtros y paginación
        $filters = [
            'partner' => $_GET['partner'] ?? '',
            'status' => $_GET['status'] ?? '1', // Por defecto pendientes
            'dateFrom' => $_GET['dateFrom'] ?? '',
            'dateTo' => $_GET['dateTo'] ?? ''
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $pageSize = (int)($_GET['pageSize'] ?? 20);
        $viewMode = $_GET['mode'] ?? 'partners'; // 'partners' o 'payments'
        $selectedPartnerId = (int)($_GET['partner'] ?? 0);

        // Obtener estadísticas
        $stats = $paymentModel->getPaymentStats();

        if ($viewMode === 'partners' && !$selectedPartnerId) {
            // Vista de socios con pagos pendientes
            $result = $paymentModel->getPartnersWithPendingPayments($filters, $page, $pageSize);
            $partners = $result['data'];
            $total = $result['total'];
            $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;
require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);
            $this->view('admin/review-payments', [
                'viewMode' => $viewMode,
                'partners' => $partners,
                'payments' => [],
                'paymentGroups' => [],
                'selectedPartner' => null,
                'filters' => $filters,
                'stats' => $stats,
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => $totalPages,
                'menuOptions' => $menuOptions,
        'roleId' => $roleId,
        'success' => $_SESSION['payment_success'] ?? null,
                'error' => $_SESSION['payment_error'] ?? null,
            ]);
        } elseif ($viewMode === 'partners' && $selectedPartnerId) {
            // Detalle de pagos de un socio específico
            $selectedPartner = $paymentModel->getPartnerInfo($selectedPartnerId);
            if (!$selectedPartner) {
                $_SESSION['error'] = "Socio no encontrado.";
                $this->redirect('admin/review-payments?mode=partners');
                return;
            }

            $paymentGroups = $paymentModel->getPaymentGroupsByPartner($selectedPartnerId, $filters);
require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);
            $this->view('admin/review-payments', [
                'viewMode' => $viewMode,
                'partners' => [],
                'payments' => [],
                'paymentGroups' => $paymentGroups,
                'selectedPartner' => $selectedPartner,
                'filters' => $filters,
                'stats' => $stats,
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => count($paymentGroups),
                'totalPages' => 1,
                'menuOptions' => $menuOptions,
        'roleId' => $roleId,
        'success' => $_SESSION['payment_success'] ?? null,
                'error' => $_SESSION['payment_error'] ?? null,
            ]);
        } else {
            // Vista de todos los pagos
            $result = $paymentModel->getAllPaymentsForReview($filters, $page, $pageSize);
            $payments = $result['data'];
            $total = $result['total'];
            $totalPages = $pageSize > 0 ? (int)ceil($total / $pageSize) : 1;
require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);
            $this->view('admin/review-payments', [
                'viewMode' => $viewMode,
                'partners' => [],
                'payments' => $payments,
                'paymentGroups' => [],
                'selectedPartner' => null,
                'filters' => $filters,
                'stats' => $stats,
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => $totalPages,
                'menuOptions' => $menuOptions,
        'roleId' => $roleId,
        'success' => $_SESSION['payment_success'] ?? null,
                'error' => $_SESSION['payment_error'] ?? null,
            ]);
        }

        unset($_SESSION['payment_success'], $_SESSION['payment_error']);
    }

    private function handlePaymentActions(Payment $paymentModel): void
    {
        try {
            $action = $_POST['action'] ?? '';
            $returnUrl = $_POST['return_url'] ?? 'admin/review-payments';

            switch ($action) {
                case 'approve':
                    $paymentId = (int)($_POST['payment_id'] ?? 0);
                    if ($paymentId <= 0) {
                        throw new \Exception("ID de pago inválido.");
                    }
                    
                    if ($paymentModel->updatePaymentStatus($paymentId, 0)) { // 0 = Aprobado (como cobros directos)
                        // Obtener datos para recibo
                        $receiptData = $paymentModel->getReceiptDataForPayment($paymentId);
                        if ($receiptData) {
                            $_SESSION['admin_receipt_data'] = $receiptData;
                            $_SESSION['payment_success'] = "Pago #$paymentId aprobado exitosamente. Se generó el recibo.";
                        } else {
                            $_SESSION['payment_success'] = "Pago #$paymentId aprobado exitosamente.";
                        }
                    } else {
                        throw new \Exception("Error al aprobar el pago.");
                    }
                    break;

                case 'reject':
                    $paymentId = (int)($_POST['payment_id'] ?? 0);
                    if ($paymentId <= 0) {
                        throw new \Exception("ID de pago inválido.");
                    }
                    
                    if ($paymentModel->updatePaymentStatus($paymentId, 2)) { // 2 = Rechazado
                        $_SESSION['payment_success'] = "Pago #$paymentId rechazado exitosamente.";
                    } else {
                        throw new \Exception("Error al rechazar el pago.");
                    }
                    break;

                case 'approve_group':
                case 'reject_group':
                    error_log("POST Data: " . print_r($_POST, true)); // Depuración
                    $paymentIds = $_POST['payment_ids'] ?? '';
                    if (empty($paymentIds)) {
                        throw new \Exception("No se proporcionaron IDs de pagos.");
                    }

                    $ids = array_map('intval', explode(',', $paymentIds));
                    $ids = array_filter($ids, function($id) { return $id > 0; });
                    
                    if (empty($ids)) {
                        throw new \Exception("IDs de pagos inválidos.");
                    }

                    if ($action === 'approve_group') {
                        $updated = $paymentModel->updateMultiplePaymentStatus($ids, 0); // 0 = Aprobado
                        if ($updated > 0) {
                            // Generar el recibo y redirigir
                            $_SESSION['success'] = "Se procesaron {$updated} pagos correctamente.";
                            
                            $_SESSION['receipt_payment_ids'] = $ids;
                            
                            if (!empty($errors)) {
                                $_SESSION['success'] .= " Errores: " . implode(', ', $errors);
                            }
                            
                            $this->redirect('cobros/recibo');
                            return;
                        } else {
                            // Concatenar los IDs en el mensaje de error
                            $idsString = implode(', ', $ids);
                            throw new \Exception("No se pudieron aprobar los pagos con IDs: $idsString. Verifica los estados o los datos enviados.");}
                    } else {
                        // CAMBIO: Eliminar en lugar de cambiar estado
                        $deleted = $paymentModel->deleteMultiplePayments($ids);
                        if ($deleted > 0) {
                            $_SESSION['payment_success'] = "$deleted pagos rechazados y eliminados. Los socios podrán volver a intentar los pagos.";
                        } else {
                            throw new \Exception("No se pudieron rechazar los pagos.");
                        }
                    }
                    break;

                default:
                    throw new \Exception("Acción no válida.");
            }

        } catch (\Exception $e) {
            error_log("Error en acción de pago: " . $e->getMessage());
            $_SESSION['payment_error'] = $e->getMessage();
        }

        $this->redirect($returnUrl);
    }

    /**
     * Mostrar recibo de pagos aprobados
     */
    public function showReceipt(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        $receiptData = $_SESSION['admin_receipt_data'] ?? null;
        if (!$receiptData) {
            $_SESSION['error'] = 'No se encontró información del recibo.';
            $this->redirect('admin/review-payments');
            return;
        }

        // Limpiar la sesión
        unset($_SESSION['admin_receipt_data']);

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        $this->view('admin/payment-receipt', [
            'receiptData' => $receiptData,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
        ]);
    }
    
}