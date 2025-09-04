<?php
// app/Models/Payment.php

require_once __DIR__ . '/../Config/database.php';

class Payment
{
    private const TABLE       = '`payment`';
    private const TABLE_PARTNER = '`partner`';
    private const TABLE_CONTRIB = '`contribution`';
    private const TABLE_TYPE  = '`paymenttype`';

    /** @var \PDO */
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    // =========================================================
    // ===============       CONSULTAS BÁSICAS     =============
    // =========================================================

    /** Listado paginado con joins */
    public function getAllPaginated(int $limit = 20, int $offset = 0): array {
        try {
            $sql = "
                SELECT 
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation AS paymentDate,

                    pa.idPartner,
                    pa.name AS partnerName,
                    pa.ci AS partnerCI,
                    pa.cellPhoneNumber,
                    pa.address,

                    c.idContribution,
                    c.amount AS contributionAmount,
                    c.notes AS contributionNotes,

                    pt.description AS paymentType
                FROM " . self::TABLE . " p
                INNER JOIN " . self::TABLE_PARTNER . " pa ON p.idPartner = pa.idPartner
                INNER JOIN " . self::TABLE_CONTRIB . " c ON p.idContribution = c.idContribution
                INNER JOIN " . self::TABLE_TYPE . " pt ON p.idPaymentType = pt.idPaymentType
                ORDER BY p.dateCreation DESC
                LIMIT :limit OFFSET :offset
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener pagos: " . $e->getMessage());
            return [];
        }
    }

    /** Contar registros para paginación */
    public function countAll(): int {
        try {
            $sql = "SELECT COUNT(*) AS total FROM " . self::TABLE;
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)($row['total'] ?? 0);
        } catch (\PDOException $e) {
            error_log("Error al contar pagos: " . $e->getMessage());
            return 0;
        }
    }

    /** Buscar un pago por ID */
    public function findById(int $id): ?array {
        try {
            $sql = "
                SELECT 
                    p.idPayment,
                    p.paidAmount,
                    p.dateCreation AS paymentDate,

                    pa.idPartner,
                    pa.name AS partnerName,
                    pa.ci AS partnerCI,
                    pa.cellPhoneNumber,
                    pa.address,

                    c.idContribution,
                    c.amount AS contributionAmount,
                    c.notes AS contributionNotes,

                    pt.description AS paymentType
                FROM " . self::TABLE . " p
                INNER JOIN " . self::TABLE_PARTNER . " pa ON p.idPartner = pa.idPartner
                INNER JOIN " . self::TABLE_CONTRIB . " c ON p.idContribution = c.idContribution
                INNER JOIN " . self::TABLE_TYPE . " pt ON p.idPaymentType = pt.idPaymentType
                WHERE p.idPayment = :id
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al buscar pago por ID: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================
    // ===============       CREAR / EDITAR        =============
    // =========================================================

    /** Crear nuevo pago */
    public function create(array $data): bool {
        try {
            $sql = "INSERT INTO " . self::TABLE . "
                (idPartner, idContribution, idPaymentType, paidAmount, dateCreation)
                VALUES (:idPartner, :idContribution, :idPaymentType, :paidAmount, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':idPartner', $data['idPartner'], \PDO::PARAM_INT);
            $stmt->bindValue(':idContribution', $data['idContribution'], \PDO::PARAM_INT);
            $stmt->bindValue(':idPaymentType', $data['idPaymentType'], \PDO::PARAM_INT);
            $stmt->bindValue(':paidAmount', $data['paidAmount'], \PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al crear pago: " . $e->getMessage());
            return false;
        }
    }

    /** Actualizar pago existente */
    public function update(int $id, array $data): bool {
        try {
            $sql = "UPDATE " . self::TABLE . "
                SET idPartner = :idPartner,
                    idContribution = :idContribution,
                    idPaymentType = :idPaymentType,
                    paidAmount = :paidAmount
                WHERE idPayment = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->bindValue(':idPartner', $data['idPartner'], \PDO::PARAM_INT);
            $stmt->bindValue(':idContribution', $data['idContribution'], \PDO::PARAM_INT);
            $stmt->bindValue(':idPaymentType', $data['idPaymentType'], \PDO::PARAM_INT);
            $stmt->bindValue(':paidAmount', $data['paidAmount'], \PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar pago: " . $e->getMessage());
            return false;
        }
    }

    /** Eliminar pago */
    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM " . self::TABLE . " WHERE idPayment = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al eliminar pago: " . $e->getMessage());
            return false;
        }
    }
}
