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
| RUTAS DE COMPETENCIA (MENÚ)
|-------------------------------------------------------------------------- 
*/

// Ver la lista de competencias
$router->addRoute('GET', '/competence/list', 'CompetenceController', 'listAll');
$router->addRoute('GET', '/competence/create', 'CompetenceController', 'create');  // Vista para crear
$router->addRoute('POST', '/competence/create', 'CompetenceController', 'create'); // Enviar formulario de creación
$router->addRoute('GET', '/competence/edit/([0-9]+)', 'CompetenceController', 'update');  // Vista para editar
$router->addRoute('POST', '/competence/edit/([0-9]+)', 'CompetenceController', 'update'); // Enviar formulario de edición
$router->addRoute('GET', '/competence/delete/([0-9]+)', 'CompetenceController', 'delete');

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

$router->addRoute('GET', '/partner/verify', 'OnlinePartnerController', 'verifyEmail');

/*
|--------------------------------------------------------------------------
| RUTAS DEL DASHBOARD
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/dashboard', 'DashboardController', 'dashboard');

/*
|--------------------------------------------------------------------------
| RUTAS DEL ROLE
|--------------------------------------------------------------------------
*/
// Define routes
$router->addRoute('GET', '/role/list', 'RoleController', 'list');
$router->addRoute('POST', '/role/list', 'RoleController', 'list'); // Add POST route

/*
|--------------------------------------------------------------------------
| RUTAS DE PERMISOS
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/permissions', 'PermissionController', 'index');
$router->addRoute('GET', '/permissions/create', 'PermissionController', 'create');
$router->addRoute('POST', '/permissions/update', 'PermissionController', 'update');

/*
|--------------------------------------------------------------------------
| RUTAS DE USUARIOS
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/users/list', 'UserController', 'listUsers');
$router->addRoute('GET', '/users/profile', 'UserController', 'userProfile');
$router->addRoute('GET', '/users/profile/edit', 'UserController', 'editProfile');
$router->addRoute('POST', '/users/profile/update', 'UserController', 'updateProfile');

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

// Exportar lista de socios a PDF
$router->addRoute('GET', '/partner/export-pdf', 'PartnerController', 'export');

/*
|--------------------------------------------------------------------------
| RUTAS DEL PAYMENT
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/payment/list', 'PaymentController', 'list');

/*
|--------------------------------------------------------------------------
| RUTAS DEL PAYMENT
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/payment/list', 'PaymentController', 'list');

/*
|--------------------------------------------------------------------------
| RUTAS DEL PARNERT ONLINE
|--------------------------------------------------------------------------
*/
$router->addRoute('POST', '/partnerOnline/createRequest', 'PartnerOnlineController', 'createRequest');

// Bandeja de pendientes de partneronline (solo vista/mostrar)
// Bandeja de solicitudes pendientes (solo lectura)
$router->addRoute('GET', '/partnerOnline/pending', 'PartnerOnlineController', 'pending');


//aprobar o rechazar solicitudes 
// Acciones sobre solicitudes (POST)
$router->addRoute('POST', '/partnerOnline/approve', 'PartnerOnlineController', 'approve');
$router->addRoute('POST', '/partnerOnline/reject',  'PartnerOnlineController', 'reject');



//cobros
/* RUTAS DE COBROS (módulo nuevo, sin chocar con Payment*) */
/* RUTAS DE COBROS */
$router->addRoute('GET',  '/cobros/list',            'CobroController', 'pagadas'); // alias
$router->addRoute('GET',  '/cobros/pagadas',         'CobroController', 'pagadas');
$router->addRoute('GET',  '/cobros/debidas',         'CobroController', 'debidas');

$router->addRoute('GET',  '/cobros/create',          'CobroController', 'create');
$router->addRoute('POST', '/cobros/create',          'CobroController', 'create');
$router->addRoute('GET',  '/cobros/edit/([0-9]+)',   'CobroController', 'edit');
$router->addRoute('POST', '/cobros/edit/([0-9]+)',   'CobroController', 'edit');
$router->addRoute('GET',  '/cobros/delete/([0-9]+)', 'CobroController', 'delete');

// AGREGAR ESTA NUEVA RUTA PARA LA API DE DEUDAS:
$router->addRoute('GET',  '/cobros/debts-api',       'CobroController', 'debtsApi');

/**/ 


// Bandeja de pendientes de partneronline (solo vista/mostrar)
// Bandeja de solicitudes pendientes (solo lectura)
$router->addRoute('GET', '/partnerOnline/pending', 'PartnerOnlineController', 'pending');


//aprobar o rechazar solicitudes 
// Acciones sobre solicitudes (POST)
$router->addRoute('POST', '/partnerOnline/approve', 'PartnerOnlineController', 'approve');
$router->addRoute('POST', '/partnerOnline/reject',  'PartnerOnlineController', 'reject');



//cobros
/* RUTAS DE COBROS (módulo nuevo, sin chocar con Payment*) */
/* RUTAS DE COBROS */
$router->addRoute('GET',  '/cobros/list',            'CobroController', 'pagadas'); // alias
$router->addRoute('GET',  '/cobros/pagadas',         'CobroController', 'pagadas');
$router->addRoute('GET',  '/cobros/debidas',         'CobroController', 'debidas');

$router->addRoute('GET',  '/cobros/create',          'CobroController', 'create');
$router->addRoute('POST', '/cobros/create',          'CobroController', 'create');
$router->addRoute('GET',  '/cobros/edit/([0-9]+)',   'CobroController', 'edit');
$router->addRoute('POST', '/cobros/edit/([0-9]+)',   'CobroController', 'edit');
$router->addRoute('GET',  '/cobros/delete/([0-9]+)', 'CobroController', 'delete');

// AGREGAR ESTA NUEVA RUTA PARA LA API DE DEUDAS:
$router->addRoute('GET',  '/cobros/debts-api',       'CobroController', 'debtsApi');

/**/ 


/*
|--------------------------------------------------------------------------
| RUTAS DE PAGOS DE SOCIOS (NUEVAS)
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/partner/payment', 'PaymentController', 'viewPayments');
$router->addRoute('POST', '/partner/payment', 'PaymentController', 'viewPayments'); // Maneja el formulario de pago
//$router->addRoute('GET', '/partner/export-pdf-payments', 'PartnerPaymentController', 'exportPDF'); // Opcional, implementar export/*

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
