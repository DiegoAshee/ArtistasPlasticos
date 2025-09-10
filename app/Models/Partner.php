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

    public function getPendingByPartner(int $idPartner): array {
        try {
            $query = "
                SELECT 
                    c.idContribution, c.amount, c.notes, c.dateCreation as contrib_date, c.monthYear, c.dateUpdate
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
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Pending payments for idPartner $idPartner: " . json_encode($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener pagos pendientes: " . $e->getMessage());
            return [];
        }
    }

    public function getHistoryByPartner(int $idPartner, ?string $monthYear = null): array {
        try {
            $query = "
                SELECT 
                    c.idContribution, c.amount, c.notes, c.dateCreation as contrib_date, c.monthYear, c.dateUpdate,
                    p.idPayment, p.paidAmount, p.dateCreation as payment_date, p.status, p.proofUrl, pt.description as payment_type
                FROM " . self::TBL_CONTRIBUTION . " c
                INNER JOIN " . self::TBL_PAYMENT . " p ON c.idContribution = p.idContribution
                LEFT JOIN " . self::TBL_PAYMENTTYPE . " pt ON p.idPaymentType = pt.idPaymentType
                WHERE p.idPartner = :idPartner AND p.status = 1 -- Solo pagos aceptados
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
            error_log("Historial para idPartner $idPartner: " . json_encode($result));
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }

    public function processPayment(int $idContribution, float $amount, int $methodId, int $idPartner, ?string $proofUrl = null): bool {
        try {
            $this->db->beginTransaction();
            $queryCheck = "SELECT idPayment, paidAmount FROM " . self::TBL_PAYMENT . " WHERE idContribution = :idContribution";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            $stmtCheck->execute();
            $existingPayment = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingPayment) {
                $newAmount = ($existingPayment['paidAmount'] ?? 0) + $amount;
                $query = "UPDATE " . self::TBL_PAYMENT . " SET paidAmount = :amount, idPaymentType = :methodId, proofUrl = :proofUrl, status = 0, dateCreation = NOW() WHERE idPayment = :idPayment";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':amount', $newAmount, PDO::PARAM_STR);
                $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
                $stmt->bindParam(':proofUrl', $proofUrl, PDO::PARAM_STR);
                $stmt->bindParam(':idPayment', $existingPayment['idPayment'], PDO::PARAM_INT);
            } else {
                $query = "INSERT INTO " . self::TBL_PAYMENT . " (paidAmount, dateCreation, idPartner, idPaymentType, idContribution, proofUrl, status) VALUES (:amount, NOW(), :idPartner, :methodId, :idContribution, :proofUrl, 0)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
                $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
                $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
                $stmt->bindParam(':proofUrl', $proofUrl, PDO::PARAM_STR);
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

    public function getTotalsByPartner(int $idPartner, bool $onlyPaid = false): array {
        try {
            $pendingQuery = "
                SELECT SUM(c.amount - COALESCE(p.paidAmount, 0)) as total_pending
                FROM " . self::TBL_CONTRIBUTION . " c
                LEFT JOIN " . self::TBL_PAYMENT . " p ON c.idContribution = p.idContribution
                WHERE c.idContribution NOT IN (
                    SELECT idContribution FROM " . self::TBL_PAYMENT . " WHERE idPartner = :idPartner
                )
                AND c.dateCreation > (
                    SELECT dateCreation FROM " . self::TBL_PARTNER . " WHERE idPartner = :idPartner
                )
            ";
            $paidQuery = "
                SELECT SUM(p.paidAmount) as total_paid
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                WHERE p.idPartner = :idPartner AND p.status = 1
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