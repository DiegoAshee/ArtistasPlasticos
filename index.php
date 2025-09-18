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
$router->addRoute('GET', '/competence/competence_list', 'CompetenceController', 'listAll');
$router->addRoute('GET', '/competence/create', 'CompetenceController', 'create');  // Vista para crear
$router->addRoute('POST', '/competence/create', 'CompetenceController', 'create'); // Enviar formulario de creación
$router->addRoute('GET', '/competence/update/([0-9]+)', 'CompetenceController', 'update');  // Vista para editar
$router->addRoute('POST', '/competence/update/([0-9]+)', 'CompetenceController', 'update'); // Enviar formulario de edición
// Delete a competence
$router->addRoute('POST', '/competence/delete/([0-9]+)', 'CompetenceController', 'delete');
$router->addRoute('GET', '/competence/delete/([0-9]+)', 'CompetenceController', 'delete'); // Keep GET for backward compatibility

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
$router->addRoute('GET', '/users/create', 'UserController', 'createAdmin');
$router->addRoute('POST', '/users/create', 'UserController', 'createAdmin');
$router->addRoute('GET', '/users/edit/([0-9]+)', 'UserController', 'editUser');
$router->addRoute('POST', '/users/edit/([0-9]+)', 'UserController', 'editUser');
$router->addRoute('GET', '/users/delete/([0-9]+)', 'UserController', 'deleteUser');
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

//pagosmultiples
$router->addRoute('GET', '/cobros/create-multiple', 'CobroController', 'createMultiple');
$router->addRoute('POST', '/cobros/create-multiple', 'CobroController', 'createMultiple');

//para recibo de multiples pagos
$router->addRoute('GET', '/cobros/recibo', 'CobroController', 'recibo');
/**/ 

/*
|--------------------------------------------------------------------------
| RUTAS DE PAGOS DE SOCIOS (NUEVAS)
|--------------------------------------------------------------------------
*/
//$router->addRoute('GET', '/partner/payment', 'PaymentController', 'viewPayments');
//$router->addRoute('POST', '/partner/payment', 'PaymentController', 'viewPayments'); // Maneja el formulario de pago
$router->addRoute('GET', '/partner/pending-payments', 'PartnerPaymentController', 'viewPendingPayments');
$router->addRoute('GET', '/partner/payment-history', 'PartnerPaymentController', 'viewPaymentHistory');
$router->addRoute('POST', '/partner/pending-payments', 'PartnerPaymentController', 'viewPendingPayments'); // Para procesar el pago
//$router->addRoute('GET', '/partner/export-pdf-payments', 'PartnerPaymentController', 'exportPDF'); // Opcional, implementar export/*

/*
|--------------------------------------------------------------------------
| RUTAS ADMINISTRATIVAS DE PAGOS
|--------------------------------------------------------------------------
*/
$router->addRoute('GET', '/admin/review-payments', 'AdminPaymentController', 'reviewPayments');
$router->addRoute('POST', '/admin/review-payments', 'AdminPaymentController', 'reviewPayments');
$router->addRoute('GET', '/admin/payment-receipt', 'AdminPaymentController', 'showReceipt');






/*rutas para opciones*/
// Rutas para Options
$router->addRoute('GET', '/options', 'OptionController', 'index');
$router->addRoute('GET', '/options/create', 'OptionController', 'create');
$router->addRoute('POST', '/options/store', 'OptionController', 'store');
$router->addRoute('GET', '/options/edit', 'OptionController', 'edit');
$router->addRoute('POST', '/options/update', 'OptionController', 'update');
$router->addRoute('POST', '/options/activate', 'OptionController', 'activate');
$router->addRoute('POST', '/options/delete', 'OptionController', 'delete');

//Coneceptos
//$router->addRoute('GET',  '/conceptos/list',            'CobroController', 'pagadas'); // alias



/*
|--------------------------------------------------------------------------
| RUTAS ADMINISTRATIVAS DE MOVIMIENTOS
|--------------------------------------------------------------------------
*/

// Lista de movimientos
$router->addRoute('GET', '/movement/list', 'MovementController', 'list');

// Crear movimiento
$router->addRoute('GET', '/movement/create', 'MovementController', 'create');
$router->addRoute('POST', '/movement/create', 'MovementController', 'store');

// Editar movimiento
$router->addRoute('GET', '/movement/edit/([0-9]+)', 'MovementController', 'edit');
$router->addRoute('POST', '/movement/edit/{id}', 'MovementController', 'update');

// Eliminar movimiento
$router->addRoute('GET', '/movement/delete/{id}', 'MovementController', 'delete');
$router->addRoute('POST', '/movement/delete/{id}', 'MovementController', 'destroy');

// Exportar PDF (para AJAX)
$router->addRoute('GET', '/movement/export-pdf', 'MovementController', 'exportPdf');







//conceptors
// Lista de movimientos
/*
$router->addRoute('GET', '/conceptos/list', 'ConceptController', 'list');

// Crear movimiento
$router->addRoute('GET', '/conceptos/create', 'ConceptController', 'create');
$router->addRoute('POST', '/conceptos/create', 'ConceptController', 'store');

// Editar movimiento
$router->addRoute('GET', '/conceptos/edit/([0-9]+)', 'ConceptController', 'edit');
$router->addRoute('POST', '/conceptos/edit/{id}', 'ConceptController', 'update');

// Eliminar movimiento
$router->addRoute('GET', '/conceptos/delete/{id}', 'ConceptController', 'delete');
$router->addRoute('POST', '/conceptos/delete/{id}', 'ConceptController', 'destroy');

// Exportar PDF (para AJAX)
$router->addRoute('GET', '/conceptos/export-pdf', 'ConceptController', 'exportPdf');*/

/* Rutas para Conceptos */
$router->addRoute('GET', '/conceptos', 'ConceptController', 'list');
$router->addRoute('GET', '/conceptos/list', 'ConceptController', 'list');
$router->addRoute('GET', '/conceptos/create', 'ConceptController', 'create');
$router->addRoute('POST', '/conceptos/store', 'ConceptController', 'store');
$router->addRoute('GET', '/conceptos/edit/{id}', 'ConceptController', 'edit');
$router->addRoute('POST', '/conceptos/update/{id}', 'ConceptController', 'update');
$router->addRoute('GET', '/conceptos/delete/{id}', 'ConceptController', 'delete');
$router->addRoute('POST', '/conceptos/destroy/{id}', 'ConceptController', 'destroy');
$router->addRoute('GET', '/conceptos/export-pdf', 'ConceptController', 'exportPdf');






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