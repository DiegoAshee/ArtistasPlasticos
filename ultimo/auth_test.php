<?php
ini_set('display_errors',1); error_reporting(E_ALL);

$cfg = __DIR__.'/app/Config/config.php';
$dbf = __DIR__.'/app/Config/database.php'; // nombre del archivo en minúscula

if (!is_file($cfg)) die("NO existe config.php en: $cfg");
if (!is_file($dbf)) die("NO existe database.php en: $dbf");

require $cfg;
require $dbf;

echo "INCLUDES OK<br>";

try {
  $pdo = Database::singleton()->getConnection();
  echo "DB SINGLETON OK<br>";
} catch (Throwable $e) {
  die("EXCEPCIÓN DB: ".$e->getMessage());
}

// Usuario/clave a probar:
$login = 'partner';
$pass  = 'partner';

$stmt = $pdo->prepare("SELECT password FROM `User` WHERE login=:l LIMIT 1");
$stmt->execute([':l'=>$login]);
$row = $stmt->fetch();
if(!$row){ die("No existe usuario con login='$login'"); }

$hash = (string)$row['password'];
echo "len=".strlen($hash)." starts=".htmlspecialchars(substr($hash,0,4))."<br>";
var_dump(password_verify($pass,$hash));
?>