<?php
// app/Controllers/ContributionController.php
declare(strict_types=1);

use App\Models\Notification;

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/Contribution.php';

class ContributionController extends BaseController
{
    public function list(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Contribution.php';
        $contributionModel = new Contribution();
        $contributions = $contributionModel->getAll();

        // Determine lastMonthYear and defaultMonthYear at the top
        $lastMonthYear = $contributionModel->getLastMonthYear();
        if ($lastMonthYear) {
            $lastDate = DateTime::createFromFormat('Y-m', $lastMonthYear);
            $lastDate->modify('first day of next month');
            $defaultMonthYear = $lastDate->format('Y-m');
        } else {
            $defaultMonthYear = date('Y-m', strtotime('first day of this month'));
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db = \Database::singleton()->getConnection();
            $db->beginTransaction();

            try {
                if (isset($_POST['action']) && $_POST['action'] === 'create') {
                    $amount = trim((string)($_POST['amount'] ?? ''));
                    $monthParts = explode('-', $defaultMonthYear);
                    $year = $monthParts[0];
                    $monthNum = (int)$monthParts[1];
                    $monthNames = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                    ];
                    $defaultNotes = "Cuota mensual {$monthNames[$monthNum]} {$year}";
                    $dateCreation = date('Y-m-d H:i:s');
                    $finalAmount = floatval($amount);
                    if ($finalAmount <= 0) {
                        throw new \Exception("El monto debe ser mayor a 0");
                    }
                    $contributionId = $contributionModel->create($finalAmount, $defaultNotes, $dateCreation, $defaultMonthYear);
                    if (!$contributionId) {
                        throw new \Exception("Failed to create contribution");
                    }
                } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
                    $id = (int)($_POST['id'] ?? 0);
                    $amount = trim((string)($_POST['amount'] ?? ''));
                    $contribution = $contributionModel->findById($id);
                    if (!$contribution) {
                        throw new \Exception("Contribución no encontrada");
                    }
                    $finalAmount = floatval($amount);
                    if ($finalAmount <= 0) {
                        throw new \Exception("El monto debe ser mayor a 0");
                    }
                    $dateUpdate = date('Y-m-d H:i:s');
                    if (!$contributionModel->update($id, $finalAmount, $contribution['notes'], $dateUpdate, $contribution['monthYear'])) {
                        throw new \Exception("Failed to update contribution");
                    }
                } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
                    $id = (int)($_POST['id'] ?? 0);
                    $contribution = $contributionModel->findById($id);
                    if (!$contribution || !$contributionModel->delete($id)) {
                        throw new \Exception("Contribución no encontrada o no se pudo eliminar");
                    }
                }
                $db->commit();
                $this->redirect('contribution/list');
                return;
            } catch (\Throwable $e) {
                $db->rollBack();
                error_log("Transaction failed: " . $e->getMessage());
                $error = "Error: " . $e->getMessage();
            }
        }

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $this->view('contribution/list', [
            'contributions' => $contributions,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'error' => $error,
            'defaultAmount' => 15.00,
            'defaultMonthYear' => $defaultMonthYear,
            'monthNames' => $monthNames,
        ]);
    }
    public function create(): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        $defaultAmount = 15.00;

        // Fetch last monthYear from DB to compute next
        require_once __DIR__ . '/../Models/Contribution.php';
        $contributionModel = new Contribution();
        $lastMonthYear = $contributionModel->getLastMonthYear();
        if ($lastMonthYear) {
            $lastDate = DateTime::createFromFormat('Y-m', $lastMonthYear);
            $lastDate->modify('first day of next month');
            $nextMonth = $lastDate->format('Y-m');
        } else {
            $nextMonth = date('Y-m', strtotime('first day of this month'));
        }

        // Default notes based on nextMonth
        $monthParts = explode('-', $nextMonth);
        $year = $monthParts[0];
        $monthNum = (int)$monthParts[1];
        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        $defaultNotes = "Cuota mensual {$monthNames[$monthNum]} {$year}";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = trim((string)($_POST['amount'] ?? ''));
            $monthYear = $nextMonth; // Fixed to nextMonth, no user input
            $notes = $defaultNotes;  // Fixed to defaultNotes, no user input

            if ($amount === '') {
                $error = "El monto es obligatorio";
            } else {
                $db = \Database::singleton()->getConnection();
                $db->beginTransaction();

                try {
                    $dateCreation = date('Y-m-d H:i:s');
                    $finalAmount = floatval($amount);
                    if ($finalAmount <= 0) {
                        throw new \Exception("El monto debe ser mayor a 0");
                    }
                    $contributionId = $contributionModel->create($finalAmount, $notes, $dateCreation, $monthYear);
                    require_once __DIR__ . '/../Models/Notification.php';
                        $notificationModel = new Notification();
                        $notificationModel->create(
                            [
                                'title'   => 'Nueva Contribucion',
                                'message' => $defaultNotes,
                                'type'    => 'info',
                                'data'    => 'Información adicional',
                                'idRol'   => 2,//rol=2 es socio
                            ]
                        );
                    if ($contributionId) {
                        $db->commit();
                        

                        $this->redirect('contribution/list');
                        return;
                    } else {
                        throw new \Exception("Failed to create contribution");
                    }
                } catch (\Throwable $e) {
                    $db->rollBack();
                    error_log("Transaction failed: " . $e->getMessage());
                    $error = "Error al crear contribución: " . $e->getMessage();
                }
            }
        }

        $this->view('contribution/create', [
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'error' => $error ?? null,
            'defaultMonthYear' => $nextMonth,
            'defaultAmount' => $defaultAmount,
            'defaultNotes' => $defaultNotes,
        ]);
    }
public function update(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        require_once __DIR__ . '/../Models/Competence.php';
        $roleId = (int)($_SESSION['role'] ?? 2);
        $menuOptions = (new Competence())->getByRole($roleId);

        require_once __DIR__ . '/../Models/Contribution.php';
        $contributionModel = new Contribution();

        $contribution = $contributionModel->findById($id);
        if (!$contribution) {
            $_SESSION['error'] = 'Contribución no encontrada';
            $this->redirect('contribution/list');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $amount = trim((string)($_POST['amount'] ?? ''));
            $notes = trim((string)($_POST['notes'] ?? ''));
            $monthYear = trim((string)($_POST['monthYear'] ?? ''));

            if ($amount === '' || $monthYear === '') {
                $error = "El monto y mes/año son obligatorios";
            } else {
                $db = \Database::singleton()->getConnection();
                $db->beginTransaction();

                try {
                    $dateUpdate = date('Y-m-d H:i:s');
                    if ($contributionModel->update($id, $amount, $notes, $dateUpdate, $monthYear)) {
                        $db->commit();
                        $this->redirect('contribution/list');
                        return;
                    } else {
                        throw new \Exception("Failed to update contribution");
                    }
                } catch (\Throwable $e) {
                    $db->rollBack();
                    error_log("Transaction failed: " . $e->getMessage());
                    $error = "Error al actualizar contribución: " . $e->getMessage();
                }
            }
        }

        $this->view('contribution/edit', [
            'contribution' => $contribution,
            'menuOptions' => $menuOptions,
            'roleId' => $roleId,
            'error' => $error ?? null,
        ]);
    }

    public function delete(int $id): void
    {
        $this->startSession();
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('login');
            return;
        }
        requireRole([1], 'login');

        require_once __DIR__ . '/../Models/Contribution.php';
        $contributionModel = new Contribution();
        $db = \Database::singleton()->getConnection();

        $db->beginTransaction();
        try {
            $contribution = $contributionModel->findById($id);
            if ($contribution && $contributionModel->delete($id)) {
                $db->commit();
            } else {
                throw new \Exception("Contribución no encontrada o no se pudo eliminar");
            }
            $this->redirect('contribution/list');
        } catch (\Throwable $e) {
            $db->rollBack();
            error_log("Transaction failed: " . $e->getMessage());
            $this->view('contribution/list', [
                'contributions' => $contributionModel->getAll(),
                'error' => 'Error al eliminar: ' . $e->getMessage(),
            ]);
        }
    }
}