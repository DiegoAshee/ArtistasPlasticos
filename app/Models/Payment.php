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
                        WHEN p.paymentStatus = 0 THEN 'Pagado'
                        WHEN p.paymentStatus = 2 THEN 'Rechazado'
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
    /**
     * Obtener estadísticas de pagos
     */
    /**
 * Obtener estadísticas de pagos
 */
public function getPaymentStats(): array {
    try {
        $query = "
            SELECT 
                SUM(CASE WHEN paymentStatus = 1 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN paymentStatus = 0 THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN paymentStatus = 2 THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN paymentStatus = 1 THEN paidAmount ELSE 0 END) as totalAmount
            FROM " . self::TBL_PAYMENT . "
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'pending' => (int)($result['pending'] ?? 0),
            'approved' => (int)($result['approved'] ?? 0),
            'rejected' => (int)($result['rejected'] ?? 0),
            'totalAmount' => (float)($result['totalAmount'] ?? 0)
        ];
    } catch (PDOException $e) {
        error_log("Error al obtener estadísticas: " . $e->getMessage());
        return ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'totalAmount' => 0];
    }
}

    /**
     * Obtener socios con pagos pendientes
     */
    public function getPartnersWithPendingPayments(array $filters = [], int $page = 1, int $pageSize = 20): array {
        try {
            $offset = ($page - 1) * $pageSize;
            $conditions = [];
            $params = [];

            // Filtros
            if (!empty($filters['partner'])) {
                $conditions[] = "(pa.name LIKE :partnerName OR COALESCE(pa.ci, '') LIKE :partnerCI)";
                $params[':partnerName'] = '%' . $filters['partner'] . '%';
                $params[':partnerCI'] = '%' . $filters['partner'] . '%';
            }

            if (!empty($filters['status'])) {
                $conditions[] = "p.paymentStatus = :status";
                $params[':status'] = (int)$filters['status'];
            } else {
                $conditions[] = "p.paymentStatus = 1"; // Solo pendientes por defecto
            }

            if (!empty($filters['dateFrom'])) {
                $conditions[] = "DATE(p.dateCreation) >= :dateFrom";
                $params[':dateFrom'] = $filters['dateFrom'];
            }

            if (!empty($filters['dateTo'])) {
                $conditions[] = "DATE(p.dateCreation) <= :dateTo";
                $params[':dateTo'] = $filters['dateTo'];
            }

            $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

            $query = "
                SELECT SQL_CALC_FOUND_ROWS
                    pa.idPartner,
                    pa.name,
                    COALESCE(pa.ci, '') as ci,
                    pa.dateRegistration,
                    COUNT(p.idPayment) as pendingPayments,
                    SUM(p.paidAmount) as totalPending,
                    MAX(p.dateCreation) as lastPaymentDate
                FROM " . self::TBL_PARTNER . " pa
                INNER JOIN " . self::TBL_PAYMENT . " p ON pa.idPartner = p.idPartner
                $whereClause
                GROUP BY pa.idPartner, pa.name, pa.ci, pa.dateRegistration
                ORDER BY lastPaymentDate DESC
                LIMIT :offset, :limit
            ";

            $stmt = $this->db->prepare($query);
            
            // Bind parámetros de filtros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = (int)$this->db->query("SELECT FOUND_ROWS()")->fetchColumn();

            return ['data' => $data, 'total' => $total];
        } catch (PDOException $e) {
            error_log("Error al obtener socios con pagos pendientes: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Obtener información de un socio
     */
    public function getPartnerInfo(int $idPartner): ?array {
        try {
            $query = "SELECT idPartner, name, COALESCE(ci, '') as ci, dateRegistration FROM " . self::TBL_PARTNER . " WHERE idPartner = :idPartner";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':idPartner', $idPartner, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error al obtener información del socio: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener datos para recibo de un pago individual
     */
    public function getReceiptDataForPayment(int $paymentId): ?array {
        try {
            $query = "
                SELECT 
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation,
                    p.voucherImageURL,
                    pa.idPartner,
                    pa.name as partnerName,
                    COALESCE(pa.ci, '') as partnerCI,
                    c.idContribution,
                    c.monthYear,
                    c.notes as contributionName,
                    c.amount as contributionAmount,
                    pt.description as paymentType
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_PARTNER . " pa ON p.idPartner = pa.idPartner
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                LEFT JOIN " . self::TBL_PAYMENTTYPE . " pt ON p.idPaymentType = pt.idPaymentType
                WHERE p.idPayment = :paymentId
            ";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':paymentId', $paymentId, PDO::PARAM_INT);
            $stmt->execute();
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) return null;

            return [
                'partner' => [
                    'idPartner' => $payment['idPartner'],
                    'name' => $payment['partnerName'],
                    'ci' => $payment['partnerCI']
                ],
                'payments' => [$payment],
                'total' => (float)$payment['paidAmount'],
                'date' => $payment['dateCreation'],
                'isMultiple' => false
            ];

        } catch (PDOException $e) {
            error_log("Error al obtener datos de recibo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener datos para recibo de múltiples pagos
     */
    public function getReceiptDataForMultiplePayments(array $paymentIds): ?array {
        if (empty($paymentIds)) return null;

        try {
            $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
            
            $query = "
                SELECT 
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation,
                    p.voucherImageURL,
                    pa.idPartner,
                    pa.name as partnerName,
                    COALESCE(pa.ci, '') as partnerCI,
                    c.idContribution,
                    c.monthYear,
                    c.notes as contributionName,
                    c.amount as contributionAmount,
                    pt.description as paymentType
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_PARTNER . " pa ON p.idPartner = pa.idPartner
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                LEFT JOIN " . self::TBL_PAYMENTTYPE . " pt ON p.idPaymentType = pt.idPaymentType
                WHERE p.idPayment IN ($placeholders)
                ORDER BY p.idPayment
            ";

            $stmt = $this->db->prepare($query);
            $stmt->execute($paymentIds);
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($payments)) return null;

            $total = 0;
            foreach ($payments as $payment) {
                $total += (float)$payment['paidAmount'];
            }

            return [
                'partner' => [
                    'idPartner' => $payments[0]['idPartner'],
                    'name' => $payments[0]['partnerName'],
                    'ci' => $payments[0]['partnerCI']
                ],
                'payments' => $payments,
                'total' => $total,
                'date' => $payments[0]['dateCreation'],
                'isMultiple' => count($payments) > 1
            ];

        } catch (PDOException $e) {
            error_log("Error al obtener datos de recibo múltiple: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener pagos agrupados por comprobante para un socio
     */
    public function getPaymentGroupsByPartner(int $idPartner, array $filters = []): array {
        try {
            $conditions = ["p.idPartner = :idPartner"];
            $params = [':idPartner' => $idPartner];

            if (!empty($filters['status'])) {
                $conditions[] = "p.paymentStatus = :status";
                $params[':status'] = (int)$filters['status'];
            } else {
                $conditions[] = "p.paymentStatus = 1"; // Solo pendientes por defecto
            }

            if (!empty($filters['dateFrom'])) {
                $conditions[] = "DATE(p.dateCreation) >= :dateFrom";
                $params[':dateFrom'] = $filters['dateFrom'];
            }

            if (!empty($filters['dateTo'])) {
                $conditions[] = "DATE(p.dateCreation) <= :dateTo";
                $params[':dateTo'] = $filters['dateTo'];
            }

            $whereClause = "WHERE " . implode(' AND ', $conditions);

            $query = "
                SELECT 
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation as paymentDate,
                    p.voucherImageURL,
                    p.paymentStatus,
                    c.idContribution,
                    c.monthYear,
                    c.notes as contributionName
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                $whereClause
                ORDER BY p.dateCreation DESC, p.voucherImageURL, p.idPayment
            ";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar por comprobante (voucherImageURL + fecha)
            $groups = [];
            foreach ($payments as $payment) {
                $groupKey = $payment['voucherImageURL'] . '|' . date('Y-m-d H:i', strtotime($payment['paymentDate']));
                
                if (!isset($groups[$groupKey])) {
                    $groups[$groupKey] = [
                        'groupId' => implode(',', []), // Se llenará después
                        'voucherImageURL' => $payment['voucherImageURL'],
                        'paymentDate' => $payment['paymentDate'],
                        'totalAmount' => 0,
                        'payments' => []
                    ];
                }
                
                $groups[$groupKey]['payments'][] = $payment;
                $groups[$groupKey]['totalAmount'] += (float)$payment['paidAmount'];
            }

            // Completar groupId con los IDs de los pagos
            foreach ($groups as &$group) {
                $paymentIds = array_column($group['payments'], 'idPayment');
                $group['groupId'] = implode(',', $paymentIds);
            }

            return array_values($groups);
        } catch (PDOException $e) {
            error_log("Error al obtener grupos de pagos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los pagos para revisión
     */
    public function getAllPaymentsForReview(array $filters = [], int $page = 1, int $pageSize = 20): array {
        try {
            $offset = ($page - 1) * $pageSize;
            $conditions = [];
            $params = [];

            if (!empty($filters['partner'])) {
                $conditions[] = "(pa.name LIKE :partnerName OR pa.ci LIKE :partnerCI)";
                $params[':partnerName'] = '%' . $filters['partner'] . '%';
                $params[':partnerCI'] = '%' . $filters['partner'] . '%';
            }

            if (!empty($filters['status'])) {
                $conditions[] = "p.paymentStatus = :status";
                $params[':status'] = (int)$filters['status'];
            }

            if (!empty($filters['dateFrom'])) {
                $conditions[] = "DATE(p.dateCreation) >= :dateFrom";
                $params[':dateFrom'] = $filters['dateFrom'];
            }

            if (!empty($filters['dateTo'])) {
                $conditions[] = "DATE(p.dateCreation) <= :dateTo";
                $params[':dateTo'] = $filters['dateTo'];
            }

            $whereClause = !empty($conditions) ? "WHERE " . implode(' AND ', $conditions) : "";

            $query = "
                SELECT SQL_CALC_FOUND_ROWS
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation,
                    p.voucherImageURL,
                    p.paymentStatus,
                    pa.idPartner,
                    pa.name as partnerName,
                    pa.ci as partnerCI,
                    c.idContribution,
                    c.monthYear,
                    c.notes as contributionName
                FROM " . self::TBL_PAYMENT . " p
                INNER JOIN " . self::TBL_PARTNER . " pa ON p.idPartner = pa.idPartner
                INNER JOIN " . self::TBL_CONTRIBUTION . " c ON p.idContribution = c.idContribution
                $whereClause
                ORDER BY p.dateCreation DESC
                LIMIT :offset, :limit
            ";

            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $pageSize, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total = (int)$this->db->query("SELECT FOUND_ROWS()")->fetchColumn();

            return ['data' => $data, 'total' => $total];
        } catch (PDOException $e) {
            error_log("Error al obtener pagos para revisión: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Actualizar estado de un pago
     */
    public function updatePaymentStatus(int $idPayment, int $newStatus): bool {
        try {
            $query = "UPDATE " . self::TBL_PAYMENT . " SET paymentStatus = :status WHERE idPayment = :idPayment";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $newStatus, PDO::PARAM_INT);
            $stmt->bindParam(':idPayment', $idPayment, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar estado de pago: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de múltiples pagos
     */
    public function updateMultiplePaymentStatus(array $paymentIds, int $newStatus): int {
    error_log("Updating payments: " . print_r($paymentIds, true) . " with status: $newStatus"); // Depuración
    if (empty($paymentIds)) {
        return 0;
    }
    try {
        $this->db->beginTransaction();
        $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
        $query = "UPDATE " . self::TBL_PAYMENT . " SET paymentStatus = ? WHERE idPayment IN ($placeholders)";
        error_log("Query: $query"); // Depuración
        $stmt = $this->db->prepare($query);
        $params = array_merge([$newStatus], $paymentIds);
        error_log("Params: " . print_r($params, true)); // Depuración
        $stmt->execute($params);
        $updated = $stmt->rowCount();
        $this->db->commit();
        return $updated;
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error al actualizar múltiples estados de pago: " . $e->getMessage());
        return 0;
    }
}
}