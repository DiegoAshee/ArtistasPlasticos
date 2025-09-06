<?php
// app/Controllers/OnlinePartnerController.php
declare(strict_types=1);

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Config/config.php';

class OnlinePartnerController extends BaseController
{
     // ====== REGISTRO PÚBLICO DE SOCIO ======
    // app/Models/PartnerOnline.php

    public function registerPartner(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $recaptchaResponse = $_POST['g-recaptcha-response'] ?? '';
            $secretKey = '6Lf4Pb0rAAAAANUMOu-OhH74s6C54v769nxBPHez'; // reemplaza con tu clave secreta
            $verify = file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .
                '&response=' . urlencode($recaptchaResponse)
            );
            $resp = json_decode($verify);

            if (!$resp->success) {
                $this->view('partner/register', ['error' => 'Captcha inválido o no verificado. Por favor, marca "No soy un robot".']);
                return;
            }
            $name            = trim((string)($_POST['name'] ?? ''));
            $ci              = trim((string)($_POST['ci'] ?? ''));
            $cellPhoneNumber = trim((string)($_POST['cellPhoneNumber'] ?? ''));
            $address         = trim((string)($_POST['address'] ?? ''));
            $birthday        = trim((string)($_POST['birthday'] ?? ''));
            $email           = trim((string)($_POST['email'] ?? ''));
            $ciPhoto           = trim((string)($_POST['ciPhoto'] ?? ''));
            // 1) Campos obligatorios
            if ($name === '' || $ci === '' || $cellPhoneNumber === '' || $address === '' || $birthday === '' || $email === '') {
                $error = "Todos los campos son obligatorios.";
            }
            // 2) Email válido
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "El correo no tiene un formato válido.";
            }
            // 3) Edad 18–120
            else {
                try {
                    $dob   = new \DateTime($birthday);
                    $today = new \DateTime('today');
                    $age   = $dob->diff($today)->y;

                    if ($age < 18) {
                        $error = "Debes ser mayor de edad (18+).";
                    } elseif ($age > 120) {
                        $error = "La fecha de nacimiento es inválida (edad > 120).";
                    }
                } catch (\Exception $e) {
                    $error = "Fecha de nacimiento inválida.";
                }
            }

            // 4) Reglas simples opcionales (puedes ajustar)
            /* if (!isset($error)) {
                if (!preg_match('/^[0-9A-Za-z-\.]{4,20}$/', $ci)) {
                    $error = "CI inválido.";
                } elseif (!preg_match('/^[0-9\s+\-]{6,20}$/', $cellPhoneNumber)) {
                    $error = "Celular inválido.";
                }
            } */
        // Manejo de archivo
            $ciPhotoPath = null;
            if (isset($_FILES['ciPhoto']) && $_FILES['ciPhoto']['error'] === UPLOAD_ERR_OK) {
                $fileTmp  = $_FILES['ciPhoto']['tmp_name'];
                $fileName = basename($_FILES['ciPhoto']['name']);
                $fileSize = $_FILES['ciPhoto']['size'];
                $fileType = mime_content_type($fileTmp);

                // Validaciones: tamaño y tipo
                if ($fileSize > 2 * 1024 * 1024) { // 2 MB
                    $error = "El archivo excede los 2MB.";
                } elseif (!in_array($fileType, ['image/jpeg', 'image/png'])) {
                    $error = "Solo se permiten imágenes JPEG o PNG.";
                } else {
                    // Crear carpeta si no existe
                    /* $uploadDir = __DIR__ . "/../uploads/ci/";
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    // Generar nombre único
                    $newName = uniqid('ci_') . "." . pathinfo($fileName, PATHINFO_EXTENSION);
                    $destPath = $uploadDir . $newName;

                    if (move_uploaded_file($fileTmp, $destPath)) {
                        // Ruta relativa para guardar en BD
                        $ciPhotoPath = "uploads/ci/" . $newName;
                    } else {
                        $error = "Error al guardar la foto.";
                    } */
                }
            }

            // 5) Duplicados en todas las tablas
            if (!isset($error)) {
                require_once __DIR__ . '/../Models/PartnerOnline.php';
                $partnerOnlineModel = new \PartnerOnline();

                if ($partnerOnlineModel->emailExistsAnywhere($email)) {
                    $error = "Este correo ya está registrado.";
                } elseif ($partnerOnlineModel->ciExistsAnywhere($ci)) {
                    $error = "Este CI ya está registrado.";
                } else {
                    // Crear registro (el modelo fija NOW() para dateCreation/dateRegistration)
                    $ok = $partnerOnlineModel->create($name, $ci, $cellPhoneNumber, $address, $birthday, $email);
                    //$ok = $partnerOnlineModel->create($name, $ci, $cellPhoneNumber, $address, $birthday, $email, $ciPhotoPath);

                    if ($ok) {
                        $this->sendPartnerRegistrationEmail($email, [
                            'name' => $name,
                            'ci' => $ci,
                            'email' => $email,
                            'cellphoneNumber' => $cellPhoneNumber,
                            'birthday' => $birthday
                        ]);
                        $this->redirect('partner/register?success=1'); // <- aquí rediriges
                        return;
                    }
                    $error = "Error al enviar la solicitud.";
                }
            }
        } elseif (isset($_GET['success'])) {
            $success = "Solicitud enviada con éxito. Será revisada por un administrador.";
            $this->view('partner/register', ['success' => $success]);
            return;
        }

        $this->view('partner/register', isset($error) ? ['error' => $error] : []);
    }

    private function sendPartnerRegistrationEmail(string $email, array $data): bool
    {
        try {
            // Validar email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            // Rutas de PHPMailer (ya configuradas en tu proyecto)
            $phpmailerPath = __DIR__ . '/../Lib/PHPMailer/PHPMailer.php';
            $exceptionPath = __DIR__ . '/../Lib/PHPMailer/Exception.php';
            $smtpPath      = __DIR__ . '/../Lib/PHPMailer/SMTP.php';

            if (is_file($phpmailerPath) && is_file($exceptionPath) && is_file($smtpPath)) {
                require_once $phpmailerPath;
                require_once $exceptionPath;
                require_once $smtpPath;

                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

                // CONFIG SMTP
                $mail->isSMTP();
                $mail->Host       = 'mail.algoritmos.com.bo';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'abap@algoritmos.com.bo';
                $mail->Password   = 'Pl4st1c0s2025*';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                        'allow_self_signed' => true,
                    ]
                ];

                // Configuración general
                $mail->CharSet = 'UTF-8';

                // Remitente y destinatario
                $mail->setFrom('abap@algoritmos.com.bo', 'Asociación de Artistas');
                $mail->addAddress($email, $data['name']);
                $mail->addReplyTo('abap@algoritmos.com.bo', 'No Responder');

                // Contenido HTML
                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de Registro - Asociación de Artistas';
                $mail->Body    = "
                    <h2>¡Hola {$data['name']}!</h2>
                    <p>Tu registro fue recibido exitosamente en la Asociación de Artistas.</p>
                    <p><b>CI:</b> {$data['ci']}<br>
                    <b>Correo:</b> {$data['email']}<br>
                    <b>Celular:</b> {$data['cellphoneNumber']}<br>
                    <b>Fecha de nacimiento:</b> {$data['birthday']}</p>
                    <p>Nos pondremos en contacto contigo muy pronto.</p>
                    <br>
                    <p>Saludos,<br><b>Equipo de Registro</b></p>
                ";

                return $mail->send();
            }

            // Fallback: mail()
            $subject = 'Confirmación de Registro - Asociación de Artistas';
            $message = "Hola {$data['name']},\n\n".
                    "Tu registro fue recibido exitosamente.\n\n".
                    "CI: {$data['ci']}\n".
                    "Correo: {$data['email']}\n".
                    "Celular: {$data['cellphoneNumber']}\n".
                    "Fecha de nacimiento: {$data['birthday']}\n\n".
                    "Saludos,\nEquipo de Registro";

            $headers = [
                'From: Asociación de Artistas <no-reply@localhost>',
                'Reply-To: no-reply@localhost',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8'
            ];

            return mail($email, $subject, $message, implode("\r\n", $headers));

        } catch (\Throwable $e) {
            error_log("Error en envío de confirmación: " . $e->getMessage());
            return false;
        }
    }
}