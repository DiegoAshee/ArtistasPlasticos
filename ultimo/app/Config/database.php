<?php
// app/Config/Database.php

require_once __DIR__ . '/../Config/config.php';  // Incluir el archivo de configuración



class Database {
    private $conn = null;
    private static $instancia;

    private function __construct() {
        // Usar la constante DB_NAME definida en config.php
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS);  // Usar las constantes DB_USER y DB_PASS definidas en config.php
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Para mostrar errores correctamente
        } catch (PDOException $e) {
            die("¡Error!: " . $e->getMessage() . "<br/>");
        }
    }

    public function __destruct() {
        $this->conn = null;
    }

    // Singleton
    public static function singleton() {
        if (!isset(self::$instancia)) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function getConnection() {
        return $this->conn;  // Retorna la conexión PDO
    }
}
?>
