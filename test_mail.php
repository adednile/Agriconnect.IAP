<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Config\MailService;

$mail = new MailService();

if ($mail->send('elindeda1@gmail.com', 'Test Email', '<h1>This is a test email from AgriMarket</h1>')) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email.";
}
