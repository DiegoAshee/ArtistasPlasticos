<?php
// PHP: Generar hash para la contraseña "1234"
$password = '1234';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Imprimir el hash
echo $hashedPassword;
?>
