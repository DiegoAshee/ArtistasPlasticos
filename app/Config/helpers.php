<?php
// app/Config/helpers.php

if (!function_exists('u')) {
    /**
     * Construye URL absoluta a partir de BASE_URL.
     * Ej: u('images/carnets/foto.jpg')
     * => https://algoritmos.com.bo/pasantes/jcrojas/images/carnets/foto.jpg
     */
    function u(string $path = ''): string {
        $base = rtrim(defined('BASE_URL') ? BASE_URL : '/', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return u($path);
    }
}

if (!function_exists('p')) {
    /**
     * Construye ruta física en disco a partir de BASE_PATH.
     * Ej: p('images/carnets')
     * => /home/USUARIO/public_html/pasantes/jcrojas/images/carnets
     */
    function p(string $path = ''): string {
        $base = rtrim(defined('BASE_PATH') ? BASE_PATH : __DIR__, DIRECTORY_SEPARATOR);
        $normalized = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, ltrim($path, '/'));
        return $base . DIRECTORY_SEPARATOR . $normalized;
    }
}

if (!function_exists('sendEmail')) {
    /**
     * Helper function para enviar emails usando el servicio centralizado
     * 
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $htmlBody Contenido HTML
     * @param string $textBody Contenido texto plano (opcional)
     * @param array $options Opciones adicionales
     * @return bool
     */
    function sendEmail(
        string $to, 
        string $subject, 
        string $htmlBody, 
        string $textBody = '', 
        array $options = []
    ): bool {
        require_once __DIR__ . '/../Services/EmailService.php';
        return EmailService::getInstance()->sendEmail($to, $subject, $htmlBody, $textBody, $options);
    }
}

if (!function_exists('sendVerificationEmail')) {
    /**
     * Envía email de verificación de cuenta
     */
    function sendVerificationEmail(string $email, string $token, array $data): bool
    {
        require_once __DIR__ . '/../Services/EmailTemplates.php';
        
        $verificationLink = u('partner/verify?token=' . urlencode($token));
        $templateData = array_merge($data, ['verification_link' => $verificationLink]);
        $template = EmailTemplates::emailVerification($templateData);
        
        return sendEmail(
            $email, 
            $template['subject'], 
            $template['htmlBody'], 
            $template['textBody'],
            ['recipient_name' => $data['name'] ?? '']
        );
    }
}

if (!function_exists('sendRegistrationConfirmationEmail')) {
    /**
     * Envía email de confirmación de registro
     */
    function sendRegistrationConfirmationEmail(string $email, array $data): bool
    {
        require_once __DIR__ . '/../Services/EmailTemplates.php';
        
        $template = EmailTemplates::registrationConfirmation($data);
        
        return sendEmail(
            $email, 
            $template['subject'], 
            $template['htmlBody'], 
            $template['textBody'],
            ['recipient_name' => $data['name'] ?? '']
        );
    }
}

if (!function_exists('sendApprovalEmail')) {
    /**
     * Envía email de aprobación de solicitud
     */
    function sendApprovalEmail(string $email, array $data): bool
    {
        require_once __DIR__ . '/../Services/EmailTemplates.php';
        
        $template = EmailTemplates::approvalNotification($data);
        
        return sendEmail(
            $email, 
            $template['subject'], 
            $template['htmlBody'], 
            $template['textBody'],
            ['recipient_name' => $data['name'] ?? '']
        );
    }
}

if (!function_exists('sendPasswordResetEmail')) {
    /**
     * Envía email de recuperación de contraseña
     */
    function sendPasswordResetEmail(string $email, string $resetLink): bool
    {
        require_once __DIR__ . '/../Services/EmailTemplates.php';
        
        $template = EmailTemplates::passwordReset(['reset_link' => $resetLink]);
        
        return sendEmail(
            $email, 
            $template['subject'], 
            $template['htmlBody'], 
            $template['textBody']
        );
    }
}

if (!function_exists('sendFirstLoginEmail')) {
    /**
     * Envía email de primer login
     */
    function sendFirstLoginEmail(string $email, string $username): bool
    {
        require_once __DIR__ . '/../Services/EmailTemplates.php';
        
        $template = EmailTemplates::firstLogin(['username' => $username]);
        
        return sendEmail(
            $email, 
            $template['subject'], 
            $template['htmlBody'], 
            $template['textBody']
        );
    }
}