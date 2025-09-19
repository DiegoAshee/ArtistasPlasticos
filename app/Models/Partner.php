<?php
// app/Models/Partner.php

require_once __DIR__ . '/../Config/database.php';

class Partner {
    private const TBL = '`partner`';
    private const TBL2 = '`user`';
    private $db;

    public function __construct() {
        $this->db = Database::singleton()->getConnection();
    }

    public function create($name, $ci, $cellPhoneNumber, $address, $dateCreation, $birthday, $dateRegistration,$frontImageURL,$backImageURL) {
        try {
            // Generar URLs de imágenes automáticamente basadas en el CI
            /* $frontImageURL = "images/carnets/front_" . $ci . ".png";
            $backImageURL = "images/carnets/back_" . $ci . ".png"; */
            
            $query = "INSERT INTO " . self::TBL . " 
                      (name, ci, cellPhoneNumber, address, dateCreation, birthday, dateRegistration, status, frontImageURL, backImageURL) 
                      VALUES (:name, :ci, :cellPhoneNumber, :address, :dateCreation, :birthday, :dateRegistration, 1, :frontImageURL, :backImageURL)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':dateCreation', $dateCreation, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':dateRegistration', $dateRegistration, PDO::PARAM_STR);
            $stmt->bindParam(':frontImageURL', $frontImageURL, PDO::PARAM_STR);
            $stmt->bindParam(':backImageURL', $backImageURL, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            
            if ($result) {
                $partnerId = $this->db->lastInsertId();
                error_log("Partner creado con ID: $partnerId, frontImage: $frontImageURL, backImage: $backImageURL");
                return $partnerId;
            } else {
                error_log("Error al ejecutar INSERT partner: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error al crear socio: " . $e->getMessage());
            return false;
        }
    }

    public function getAllSocios() {
        try {
            $query = "SELECT 
                        p.idPartner,
                        p.name,
                        p.ci,
                        p.cellPhoneNumber,
                        p.address,
                        p.frontImageURL,
                        p.backImageURL,
                        DATE_FORMAT(p.dateCreation, '%Y-%m-%d %H:%i:%s') as dateCreation,
                        DATE_FORMAT(p.birthday, '%Y-%m-%d') as birthday,
                        DATE_FORMAT(p.dateRegistration, '%Y-%m-%d') as dateRegistration,
                        u.login,
                        u.email,
                        u.status as userStatus
                      FROM " . self::TBL . " p 
                      JOIN " . self::TBL2 . " u ON p.idPartner = u.idPartner 
                      WHERE u.idRol = 2 
                        AND p.status = 1 
                        AND u.status = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log('Socios activos data: ' . print_r($result, true));
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error al obtener socios: " . $e->getMessage());
            return [];
        }
    }

    public function findById($id) {
        try {
            $query = "SELECT * FROM " . self::TBL . " WHERE idPartner = :id AND status = 1 LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar socio: " . $e->getMessage());
            return null;
        }
    }

    /*public function update($id, $name, $ci, $cellPhoneNumber, $address, $birthday, $dateRegistration) {
        try {
            // Generar URLs de imágenes basadas en el nuevo CI
            $frontImageURL = "images/carnets/front_" . $ci . ".png";
            $backImageURL = "images/carnets/back_" . $ci . ".png";
            
            $query = "UPDATE " . self::TBL . " 
                      SET name = :name, ci = :ci, cellPhoneNumber = :cellPhoneNumber, 
                          address = :address, birthday = :birthday, dateRegistration = :dateRegistration,
                          frontImageURL = :frontImageURL, backImageURL = :backImageURL
                      WHERE idPartner = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
            $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber, PDO::PARAM_STR);
            $stmt->bindParam(':address', $address, PDO::PARAM_STR);
            $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
            $stmt->bindParam(':dateRegistration', $dateRegistration, PDO::PARAM_STR);
            $stmt->bindParam(':frontImageURL', $frontImageURL, PDO::PARAM_STR);
            $stmt->bindParam(':backImageURL', $backImageURL, PDO::PARAM_STR);
            
            error_log("DEBUG - Actualizando partner con imágenes: front=$frontImageURL, back=$backImageURL");
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar socio: " . $e->getMessage());
            return false;
        }
    }*/




    public function update($id, $name, $ci, $cellPhoneNumber, $address, $birthday, $dateRegistration, $frontImageURL = null, $backImageURL = null) {
    try {
        $query = "UPDATE " . self::TBL . " 
                  SET name = :name, CI = :ci, cellPhoneNumber = :cellPhoneNumber, 
                      address = :address, birthday = :birthday, dateRegistration = :dateRegistration";
        
        // Solo actualizar las imágenes si se proporcionan
        if ($frontImageURL !== null) {
            $query .= ", frontImageURL = :frontImageURL";
        }
        if ($backImageURL !== null) {
            $query .= ", backImageURL = :backImageURL";
        }
        
        $query .= " WHERE idPartner = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':ci', $ci, PDO::PARAM_STR);
        $stmt->bindParam(':cellPhoneNumber', $cellPhoneNumber, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
        $stmt->bindParam(':dateRegistration', $dateRegistration, PDO::PARAM_STR);
        
        // Bindear las imágenes solo si se proporcionan
        if ($frontImageURL !== null) {
            $stmt->bindParam(':frontImageURL', $frontImageURL, PDO::PARAM_STR);
        }
        if ($backImageURL !== null) {
            $stmt->bindParam(':backImageURL', $backImageURL, PDO::PARAM_STR);
        }
        
        error_log("DEBUG - Actualizando partner: CI=$ci");
        error_log("DEBUG - Imágenes: front=" . ($frontImageURL ?? 'MANTENER') . ", back=" . ($backImageURL ?? 'MANTENER'));
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error al actualizar socio: " . $e->getMessage());
        return false;
    }
}




    






    public function delete($id) {
        try {
            // Soft delete - cambiar status a 0
            $query = "UPDATE " . self::TBL . " SET status = 0 WHERE idPartner = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar socio: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Método para actualizar solo las URLs de las imágenes
     */
    public function updateImages($id, $frontImageURL = null, $backImageURL = null) {
        try {
            $fields = [];
            $params = [':id' => $id];
            
            if ($frontImageURL !== null) {
                $fields[] = "frontImageURL = :frontImageURL";
                $params[':frontImageURL'] = $frontImageURL;
            }
            
            if ($backImageURL !== null) {
                $fields[] = "backImageURL = :backImageURL";
                $params[':backImageURL'] = $backImageURL;
            }
            
            if (empty($fields)) {
                return true; // Nada que actualizar
            }
            
            $query = "UPDATE " . self::TBL . " SET " . implode(', ', $fields) . " WHERE idPartner = :id";
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar imágenes del socio: " . $e->getMessage());
            return false;
        }
    }
}