<?php
// app/Models/Contribution.php
require_once __DIR__ . '/../Config/database.php';

class Contribution {
    private const TBL = '`contribution`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    public function getAll(): array {
        try {
            $query = "SELECT * FROM " . self::TBL . " ORDER BY dateCreation DESC";;
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener contribuciones: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id): ?array {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idContribution = :id LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar contribución: " . $e->getMessage());
            return null;
        }
    }

    private $lastError = [];
    
    public function getLastError(): array {
        return $this->lastError;
    }
    
    public function create($amount, $notes, $dateCreation, $monthYear): ?int {
    $this->lastError = []; // Reset the last error
    
    try {
        // Insert the new contribution
        $query = "INSERT INTO " . self::TBL . " (amount, notes, dateCreation, monthYear) 
                 VALUES (:amount, :notes, :dateCreation, :monthYear)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':dateCreation', $dateCreation, PDO::PARAM_STR);
        $stmt->bindParam(':monthYear', $monthYear, PDO::PARAM_STR);
        
        if (!$stmt->execute()) {
            throw new \PDOException("Error al insertar la contribución");
        }

        $contributionId = $this->db->lastInsertId();

        // Include Notification class
        require_once __DIR__ . '/Notification.php';
        
        // Create a notification for the new contribution
        $notificationData = [
            'title' => 'Nueva Contribución Creada',
            'message' => "Se ha creado una nueva contribución por un monto de $amount para el mes $monthYear",
            'type' => 'info',
            'data' => json_encode([
                'contribution_id' => $contributionId,
                'amount' => $amount,
                'monthYear' => $monthYear
            ]),
            'idRol' => 2 // Asegurarse de que el rol esté definido
        ];

        $notification = new \App\Models\Notification();
        $notificationId = $notification->create($notificationData);
        
        if ($notificationId === false) {
            error_log("Error: No se pudo crear la notificación para la contribución $contributionId");
            throw new \Exception("Fallo al crear la notificación de contribución");
        } else {
            error_log("Notificación creada con ID: " . $notificationId);
        }
        
        return $contributionId;

    } catch (\Exception $e) {
        $this->lastError = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
        error_log("Error al crear contribución: " . $e->getMessage());
        return null;
    }
}

    public function update($id, $amount, $notes, $dateUpdate, $monthYear): bool {
        try {
            $query = "UPDATE " . self::TBL . " SET amount = :amount, notes = :notes, dateUpdate = :dateUpdate, monthYear = :monthYear WHERE idContribution = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
            $stmt->bindParam(':dateUpdate', $dateUpdate, PDO::PARAM_STR);
            $stmt->bindParam(':monthYear', $monthYear, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar contribución: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id): bool {
        try {
            $query = "DELETE FROM " . self::TBL . " WHERE idContribution = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar contribución: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el último mes y año de contribución registrado
     * 
     * @return string|null El último mes y año en formato 'YYYY-MM' o null si no hay contribuciones
     */
    public function getLastMonthYear(): ?string 
    {
        try {
            $query = "SELECT monthYear FROM " . self::TBL . " ORDER BY monthYear DESC LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['monthYear'] : null;
        } catch (PDOException $e) {
            error_log("Error al obtener el último mes de contribución: " . $e->getMessage());
            return null;
        }
    }
}