<?php

// index.php - Archivo principal en htdocs
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Incluir archivos necesarios
require_once 'app/Core/Router.php';

// Crear instancia del router
$router = new Router();



// Definir rutas
$router->addRoute('GET', '/', 'UserController', 'login');
$router->addRoute('GET', '/login', 'UserController', 'login');
$router->addRoute('POST', '/login', 'UserController', 'login');
$router->addRoute('GET', '/dashboard', 'UserController', 'dashboard');
$router->addRoute('GET', '/logout', 'UserController', 'logout');
$router->addRoute('GET', '/partner/create', 'UserController', 'createPartner');
$router->addRoute('POST', '/partner/create', 'UserController', 'createPartner');
$router->addRoute('GET', '/users/list', 'UserController', 'listUsers');
$router->addRoute('GET', '/users/profile', 'UserController', 'UserProfile'); 
$router->addRoute('GET', '/partner/list', 'UserController', 'listSocios');

$router->addRoute('GET', '/partner/edit/([0-9]+)', 'UserController', 'updatePartner');
$router->addRoute('POST', '/partner/edit/([0-9]+)', 'UserController', 'updatePartner');
$router->addRoute('GET', '/partner/delete/([0-9]+)', 'UserController', 'deletePartner');

// New routes
$router->addRoute('GET', '/partner/register', 'UserController', 'registerPartner');
$router->addRoute('POST', '/partner/register', 'UserController', 'registerPartner');
$router->addRoute('GET', '/partner/register/success', 'UserController', 'registerPartner'); // Simple success page
$router->addRoute('GET', '/partner/manage', 'UserController', 'manageRegistrations');
$router->addRoute('POST', '/partner/manage', 'UserController', 'manageRegistrations');


// Rutas de recuperación de contraseña
  // Aquí procesamos el formulario de olvido de contraseña

$router->addRoute('GET', '/forgot-password', 'UserController', 'forgotPassword');
$router->addRoute('POST', '/forgot-password', 'UserController', 'forgotPassword');  // Aquí procesamos el formulario
$router->addRoute('GET', '/reset-password', 'UserController', 'resetPassword');  // Ruta para resetear la contraseña
$router->addRoute('POST', '/reset-password', 'UserController', 'resetPassword');  // Ruta para procesar el formulario de reset



set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr - $errfile:$errline<br>";
});
set_exception_handler(function($e) {
    echo "<b>Excepción:</b> " . $e->getMessage() . " - " . $e->getFile() . ":" . $e->getLine() . "<br>";
});

$router->dispatch();

?>
