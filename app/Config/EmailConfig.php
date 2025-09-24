<?php
// app/Config/EmailConfig.php

class EmailConfig 
{
    /**
     * Configuración SMTP para PHPMailer
     */
    public const SMTP_CONFIG = [
        'host'         => SMTP_HOST,
        'username'     => SMTP_USER, 
        'password'     => SMTP_PASS,
        'port'         => SMTP_PORT,
        'encryption'   => 'ssl', // 'ssl' para puerto 465, 'tls' para puerto 587
        'timeout'      => 30,
        'charset'      => 'UTF-8'
    ];

    /**
     * Configuración SSL para PHPMailer
     */
    public const SSL_OPTIONS = [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false, 
            'allow_self_signed' => true,
        ]
    ];

    /**
     * Configuración del remitente por defecto
     */
    public const DEFAULT_SENDER = [
        'email' => 'abap@algoritmos.com.bo',
        'name'  => 'Asociación de Artistas'
    ];

    /**
     * Rutas de PHPMailer relativas al directorio actual
     */
    public const PHPMAILER_PATHS = [
        'phpmailer'  => '/../Lib/PHPMailer/PHPMailer.php',
        'exception'  => '/../Lib/PHPMailer/Exception.php', 
        'smtp'       => '/../Lib/PHPMailer/SMTP.php'
    ];

    /**
     * Configuración de fallback para mail()
     */
    public const FALLBACK_HEADERS = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
    ];
}