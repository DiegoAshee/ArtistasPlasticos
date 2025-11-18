<?php
namespace App\Models;

require_once __DIR__ . '/../Config/database.php';
use Database;

class Notification
{
    protected $db;
    protected $lastError = [];

    public function __construct()
    {
        $this->db = Database::singleton()->getConnection();
    }

    /**
     * Verifica la conexión a la base de datos y la estructura de la tabla
     * @return array Estado de la verificación
     */
    public function checkDatabase()
    {
        try {
            // Verificar conexión
            if (!$this->db) {
                return ['success' => false, 'message' => 'No hay conexión a la base de datos'];
            }

            // Verificar si la tabla existe
            $tableExists = $this->db->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
            if (!$tableExists) {
                return ['success' => false, 'message' => "La tabla 'notifications' no existe"];
            }

            // Verificar estructura de la tabla
            $expectedColumns = ['id', 'title', 'message', 'type', 'data', 'created_at', 'role_id'];
            $stmt = $this->db->query("DESCRIBE notifications");
            $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $missingColumns = array_diff($expectedColumns, $existingColumns);
            if (!empty($missingColumns)) {
                return [
                    'success' => false, 
                    'message' => 'Faltan columnas en la tabla: ' . implode(', ', $missingColumns)
                ];
            }

            return ['success' => true, 'message' => 'Conexión y estructura de tabla verificadas correctamente'];
            
        } catch (\PDOException $e) {
            return [
                'success' => false, 
                'message' => 'Error de base de datos: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function getNotificationsForUser(int $userId, int $roleId): array
    {
        // Resolve roleId from DB if possible to avoid session mismatches
        try {
            $stmtRole = $this->db->prepare("SELECT idRol FROM users WHERE id = :uid LIMIT 1");
            $stmtRole->bindValue(':uid', (int)$userId, \PDO::PARAM_INT);
            $stmtRole->execute();
            $dbRole = $stmtRole->fetchColumn();
            if ($dbRole !== false && $dbRole !== null && $dbRole !== '') {
                $roleId = (int)$dbRole;
            }
        } catch (\Throwable $e) {
            // ignore and continue with provided $roleId
        }

        // 1) Detect role column in notifications table
        $roleColumn = null;
        try {
            $colsStmt = $this->db->query("DESCRIBE notifications");
            $notifColumns = $colsStmt->fetchAll(\PDO::FETCH_COLUMN);
            if (in_array('idRol', $notifColumns, true)) {
                $roleColumn = 'idRol';
            } elseif (in_array('role_id', $notifColumns, true)) {
                $roleColumn = 'role_id';
            } elseif (in_array('id_Rol', $notifColumns, true)) {
                $roleColumn = 'id_Rol';
            }
        } catch (\Throwable $e) {
            $roleColumn = null;
        }

        // 2) Detect optional Notification_User table and its columns
        $userJoinTable = 'Notification_User';
        $nuHasTable = false;
        $nuReadCol = 'is_read';
        $nuUserCol = 'user_id';
        $nuNotifIdCol = 'notification_id';
        try {
            $nuColsStmt = $this->db->query("DESCRIBE `{$userJoinTable}`");
            $nuCols = $nuColsStmt->fetchAll(\PDO::FETCH_COLUMN);
            if (!empty($nuCols)) {
                $nuHasTable = true;
                if (in_array('isRead', $nuCols, true)) { $nuReadCol = 'isRead'; }
                if (in_array('idUser', $nuCols, true)) { $nuUserCol = 'idUser'; }
                if (in_array('idNotification', $nuCols, true)) { $nuNotifIdCol = 'idNotification'; }
                if (!in_array($nuReadCol, $nuCols, true) || !in_array($nuUserCol, $nuCols, true) || !in_array($nuNotifIdCol, $nuCols, true)) {
                    $nuHasTable = false;
                }
            }
        } catch (\Throwable $e) {
            $nuHasTable = false;
        }

        // 3) Build SQL
        $selectRead = $nuHasTable ? "COALESCE(nu.{$nuReadCol}, 0)" : '0';
        $joinSql = $nuHasTable
            ? "LEFT JOIN `{$userJoinTable}` nu ON n.id = nu.{$nuNotifIdCol} AND nu.{$nuUserCol} = :userId"
            : '';

        // Include public (NULL) and exact role match (tolerant to string/spacing)
        $roleWhere = $roleColumn
            ? "( n.{$roleColumn} IS NULL
                 OR CAST(n.{$roleColumn} AS UNSIGNED) = :roleId
                 OR TRIM(CAST(n.{$roleColumn} AS CHAR)) = :roleIdStr )"
            : '1=1';

        $sql = "SELECT n.*, {$selectRead} AS user_is_read
                FROM notifications n
                {$joinSql}
                WHERE {$roleWhere}
                ORDER BY n.created_at DESC";

        // 4) Bind explicitly to avoid HY093
        $stmt = $this->db->prepare($sql);
        if ($roleColumn) {
            $stmt->bindValue(':roleId', (int)$roleId, \PDO::PARAM_INT);
            $stmt->bindValue(':roleIdStr', (string)(string)$roleId, \PDO::PARAM_STR);
        }
        if ($nuHasTable) {
            $stmt->bindValue(':userId', (int)$userId, \PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log('[Notifications] roleCol=' . ($roleColumn ?: 'none') . ' roleId=' . (int)$roleId . ' rows=' . count($rows));
        return $rows;
    }

    /**
     * Obtiene información detallada del último error ocurrido
     */
    public function getErrorInfo(): array
    {
        return $this->lastError ?: [
            'message' => 'No error information available',
            'context' => [],
            'time' => date('Y-m-d H:i:s'),
            'trace' => []
        ];
    }

    /**
     * Establece la información del último error
     */
    private function setError(string $message, array $context = []): void
    {
        $this->lastError = [
            'message' => $message,
            'context' => $context,
            'time' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
        ];
        
        error_log('Notification Error: ' . $message . ' - ' . json_encode($context));
    }

    public function markNotificationAsRead(int $notificationId, int $userId): bool
{
    try {
        if (!$this->db) {
            throw new \RuntimeException('No hay conexión a la base de datos');
        }

        // Verificar si la notificación existe
        $stmt = $this->db->prepare("SELECT id FROM notifications WHERE id = :id");
        $stmt->execute([':id' => $notificationId]);
        if (!$stmt->fetch()) {
            throw new \RuntimeException('La notificación especificada no existe');
        }

        // Verificar si ya existe un registro en Notification_User
        $stmt = $this->db->prepare("
            SELECT IdNotification_User 
            FROM Notification_User 
            WHERE idNotification = :notification_id 
            AND idUser = :user_id
        ");
        $stmt->execute([
            ':notification_id' => $notificationId,
            ':user_id' => $userId
        ]);
        
        $exists = $stmt->fetchColumn() !== false;

        // Iniciar transacción
        $this->db->beginTransaction();

        try {
            if ($exists) {
                // Actualizar registro existente
                $stmt = $this->db->prepare("
                    UPDATE Notification_User 
                    SET isRead = 1, 
                        dateRead = NOW() 
                    WHERE idNotification = :notification_id 
                    AND idUser = :user_id
                ");
            } else {
                // Insertar nuevo registro
                $stmt = $this->db->prepare("
                    INSERT INTO Notification_User (
                        idNotification, 
                        idUser, 
                        isRead, 
                        dateRead
                    ) VALUES (
                        :notification_id, 
                        :user_id, 
                        1, 
                        NOW()
                    )
                ");
            }

            // Ejecutar la consulta
            $result = $stmt->execute([
                ':notification_id' => $notificationId,
                ':user_id' => $userId
            ]);

            if (!$result) {
                throw new \RuntimeException('No se pudo actualizar el estado de lectura de la notificación');
            }

            // Actualizar el contador de no leídas si la columna existe
            $columnsStmt = $this->db->query("SHOW COLUMNS FROM notifications LIKE 'unread_count'");
            if ($columnsStmt->rowCount() > 0) {
                $updateStmt = $this->db->prepare("
                    UPDATE notifications 
                    SET unread_count = GREATEST(0, unread_count - 1) 
                    WHERE id = :notification_id
                ");
                $updateStmt->execute([':notification_id' => $notificationId]);
            }

            // Confirmar transacción
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    } catch (\Exception $e) {
        $this->setError('Error al marcar la notificación como leída: ' . $e->getMessage(), [
            'notification_id' => $notificationId,
            'user_id' => $userId
        ]);
        return false;
    }
}

    /**
     * Obtener una notificación por su ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM notifications WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            $this->setError('Error al obtener la notificación: ' . $e->getMessage(), ['id' => $id]);
            return null;
        }
    }

    public function create(array $data)
    {
        try {
            // Detectar columnas disponibles para compatibilidad de esquema
            $columnsStmt = $this->db->query("DESCRIBE notifications");
            $existingColumns = $columnsStmt->fetchAll(\PDO::FETCH_COLUMN);
            $roleColumn = null;
            if (in_array('role_id', $existingColumns, true)) {
                $roleColumn = 'role_id';
            } elseif (in_array('idRol', $existingColumns, true)) {
                $roleColumn = 'idRol';
            } elseif (in_array('id_Rol', $existingColumns, true)) {
                $roleColumn = 'id_Rol';
            }

            // Construir SQL dinámicamente según exista columna de rol
            $baseColumns = ['title', 'message', 'type', 'data', 'created_at'];
            $columnsSql = implode(', ', $baseColumns) . ($roleColumn ? ", {$roleColumn}" : '');
            $valuesSql = ':title, :message, :type, :data, NOW()' . ($roleColumn ? ', :role_value' : '');
            $sql = "INSERT INTO notifications ({$columnsSql}) VALUES ({$valuesSql})";

            // Preparar los datos de la notificación
            $payload = [
                'title'      => $data['title'] ?? 'New notification',
                'message'    => $data['message'] ?? '',
                'type'       => $data['type'] ?? 'info',
                'data'       => is_string($data['data'] ?? null) ? $data['data'] : json_encode($data['data'] ?? []),
            ];

            // NORMALIZAR: intentar detectar ids/entidades comunes y añadir campos estandarizados
            // para facilitar redirecciones posteriores (ej. entity, id, url).
            try {
                $decoded = json_decode($payload['data'], true);
                if (!is_array($decoded)) {
                    $decoded = [];
                }

                $normalized = $decoded;

                // Helpers para buscar variantes de keys
                $findKey = function(array $arr, array $candidates) {
                    foreach ($candidates as $k) {
                        if (isset($arr[$k]) && $arr[$k] !== '') {
                            return $arr[$k];
                        }
                    }
                    return null;
                };

                // CONTRIBUTION
                $cid = $findKey($normalized, ['contribution_id', 'idContribution', 'contributionId', 'id_contribution']);
                if ($cid === null && !empty($normalized['idContributions']) && is_array($normalized['idContributions'])) {
                    $first = $normalized['idContributions'][0] ?? null;
                    if (is_array($first)) {
                        $cid = $findKey($first, ['idContribution', 'contribution_id', 'contributionId']);
                    } elseif (is_numeric($first)) {
                        $cid = (int)$first;
                    }
                }

                if ($cid !== null && $cid !== '') {
                    $normalized['entity'] = 'contribution';
                    $normalized['id'] = (int)$cid;
                    // also provide a convenience url that open() can use directly
                    if (empty($normalized['url'])) {
                        $normalized['url'] = 'contribution/edit/' . (int)$cid;
                    }
                }

                // PARTNER
                $pid = $findKey($normalized, ['idPartner', 'partner_id', 'id_partner', 'partnerId']);
                if ($pid !== null && $pid !== '') {
                    $normalized['entity'] = $normalized['entity'] ?? 'partner';
                    $normalized['id'] = $normalized['id'] ?? (int)$pid;
                    if (empty($normalized['url'])) {
                        $normalized['url'] = 'admin/review-payments?mode=partners&partner=' . (int)$pid;
                    }
                }

                // PAYMENT
                $payId = $findKey($normalized, ['payment_id', 'idPayment', 'paymentId', 'id_payment']);
                if ($payId !== null && $payId !== '') {
                    $normalized['entity'] = $normalized['entity'] ?? 'payment';
                    $normalized['id'] = $normalized['id'] ?? (int)$payId;
                    if (empty($normalized['url'])) {
                        $normalized['url'] = 'payment/edit/' . (int)$payId;
                    }
                }

                // Re-encode normalizado
                $payload['data'] = json_encode($normalized, JSON_UNESCAPED_UNICODE);
            } catch (\Throwable $e) {
                // No bloquear la creación por un fallo de normalización
                error_log('Notification normalization error: ' . $e->getMessage());
            }
            if ($roleColumn) {
                $payload['role_value'] = $data['idRol'] ?? ($data['role_id'] ?? ($data['id_Rol'] ?? null));
            }
            
            // Debug: Log SQL and data
            error_log('=== INICIO DE CREACIÓN DE NOTIFICACIÓN ===');
            error_log('SQL: ' . $sql);
            error_log('Datos: ' . print_r($payload, true));
            
            // Verify database connection
            if (!$this->db) {
                $error = "No hay conexión a la base de datos";
                error_log('ERROR: ' . $error);
                throw new \Exception($error);
            }
            
            // Check if table exists (using the correct syntax for SHOW TABLES)
            try {
                $tableCheck = $this->db->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
                if (!$tableCheck) {
                    $error = "La tabla 'notifications' no existe en la base de datos";
                    error_log('ERROR: ' . $error);
                    throw new \Exception($error);
                }
            } catch (\PDOException $e) {
                $error = "Error al verificar la tabla: " . $e->getMessage();
                error_log('ERROR: ' . $error);
                throw new \Exception($error);
            }
            
            // Prepare and execute the query
            try {
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute($payload);
                
                if (!$result) {
                    $errorInfo = $stmt->errorInfo();
                    $error = "Error al ejecutar la consulta: " . ($errorInfo[2] ?? 'Error desconocido');
                    error_log('ERROR: ' . $error);
                    error_log('Error Info: ' . print_r($errorInfo, true));
                    throw new \Exception($error);
                }
                
                $id = (int)$this->db->lastInsertId();
                error_log("ÉXITO: Notificación creada con ID: " . $id);
                return $id;
                
            } catch (\PDOException $e) {
                $error = "Error de PDO: " . $e->getMessage();
                error_log('ERROR: ' . $error);
                error_log('Código de error: ' . $e->getCode());
                throw new \Exception($error);
            }
            
        } catch (\PDOException $e) {
            error_log('Error PDO al crear notificación: ' . $e->getMessage());
            error_log('Código de error: ' . $e->getCode());
            error_log('Trace: ' . $e->getTraceAsString());
            return false;
        } catch (\Exception $e) {
            error_log('Error al crear notificación: ' . $e->getMessage());
            error_log('Trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Elimina una notificación (solo admin) o la valida por rol (si se pasa userRole).
     */
    public function delete(int $notificationId, ?int $userRole = null): bool
    {
        if ($userRole === null) {
            $sql = "DELETE FROM notifications WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return (bool)$stmt->execute(['id' => $notificationId]);
        }

        $sql = "DELETE FROM notifications WHERE id = :id AND (idRol = :role_id OR idRol = 0 OR idRol IS NULL)";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute(['id' => $notificationId, 'role_id' => $userRole]);
    }

    /**
     * Mark all unread notifications as read for a specific user
     * @return bool True on success, false on failure
     */
    public function markAllAsRead(int $userId): bool
    {
        // Detect join table and columns
        $table = 'Notification_User';
        $cols = [];
        $readCol = 'is_read';
        $readAtCol = 'read_at';
        $userCol = 'user_id';
        $notifCol = 'notification_id';
        $idCol = 'id';
        try {
            $desc = $this->db->query("DESCRIBE `{$table}`");
            $cols = $desc->fetchAll(\PDO::FETCH_COLUMN);
            if (in_array('isRead', $cols, true)) { $readCol = 'isRead'; }
            if (in_array('readAt', $cols, true)) { $readAtCol = 'readAt'; }
            if (in_array('idUser', $cols, true)) { $userCol = 'idUser'; }
            if (in_array('idNotification', $cols, true)) { $notifCol = 'idNotification'; }
            if (in_array('id', $cols, true)) { $idCol = 'id'; }
        } catch (\Throwable $e) {
            return false;
        }

        $sql = "INSERT INTO `{$table}` ({$readCol}, {$readAtCol}, {$notifCol}, {$userCol})
                SELECT 1, NOW(), n.id, :uid
                FROM notifications n
                LEFT JOIN `{$table}` nu ON n.id = nu.{$notifCol} AND nu.{$userCol} = :uid
                WHERE nu.{$idCol} IS NULL OR nu.{$readCol} = 0";
        $stmt = $this->db->prepare($sql);
        return (bool)$stmt->execute(['uid' => $userId]);
    }
    

    
}