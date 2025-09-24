<?php
// app/Services/EmailTemplates.php
declare(strict_types=1);
require_once __DIR__ . '/../Models/Option.php';

class EmailTemplates 
{
    private $options;

    public function __construct()
    {
        $optionModel = new Option();
        $this->options = $optionModel->getAll();
    }
    /**
     * Template para email de verificación
     */
    public static function emailVerification(array $data): array
    {
        $name = htmlspecialchars($data['name'] ?? '', ENT_QUOTES, 'UTF-8');
        $verificationLink = $data['verification_link'] ?? '';
        $title = htmlspecialchars($options['title'] ?? '', ENT_QUOTES, 'UTF-8');

        $subject = "Verifica tu correo - {$title}";

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #333;'>¡Hola {$name}!</h2>
                <p>Gracias por registrarte en la {$title}.</p>
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
                <p><strong>Saludos,<br>Equipo de Registro</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Por favor, verifica tu correo haciendo clic en el siguiente enlace:\n" .
                    "{$verificationLink}\n\n" .
                    "Este enlace expirará en 24 horas.\n\n" .
                    "Si no solicitaste este registro, ignora este correo.\n\n" .
                    "Saludos,\nEquipo de Registro";

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
        $cellphone = htmlspecialchars($data['cellphoneNumber'] ?? '', ENT_QUOTES, 'UTF-8');
        $birthday = htmlspecialchars($data['birthday'] ?? '', ENT_QUOTES, 'UTF-8');

        $title = htmlspecialchars($options['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $subject = "Confirmación de Registro - {$title}";

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Hola {$name}!</h2>
                <p>Tu registro fue recibido exitosamente en la {$title}.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                    <h3 style='margin-top: 0; color: #333;'>Datos registrados:</h3>
                    <p><strong>CI:</strong> {$ci}</p>
                    <p><strong>Correo:</strong> {$email}</p>
                    <p><strong>Celular:</strong> {$cellphone}</p>
                    <p><strong>Fecha de nacimiento:</strong> {$birthday}</p>
                </div>
                
                <p>Nos pondremos en contacto contigo muy pronto.</p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>Equipo de Registro</strong></p>
            </div>
        ";

        $textBody = "Hola {$name},\n\n" .
                    "Tu registro fue recibido exitosamente.\n\n" .
                    "DATOS REGISTRADOS:\n" .
                    "CI: {$ci}\n" .
                    "Correo: {$email}\n" .
                    "Celular: {$cellphone}\n" .
                    "Fecha de nacimiento: {$birthday}\n\n" .
                    "Nos pondremos en contacto contigo muy pronto.\n\n" .
                    "Saludos,\nEquipo de Registro";

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
        $loginUrl = $data['login_url'] ?? u('login');

        $subject = '¡Solicitud Aprobada! - Asociación de Artistas';

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #28a745;'>¡Felicidades {$name}!</h2>
                <p>Tu solicitud de registro ha sido <strong style='color: #28a745;'>aprobada</strong> 
                   y ya eres socio de la Asociación de Artistas.</p>
                
                <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;'>
                    <h3 style='color: #28a745; margin-top: 0;'>📧 Datos de acceso al sistema:</h3>
                    <p><strong>Usuario:</strong> {$login}</p>
                    <p><strong>Contraseña temporal:</strong> {$password}</p>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>⚠️ IMPORTANTE:</strong> Por tu seguridad, al ingresar por primera vez 
                        al sistema deberás cambiar tu contraseña.
                    </p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$loginUrl}\" 
                       style=\"display:inline-block; padding:15px 30px; background:#28a745; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold; font-size: 16px;\">
                        🔐 Acceder al Sistema
                    </a>
                </div>
                
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>¡Bienvenido a la Asociación de Artistas!<br>Equipo Administrativo</strong></p>
            </div>
        ";

        $textBody = "¡Felicidades {$name}!\n\n" .
                    "Tu solicitud de registro ha sido APROBADA y ya eres socio de la Asociación de Artistas.\n\n" .
                    "DATOS DE ACCESO:\n" .
                    "Usuario: {$login}\n" .
                    "Contraseña temporal: {$password}\n\n" .
                    "IMPORTANTE: Al ingresar por primera vez deberás cambiar tu contraseña.\n\n" .
                    "Puedes acceder al sistema en: {$loginUrl}\n\n" .
                    "Si tienes alguna pregunta, no dudes en contactarnos.\n\n" .
                    "¡Bienvenido a la Asociación de Artistas!\n" .
                    "Equipo Administrativo";

        return compact('subject', 'htmlBody', 'textBody');
    }

    /**
     * Template para email de recuperación de contraseña
     */
    public static function passwordReset(array $data): array
    {
        $resetLink = $data['reset_link'] ?? '';

        $subject = 'Recuperación de Contraseña - Asociación de Artistas';

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #dc3545;'>🔐 Recuperación de Contraseña</h2>
                <p>Has solicitado restablecer tu contraseña en el Sistema de la Asociación de Artistas.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href=\"{$resetLink}\" 
                       style=\"display:inline-block; padding:12px 24px; background:#dc3545; color:#fff; 
                              text-decoration:none; border-radius:8px; font-weight:bold;\">
                        Restablecer Contraseña
                    </a>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <p style='margin: 0; color: #856404;'>
                        <strong>⏰ Este enlace expirará en 24 horas por seguridad.</strong>
                    </p>
                </div>
                
                <p><small>Si no solicitaste este cambio, puedes ignorar este correo.</small></p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>Equipo de Asociación de Artistas</strong></p>
            </div>
        ";

        $textBody = "RECUPERACIÓN DE CONTRASEÑA\n\n" .
                    "Has solicitado restablecer tu contraseña en el Sistema de la Asociación de Artistas.\n" .
                    "Visita el siguiente enlace para continuar:\n\n" .
                    "{$resetLink}\n\n" .
                    "Este enlace expirará en 24 horas por seguridad.\n\n" .
                    "Si no solicitaste este cambio, puedes ignorar este correo.\n\n" .
                    "Saludos,\nEquipo de Asociación de Artistas";

        return compact('subject', 'htmlBody', 'textBody');
    }

    /**
     * Template para email de primer login
     */
    public static function firstLogin(array $data): array
    {
        $username = htmlspecialchars($data['username'] ?? '', ENT_QUOTES, 'UTF-8');

        $subject = 'Primer inicio de sesión detectado - Asociación de Artistas';

        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #17a2b8;'>👋 ¡Hola {$username}!</h2>
                <p>Hemos detectado tu primer inicio de sesión en el Sistema de la Asociación de Artistas.</p>
                
                <div style='background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #17a2b8;'>
                    <p style='margin: 0; color: #0c5460;'>
                        <strong>🔒 Por seguridad, se te solicitará cambiar la contraseña.</strong>
                    </p>
                </div>
                
                <p>Si no fuiste tú, contacta inmediatamente al administrador.</p>
                <hr style='margin: 30px 0; border: 1px solid #eee;'>
                <p><strong>Saludos,<br>Sistema de Asociación de Artistas</strong></p>
            </div>
        ";

        $textBody = "Hola {$username},\n\n" .
                    "Hemos detectado tu primer inicio de sesión en el Sistema de la Asociación de Artistas.\n" .
                    "Por seguridad, se te solicitará cambiar la contraseña.\n\n" .
                    "Si no fuiste tú, contacta al administrador.\n\n" .
                    "Saludos,\nSistema de Asociación de Artistas";

        return compact('subject', 'htmlBody', 'textBody');
    }
}