<?php
// app/Controllers/PaymentController.php

require_once __DIR__ . '/BaseController.php';

class PaymentController extends BaseController
{
    /**
     * Listado de pagos
     */
    public function list(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        // Menú dinámico
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        // Modelo Payment
        require_once __DIR__ . '/../Models/Payment.php';
        $paymentModel = new \Payment();

        $payments = $paymentModel->getAllPaginated(20, 0);
        $total    = $paymentModel->countAll();

        $this->view('payment/list', [
            'payments'    => $payments,
            'total'       => $total,
            'menuOptions' => $menuOptions,
            'roleId'      => $roleId,
            'currentPath' => 'payment',
        ]);
    }

    /**
     * Crear pago
     */
    public function create(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        // Menú dinámico
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Payment.php';
        $paymentModel = new \Payment();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'idPartner'     => $_POST['idPartner'] ?? null,
                'idContribution'=> $_POST['idContribution'] ?? null,
                'idPaymentType' => $_POST['idPaymentType'] ?? null,
                'paidAmount'    => $_POST['paidAmount'] ?? 0,
            ];

            if ($paymentModel->create($data)) {
                $this->redirect('payment/list');
            }
        }

        $this->view('payment/create', [
            'menuOptions' => $menuOptions,
            'roleId'      => $roleId,
            'currentPath' => 'payment',
        ]);
    }

    /**
     * Editar pago
     */
    public function edit(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        // Menú dinámico
        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new \Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Payment.php';
        $paymentModel = new \Payment();
        $payment = $paymentModel->findById($id);

        if (!$payment) {
            $this->redirect('payment/list');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'idPartner'     => $_POST['idPartner'] ?? null,
                'idContribution'=> $_POST['idContribution'] ?? null,
                'idPaymentType' => $_POST['idPaymentType'] ?? null,
                'paidAmount'    => $_POST['paidAmount'] ?? 0,
            ];

            if ($paymentModel->update($id, $data)) {
                $this->redirect('payment/list');
            }
        }

        $this->view('payment/edit', [
            'payment'     => $payment,
            'menuOptions' => $menuOptions,
            'roleId'      => $roleId,
            'currentPath' => 'payment',
        ]);
    }

    /**
     * Eliminar pago
     */
    public function delete(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
        }

        require_once __DIR__ . '/../Models/Payment.php';
        $paymentModel = new \Payment();
        $paymentModel->delete($id);

        $this->redirect('payment/list');
    }
}
