<?php
// ============================================================
// CLASE MAILER
// Maneja el envío de correos usando PHPMailer + Gmail SMTP
// ============================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/core/mailer/Exception.php';
require_once ROOT_PATH . '/core/mailer/PHPMailer.php';
require_once ROOT_PATH . '/core/mailer/SMTP.php';

class Mailer
{
    private PHPMailer $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Configuración SMTP Gmail
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = MAIL_FROM;
        $this->mail->Password   = MAIL_PASSWORD;
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->SMTPOptions = [
    'ssl' => [
        'verify_peer'       => false,
        'verify_peer_name'  => false,
        'allow_self_signed' => true,
    ]
];
        $this->mail->Port       = 587;
        $this->mail->CharSet    = 'UTF-8';

        // Remitente por defecto
        $this->mail->setFrom(MAIL_FROM, MAIL_NAME);
    }

    // Envía un correo
    // Uso: $mailer->send('destino@email.com', 'Nombre', 'Asunto', '<h1>Cuerpo</h1>')
    public function send(string $toEmail, string $toName, string $subject, string $body): bool
    {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($toEmail, $toName);
            $this->mail->Subject = $subject;
            $this->mail->isHTML(true);
            $this->mail->Body    = $body;
            $this->mail->AltBody = strip_tags($body); // Versión texto plano
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            // Guarda el error en log
            $logFile = LOGS_PATH . '/mail_errors.log';
            $msg = date('Y-m-d H:i:s') . ' | Error al enviar a ' . $toEmail . ': ' . $e->getMessage() . PHP_EOL;
            file_put_contents($logFile, $msg, FILE_APPEND);
            return false;
        }
    }

    // ── PLANTILLAS DE CORREO ─────────────────────────────────

    // Correo de bienvenida con credenciales de acceso
    public function sendWelcome(string $toEmail, string $toName, string $password): bool
    {
        $subject = '¡Bienvenido a ' . APP_NAME . '! Tus credenciales de acceso';

        $body = $this->template('Bienvenido a ' . APP_NAME, "
            <p>Hola <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Tu cuenta ha sido creada exitosamente. Aquí están tus credenciales de acceso:</p>

            <div style='background:#f4f6f9;border-radius:8px;padding:20px;margin:20px 0;'>
                <p style='margin:0 0 8px;'><strong>Usuario (email):</strong></p>
                <p style='margin:0 0 16px;font-size:1.1em;color:#2d3a5e;'>" . htmlspecialchars($toEmail) . "</p>
                <p style='margin:0 0 8px;'><strong>Contraseña temporal:</strong></p>
                <p style='margin:0;font-size:1.3em;font-weight:bold;color:#2d3a5e;letter-spacing:2px;'>" . htmlspecialchars($password) . "</p>
            </div>

            <p>Por seguridad, te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez.</p>

            <div style='text-align:center;margin:30px 0;'>
                <a href='" . url('auth/login') . "'
                   style='background:linear-gradient(135deg,#1a2035,#2d3a5e);color:white;padding:12px 32px;
                          border-radius:8px;text-decoration:none;font-weight:bold;'>
                    Iniciar Sesión
                </a>
            </div>

            <p style='color:#888;font-size:0.85em;'>
                Si no solicitaste esta cuenta, ignora este correo.
            </p>
        ");

        return $this->send($toEmail, $toName, $subject, $body);
    }

    // Correo de restablecimiento de contraseña
    public function sendPasswordReset(string $toEmail, string $toName, string $newPassword): bool
    {
        $subject = APP_NAME . ' — Tu nueva contraseña';

        $body = $this->template('Nueva Contraseña', "
            <p>Hola <strong>" . htmlspecialchars($toName) . "</strong>,</p>
            <p>Tu contraseña ha sido restablecida. Aquí está tu nueva contraseña temporal:</p>

            <div style='background:#f4f6f9;border-radius:8px;padding:20px;margin:20px 0;text-align:center;'>
                <p style='margin:0 0 8px;color:#666;'>Nueva contraseña:</p>
                <p style='margin:0;font-size:1.5em;font-weight:bold;color:#2d3a5e;letter-spacing:3px;'>
                    " . htmlspecialchars($newPassword) . "
                </p>
            </div>

            <div style='text-align:center;margin:30px 0;'>
                <a href='" . url('auth/login') . "'
                   style='background:linear-gradient(135deg,#1a2035,#2d3a5e);color:white;padding:12px 32px;
                          border-radius:8px;text-decoration:none;font-weight:bold;'>
                    Iniciar Sesión
                </a>
            </div>
        ");

        return $this->send($toEmail, $toName, $subject, $body);
    }

    // ── PLANTILLA BASE HTML ──────────────────────────────────
    private function template(string $title, string $content): string
    {
        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width,initial-scale=1'>
            <title>" . htmlspecialchars($title) . "</title>
        </head>
        <body style='margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;'>
                <tr>
                    <td align='center'>
                        <table width='560' cellpadding='0' cellspacing='0'
                               style='background:#fff;border-radius:12px;overflow:hidden;
                                      box-shadow:0 4px 20px rgba(0,0,0,0.08);'>

                            <!-- Header -->
                            <tr>
                                <td style='background:linear-gradient(135deg,#1a2035,#2d3a5e);
                                           padding:32px;text-align:center;'>
                                    <h1 style='color:#fff;margin:0;font-size:1.4em;'>" . APP_NAME . "</h1>
                                    <p style='color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.85em;'>
                                        " . htmlspecialchars($title) . "
                                    </p>
                                </td>
                            </tr>

                            <!-- Contenido -->
                            <tr>
                                <td style='padding:32px;color:#333;line-height:1.6;'>
                                    {$content}
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style='background:#f8f9fa;padding:20px 32px;
                                           text-align:center;border-top:1px solid #e2e8f0;'>
                                    <p style='margin:0;color:#888;font-size:0.8em;'>
                                        © " . date('Y') . " " . APP_NAME . " — Este correo fue generado automáticamente.
                                    </p>
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }
}
