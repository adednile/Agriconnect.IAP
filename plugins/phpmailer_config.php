<?php
// plugins/phpmailer_config.php: PHPMailer setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

function send_welcome_email($to, $name) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com'; // Set SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your@email.com'; // SMTP username
        $mail->Password   = 'yourpassword';   // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('no-reply@agriwebapp.com', 'Agri-WebApp');
        $mail->addAddress($to, $name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Karibu Agri-WebApp!';
        $mail->Body    = "<h3>Welcome, $name!</h3><p>Your account is ready. Start using Agri-WebApp today.</p>";
        $mail->AltBody = "Welcome, $name! Your account is ready. Start using Agri-WebApp today.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>