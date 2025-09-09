<?php
// app/Controllers/PartnerPaymentController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Payment.php';
require_once __DIR__ . '/../Models/Usuario.php';  // Para obtener idPartner del usuario

class PaymentController extends BaseController
{
    public function viewPayments(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }

        // Obtener idPartner del usuario logueado
        $userModel = new Usuario();
        $user = $userModel->findById((int)$_SESSION['user_id']);
        $idPartner = (int)($user['idPartner'] ?? 0);

        if (!$idPartner) {
            $_SESSION['error'] = "No estás asociado a un socio.";
            $this->redirect('dashboard');
            return;
        }

        $paymentModel = new Payment();
        $pendingPayments = $paymentModel->getPendingByPartner($idPartner);
        $historyPayments = $paymentModel->getHistoryByPartner($idPartner);  // Sin filtro inicial
        $totals = $paymentModel->getTotalsByPartner($idPartner);

        $monthYearFilter = $_GET['filter'] ?? null;  // Para filtro en historial
        if ($monthYearFilter) {
            $historyPayments = $paymentModel->getHistoryByPartner($idPartner, $monthYearFilter);
        }

        $success = $_SESSION['payment_success'] ?? null;
        $error = $_SESSION['payment_error'] ?? null;
        unset($_SESSION['payment_success'], $_SESSION['payment_error']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $db = \Database::singleton()->getConnection();
    $db->beginTransaction();
    try {
        $idContribution = (int)($_POST['idContribution'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $methodId = (int)($_POST['paymentType'] ?? 1);
        // Usar el idPartner obtenido previamente
        if (!$idPartner) {
            throw new \Exception("No se encontró el ID del socio.");
        }
        if ($paymentModel->processPayment($idContribution, $amount, $methodId, $idPartner)) {
            $db->commit();
            $_SESSION['payment_success'] = "Pago realizado exitosamente.";
            $this->redirect('partner/payments');
            return;
        } else {
            throw new \Exception("Error al procesar el pago.");
        }
    } catch (\Throwable $e) {
        $db->rollBack();
        error_log("Pago falló: " . $e->getMessage());
        $_SESSION['payment_error'] = $e->getMessage();
    }
}

        // Menú (asumir Competence para roles, como en ejemplo)
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        $this->view('partner/payment', [
            'pendingPayments' => $pendingPayments,
            'historyPayments' => $historyPayments,
            'totals' => $totals,
            'monthYearFilter' => $monthYearFilter,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'success' => $success,
            'error' => $error,
            'idPartner' => $idPartner,
        ]);
    }
}