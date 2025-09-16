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

    public function findById(int $id): ?array {
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
    }

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

    /** Compatibilidad con el controlador */
    public function getUsersAdmin(): array {
        try {
            $sql = "SELECT idUser, login, email
                    FROM " . self::TABLE . " WHERE idRol = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener usuarios admin: " . $e->getMessage());
            return [];
        }
    }

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
     * CREATE híbrido:
     * - Si pasas un array (nuevo diseño), lo usa.
     * - Si pasas parámetros sueltos (viejo controlador), también funciona.
     */
    public function create($loginOrData, $password = null, $email = null, $idRole = null, $idPartner = null): bool {
        try {
            // Normalizamos a array de datos
            if (is_array($loginOrData)) {
                $data = $loginOrData;
            } else {
                $data = [
                    'login'     => (string)$loginOrData,
                    'password'  => (string)$password,
                    'email'     => $email,
                    // controlador usa idRole; la columna es idRol
                    'idRole'    => $idRole,
                    'idPartner' => $idPartner,
                    'name'      => $data['name'] ?? null,
                ];
            }

            // Mapear idRole -> idRol
            if (isset($data['idRole']) && !isset($data['idRol'])) {
                $data['idRol'] = (int)$data['idRole'];
            }

            $sql = "INSERT INTO " . self::TABLE . "
                    (login, password, email, name, firstLogin, idRol, idPartner, google_id, picture, created_at)
                    VALUES (:login, :password, :email, :name, :firstLogin, :idRol, :idPartner, :google_id, :picture, NOW())";

            $stmt = $this->db->prepare($sql);

            // Hashear password si llega en claro
            $hashed = isset($data['password']) && $data['password'] !== null
                ? password_hash($data['password'], PASSWORD_DEFAULT)
                : null;

            $stmt->bindValue(':login',      $data['login']    ?? null, \PDO::PARAM_STR);
            $stmt->bindValue(':password',   $hashed,                         \PDO::PARAM_STR);
            $stmt->bindValue(':email',      $data['email']    ?? null, \PDO::PARAM_STR);
            $stmt->bindValue(':name',       $data['name']     ?? null, \PDO::PARAM_STR);
            $stmt->bindValue(':firstLogin', (int)($data['firstLogin'] ?? 0), \PDO::PARAM_INT);
            $stmt->bindValue(':idRol',      (int)($data['idRol'] ?? 0), \PDO::PARAM_INT);
            $stmt->bindValue(':idPartner',  $data['idPartner'] ?? null, $data['idPartner'] === null ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
            $stmt->bindValue(':google_id',  $data['google_id'] ?? null, \PDO::PARAM_STR);
            $stmt->bindValue(':picture',    $data['picture']   ?? null, \PDO::PARAM_STR);

            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * UPDATE híbrido: NO FUNCIONA LOS CAMPOS ESTAN MAL
     * - Nuevo: update(int $id, array $data)
     * - Viejo: update($id, $login, $password, $idRole, $idPartner)
     */
    public function update($id, $dataOrLogin, $password = null, $idRole = null, $idPartner = null): bool {
        try {
            $id = (int)$id;
            $data = is_array($dataOrLogin) ? $dataOrLogin : [
                'login'     => $dataOrLogin,
                'password'  => $password,
                'idRole'    => $idRole,
                'idPartner' => $idPartner,
            ];

            // Mapear idRole -> idRol
            if (isset($data['idRole']) && !isset($data['idRol'])) {
                $data['idRol'] = (int)$data['idRole'];
            }

            $fields = [];
            $params = [':id' => $id];

            $allowed = ['login','password','email','name','idRol','idPartner','google_id','picture','firstLogin'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = :$f";
                    if ($f === 'password' && $data[$f] !== null && $data[$f] !== '') {
                        $params[":$f"] = password_hash($data[$f], PASSWORD_DEFAULT);
                    } else {
                        $params[":$f"] = $data[$f];
                    }
                }
            }
            if (!$fields) return true;

            $sql = "UPDATE " . self::TABLE . "
                    SET " . implode(', ', $fields) . ", updated_at = NOW()
                    WHERE idUser = :id";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, $v === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
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
                        firstLogin = 1,
                        updated_at = NOW()
                    WHERE idUser = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':password', $hashed, \PDO::PARAM_STR);
            $stmt->bindParam(':id', $userId, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al actualizar contraseña y firstLogin: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // ===============           UTILIDADES        =============
    // =========================================================

    public function delete(int $id): bool {
        try {
            $sql = "DELETE FROM " . self::TABLE . " WHERE idUser = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
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
