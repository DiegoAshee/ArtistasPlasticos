<?php
ini_set('display_errors',1); error_reporting(E_ALL);

require __DIR__.'/app/Config/config.php';
require __DIR__.'/app/Config/database.php';

$pdo = Database::singleton()->getConnection();
echo "INCLUDES OK<br>DB SINGLETON OK<br>";

$login = isset($_GET['login']) ? trim($_GET['login']) : 'partner';
$pass  = isset($_GET['pass'])  ? (string)$_GET['pass'] : 'partner';
echo "LOGIN='".htmlspecialchars($login,ENT_QUOTES)."', LEN_LOGIN=".strlen($login).", HEX_LOGIN=".bin2hex($login)."<br>";

/** Detectar tabla real: User / user / users */
$table = null;
foreach (['User','user','users'] as $t) {
  try { $pdo->query("SELECT 1 FROM `$t` LIMIT 1"); $table = $t; break; } catch (Throwable $e) {}
}
if (!$table) { die("No encuentro tabla (probé User/user/users)"); }
echo "TABLE=$table<br>";

try {
  $stmt = $pdo->prepare("SELECT password FROM `$table` WHERE login = :l LIMIT 1");
  $stmt->execute([':l'=>$login]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) { die("No existe usuario con login='$login'"); }

  $hash = (string)$row['password'];
  echo "HASH_LEN=".strlen($hash).", HASH_STARTS=".htmlspecialchars(substr($hash,0,4)).", HASH_HEX_STARTS=".bin2hex(substr($hash,0,4))."<br>";
  $ok = password_verify($pass, $hash);
  echo "VERIFY=" . ($ok ? 'TRUE' : 'FALSE');
} catch (Throwable $e) {
  echo "EXCEPCIÓN SELECT/VERIFY: ".$e->getMessage();
}
?>