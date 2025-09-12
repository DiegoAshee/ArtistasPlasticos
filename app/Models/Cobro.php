<?php
// app/Models/Cobro.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class Cobro {
    private \PDO $db;
    public function __construct() { $this->db = Database::singleton()->getConnection(); }

    /** Util: %like% seguro */
    private function like(string $q): string {
        $q = trim($q);
        return '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
    }

public function searchPagadas(array $f = [], int $page = 1, int $pageSize = 20, ?int &$total = null): array {
    $where = [];
    $par   = [];

    if (!empty($f['q'])) {
        // Busca por nombre socio, CI, tipo (pt.description) y aporte (c.notes)
        $where[] = "(pr.name LIKE :q OR pr.CI LIKE :q OR pt.description LIKE :q OR c.notes LIKE :q)";
        $par[':q'] = $this->like($f['q']);
    }
    if (!empty($f['idPaymentType'])) {
        $where[] = "p.idPaymentType = :t";
        $par[':t'] = (int)$f['idPaymentType'];
    }
    if (!empty($f['idContribution'])) {
        $where[] = "p.idContribution = :c";
        $par[':c'] = (int)$f['idContribution'];
    }
    if (!empty($f['from'])) { $where[] = "p.dateCreation >= :from"; $par[':from'] = $f['from'].' 00:00:00'; }
    if (!empty($f['to']))   { $where[] = "p.dateCreation <= :to";   $par[':to']   = $f['to'].' 23:59:59'; }
    
    // AGREGAR ESTE FILTRO QUE FALTABA:
    if (!empty($f['idPartner'])) {
        $where[] = "p.idPartner = :pid";
        $par[':pid'] = (int)$f['idPartner'];
    }

    $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

    // 1) Total (para mostrar # de páginas)
    $sqlCount = "
      SELECT COUNT(*)
      FROM payment p
      LEFT JOIN partner pr     ON pr.idPartner     = p.idPartner
      LEFT JOIN paymenttype pt ON pt.idPaymentType = p.idPaymentType
      LEFT JOIN contribution c ON c.idContribution = p.idContribution
      $whereSql
    ";
    $st = $this->db->prepare($sqlCount);
    foreach ($par as $k=>$v) $st->bindValue($k,$v);
    $st->execute();
    $total = (int)$st->fetchColumn();

    // 2) Datos paginados
    $offset = max(0, ($page-1) * $pageSize);
    $sql = "
      SELECT
        p.idPayment, p.paidAmount, p.dateCreation,
        p.idPartner, pr.name AS partnerName, pr.CI AS partnerCI,
        p.idPaymentType, pt.description AS paymentTypeName,
        p.idContribution, c.notes AS contributionName,
        p.idContribution, c.notes AS contributionName,
        CASE 
                        WHEN p.paymentStatus = 1 THEN 'Pendiente'
                        WHEN p.paymentStatus = 0 THEN 'Pagado'
                        WHEN p.paymentStatus = 2 THEN 'Rechazado'
                        ELSE 'Desconocido'
                    END as status_text,
        1 AS isPaid
      FROM payment p
      LEFT JOIN partner pr     ON pr.idPartner     = p.idPartner
      LEFT JOIN paymenttype pt ON pt.idPaymentType = p.idPaymentType
      LEFT JOIN contribution c ON c.idContribution = p.idContribution
      $whereSql
      -- MUY RÁPIDO: usa la PK (idPayment) en orden descendente
      ORDER BY p.idPayment DESC
      LIMIT :lim OFFSET :off
    ";
    $st = $this->db->prepare($sql);
    foreach ($par as $k=>$v) $st->bindValue($k,$v);
    $st->bindValue(':lim', $pageSize, \PDO::PARAM_INT);
    $st->bindValue(':off', $offset,   \PDO::PARAM_INT);
    $st->execute();
    return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}

public function searchDebidas(array $f = [], int $page = 1, int $pageSize = 20, ?int &$total = null): array {
    $params = [];
    
    // Construir condiciones de WHERE para contribution
    if (!empty($f['idContribution'])) {
        $contribWhere = "c.idContribution = :cid";
        $params[':cid'] = (int)$f['idContribution'];
    } else {
        // Mostrar todas las aportaciones posteriores al registro del socio
        $contribWhere = "c.dateCreation > pr.dateRegistration";
    }

    // Filtro de tipo de pago (opcional)
    $typeFilter = '';
    if (!empty($f['idPaymentType'])) {
        $typeFilter = "AND p.idPaymentType = :tid";
        $params[':tid'] = (int)$f['idPaymentType'];
    }

    // Filtro de búsqueda por texto
    $searchFilter = '';
    if (!empty($f['q'])) {
        $searchFilter = "AND (pr.name LIKE :q OR pr.CI LIKE :q OR c.notes LIKE :q)";
        $params[':q'] = $this->like($f['q']);
    }

    // Filtro por socio específico
    if (!empty($f['idPartner'])) {
        $searchFilter .= " AND pr.idPartner = :pid";
        $params[':pid'] = (int)$f['idPartner'];
    }

    // Consulta para el total
    $sqlCount = "
        SELECT COUNT(*)
        FROM partner pr
        CROSS JOIN contribution c
        WHERE $contribWhere
        AND NOT EXISTS (
            SELECT 1 FROM payment p
            WHERE p.idPartner = pr.idPartner
            AND p.idContribution = c.idContribution
            $typeFilter
        )
        $searchFilter
    ";
    
    $st = $this->db->prepare($sqlCount);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->execute();
    $total = (int)$st->fetchColumn();

    // Consulta para los datos paginados
    $offset = max(0, ($page - 1) * $pageSize);
    
    // Preparar los valores para el SELECT
    $paymentTypeId = !empty($f['idPaymentType']) ? (int)$f['idPaymentType'] : null;
    $paymentTypeName = 'NULL';
    
    if ($paymentTypeId) {
        $paymentTypeName = "(SELECT pt.description FROM paymenttype pt WHERE pt.idPaymentType = :tid_select)";
        $params[':tid_select'] = $paymentTypeId;
    }

    $sql = "
        SELECT
            pr.idPartner,
            pr.name AS partnerName,
            pr.CI AS partnerCI,
            pr.dateRegistration AS partnerRegistrationDate,
            c.idContribution,
            c.notes AS contributionName,
            c.amount,
            c.monthYear,
            c.dateCreation AS contributionDate,
            " . ($paymentTypeId ? ":tid_select2" : "NULL") . " AS idPaymentType,
            $paymentTypeName AS paymentTypeName,
            0 AS isPaid
        FROM partner pr
        CROSS JOIN contribution c
        WHERE $contribWhere
        AND NOT EXISTS (
            SELECT 1 FROM payment p
            WHERE p.idPartner = pr.idPartner
            AND p.idContribution = c.idContribution
            $typeFilter
        )
        $searchFilter
        ORDER BY pr.name ASC, c.idContribution ASC
        LIMIT :lim OFFSET :off
    ";

    $st = $this->db->prepare($sql);
    foreach ($params as $k => $v) {
        $st->bindValue($k, $v);
    }
    
    // Parámetros adicionales para el SELECT
    if ($paymentTypeId) {
        $st->bindValue(':tid_select2', $paymentTypeId, \PDO::PARAM_INT);
    }
    
    $st->bindValue(':lim', $pageSize, \PDO::PARAM_INT);
    $st->bindValue(':off', $offset, \PDO::PARAM_INT);
    $st->execute();
    
    return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}

// Método auxiliar para obtener detalles de la deuda
// En app/Models/Cobro.php, añade este método:
public function getDebtDetails(int $idPartner, int $idContribution): ?array
{
    try {
        $sql = "
            SELECT 
                pr.idPartner,
                pr.name AS partnerName,
                pr.CI AS partnerCI,
                c.idContribution,
                c.notes AS contributionName,
                c.amount,
                c.monthYear,
                c.dateCreation AS contributionDate
            FROM partner pr
            CROSS JOIN contribution c
            WHERE pr.idPartner = :idPartner
            AND c.idContribution = :idContribution
            AND NOT EXISTS (
                SELECT 1 FROM payment p
                WHERE p.idPartner = pr.idPartner
                AND p.idContribution = c.idContribution
            )
            AND c.dateCreation > pr.dateRegistration
            LIMIT 1
        ";
        
        $st = $this->db->prepare($sql);
        $st->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
        $st->bindValue(':idContribution', $idContribution, \PDO::PARAM_INT);
        $st->execute();
        
        return $st->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\PDOException $e) {
        error_log('Cobro::getDebtDetails '.$e->getMessage());
        return null;
    }
}








    /** === CRUD minimalista sobre payment === */
    public function findById(int $id): ?array {
        try {
            $sql = "
            SELECT
                p.*,
                pr.name AS partnerName, pr.CI AS partnerCI,
                pt.description AS paymentTypeName,
                c.notes  AS contributionName
            FROM `payment` p
            LEFT JOIN `partner`      pr ON pr.idPartner      = p.idPartner
            LEFT JOIN `paymenttype`  pt ON pt.idPaymentType  = p.idPaymentType
            LEFT JOIN `contribution` c  ON c.idContribution  = p.idContribution
            WHERE p.idPayment = :id
            LIMIT 1";
            $st = $this->db->prepare($sql);
            $st->bindValue(':id', $id, \PDO::PARAM_INT);
            $st->execute();
            $row = $st->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            error_log('Cobro::findById '.$e->getMessage());
            return null;
        }
    }

/**
 * SIN DEBUG
 * @param array $file
 * @param int $partnerId
 * @return array{error: string, success: bool|array{path: string, success: bool}}
 */

public function create(int $idPartner, int $idPaymentType, int $idContribution, float $paidAmount, ?string $voucherImageURL = null) {
    try {
        error_log("DEBUG Cobro::create - Parameters:");
        error_log("  idPartner: " . $idPartner);
        error_log("  idPaymentType: " . $idPaymentType);
        error_log("  idContribution: " . $idContribution);
        error_log("  paidAmount: " . $paidAmount);
        error_log("  voucherImageURL: " . ($voucherImageURL ?? 'NULL'));
        
        // Verificar que no exista ya un pago para esta combinación
        $stCheck = $this->db->prepare("
            SELECT idPayment FROM payment 
            WHERE idPartner = :idPartner 
            AND idContribution = :idContribution 
            LIMIT 1
        ");
        $stCheck->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
        $stCheck->bindValue(':idContribution', $idContribution, \PDO::PARAM_INT);
        $stCheck->execute();
        
        if ($stCheck->fetch()) {
            error_log("DEBUG: Payment already exists for this combination");
            throw new \Exception('Ya existe un pago para esta contribución');
        }

        // Preparar la consulta de inserción
        $sql = "
            INSERT INTO `payment`
            (paidAmount, dateCreation, voucherImageURL, paymentStatus, idPartner, idPaymentType, idContribution)
            VALUES (:paidAmount, NOW(), :voucherImageURL, 0, :idPartner, :idPaymentType, :idContribution)
        ";
        
        error_log("DEBUG: SQL Query: " . $sql);
        
        $st = $this->db->prepare($sql);
        $st->bindValue(':paidAmount', $paidAmount, \PDO::PARAM_STR);
        $st->bindValue(':voucherImageURL', $voucherImageURL, \PDO::PARAM_STR);
        $st->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
        $st->bindValue(':idPaymentType', $idPaymentType, \PDO::PARAM_INT);
        $st->bindValue(':idContribution', $idContribution, \PDO::PARAM_INT);
        
        $result = $st->execute();
        error_log("DEBUG: Execute result: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if ($result) {
            $insertId = $this->db->lastInsertId();
            error_log("DEBUG: Insert ID: " . $insertId);
            
            // Verificar que se insertó correctamente
            $stVerify = $this->db->prepare("SELECT voucherImageURL FROM payment WHERE idPayment = :id");
            $stVerify->bindValue(':id', $insertId, \PDO::PARAM_INT);
            $stVerify->execute();
            $row = $stVerify->fetch(\PDO::FETCH_ASSOC);
            
            if ($row) {
                error_log("DEBUG: Verified voucherImageURL in DB: " . ($row['voucherImageURL'] ?? 'NULL'));
            }
            
            return $insertId;
        }
        
        return false;
        
    } catch (\PDOException $e) {
        error_log('Cobro::create PDOException: ' . $e->getMessage());
        error_log('SQL State: ' . $e->getCode());
        return false;
    } catch (\Exception $e) {
        error_log('Cobro::create Exception: ' . $e->getMessage());
        return false;
    }
}

    public function update(int $idPayment, int $idPartner, int $idPaymentType, int $idContribution, float $paidAmount): bool {
        try {
            $st = $this->db->prepare("
                UPDATE `payment`
                SET paidAmount = :paidAmount,
                    idPartner = :idPartner,
                    idPaymentType = :idPaymentType,
                    idContribution = :idContribution
                WHERE idPayment = :id
            ");
            $st->bindValue(':id', $idPayment, \PDO::PARAM_INT);
            $st->bindValue(':paidAmount', $paidAmount);
            $st->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
            $st->bindValue(':idPaymentType', $idPaymentType, \PDO::PARAM_INT);
            $st->bindValue(':idContribution', $idContribution, \PDO::PARAM_INT);
            return $st->execute();
        } catch (\PDOException $e) {
            error_log('Cobro::update '.$e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $st = $this->db->prepare("DELETE FROM `payment` WHERE idPayment = :id");
            $st->bindValue(':id', $id, \PDO::PARAM_INT);
            return $st->execute();
        } catch (\PDOException $e) {
            error_log('Cobro::delete '.$e->getMessage());
            return false;
        }
    }

    // Catálogos (alias 'label' para usar directo en <select>)
    public function allPartners(): array {
        try {
            $st = $this->db->query("SELECT idPartner, name, CI FROM `partner` ORDER BY name ASC");
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('Cobro::allPartners '.$e->getMessage()); return []; }
    }
    public function allTypes(): array {
        try {
            $st = $this->db->query("SELECT idPaymentType, description AS label FROM `paymenttype` ORDER BY idPaymentType ASC");
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('Cobro::allTypes '.$e->getMessage()); return []; }
    }
    public function allContributions(): array {
        try {
            $st = $this->db->query("SELECT idContribution, notes AS label FROM `contribution` ORDER BY idContribution DESC");
            return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) { error_log('Cobro::allContributions '.$e->getMessage()); return []; }
    }

public function debtsByPartnerDetailed(int $idPartner, ?int $idPaymentType = null): array {
    try {
        $sql  = "SELECT ";
        $sql .= "  c.idContribution, ";
        $sql .= "  COALESCE(c.notes, CONCAT('Aporte #', c.idContribution)) AS notes, ";
        $sql .= "  c.amount, ";
        $sql .= "  c.monthYear, ";
        $sql .= "  c.dateCreation AS contributionDate, ";
        $sql .= "  (SELECT pr.dateRegistration FROM partner pr WHERE pr.idPartner = :idPartner) AS partnerRegistrationDate ";
        $sql .= "FROM `contribution` c ";
        $sql .= "WHERE NOT EXISTS ( ";
        $sql .= "  SELECT 1 FROM `payment` p ";
        $sql .= "  WHERE p.idPartner = :idPartner ";
        $sql .= "    AND p.idContribution = c.idContribution ";
        if ($idPaymentType !== null) {
            $sql .= "    AND p.idPaymentType = :idPaymentType ";
        }
        $sql .= ") ";
        $sql .= "AND c.dateCreation > (SELECT dateRegistration FROM partner WHERE idPartner = :idPartner) ";
        $sql .= "ORDER BY c.idContribution DESC";

        $st = $this->db->prepare($sql);
        $st->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
        if ($idPaymentType !== null) {
            $st->bindValue(':idPaymentType', $idPaymentType, \PDO::PARAM_INT);
        }
        $st->execute();
        return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    } catch (\PDOException $e) {
        error_log('Cobro::debtsByPartnerDetailed '.$e->getMessage());
        return [];
    }
}


public function listPartnersWithTotals(array $f = [], int $page = 1, int $pageSize = 20, ?int &$total = null): array {
    $where = [];
    $params = [];

    // Filtro por nombre o CI
    if (!empty($f['q'])) {
        $where[] = "(pr.name LIKE :q OR pr.CI LIKE :q)";
        $params[':q'] = $this->like($f['q']);
    }

    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Consulta para el total de socios
    $sqlCount = "SELECT COUNT(*) FROM partner pr $whereSql";
    $st = $this->db->prepare($sqlCount);
    foreach ($params as $k => $v) $st->bindValue($k, $v);
    $st->execute();
    $total = (int)$st->fetchColumn();

    // Consulta principal con totales usando dateRegistration
    $offset = max(0, ($page - 1) * $pageSize);
    $sql = "
        SELECT 
            pr.idPartner,
            pr.name AS partnerName,
            pr.CI AS partnerCI,
            pr.dateRegistration AS partnerRegistrationDate,
            COALESCE(
                (SELECT SUM(p.paidAmount)
                 FROM payment p
                 WHERE p.idPartner = pr.idPartner
                 AND p.dateCreation > pr.dateRegistration), 
                0
            ) AS totalPaid,
            COALESCE(
                (SELECT SUM(c.amount)
                 FROM contribution c
                 WHERE NOT EXISTS (
                     SELECT p2.idContribution FROM payment p2 
                     WHERE p2.idPartner = pr.idPartner 
                     AND p2.idContribution = c.idContribution
                 )
                 AND c.dateCreation > pr.dateRegistration), 
                0
            ) AS totalDebt
        FROM partner pr
        $whereSql
        ORDER BY pr.name ASC
        LIMIT :lim OFFSET :off
    ";

    $st = $this->db->prepare($sql);
    foreach ($params as $k => $v) $st->bindValue($k, $v);
    $st->bindValue(':lim', $pageSize, \PDO::PARAM_INT);
    $st->bindValue(':off', $offset, \PDO::PARAM_INT);
    $st->execute();

    return $st->fetchAll(\PDO::FETCH_ASSOC) ?: [];
}

// Agregar este método al final de la clase Cobro
public function getReceiptData(array $paymentIds): ?array {
    try {
        if (empty($paymentIds)) return null;
        
        $placeholders = implode(',', array_fill(0, count($paymentIds), '?'));
        
        $sql = "
            SELECT 
                p.idPayment,
                p.paidAmount,
                p.dateCreation,
                pr.name AS partnerName,
                COALESCE(pr.CI, 'Sin CI') AS partnerCI,
                pt.description AS paymentTypeName,
                c.notes AS contributionName,
                c.monthYear
            FROM payment p
            INNER JOIN partner pr ON pr.idPartner = p.idPartner
            INNER JOIN paymenttype pt ON pt.idPaymentType = p.idPaymentType
            INNER JOIN contribution c ON c.idContribution = p.idContribution
            WHERE p.idPayment IN ($placeholders)
            ORDER BY p.dateCreation DESC
        ";
        
        $st = $this->db->prepare($sql);
        $st->execute($paymentIds);
        $payments = $st->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($payments)) return null;
        
        // Calcular totales
        $totalAmount = array_sum(array_column($payments, 'paidAmount'));
        
        return [
            'payments' => $payments,
            'partnerName' => $payments[0]['partnerName'],
            'partnerCI' => $payments[0]['partnerCI'],
            'paymentTypeName' => $payments[0]['paymentTypeName'],
            'totalAmount' => $totalAmount,
            'paymentDate' => $payments[0]['dateCreation'],
            'receiptNumber' => 'REC-' . str_pad((string)$payments[0]['idPayment'], 6, '0', STR_PAD_LEFT)
        ];
        
    } catch (\PDOException $e) {
        error_log('Cobro::getReceiptData '.$e->getMessage());
        return null;
    }
}





}
