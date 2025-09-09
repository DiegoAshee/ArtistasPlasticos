<?php
// app/Models/Payment.php
require_once __DIR__ . '/../Config/database.php';

class Payment {
    private const TBL_PAYMENT = '`payment`';
    private const TBL_CONTRIBUTION = '`contribution`';
    private const TBL_PARTNER = '`partner`';
    private const TBL_PAYMENTTYPE = '`paymenttype`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    // Obtener pagos pendientes por socio (contribuciones sin pago completo)
    public function getPendingByPartner(int $idPartner): array 
    {
        try {
            $query = "
                SELECT 
                    c.idContribution, c.amount, c.notes, c.dateCreation as contrib_date, c.monthYear, c.dateUpdate,
                    NULL as idPayment, NULL as paidAmount, NULL as payment_date, NULL as payment_type
                FROM " . self::TBL_CONTRIBUTION . " c
                WHERE c.idContribution NOT IN (
                    SELECT idContribution FROM " . self::TBL_PAYMENT . " WHERE idPartner = :idPartner
                )
                AND c.dateCreation > (
                    SELECT dateCreation FROM " . self::TBL_PARTNER . " WHERE idPartner = :idPartner
                )
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener pagos pendientes: " . $e->getMessage());
            return [];
        }
    }

    // Obtener historial de pagos por socio (pagos completados)
    public function getHistoryByPartner(int $idPartner, ?string $monthYear = null): array {
    try {
        $query = "
            SELECT 
                c.idContribution, c.amount, c.notes, c.dateCreation as contrib_date, c.monthYear, c.dateUpdate,
                p.idPayment, p.paidAmount, p.dateCreation as payment_date, pt.description as payment_type
            FROM " . self::TBL_CONTRIBUTION . " c
            INNER JOIN " . self::TBL_PAYMENT . " p ON c.idContribution = p.idContribution
            LEFT JOIN " . self::TBL_PAYMENTTYPE . " pt ON p.idPaymentType = pt.idPaymentType
            WHERE p.idPartner = :idPartner
        ";
        $params = [':idPartner' => [$idPartner, PDO::PARAM_INT]];
        if ($monthYear) {
            $query .= " AND c.monthYear = :monthYear";
            $params[':monthYear'] = [$monthYear, PDO::PARAM_STR];
        }
        $query .= " ORDER BY p.dateCreation DESC";

        $stmt = $this->db->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindParam($key, $value[0], $value[1]);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Depuración: Registrar resultados
        error_log("Historial para idPartner $idPartner: " . json_encode($result));
        return $result;
    } catch (PDOException $e) {
        error_log("Error al obtener historial: " . $e->getMessage());
        return [];
    }
}

    // Procesar pago (actualizar o insertar pago)
    public function processPayment(int $idContribution, float $amount, int $methodId, int $idPartner): bool {
        try {
            $this->db->beginTransaction();
            // Verificar si ya existe un pago para esta contribución
            $queryCheck = "SELECT idPayment, paidAmount FROM " . self::TBL_PAYMENT . " WHERE idContribution = :idContribution";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            $stmtCheck->execute();
            $existingPayment = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingPayment) {
                // Actualizar pago existente
                $newAmount = ($existingPayment['paidAmount'] ?? 0) + $amount;
                $query = "UPDATE " . self::TBL_PAYMENT . " SET paidAmount = :amount, idPaymentType = :methodId, dateCreation = NOW() WHERE idPayment = :idPayment";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':amount', $newAmount, PDO::PARAM_STR);
                $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
                $stmt->bindParam(':idPayment', $existingPayment['idPayment'], PDO::PARAM_INT);
            } else {
                // Insertar nuevo pago
                $query = "INSERT INTO " . self::TBL_PAYMENT . " (paidAmount, dateCreation, idPartner, idPaymentType, idContribution) VALUES (:amount, NOW(), :idPartner, :methodId, :idContribution)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
                $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
                $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            }

            $result = $stmt->execute();
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al procesar pago: " . $e->getMessage());
            return false;
        }
    }

    // Calcular totales (pendiente y pagado)
    public function getTotalsByPartner(int $idPartner): array {
        try {
            $pendingQuery = "
                SELECT SUM(c.amount - COALESCE(p.paidAmount, 0)) as total_pending
                FROM " . self::TBL_CONTRIBUTION . " c
                LEFT JOIN " . self::TBL_PAYMENT . " p ON c.idPartner = p.idPartner AND c.idContribution = p.idContribution
                WHERE c.idPartner = :idPartner AND (p.idPayment IS NULL OR p.paidAmount < c.amount OR p.paidAmount IS NULL)
            ";
            $paidQuery = "
                SELECT SUM(p.paidAmount) as total_paid
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                WHERE c.idPartner = :idPartner AND p.paidAmount > 0
            ";

            $stmt = $this->db->prepare($pendingQuery);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            $pending = $stmt->fetch(PDO::FETCH_ASSOC)['total_pending'] ?? 0;

            $stmt = $this->db->prepare($paidQuery);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            $paid = $stmt->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;

            return ['pending' => $pending, 'paid' => $paid];
        } catch (PDOException $e) {
            error_log("Error al calcular totales: " . $e->getMessage());
            return ['pending' => 0, 'paid' => 0];
        }
    }
}