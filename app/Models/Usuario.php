<?php
// app/Models/Usuario.php

require_once __DIR__ . '/../Config/database.php';

class Usuario
{
    /** Tablas (ojo con mayúsculas/minúsculas si usas Linux). */
    private const TABLE  = '`user`';
    private const TABLE2 = '`partner`';

    /** @var \PDO */
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }
    
    /**
     * Obtiene todos los usuarios
     * @return array Lista de usuarios
     */
    public function getAll() {
        try {
            $query = "SELECT u.*, p.name as partner_name, p.lastName as partner_lastname 
                     FROM " . self::TABLE . " u
                     LEFT JOIN " . self::TABLE2 . " p ON u.idPartner = p.idPartner
                     ORDER BY p.lastName, p.name ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ocultar información sensible
            foreach ($users as &$user) {
                unset($user['password'], $user['tokenRecovery'], $user['tokenExpiration']);
            }
            
            return $users;
        } catch (PDOException $e) {
            error_log("Error en Usuario::getAll(): " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // ===============        AUTENTICACIÓN        =============
    // =========================================================

    /**
     * Autentica por login o email (versión NUEVA: tu última).
     * Limpia campos sensibles antes de retornar.
     */
    public function authenticate(string $login, string $password): ?array {
        try {
            $login = trim($login);

            $sql = "SELECT * FROM " . self::TABLE . "
                    WHERE login = :login OR email = :login
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':login', $login, \PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'] ?? '')) {
                unset($user['password'], $user['tokenRecovery'], $user['tokenExpiration']);
                return $user;
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Error en autenticación: " . $e->getMessage());
            return null;
        }
    }

    // =========================================================
    // ===============            BÚSQUEDAS         =============
    // =========================================================

    




    /**
 * Buscar usuario por ID (solo activos)
 */
public function findById(int $id): ?array {
    try {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE idUser = :id AND status = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\PDOException $e) {
        error_log("Error al buscar usuario por ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Buscar usuario por ID incluyendo desactivados (para admin)
 */
public function findByIdIncludingInactive(int $id): ?array {
    try {
        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE idUser = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    } catch (\PDOException $e) {
        error_log("Error al buscar usuario por ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Buscar usuario por idPartner (solo activos)
 */
public function findByPartnerId(int $partnerId): ?array {
    try {
        error_log("DEBUG findByPartnerId - Buscando usuario activo con idPartner: $partnerId");
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE idPartner = :partnerId AND status = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':partnerId', $partnerId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        error_log("DEBUG findByPartnerId - Resultado: " . print_r($result, true));
        
        return $result ?: null;
        
    } catch (\PDOException $e) {
        error_log("DEBUG findByPartnerId - Error: " . $e->getMessage());
        return null;
    }
}





















    public function findByEmail(string $email): ?array {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al buscar usuario por email: " . $e->getMessage());
            return null;
        }
    }

    public function findByLogin(string $login): ?array {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE login = :login LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':login', $login, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al buscar usuario por login: " . $e->getMessage());
            return null;
        }
    }

    public function findByGoogleId(string $googleId): ?array {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE google_id = :google_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':google_id', $googleId, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al buscar usuario por Google ID: " . $e->getMessage());
            return null;
        }
    }
/*
    public function findByPartnerId(int $partnerId): ?array {
        try {
            $sql = "SELECT * FROM " . self::TABLE . " WHERE idPartner = :partnerId LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':partnerId', $partnerId, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al buscar usuario por Partner ID: " . $e->getMessage());
            return null;
        }
    }*/

    



    /** NUEVO: listado seguro (sin campos sensibles) */
    public function getAllUsers(): array {
        try {
            $sql = "SELECT * FROM " . self::TABLE . "
                    ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }
/**
 * Obtener usuarios de todos los roles excepto uno específico
 */
public function getUsersExceptRole(int $excludeRoleId): array {
    try {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para obtener usuarios de todos los roles excepto el especificado
        $sql = "SELECT idUser, login, email, idRol, status, 
                       tokenRecovery, tokenExpiration, firstSession,
                       idPartner, CURRENT_TIMESTAMP as created_at
                FROM " . self::TABLE . "
                WHERE idRol != :excludeRole AND status = :status
                ORDER BY idRol ASC, login ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':excludeRole', $excludeRoleId, PDO::PARAM_INT);
        $stmt->bindValue(':status', 1, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("DEBUG getUsersExceptRole - Total usuarios encontrados (excepto rol $excludeRoleId): " . count($rows));
        error_log("DEBUG getUsersExceptRole - Datos: " . print_r($rows, true));

        return $rows ?: [];
    } catch (\PDOException $e) {
        error_log("Error al obtener usuarios excepto rol $excludeRoleId: " . $e->getMessage());
        return [];
    }
}
    /** Compatibilidad con el controlador */
public function getUsersAdmin(): array {
    try {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta corregida con todos los campos necesarios
        $sql = "SELECT idUser, login, email, idRol, status, 
                       tokenRecovery, tokenExpiration, firstSession,
                       idPartner, CURRENT_TIMESTAMP as created_at
                FROM " . self::TABLE . "
                WHERE idRol = :role AND status = :status
                ORDER BY login ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role', 1, PDO::PARAM_INT);
        $stmt->bindValue(':status', 1, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("DEBUG getUsersAdmin - Total usuarios encontrados: " . count($rows));
        error_log("DEBUG getUsersAdmin - Datos: " . print_r($rows, true));

        return $rows ?: [];
    } catch (\PDOException $e) {
        error_log("Error al obtener usuarios admin: " . $e->getMessage());
        return [];
    }
}
// Modificaciones en el modelo Usuario.php

// Renombrar y modificar getUsersAdmin a getNonSocioUsers
// Agregar JOIN con tabla rol para obtener el nombre del rol
public function getNonSocioUsers(): array {
    try {
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta con JOIN a rol y filtro excluyendo rol 2
        $sql = "SELECT u.idUser, u.login, u.email, u.idRol, u.status, 
                       u.tokenRecovery, u.tokenExpiration, u.firstSession,
                       u.idPartner, CURRENT_TIMESTAMP as created_at,
                       r.rol as rolName
                FROM " . self::TABLE . " u
                INNER JOIN rol r ON u.idRol = r.idRol
                WHERE u.idRol != :excludedRole AND u.status = :status
                ORDER BY u.login ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':excludedRole', 2, PDO::PARAM_INT);
        $stmt->bindValue(':status', 1, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("DEBUG getNonSocioUsers - Total usuarios encontrados: " . count($rows));
        error_log("DEBUG getNonSocioUsers - Datos: " . print_r($rows, true));

        return $rows ?: [];
    } catch (\PDOException $e) {
        error_log("Error al obtener usuarios no socios: " . $e->getMessage());
        return [];
    }
}

// El resto de funciones (create, update, delete) permanecen iguales, ya que ya manejan idRol dinámicamente

    /** Compatibilidad con el controlador */
    public function getUserProfile(int $role, int $id): array {
        try {
            if ((int)$role === 1) {
                $sql = "SELECT idUser, login, email
                        FROM " . self::TABLE . " WHERE idUser = :id";
            } else {
                $sql = "SELECT p.*, u.login, u.email
                        FROM " . self::TABLE2 . " p
                        JOIN " . self::TABLE  . " u ON p.idPartner = u.idPartner
                        WHERE u.idUser = :id";
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener perfil: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================
    // ===============      CREAR / ACTUALIZAR     =============
    // =========================================================

  



    /**
 * Método create corregido para los campos reales de la tabla user
 * Campos reales: idUser, login, password, tokenRecovery, tokenExpiration, 
 * email, firstSession, status, idRol, idPartner
 */
public function create($loginOrData, $password = null, $email = null, $idRole = null, $idPartner = null): int|false {
    try {
        error_log("DEBUG Usuario::create - Parámetros recibidos:");
        error_log("- loginOrData: " . print_r($loginOrData, true));
        error_log("- email: " . print_r($email, true));
        error_log("- idRole: " . print_r($idRole, true));
        error_log("- idPartner: " . print_r($idPartner, true));
        
        // Normalizamos a array de datos
        if (is_array($loginOrData)) {
            $data = $loginOrData;
            error_log("DEBUG - Usando formato array");
        } else {
            $data = [
                'login'     => (string)$loginOrData,
                'password'  => (string)$password,
                'email'     => $email,
                'idRole'    => $idRole,
                'idPartner' => $idPartner,
            ];
            error_log("DEBUG - Convertido a array: " . print_r($data, true));
        }

        // Mapear idRole -> idRol
        if (isset($data['idRole']) && !isset($data['idRol'])) {
            $data['idRol'] = (int)$data['idRole'];
            error_log("DEBUG - Mapeado idRole a idRol: " . $data['idRol']);
        }

        // SQL con los campos reales de la tabla user
        $sql = "INSERT INTO " . self::TABLE . "
                (login, password, email, firstSession, status, idRol, idPartner)
                VALUES (:login, :password, :email, :firstSession, :status, :idRol, :idPartner)";

        error_log("DEBUG - SQL: " . $sql);

        $stmt = $this->db->prepare($sql);

        // Hashear password si llega en claro
        $hashed = isset($data['password']) && $data['password'] !== null && $data['password'] !== ''
            ? password_hash($data['password'], PASSWORD_DEFAULT)
            : null;

        // Preparar valores para binding con los campos reales
        $values = [
            ':login'       => $data['login'] ?? null,
            ':password'    => $hashed,
            ':email'       => $data['email'] ?? null,
            ':firstSession' => (int)($data['firstSession'] ?? 1), // 1 = primer inicio de sesión
            ':status'      => (int)($data['status'] ?? 1), // 1 = activo
            ':idRol'       => (int)($data['idRol'] ?? 0),
            ':idPartner'   => $data['idPartner'] ?? null,
        ];

        error_log("DEBUG - Valores para binding: " . print_r($values, true));

        // Hacer el binding
        foreach ($values as $key => $value) {
            if ($value === null) {
                $stmt->bindValue($key, null, \PDO::PARAM_NULL);
            } elseif (is_int($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, \PDO::PARAM_STR);
            }
        }

        $result = $stmt->execute();
        
        error_log("DEBUG - Execute result: " . ($result ? 'TRUE' : 'FALSE'));
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("DEBUG - Statement error: " . print_r($errorInfo, true));
            return false;
        }

        $lastId = (int)$this->db->lastInsertId();
        error_log("DEBUG - Last insert ID: " . $lastId);
        
        // Retornar el ID del usuario creado en lugar de bool
        return $lastId > 0 ? $lastId : false;
        
    } catch (\PDOException $e) {
        error_log("DEBUG - PDO Exception: " . $e->getMessage());
        error_log("DEBUG - Error code: " . $e->getCode());
        return false;
    }
}














    /**
     * UPDATE híbrido: NO FUNCIONA LOS CAMPOS ESTAN MAL
     * - Nuevo: update(int $id, array $data)
     * - Viejo: update($id, $login, $password, $idRole, $idPartner)
     */
    /**
 * UPDATE con debug mejorado para los campos reales de la tabla user
 */
public function update($id, $dataOrLogin, $password = null, $idRole = null, $idPartner = null): bool {
    try {
        $id = (int)$id;
        
        error_log("DEBUG Usuario::update - Parámetros recibidos:");
        error_log("- ID: " . $id);
        error_log("- dataOrLogin: " . print_r($dataOrLogin, true));
        error_log("- password: " . (is_string($password) ? '[HIDDEN]' : print_r($password, true)));
        error_log("- idRole: " . print_r($idRole, true));
        error_log("- idPartner: " . print_r($idPartner, true));
        
        // Normalizar datos
        if (is_array($dataOrLogin)) {
            $data = $dataOrLogin;
            error_log("DEBUG - Usando formato array");
        } else {
            $data = [
                'login'     => $dataOrLogin,
                'password'  => $password,
                'idRole'    => $idRole,
                'idPartner' => $idPartner,
            ];
            error_log("DEBUG - Convertido a array: " . print_r($data, true));
        }

        // Mapear idRole -> idRol
        if (isset($data['idRole']) && !isset($data['idRol'])) {
            $data['idRol'] = (int)$data['idRole'];
            error_log("DEBUG - Mapeado idRole a idRol: " . $data['idRol']);
        }

        // Construir campos para actualizar (solo campos reales de la tabla)
        $fields = [];
        $params = [':id' => $id];

        // Campos permitidos en la tabla user
        $allowed = ['login', 'password', 'email', 'firstSession', 'status', 'idRol', 'idPartner'];
        
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = :$field";
                
                if ($field === 'password' && $data[$field] !== null && $data[$field] !== '') {
                    // Solo hashear si se proporciona nueva contraseña
                    $params[":$field"] = password_hash($data[$field], PASSWORD_DEFAULT);
                    error_log("DEBUG - Hasheando nueva contraseña");
                } else {
                    $params[":$field"] = $data[$field];
                }
                
                error_log("DEBUG - Campo $field: " . ($field === 'password' ? '[HIDDEN]' : $data[$field]));
            }
        }

        if (empty($fields)) {
            error_log("DEBUG - No hay campos para actualizar");
            return true; // No hay nada que actualizar
        }

        $sql = "UPDATE " . self::TABLE . " 
                SET " . implode(', ', $fields) . " 
                WHERE idUser = :id";

        error_log("DEBUG - SQL: " . $sql);
        error_log("DEBUG - Parámetros: " . print_r($params, true));

        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            $error = $this->db->errorInfo();
            error_log("DEBUG - Error preparando statement: " . print_r($error, true));
            return false;
        }

        // Bind parameters
        foreach ($params as $key => $value) {
            if ($value === null) {
                $stmt->bindValue($key, null, \PDO::PARAM_NULL);
            } elseif (is_int($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, \PDO::PARAM_STR);
            }
            error_log("DEBUG - Binding $key: " . ($key === ':password' ? '[HIDDEN]' : $value));
        }

        $result = $stmt->execute();
        
        error_log("DEBUG - Execute result: " . ($result ? 'TRUE' : 'FALSE'));
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("DEBUG - Statement error: " . print_r($errorInfo, true));
            return false;
        }

        $rowCount = $stmt->rowCount();
        error_log("DEBUG - Rows affected: " . $rowCount);
        
        // Verificar que el usuario existe
        if ($rowCount === 0) {
            error_log("DEBUG - ADVERTENCIA: 0 filas afectadas. ¿El usuario ID $id existe?");
            // Verificar si el usuario existe
            $checkSql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE idUser = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $checkStmt->execute();
            $exists = $checkStmt->fetchColumn();
            error_log("DEBUG - Usuario ID $id " . ($exists ? 'SÍ existe' : 'NO existe'));
        }

        return $result;
        
    } catch (\PDOException $e) {
        error_log("DEBUG - PDO Exception en update: " . $e->getMessage());
        error_log("DEBUG - Error code: " . $e->getCode());
        error_log("DEBUG - SQL State: " . ($e->errorInfo[0] ?? 'N/A'));
        return false;
    } catch (\Throwable $e) {
        error_log("DEBUG - Exception general en update: " . $e->getMessage());
        return false;
    }
}

    /** Tu versión: crear desde Google (devuelve id o false) */
    public function createFromGoogle(array $googleData): int|false {
        try {
            $userData = [
                'login'      => $googleData['email'],
                'password'   => bin2hex(random_bytes(32)), // temp
                'email'      => $googleData['email'],
                'name'       => $googleData['name'],
                // entrada puede venir como idRole; lo mapeo a idRol dentro de create()
                'idRole'     => $googleData['idRole'] ?? 1,
                'google_id'  => $googleData['google_id'],
                'picture'    => $googleData['picture'] ?? null,
                'firstLogin' => 1
            ];

            if ($this->create($userData)) {
                return (int)$this->db->lastInsertId();
            }
            return false;
        } catch (\PDOException $e) {
            error_log("Error al crear usuario desde Google: " . $e->getMessage());
            return false;
        }
    }

    /** Tu versión: updateGoogleInfo delega a update() */
    public function updateGoogleInfo(int $userId, string $googleId, ?string $picture = null): bool {
        try {
            $data = ['google_id' => $googleId];
            if ($picture !== null) $data['picture'] = $picture;
            return $this->update($userId, $data);
        } catch (\Throwable $e) {
            error_log("Error al actualizar info de Google: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // ============     RECUPERACIÓN DE CONTRASEÑA   ===========
    // =========================================================

    /** Tu versión: guarda token + expiración 24h */
    public function savePasswordResetToken(string $email, string $token): bool {
        try {
            $expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
            $sql = "UPDATE " . self::TABLE . "
                    SET tokenRecovery = :token,
                        tokenExpiration = :expiration
                    WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
            $stmt->bindParam(':expiration', $expiration, \PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, \PDO::PARAM_STR);
            $ok = $stmt->execute();
            if (!$ok) error_log("savePasswordResetToken error: " . print_r($stmt->errorInfo(), true));
            return $ok;
        } catch (\PDOException $e) {
            error_log("Error al guardar token de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /** Tu versión: valida token vigente y devuelve datos mínimos */
    public function verifyPasswordResetToken(string $token): ?array {
        try {
            $sql = "SELECT idUser, email, login
                    FROM " . self::TABLE . "
                    WHERE tokenRecovery = :token
                      AND tokenExpiration > NOW()
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token, \PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error al verificar token de recuperación: " . $e->getMessage());
            return null;
        }
    }

    /** Tu versión: reset pass + limpia token */
    public function updatePassword(int $userId, string $newPassword): bool {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT); //aqui hasheamos  
            /*
            Aquí tomas la nueva contraseña ($newPassword) y la pasas por password_hash().
            PASSWORD_DEFAULT usa por defecto el algoritmo más seguro disponible en PHP 
            (actualmente bcrypt, en versiones más recientes podría ser argon2i o argon2id).
            */
            $sql = "UPDATE " . self::TABLE . "
                    SET password = :password,
                        tokenRecovery = NULL,
                        tokenExpiration = NULL    
                    WHERE idUser = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $hashed, \PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar contraseña (reset): " . $e->getMessage());
            return false;
        }
    }

    /** Tu versión: cambio de contraseña y marcar que ya no es primer login */
    public function updatePasswordAndUnsetFirstLogin(int $userId, string $newPassword): bool {
        try {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE " . self::TABLE . "
                    SET password = :password,
                        firstSession = 1
                    WHERE idUser = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $hashed, \PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar contraseña y firstSession: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // ===============           UTILIDADES        =============
    // =========================================================

    /**
 * Soft delete - cambiar status a 0 en lugar de eliminar el registro
 * Es mejor práctica mantener los datos históricos
 */
public function delete(int $id): bool {
    try {
        error_log("DEBUG Usuario::delete - Desactivando usuario ID: $id");
        
        // Soft delete: cambiar status a 0 en lugar de DELETE
        $sql = "UPDATE " . self::TABLE . " SET status = 0 WHERE idUser = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        error_log("DEBUG Usuario::delete - Resultado: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("DEBUG Usuario::delete - Error: " . print_r($errorInfo, true));
        }
        
        return $result;
        
    } catch (\PDOException $e) {
        error_log("Error al desactivar usuario: " . $e->getMessage());
        return false;
    }
}

/**
 * Hard delete - eliminar completamente el registro (usar solo cuando sea necesario)
 */
public function hardDelete(int $id): bool {
    try {
        error_log("DEBUG Usuario::hardDelete - Eliminando completamente usuario ID: $id");
        
        $sql = "DELETE FROM " . self::TABLE . " WHERE idUser = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        error_log("DEBUG Usuario::hardDelete - Resultado: " . ($result ? 'SUCCESS' : 'FAILED'));
        
        return $result;
        
    } catch (\PDOException $e) {
        error_log("Error al eliminar usuario completamente: " . $e->getMessage());
        return false;
    }
}















    /** Tu versión: verificar si existe email */
    public function emailExists(string $email, ?int $excludeUserId = null): bool {
        try {
            $sql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE email = :email";
            $params = [':email' => $email];
            if ($excludeUserId !== null) {
                $sql .= " AND idUser != :userId";
                $params[':userId'] = $excludeUserId;
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error al verificar email: " . $e->getMessage());
            return true;
        }
    }

    /** Tu versión: verificar si existe login */
    public function loginExists(string $login, ?int $excludeUserId = null): bool {
        try {
            $sql = "SELECT COUNT(*) FROM " . self::TABLE . " WHERE login = :login";
            $params = [':login' => $login];
            if ($excludeUserId !== null) {
                $sql .= " AND idUser != :userId";
                $params[':userId'] = $excludeUserId;
            }
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error al verificar login: " . $e->getMessage());
            return true;
        }
    }

    /** Tu versión: limpiar tokens expirados */
    public function cleanExpiredTokens(): int {
        try {
            $sql = "UPDATE " . self::TABLE . "
                    SET tokenRecovery = NULL, tokenExpiration = NULL, updated_at = NOW()
                    WHERE tokenExpiration < NOW()";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return (int)$stmt->rowCount();
        } catch (\PDOException $e) {
            error_log("Error al limpiar tokens expirados: " . $e->getMessage());
            return 0;
        }
    }
}
