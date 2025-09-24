<?php
// app/Services/EmailService.php

require_once __DIR__ . '/../Config/EmailConfig.php';

class EmailService 
{
    private static $instance = null;
    private $debugMessages = [];

    /**
     * Singleton pattern
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Envía un email usando PHPMailer o mail() como fallback
     * 
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $htmlBody Contenido HTML
     * @param string $textBody Contenido texto plano (opcional)
     * @param array $options Opciones adicionales
     * @return bool
     */
    public function sendEmail(
        string $to, 
        string $subject, 
        string $htmlBody, 
        string $textBody = '', 
        array $options = []
    ): bool {
        try {
            $this->addDebug("=== INICIO EmailService::sendEmail ===");
            $this->addDebug("Destinatario: " . $to);
            $this->addDebug("Asunto: " . $subject);

            // Validar email
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                $this->addDebug("❌ Email inválido: " . $to);
                return false;
            }

            // Intentar envío con PHPMailer
            if ($this->sendWithPHPMailer($to, $subject, $htmlBody, $textBody, $options)) {
                return true;
            }

            // Fallback a mail()
            $this->addDebug("🔄 Fallback a función mail()");
            return $this->sendWithMailFunction($to, $subject, $textBody ?: strip_tags($htmlBody), $options);

        } catch (\Throwable $e) {
            $this->addDebug("❌ Error general: " . $e->getMessage());
            return false;
        } finally {
            $this->addDebug("=== FIN EmailService::sendEmail ===");
        }
    }

    /**
     * Envía email usando PHPMailer
     */
    private function sendWithPHPMailer(
        string $to, 
        string $subject, 
        string $htmlBody, 
        string $textBody, 
        array $options
    ): bool {
        try {
            // Verificar archivos PHPMailer
            $phpmailerPath = __DIR__ . EmailConfig::PHPMAILER_PATHS['phpmailer'];
            $exceptionPath = __DIR__ . EmailConfig::PHPMAILER_PATHS['exception'];
            $smtpPath = __DIR__ . EmailConfig::PHPMAILER_PATHS['smtp'];

            if (!is_file($phpmailerPath) || !is_file($exceptionPath) || !is_file($smtpPath)) {
                $this->addDebug("❌ Archivos PHPMailer no encontrados");
                return false;
            }

            // Cargar PHPMailer
            require_once $phpmailerPath;
            require_once $exceptionPath; 
            require_once $smtpPath;

            $this->addDebug("✅ PHPMailer cargado correctamente");

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host       = EmailConfig::SMTP_CONFIG['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = EmailConfig::SMTP_CONFIG['username'];
            $mail->Password   = EmailConfig::SMTP_CONFIG['password'];
            $mail->SMTPSecure = EmailConfig::SMTP_CONFIG['encryption'] === 'ssl' 
                              ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS 
                              : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = EmailConfig::SMTP_CONFIG['port'];
            $mail->CharSet    = EmailConfig::SMTP_CONFIG['charset'];
            $mail->Timeout    = EmailConfig::SMTP_CONFIG['timeout'];
            $mail->SMTPDebug  = 0;

            // Configurar SSL
            $mail->SMTPOptions = EmailConfig::SSL_OPTIONS;

            // Configurar remitente
            $senderEmail = $options['sender_email'] ?? EmailConfig::DEFAULT_SENDER['email'];
            $senderName = $options['sender_name'] ?? EmailConfig::DEFAULT_SENDER['name'];
            $mail->setFrom($senderEmail, $senderName);
            $mail->addReplyTo($senderEmail, $senderName);

            // Configurar destinatario
            $recipientName = $options['recipient_name'] ?? '';
            $mail->addAddress($to, $recipientName);

            // Configurar contenido
            $mail->isHTML(!empty($htmlBody));
            $mail->Subject = $subject;
            
            if (!empty($htmlBody)) {
                $mail->Body = $htmlBody;
                $mail->AltBody = $textBody ?: strip_tags($htmlBody);
            } else {
                $mail->Body = $textBody;
            }

            // Enviar
            $result = $mail->send();
            $this->addDebug("PHPMailer resultado: " . ($result ? "ÉXITO" : "FALLO"));
            
            if (!$result) {
                $this->addDebug("❌ Error PHPMailer: " . $mail->ErrorInfo);
            }

            return $result;

        } catch (\Exception $e) {
            $this->addDebug("❌ Excepción PHPMailer: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Envía email usando función mail() como fallback
     */
    private function sendWithMailFunction(
        string $to, 
        string $subject, 
        string $message, 
        array $options
    ): bool {
        try {
            $senderEmail = $options['sender_email'] ?? EmailConfig::DEFAULT_SENDER['email'];
            $senderName = $options['sender_name'] ?? EmailConfig::DEFAULT_SENDER['name'];

            $headers = EmailConfig::FALLBACK_HEADERS;
            $headers[] = "From: {$senderName} <{$senderEmail}>";
            $headers[] = "Reply-To: {$senderEmail}";
            $headers[] = 'X-Mailer: PHP/' . phpversion();

            $result = mail($to, $subject, $message, implode("\r\n", $headers));
            $this->addDebug("mail() resultado: " . ($result ? "TRUE" : "FALSE"));

            return (bool)$result;

        } catch (\Throwable $e) {
            $this->addDebug("❌ Error mail(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Agregar mensaje de debug
     */
    private function addDebug(string $message): void
    {
        $this->debugMessages[] = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        error_log($message);
    }

    /**
     * Obtener mensajes de debug
     */
    public function getDebugMessages(): array
    {
        return $this->debugMessages;
    }

    /**
     * Limpiar mensajes de debug
     */
    public function clearDebugMessages(): void
    {
        $this->debugMessages = [];
    }
}