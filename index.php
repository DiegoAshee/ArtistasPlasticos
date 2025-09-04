<?php

// index.php - Archivo principal en htdocs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir archivos necesarios
require_once 'app/Core/Router.php';

// Crear instancia del router
$router = new Router();

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACIÓN
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/', 'AuthController', 'login');
$router->addRoute('GET', '/login', 'AuthController', 'login');
$router->addRoute('POST', '/login', 'AuthController', 'login');
$router->addRoute('GET', '/logout', 'AuthController', 'logout');

// Recuperación de contraseña
$router->addRoute('GET', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('POST', '/forgot-password', 'AuthController', 'forgotPassword');
$router->addRoute('GET', '/reset-password', 'AuthController', 'resetPassword');
$router->addRoute('POST', '/reset-password', 'AuthController', 'resetPassword');

// Cambio de contraseña dentro de sesión
$router->addRoute('GET', '/change-password', 'AuthController', 'changePassword');
$router->addRoute('POST', '/change-password', 'AuthController', 'changePassword');

/*
|--------------------------------------------------------------------------
| RUTAS DE CONTRIBUTION
|--------------------------------------------------------------------------
*/

// In index.php, under RUTAS DEL PAYMENT or a new section
$router->addRoute('GET', '/contribution/list', 'ContributionController', 'list');
$router->addRoute('POST', '/contribution/list', 'ContributionController', 'list'); // Add POST route$router->addRoute('GET', '/contribution/create', 'ContributionController', 'create');
$router->addRoute('POST', '/contribution/create', 'ContributionController', 'create');
$router->addRoute('GET', '/contribution/edit/([0-9]+)', 'ContributionController', 'update');
$router->addRoute('POST', '/contribution/edit/([0-9]+)', 'ContributionController', 'update');
$router->addRoute('GET', '/contribution/delete/([0-9]+)', 'ContributionController', 'delete');

/*
|--------------------------------------------------------------------------
| RUTAS DE ONLINEPARTNER
|--------------------------------------------------------------------------
*/

// Registro de socio
$router->addRoute('GET', '/partner/register', 'OnlinePartnerController', 'registerPartner');
$router->addRoute('POST', '/partner/register', 'OnlinePartnerController', 'registerPartner');
$router->addRoute('GET', '/partner/register/success', 'OnlinePartnerController', 'registerPartner'); // página simple de éxito

/*
|--------------------------------------------------------------------------
| RUTAS DEL DASHBOARD
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/dashboard', 'DashboardController', 'dashboard');

/*
|--------------------------------------------------------------------------
| RUTAS DE USUARIOS
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/users/list', 'UserController', 'listUsers');
$router->addRoute('GET', '/users/profile', 'UserController', 'userProfile');

/*
|--------------------------------------------------------------------------
| RUTAS DE SOCIOS (PARTNERS)
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/partner/create', 'PartnerController', 'createPartner');
$router->addRoute('POST', '/partner/create', 'PartnerController', 'createPartner');
$router->addRoute('GET', '/partner/list', 'PartnerController', 'listSocios');

$router->addRoute('GET', '/partner/edit/([0-9]+)', 'PartnerController', 'updatePartner');
$router->addRoute('POST', '/partner/edit/([0-9]+)', 'PartnerController', 'updatePartner');
$router->addRoute('GET', '/partner/delete/([0-9]+)', 'PartnerController', 'deletePartner');

// gestión de registros de socios pendientes
$router->addRoute('GET', '/partner/manage', 'PartnerController', 'manageRegistrations');
$router->addRoute('POST', '/partner/manage', 'PartnerController', 'manageRegistrations');

/*
|--------------------------------------------------------------------------
| RUTAS DEL PAYMENT
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/payment/list', 'PaymentController', 'list');

/*
|--------------------------------------------------------------------------
| MANEJO DE ERRORES
|--------------------------------------------------------------------------
*/
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "<b>Error:</b> [$errno] $errstr - $errfile:$errline<br>";
});
set_exception_handler(function($e) {
    echo "<b>Excepción:</b> " . $e->getMessage() . " - " . $e->getFile() . ":" . $e->getLine() . "<br>";
});

// Despachar la ruta
$router->dispatch();

