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

    /**
     * Obtener contribuciones pendientes con filtros y paginación
     */
    public function getPendingByPartner(int $idPartner, ?string $year = null, int $page = 1, int $pageSize = 10): array {
    try {
        $offset = ($page - 1) * $pageSize;

        $query = "
            SELECT SQL_CALC_FOUND_ROWS
                c.idContribution,
                c.amount,
                c.notes,
                c.dateCreation AS contrib_date,
                c.monthYear,
                c.dateUpdate,
                COALESCE(p.sum_paid, 0) AS paidAmount,
                (c.amount - COALESCE(p.sum_paid, 0)) AS balance
            FROM " . self::TBL_CONTRIBUTION . " c
            LEFT JOIN (
                SELECT idContribution, SUM(paidAmount) AS sum_paid
                FROM " . self::TBL_PAYMENT . "
                WHERE idPartner = :idPartner
                GROUP BY idContribution
            ) p ON p.idContribution = c.idContribution
            WHERE (p.sum_paid IS NULL OR p.sum_paid < c.amount)
              AND c.dateCreation > (
                  SELECT pa.dateRegistration 
                  FROM " . self::TBL_PARTNER . " pa 
                  WHERE pa.idPartner = :idPartner
                  LIMIT 1
              )
        ";

        // Filtro por año si se proporciona
        if ($year) {
            $query .= " AND YEAR(STR_TO_DATE(c.monthYear, '%Y-%m')) = :year";
        }

        $query .= "
            ORDER BY c.dateCreation DESC
            LIMIT :offset, :limit
        ";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
        if ($year) {
            $stmt->bindParam(':year', $year, PDO::PARAM_STR);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = (int)$this->db->query("SELECT FOUND_ROWS()")->fetchColumn();

        return ['data' => $data, 'total' => $total];
    } catch (PDOException $e) {
        error_log("Error al obtener pagos pendientes: " . $e->getMessage());
        return ['data' => [], 'total' => 0];
    }
}


    /**
     * Obtener años disponibles para filtros
     */
    public function getAvailableYears(int $idPartner): array {
        try {
            $query = "
                SELECT DISTINCT YEAR(STR_TO_DATE(monthYear, '%Y-%m')) as year
                FROM " . self::TBL_CONTRIBUTION . "
                WHERE idPartner = :idPartner
                ORDER BY year DESC
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error al obtener años: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Historial paginado por socio (SIN array_slice adicional)
     */
    public function getHistoryByPartner(int $idPartner, ?string $monthYear = null, int $page = 1, int $pageSize = 20): array {
        try {
            $offset = ($page - 1) * $pageSize;

            $query = "
                SELECT SQL_CALC_FOUND_ROWS
                    c.idContribution,
                    c.amount,
                    c.notes,
                    c.dateCreation AS contrib_date,
                    c.monthYear,
                    c.dateUpdate,
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation AS payment_date,
                    pt.description AS payment_type,
                    p.voucherImageURL,
                    CASE 
                        WHEN p.paymentStatus = 1 THEN 'Pendiente'
                        WHEN p.paymentStatus = 2 THEN 'Aprobado'
                        WHEN p.paymentStatus = 3 THEN 'Rechazado'
                        ELSE 'Desconocido'
                    END as status_text,
                    p.paymentStatus
                FROM " . self::TBL_CONTRIBUTION . " c
                INNER JOIN " . self::TBL_PAYMENT . " p ON c.idContribution = p.idContribution
                LEFT JOIN " . self::TBL_PAYMENTTYPE . " pt ON p.idPaymentType = pt.idPaymentType
                WHERE p.idPartner = :idPartner
            ";

            if ($monthYear) {
                $query .= " AND c.monthYear = :monthYear";
            }

            $query .= " ORDER BY p.dateCreation DESC LIMIT :offset, :limit";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':idPartner', $idPartner, PDO::PARAM_INT);
            if ($monthYear) $stmt->bindValue(':monthYear', $monthYear, PDO::PARAM_STR);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Total real
            $total = (int)$this->db->query("SELECT FOUND_ROWS()")->fetchColumn();

            return ['data' => $data, 'total' => $total];
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Procesar pago con status pendiente (1)
     */
    public function processPayment(int $idContribution, float $amount, int $methodId, int $idPartner, ?string $voucherImageURL = null): bool {
        try {
            $this->db->beginTransaction();

            // Insertar pago con status pendiente (1)
            $query = "INSERT INTO " . self::TBL_PAYMENT . " 
                (paidAmount, dateCreation, idPartner, idPaymentType, idContribution, voucherImageURL, paymentStatus)
                VALUES (:amount, NOW(), :idPartner, :methodId, :idContribution, :voucherImageURL, 1)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
            $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            $stmt->bindValue(':voucherImageURL', $voucherImageURL, $voucherImageURL ? PDO::PARAM_STR : PDO::PARAM_NULL);

            $result = $stmt->execute();
            $this->db->commit();
            return (bool)$result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al procesar pago: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Totales por socio: solo contar pagos aprobados (status = 2)
     */
    public function getTotalsByPartner(int $idPartner): array {
        try {
            // Total pendiente: contributions - approved payments
            $pendingQuery = "
                SELECT SUM(c.amount - COALESCE(sub.paidSum,0)) as total_pending
                FROM " . self::TBL_CONTRIBUTION . " c
                LEFT JOIN (
                    SELECT idContribution, SUM(paidAmount) as paidSum
                    FROM " . self::TBL_PAYMENT . "
                    WHERE idPartner = :idPartner AND paymentStatus = 2
                    GROUP BY idContribution
                ) sub ON c.idContribution = sub.idContribution
                WHERE c.idPartner = :idPartner
            ";

            // Solo pagos aprobados
            $paidQuery = "
                SELECT COALESCE(SUM(paidAmount),0) as total_paid
                FROM " . self::TBL_PAYMENT . " p
                WHERE p.idPartner = :idPartner AND p.paymentStatus = 2
            ";

            $stmt = $this->db->prepare($pendingQuery);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            $pending = $stmt->fetch(PDO::FETCH_ASSOC)['total_pending'] ?? 0;

            $stmt = $this->db->prepare($paidQuery);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            $paid = $stmt->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;

            return ['pending' => (float)$pending, 'paid' => (float)$paid];
        } catch (PDOException $e) {
            error_log("Error al calcular totales: " . $e->getMessage());
            return ['pending' => 0, 'paid' => 0];
        }
    }

    /**
     * Obtener detalles de una contribución
     */
    public function getContributionDetails(int $idContribution): ?array {
        try {
            $query = "SELECT idContribution, amount, notes, dateCreation, monthYear, dateUpdate FROM " . self::TBL_CONTRIBUTION . " WHERE idContribution = :idContribution";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener detalles de contribución: " . $e->getMessage());
            return null;
        }
    }
    public function getContributionBalance(int $idContribution, int $idPartner): float {
        try {
            $query = "
                SELECT (c.amount - COALESCE(SUM(p.paidAmount), 0)) AS balance
                FROM " . self::TBL_CONTRIBUTION . " c
                LEFT JOIN " . self::TBL_PAYMENT . " p ON p.idContribution = c.idContribution AND p.idPartner = :idPartner
                WHERE c.idContribution = :idContribution
                GROUP BY c.idContribution, c.amount
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($result['balance'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error al obtener saldo de contribución: " . $e->getMessage());
            return 0.0;
        }
    }

    /**
     * Procesar múltiples pagos con status pendiente (1)
     */
    public function processMultiplePayments(array $contributions, int $methodId, int $idPartner, ?string $voucherImageURL = null): bool {
        if (empty($contributions)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            $query = "INSERT INTO " . self::TBL_PAYMENT . " 
                (paidAmount, dateCreation, idPartner, idPaymentType, idContribution, voucherImageURL, paymentStatus)
                VALUES (:amount, NOW(), :idPartner, :methodId, :idContribution, :voucherImageURL, 1)";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->bindParam(':methodId', $methodId, PDO::PARAM_INT);
            $stmt->bindValue(':voucherImageURL', $voucherImageURL, $voucherImageURL ? PDO::PARAM_STR : PDO::PARAM_NULL);

            foreach ($contributions as $contribution) {
                $idContribution = (int)$contribution['idContribution'];
                $amount = (float)$contribution['amount'];

                $stmt->bindParam(':idContribution', $idContribution, PDO::PARAM_INT);
                $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);

                if (!$stmt->execute()) {
                    throw new \PDOException("Error al insertar pago para contribución #$idContribution");
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("Error al procesar pagos múltiples: " . $e->getMessage());
            return false;
        }
    }
    public function getQrImageUrl(): ?string {
        try {
            $query = "SELECT imageURL FROM `option` WHERE status = 1 ORDER BY idOption DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result && !empty($result['imageURL'])) {
                return BASE_URL . $result['imageURL']; // Concatena BASE_URL con la ruta
            }
            return null; // Retorna null si no hay QR
        } catch (PDOException $e) {
            error_log("Error al recuperar QR: " . $e->getMessage());
            return null;
        }
    }
}