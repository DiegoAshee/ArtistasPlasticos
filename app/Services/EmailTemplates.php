<?php
// app/Services/EmailTemplates.php
declare(strict_types=1);
require_once __DIR__ . '/../Models/Option.php';

class EmailTemplates 
{
    /**
     * Obtiene el título de la organización desde la tabla option
     */
    private static function getOrganizationTitle(): string
    {
        try {
            $optionModel = new Option();
            $activeOption = $optionModel->getActive();
            
            if ($activeOption && !empty($activeOption['title'])) {
                return $activeOption['title'];
            }
            
            // Si no hay opción activa, obtener la primera disponible
            $allOptions = $optionModel->getAll();
            if (!empty($allOptions) && !empty($allOptions[0]['title'])) {
                return $allOptions[0]['title'];
            }
            
            // Fallback por defecto
            return 'Asociación Boliviana de Artistas Plásticos';
            
        } catch (\Exception $e) {
            error_log('Error al obtener título de organización: ' . $e->getMessage());
            return 'Asociación Boliviana de Artistas Plásticos';
        }
    }

    /**
     * Obtiene el teléfono de contacto desde la tabla option
     */
    private static function getContactPhone(): string
    {
        try {
            $optionModel = new Option();
            $activeOption = $optionModel->getActive();
            
            if ($activeOption && !empty($activeOption['telephoneContact'])) {
                return $activeOption['telephoneContact'];
            }
            
            return '';
            
        } catch (\Exception $e) {
            error_log('Error al obtener teléfono de contacto: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Formatea fecha de YYYY-MM-DD a DD-MM-YYYY
     */
    private static function formatDate(string $date): string
    {
        try {
            if (empty($date)) return $date;
            
            // Si ya está en formato DD-MM-YYYY, devolverla tal cual
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
                return $date;
            }
            
            // Convertir de YYYY-MM-DD a DD-MM-YYYY
            $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateObj) {
                return $dateObj->format('d-m-Y');
            }
            
            return $date;
        } catch (\Exception $e) {
            error_log('Error al formatear fecha: ' . $e->getMessage());
            return $date;
        }
    }

    /**
     * Template para email de verificación
     */
    public static function emailVerification(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $verificationLink = $data['verification_link'] ?? '';
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');

        $subject = "Verifica tu correo - {$title}";

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>¡Hola {$name}!</h2>
                <p>Gracias por registrarte en {$title}.</p>
                <p>Por favor, verifica tu correo haciendo clic en el siguiente enlace:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$verificationLink}\" 
                       style=\"display:inline-block; padding:12px 24px; background:#bca478; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold;\">
                        Verificar Correo
                    </a>
                </div>
                <p><small>Este enlace expirará en 24 horas.</small></p>
                <p><small>Si no solicitaste este registro, ignora este correo.</small></p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Gracias por registrarte en {$title}.\n" .
                    "Por favor, verifica tu correo haciendo clic en el siguiente enlace:\n" .
                    "{$verificationLink}\n\n" .
                    "Este enlace expirará en 24 horas.\n\n" .
                    "Si no solicitaste este registro, ignora este correo.\n\n" .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }

    /**
     * Template para email de confirmación de registro
     */
    public static function registrationConfirmation(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $ci = htmlspecialchars($data['ci'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($data['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $cellphone = htmlspecialchars($data['cellphoneNumber'] ?? '', ENT_QUOTES, 'UTF-8');
        $birthday = self::formatDate($data['birthday'] ?? '');
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "Confirmación desolicitud de Registro como socio de {$title}";

        // Construir mensaje de contacto si hay teléfono
        $contactInfo = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p>Si tienes alguna consulta, puedes comunicarte con nosotros al <strong>{$contactPhone}</strong>.</p>";
            $contactInfoText = "Si tienes alguna consulta, puedes comunicarte con nosotros al {$contactPhone}.\n\n";
        } else {
            $contactInfoText = '';
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Hola {$name}!</h2>
                <p>Tu registro fue recibido exitosamente en {$title}.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #333;'>Datos registrados:</h3>
                    <p><strong>CI:</strong> {$ci}</p>
                    <p><strong>Correo:</strong> {$email}</p>
                    <p><strong>Celular:</strong> {$cellphone}</p>
                    <p><strong>Dirección:</strong> {$address}</p>
                    <p><strong>Fecha de nacimiento:</strong> {$birthday}</p>
                </div>
                
                <p>Nos pondremos en contacto contigo muy pronto.</p>
                {$contactInfo}
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Tu registro fue recibido exitosamente en {$title}.\n\n" .
                    "DATOS REGISTRADOS:\n" .
                    "CI: {$ci}\n" .
                    "Correo: {$email}\n" .
                    "Celular: {$cellphone}\n" .
                    "Dirección: {$address}\n" .
                    "Fecha de nacimiento: {$birthday}\n\n" .
                    "Nos pondremos en contacto contigo muy pronto.\n\n" .
                    $contactInfoText .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }
/**
     * Template para email de cambios de registro
     */
    public static function changeInformation(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $ci = htmlspecialchars($data['ci'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $cellphone = htmlspecialchars($data['cellphoneNumber'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($data['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $birthday = self::formatDate($data['birthday'] ?? '');
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "Confirmación de Registro de Solicitud de Cambios de cuanta en {$title}";

        // Construir mensaje de contacto si hay teléfono
        $contactInfo = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p>Si tienes alguna consulta, puedes comunicarte con nosotros al <strong>{$contactPhone}</strong>.</p>";
            $contactInfoText = "Si tienes alguna consulta, puedes comunicarte con nosotros al {$contactPhone}.\n\n";
        } else {
            $contactInfoText = '';
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Hola {$name}!</h2>
                <p>Tu solicitud de cambios fue recibido exitosamente en {$title}.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #333;'>Datos registrados:</h3>
                    <p><strong>CI:</strong> {$ci}</p>
                    <p><strong>Correo:</strong> {$email}</p>
                    <p><strong>Celular:</strong> {$cellphone}</p>
                    <p><strong>Fecha de nacimiento:</strong> {$birthday}</p>
                </div>
                
                <p>Nos pondremos en contacto contigo muy pronto.</p>
                {$contactInfo}
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Tu solicitud de cambios fue recibido exitosamente en {$title}.\n\n" .
                    "DATOS REGISTRADOS:\n" .
                    "Nombre: {$name}\n" .
                    "CI: {$ci}\n" .
                    "Dirección: {$address}\n" .
                    "Correo: {$email}\n" .
                    "Celular: {$cellphone}\n" .
                    "Fecha de nacimiento: {$birthday}\n\n" .
                    "Nos pondremos en contacto contigo muy pronto.\n\n" .
                    $contactInfoText .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }
    /**
     * Template para email de cambios de registro
     */
    public static function changeConfirmation(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $ci = htmlspecialchars($data['ci'] ?? '', ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($data['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $cellphone = htmlspecialchars($data['cellphoneNumber'] ?? '', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($data['address'] ?? '', ENT_QUOTES, 'UTF-8');
        $birthday = self::formatDate($data['birthday'] ?? '');
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "Confirmación de Cambios en cuanta de {$title}";

        // Construir mensaje de contacto si hay teléfono
        $contactInfo = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p>Si tienes alguna consulta, puedes comunicarte con nosotros al <strong>{$contactPhone}</strong>.</p>";
            $contactInfoText = "Si tienes alguna consulta, puedes comunicarte con nosotros al {$contactPhone}.\n\n";
        } else {
            $contactInfoText = '';
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Hola {$name}!</h2>
                <p>Tu solicitud de cambios en tu cuenta de {$title} fue aceptado.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #333;'>Datos nuevos:</h3>
                    <p><strong>Nombre:</strong> {$name}</p>
                    <p><strong>CI:</strong> {$ci}</p>
                    <p><strong>Address:</strong> {$address}</p>
                    <p><strong>Correo:</strong> {$email}</p>
                    <p><strong>Celular:</strong> {$cellphone}</p>
                    <p><strong>Fecha de nacimiento:</strong> {$birthday}</p>
                </div>
                
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Tu solicitud de cambios en tu cuenta de {$title} fue aceptado.\n\n" .
                    "DATOS NUEVOS:\n" .
                    "Nombre: {$name}\n" .
                    "CI: {$ci}\n" .
                    "Dirección: {$address}\n" .
                    "Correo: {$email}\n" .
                    "Celular: {$cellphone}\n" .
                    "Fecha de nacimiento: {$birthday}\n\n" .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }
    /**
     * Template para credenciales de login (genérico)
     */
    public static function LoginCredentialsNotification(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $login = htmlspecialchars($data['login'] ?? '', ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($data['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $loginUrl = $data['login_url'] ?? BASE_URL . 'login';
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "Credenciales de Acceso - {$title}";

        // Construir mensaje de contacto
        $contactInfo = '';
        $contactInfoText = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p>Para más información, comunícate al <strong>{$contactPhone}</strong>.</p>";
            $contactInfoText = "Para más información, comunícate al {$contactPhone}.\n\n";
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <!--<h2 style='color: #28a745;'>¡Bienvenido!</h2>-->
                <h2 style='color: #28a745;'>¡Bienvenido {$name}!</h2>
                <p>Se han generado tus credenciales de acceso al sistema de {$title}.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <h3 style='color: #28a745; margin-top: 0;'>Datos de acceso al sistema:</h3>
                    <p><strong>Usuario:</strong> {$login}</p>
                    <p><strong>Contraseña temporal:</strong> {$password}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>IMPORTANTE:</strong> Por tu seguridad, al ingresar por primera vez 
                        al sistema deberás cambiar tu contraseña.
                    </p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$loginUrl}\" 
                       style=\"display:inline-block; padding:15px 30px; background:#28a745; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold; font-size: 16px;\">
                        Acceder al Sistema
                    </a>
                </div>
                
                {$contactInfo}
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Equipo Administrativo de {$title}</strong></p>
            </div>
        ";

        $textBody = //"¡Bienvenido!\n\n" .
                    "¡Bienvenido {$name}!\n\n" .
                    "Se han generado tus credenciales de acceso al sistema de {$title}.\n\n" .
                    "DATOS DE ACCESO:\n" .
                    "Usuario: {$login}\n" .
                    "Contraseña temporal: {$password}\n\n" .
                    "IMPORTANTE: Al ingresar por primera vez deberás cambiar tu contraseña.\n\n" .
                    "Puedes acceder al sistema en: {$loginUrl}\n\n" .
                    $contactInfoText .
                    "Equipo Administrativo de {$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }

    /**
     * Template para email de aprobación de solicitud
     */
    public static function approvalNotification(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $login = htmlspecialchars($data['login'] ?? '', ENT_QUOTES, 'UTF-8');
        $password = htmlspecialchars($data['password'] ?? '', ENT_QUOTES, 'UTF-8');
        $loginUrl = $data['login_url'] ?? BASE_URL . 'login';
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "¡Solicitud Aprobada! - {$title}";

        // Construir mensaje de contacto
        $contactInfo = '';
        $contactInfoText = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p>Si tienes alguna pregunta, comunícate con nosotros al <strong>{$contactPhone}</strong>.</p>";
            $contactInfoText = "Si tienes alguna pregunta, comunícate con nosotros al {$contactPhone}.\n\n";
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Felicidades {$name}!</h2>
                <p>Su solicitud ha sido <strong style='color: #28a745;'>aceptada</strong> como miembro de {$title}.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <h3 style='color: #28a745; margin-top: 0;'>Datos de acceso al sistema:</h3>
                    <p><strong>Usuario:</strong> {$login}</p>
                    <p><strong>Contraseña temporal:</strong> {$password}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>IMPORTANTE:</strong> Por tu seguridad, al ingresar por primera vez 
                        al sistema deberás cambiar tu contraseña.
                    </p>
                </div>
                
                <div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0066cc;'>
                    <p style='margin: 0; color: #004085;'>
                        <strong>RECORDATORIO:</strong> Una vez en el sistema, debe realizar el pago de su cuota mensual.
                    </p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$loginUrl}\" 
                       style=\"display:inline-block; padding:15px 30px; background:#28a745; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold; font-size: 16px;\">
                        Acceder al Sistema
                    </a>
                </div>
                
                {$contactInfo}
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>¡Bienvenido a {$title}!<br>Equipo Administrativo</strong></p>
            </div>
        ";

        $textBody = "¡Felicidades {$name}!\n\n" .
                    "Su solicitud ha sido ACEPTADA como miembro de {$title}.\n\n" .
                    "DATOS DE ACCESO:\n" .
                    "Usuario: {$login}\n" .
                    "Contraseña temporal: {$password}\n\n" .
                    "IMPORTANTE: Al ingresar por primera vez deberás cambiar tu contraseña.\n\n" .
                    "RECORDATORIO: Una vez en el sistema, debe realizar el pago de su cuota mensual.\n\n" .
                    "Puedes acceder al sistema en: {$loginUrl}\n\n" .
                    $contactInfoText .
                    "¡Bienvenido a {$title}!\n" .
                    "Equipo Administrativo";

        return compact('subject', 'htmlBody', 'textBody');
    }
    /**
 * Template para email de desaprobación de solicitud
 */
public static function disapprovalNotification(array $data): array
{
    $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $registerUrl = $data['register_url'] ?? (defined('BASE_URL') ? BASE_URL . 'partner/register' : '#');
    $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
    $contactPhone = self::getContactPhone();

    $subject = "Solicitud de Asociación Rechazada - {$title}";

    // Construir mensaje de contacto
    $contactInfo = '';
    $contactInfoText = '';
    if (!empty($contactPhone)) {
        $contactInfo = "<p>Para más información o aclaraciones, comunícate con nosotros al <strong>{$contactPhone}</strong>.</p>";
        $contactInfoText = "Para más información o aclaraciones, comunícate con nosotros al {$contactPhone}.\n\n";
    }

    $htmlBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #dc3545;'>Estimado/a {$name}</h2>
            <p>Lamentamos informarte que tu solicitud para ser miembro de <strong>{$title}</strong> 
               ha sido <strong style='color: #dc3545;'>rechazada</strong>.</p>
            
            <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                <p style='margin: 0; color: #856404;'>
                    Si consideras que esta decisión fue un error o deseas presentar una nueva solicitud, 
                    no dudes en contactarnos.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <a href=\"{$registerUrl}\" 
                   style=\"display:inline-block; padding:15px 30px; background:#007bff; color:#fff; 
                          text-decoration:none; border-radius:8px; font-weight:bold; font-size: 16px;\">
                    Realizar Nueva Solicitud
                </a>
            </div>
            
            {$contactInfo}
            <hr style='margin: 30px 0; border: 1px solid #eee;'>
            <p><strong>Atentamente,<br>Equipo Administrativo de {$title}</strong></p>
        </div>
    ";

    $textBody = "Estimado/a {$name},\n\n" .
                "Lamentamos informarte que tu solicitud para ser miembro de {$title} ha sido RECHAZADA.\n\n" .
                "Si consideras que esta decisión fue un error o deseas presentar una nueva solicitud, no dudes en contactarnos.\n\n" .
                "Puedes realizar una nueva solicitud en: {$registerUrl}\n\n" .
                $contactInfoText .
                "Atentamente,\n" .
                "Equipo Administrativo de {$title}";

    return compact('subject', 'htmlBody', 'textBody');
}
    /**
     * Template para email de recuperación de contraseña (legacy)
     */
    public static function passwordReset(array $data): array
    {
        $resetLink = $data['reset_link'] ?? '';
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');

        $subject = "Recuperación de Contraseña - {$title}";

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc3545;'>Recuperación de Contraseña</h2>
                <p>Has solicitado restablecer tu contraseña en el Sistema de {$title}.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$resetLink}\" 
                       style=\"display:inline-block; padding:12px 24px; background:#dc3545; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold;\">
                        Restablecer Contraseña
                    </a>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>Este enlace expirará en 24 horas por seguridad.</strong>
                    </p>
                </div>
                
                <p><small>Si no solicitaste este cambio, puedes ignorar este correo.</small></p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "RECUPERACIÓN DE CONTRASEÑA\n\n" .
                    "Has solicitado restablecer tu contraseña en el Sistema de {$title}.\n" .
                    "Visita el siguiente enlace para continuar:\n\n" .
                    "{$resetLink}\n\n" .
                    "Este enlace expirará en 24 horas por seguridad.\n\n" .
                    "Si no solicitaste este cambio, puedes ignorar este correo.\n\n" .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }

    /**
     * Template para email de recuperación de contraseña (nuevo estándar)
     */
    public static function PasswordResetNotification(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $resetLink = $data['reset_link'] ?? '';
        $title = htmlspecialchars(self::getOrganizationTitle(), ENT_QUOTES, 'UTF-8');
        $contactPhone = self::getContactPhone();

        $subject = "Recuperación de Contraseña - {$title}";

        // Construir mensaje de contacto
        $contactInfo = '';
        $contactInfoText = '';
        if (!empty($contactPhone)) {
            $contactInfo = "<p><small>Si tienes problemas, contáctanos al <strong>{$contactPhone}</strong>.</small></p>";
            $contactInfoText = "Si tienes problemas, contáctanos al {$contactPhone}.\n\n";
        }

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc3545;'>Recuperación de Contraseña</h2>
                <!--<h2 style='color: #dc3545;'>Hola {$name}</h2>-->
                <p>Has solicitado restablecer tu contraseña en el Sistema de {$title}.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$resetLink}\" 
                       style=\"display:inline-block; padding:12px 24px; background:#dc3545; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold;\">
                        Restablecer Contraseña
                    </a>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>Este enlace expirará en 24 horas por seguridad.</strong>
                    </p>
                </div>
                
                <p><small>Si no solicitaste este cambio, puedes ignorar este correo.</small></p>
                {$contactInfo}
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>{$title}</strong></p>
            </div>
        ";

        $textBody = "RECUPERACIÓN DE CONTRASEÑA\n\n" .
                    //"Hola {$name},\n\n" .
                    "Has solicitado restablecer tu contraseña en el Sistema de {$title}.\n" .
                    "Visita el siguiente enlace para continuar:\n\n" .
                    "{$resetLink}\n\n" .
                    "Este enlace expirará en 24 horas por seguridad.\n\n" .
                    "Si no solicitaste este cambio, puedes ignorar este correo.\n\n" .
                    $contactInfoText .
                    "Saludos,\n{$title}";

        return compact('subject', 'htmlBody', 'textBody');
    }


}