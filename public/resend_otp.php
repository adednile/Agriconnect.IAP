<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $auth = new AuthController();
    $message = $auth->resendOTP($email);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resend OTP</title>
</head>
<body>
    <h2>Resend OTP</h2>
    <form method="POST">
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Resend OTP</button>
    </form>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
</body>
</html>
