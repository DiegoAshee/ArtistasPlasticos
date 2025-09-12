<?php
// app/Models/SystemOption.php
declare(strict_types=1);

require_once __DIR__ . '/../Config/database.php';

class SystemOption
{
    private $lastError = [];
    
    public function getLastError(): array {
        return $this->lastError;
    }
    
    private function setError(string $message, $code = 0): void {
        $this->lastError = [
            'message' => $message,
            'code' => is_numeric($code) ? (int)$code : 0
        ];
    }
    
    /**
     * Obtener todas las opciones del sistema
     */
    public function getAllOptions(): array
    {
        $db = Database::singleton()->getConnection();
        
        try {
            $sql = "SELECT * FROM `system_options` ORDER BY idOption ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opciones del sistema: ' . $e->getMessage(), $e->getCode());
            error_log('SystemOption::getAllOptions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener opciones específicas para el logo y título
     */
    public function getHeaderOptions(): array
    {
        $db = Database::singleton()->getConnection();
        
        try {
            $sql = "SELECT * FROM `system_options` 
                    WHERE option_key IN ('logo_url', 'site_title', 'tagline') 
                    ORDER BY idOption ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $options = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            
            // Formatear como array asociativo
            $result = [];
            foreach ($options as $option) {
                $result[$option['option_key']] = $option['option_value'];
            }
            
            return $result;
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opciones del encabezado: ' . $e->getMessage(), $e->getCode());
            error_log('SystemOption::getHeaderOptions error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar una opción del sistema
     */
    public function updateOption(string $key, string $value): bool
    {
        if (empty(trim($key))) {
            $this->setError('La clave de la opción no puede estar vacía', 400);
            return false;
        }
        
        $db = Database::singleton()->getConnection();
        
        try {
            // Verificar si la opción existe
            $checkSql = "SELECT idOption FROM `system_options` WHERE option_key = :key";
            $checkStmt = $db->prepare($checkSql);
            $checkStmt->bindValue(':key', $key, \PDO::PARAM_STR);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                // Actualizar opción existente
                $sql = "UPDATE `system_options` SET option_value = :value WHERE option_key = :key";
            } else {
                // Insertar nueva opción
                $sql = "INSERT INTO `system_options` (option_key, option_value) VALUES (:key, :value)";
            }
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
            $stmt->bindValue(':value', $value, \PDO::PARAM_STR);
            
            return $stmt->execute();
            
        } catch (\PDOException $e) {
            $this->setError('Error de base de datos: ' . $e->getMessage(), $e->getCode());
            error_log('SystemOption::updateOption error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener opciones por su clave
     */
    public function getOptionByKey(string $key): ?string
    {
        $db = Database::singleton()->getConnection();
        
        try {
            $sql = "SELECT option_value FROM `system_options` WHERE option_key = :key";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':key', $key, \PDO::PARAM_STR);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? $result['option_value'] : null;
            
        } catch (\PDOException $e) {
            $this->setError('Error al obtener opción: ' . $e->getMessage(), $e->getCode());
            error_log('SystemOption::getOptionByKey error: ' . $e->getMessage());
            return null;
        }
    }
}
?>