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

    /** Lista completa JOIN a las 4 tablas, con filtros (pagadas) */
    

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
/*
public function searchDebidas(array $f = [], int $page = 1, int $pageSize = 20, ?int &$total = null): array {
    $params = [];
    
    // Construir condiciones de WHERE para contribution
    if (!empty($f['idContribution'])) {
        $contribWhere = "c.idContribution = :cid";
        $params[':cid'] = (int)$f['idContribution'];
    } else {
        // Si no especifican, usar la última aportación
        $contribWhere = "c.idContribution = (SELECT MAX(idContribution) FROM contribution)";
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
            c.idContribution,
            c.notes AS contributionName,
            c.amount,
            c.monthYear,
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
        ORDER BY pr.name ASC, c.idContribution DESC
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
}*/

public function searchDebidas(array $f = [], int $page = 1, int $pageSize = 20, ?int &$total = null): array {
    $params = [];
    
    // Construir condiciones de WHERE para contribution
    if (!empty($f['idContribution'])) {
        $contribWhere = "c.idContribution = :cid";
        $params[':cid'] = (int)$f['idContribution'];
    } else {
        // CAMBIO: En lugar de solo la última, mostrar TODAS las aportaciones
        // O puedes poner un filtro de fecha para solo mostrar los últimos 6 meses
        $contribWhere = "1=1"; // Todas las aportaciones
        // O si quieres limitar a los últimos meses:
        // $contribWhere = "c.idContribution >= (SELECT MAX(idContribution) - 5 FROM contribution)";
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

    //para el boton de la lista de socios
    // Filtro por socio específico (opcional)
    if (!empty($f['idPartner'])) {
        // según tu estilo, añádelo al $searchFilter para que se aplique en COUNT y SELECT
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
            c.idContribution,
            c.notes AS contributionName,
            c.amount,
            c.monthYear,
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
        ORDER BY pr.name ASC, c.idContribution DESC
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

    public function create(int $idPartner, int $idPaymentType, int $idContribution, float $paidAmount) {
        try {
            $st = $this->db->prepare("
                INSERT INTO `payment`
                (paidAmount, dateCreation, idPartner, idPaymentType, idContribution)
                VALUES (:paidAmount, NOW(), :idPartner, :idPaymentType, :idContribution)
            ");
            $st->bindValue(':paidAmount', $paidAmount);
            $st->bindValue(':idPartner', $idPartner, \PDO::PARAM_INT);
            $st->bindValue(':idPaymentType', $idPaymentType, \PDO::PARAM_INT);
            $st->bindValue(':idContribution', $idContribution, \PDO::PARAM_INT);
            $st->execute();
            return $this->db->lastInsertId();
        } catch (\PDOException $e) {
            error_log('Cobro::create '.$e->getMessage());
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
        // Construimos el SQL por partes (evita interpolaciones literales)
        $sql  = "SELECT ";
        $sql .= "  c.idContribution, ";
        $sql .= "  COALESCE(c.notes, CONCAT('Aporte #', c.idContribution)) AS notes, ";
        $sql .= "  c.amount, ";
        $sql .= "  c.monthYear ";
        $sql .= "FROM `contribution` c ";
        $sql .= "WHERE NOT EXISTS ( ";
        $sql .= "  SELECT 1 FROM `payment` p ";
        $sql .= "  WHERE p.idPartner = :idPartner ";
        $sql .= "    AND p.idContribution = c.idContribution ";
        if ($idPaymentType !== null) {
            $sql .= "    AND p.idPaymentType = :idPaymentType ";
        }
        $sql .= ") ";
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






}
