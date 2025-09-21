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
        $mail->Host       = 'smtp.gmail.com'; // Gmail SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'elindeda1@gmail.com'; // Gmail address
        $mail->Password   = '';   // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('elindeda1@gmail.com', 'Agri-WebApp');
        $mail->addAddress($to, $name);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Karibu Agri-WebApp!';
        $mail->Body    = "<h3>Welcome, $name!</h3><p>Your account is ready. Start using Agri-WebApp today.</p>";
        $mail->AltBody = "Welcome, $name! Your account is ready. Start using Agri-WebApp today.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
        return false;
    }
}
?>