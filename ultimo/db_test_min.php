<?php
ini_set('display_errors',1); error_reporting(E_ALL);

$host = 'localhost';
$db   = 'algori12_abap';
$user = 'algori12_abap';
$pass = 'Abap2025*';   // tu clave real

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8",$user,$pass,[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
  echo "DB OK";
} catch (Throwable $e) {
  echo "DB FAIL: ".$e->getMessage();
}
?>