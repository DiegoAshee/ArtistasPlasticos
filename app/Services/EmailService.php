<?php
// app/Services/EmailService.php
declare(strict_types=1);

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
            // Verificar si PHPMailer está disponible
            if (!EmailConfig::isPHPMailerAvailable()) {
                $this->addDebug("❌ PHPMailer no está disponible");
                return false;
            }

            // Obtener rutas
            $paths = EmailConfig::getPHPMailerPaths();

            // Cargar PHPMailer
            require_once $paths['phpmailer'];
            require_once $paths['exception']; 
            require_once $paths['smtp'];

            $this->addDebug("✅ PHPMailer cargado correctamente");

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Obtener configuración SMTP
            $smtpConfig = EmailConfig::getSmtpConfig();

            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host       = $smtpConfig['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpConfig['username'];
            $mail->Password   = $smtpConfig['password'];
            $mail->SMTPSecure = $smtpConfig['encryption'] === 'ssl' 
                              ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS 
                              : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpConfig['port'];
            $mail->CharSet    = $smtpConfig['charset'];
            $mail->Timeout    = $smtpConfig['timeout'];
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