<?php
// app/Models/Concept.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class Concept
{
    private $lastError = [];

    public function getLastError(): array {
        return $this->lastError;
    }

    private function setError(string $message, $code = 0): void {
        $this->lastError = [
            'message' => $message,
            'code'    => is_numeric($code) ? (int)$code : 0
        ];
    }

    /**
     * Listar todos los conceptos con filtros y paginación
     */
    public function listAll(array $filters = [], int $page = 1, int $pageSize = 20): array
    {
        $db = Database::singleton()->getConnection();
        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "description LIKE :q";
            $params[':q'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['from'])) {
            $where[] = "dateCreation >= :from";
            $params[':from'] = $filters['from'] . " 00:00:00";
        }
        if (!empty($filters['to'])) {
            $where[] = "dateCreation <= :to";
            $params[':to'] = $filters['to'] . " 23:59:59";
        }

        $sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";

        try {
            // Total
            $stmt = $db->prepare("SELECT COUNT(*) FROM concept $sqlWhere");
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            $offset = ($page - 1) * $pageSize;
            $sql = "SELECT * FROM concept $sqlWhere 
                    ORDER BY idConcept DESC 
                    LIMIT :offset, :limit";
            $stmt = $db->prepare($sql);

            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $pageSize, \PDO::PARAM_INT);

            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            return [
                'rows'       => $rows,
                'total'      => $total,
                'page'       => $page,
                'pageSize'   => $pageSize,
                'totalPages' => ceil($total / $pageSize),
            ];
        } catch (\PDOException $e) {
            $this->setError('DB error: ' . $e->getMessage(), $e->getCode());
            return ['rows'=>[], 'total'=>0, 'page'=>$page, 'pageSize'=>$pageSize, 'totalPages'=>0];
        }
    }

    public function find(int $id): ?array
    {
        $db = Database::singleton()->getConnection();
        try {
            $stmt = $db->prepare("SELECT * FROM concept WHERE idConcept = :id");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\PDOException $e) {
            $this->setError('DB error: ' . $e->getMessage(), $e->getCode());
            return null;
        }
    }

    public function create(string $description, string $type): int|false
    {
        $db = Database::singleton()->getConnection();
        try {
            $sql = "INSERT INTO concept (description, type, dateCreation) 
                    VALUES (:description, :type, NOW())";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':description', trim($description));
            $stmt->bindValue(':type', $type);
            if ($stmt->execute()) {
                return (int)$db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            $this->setError('DB error: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }

    public function update(int $id, string $description, string $type): bool
    {
        $db = Database::singleton()->getConnection();
        try {
            $sql = "UPDATE concept 
                    SET description = :description, type = :type 
                    WHERE idConcept = :id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':description', trim($description));
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->setError('DB error: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $db = Database::singleton()->getConnection();
        try {
            $stmt = $db->prepare("DELETE FROM concept WHERE idConcept = :id");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->setError('DB error: ' . $e->getMessage(), $e->getCode());
            return false;
        }
    }
}
