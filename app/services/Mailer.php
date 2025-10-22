<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    public static function sendReceipt($toEmail, $toName, $subject, $bodyHtml) {
        // Load env
        $envPath = __DIR__ . '/../../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($name, $value) = explode('=', $line, 2);
                putenv(trim($name) . '=' . trim($value));
            }
        }

        $mail = new PHPMailer(true);
        try {
            if (getenv('SMTP_DRIVER') === 'smtp') {
                $mail->isSMTP();
                $mail->Host = getenv('SMTP_HOST');
                $mail->SMTPAuth = true;
                $mail->Username = getenv('SMTP_USER');
                $mail->Password = getenv('SMTP_PASS');
                $mail->SMTPSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = intval(getenv('SMTP_PORT') ?: 587);
            } else {
                $mail->isMail();
            }

            $mail->setFrom(getenv('SMTP_FROM') ?: 'no-reply@example.com', getenv('SMTP_FROM_NAME') ?: 'AgriConnect');
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $bodyHtml;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }
}
