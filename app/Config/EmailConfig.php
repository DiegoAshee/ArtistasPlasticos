<?php
// app/Config/EmailConfig.php
declare(strict_types=1);

class EmailConfig
{
    // Configuración SMTP
    public const SMTP_CONFIG = [
        'host'       => 'mail.algoritmos.com.bo',
        'username'   => 'abap@algoritmos.com.bo',
        'password'   => 'Pl4st1c0s2025*',
        'port'       => 465,
        'encryption' => 'ssl', // o 'tls'
        'charset'    => 'UTF-8',
        'timeout'    => 30
    ];

    // Remitente por defecto
    public const DEFAULT_SENDER = [
        'email' => 'abap@algoritmos.com.bo',
        'name'  => 'Asociación Boliviana de Artistas Plásticos'
    ];

    // Rutas de PHPMailer (ajusta según tu estructura actual)
    public const PHPMAILER_PATHS = [
        'phpmailer' => '/../Lib/PHPMailer/PHPMailer.php',
        'exception' => '/../Lib/PHPMailer/Exception.php',
        'smtp'      => '/../Lib/PHPMailer/SMTP.php'
    ];

    // Headers para fallback con mail()
    public const FALLBACK_HEADERS = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8'
    ];

    // Opciones SSL
    public const SSL_OPTIONS = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ];
    
    /**
     * Obtiene las rutas absolutas de PHPMailer
     */
    public static function getPHPMailerPaths(): array
    {
        $paths = [];
        foreach (self::PHPMAILER_PATHS as $key => $relativePath) {
            $paths[$key] = __DIR__ . $relativePath;
        }
        return $paths;
    }

    /**
     * Verifica si PHPMailer está disponible
     */
    public static function isPHPMailerAvailable(): bool
    {
        $paths = self::getPHPMailerPaths();
        
        return file_exists($paths['phpmailer']) && 
               file_exists($paths['exception']) && 
               file_exists($paths['smtp']);
    }

    /**
     * Obtiene la configuración SMTP con valores dinámicos si están definidos
     */
    public static function getSmtpConfig(): array
    {
        return [
            'host'       => defined('SMTP_HOST') ? SMTP_HOST : self::SMTP_CONFIG['host'],
            'username'   => defined('SMTP_USER') ? SMTP_USER : self::SMTP_CONFIG['username'],
            'password'   => defined('SMTP_PASS') ? SMTP_PASS : self::SMTP_CONFIG['password'],
            'port'       => defined('SMTP_PORT') ? SMTP_PORT : self::SMTP_CONFIG['port'],
            'encryption' => self::SMTP_CONFIG['encryption'],
            'charset'    => self::SMTP_CONFIG['charset'],
            'timeout'    => self::SMTP_CONFIG['timeout']
        ];
    }
}