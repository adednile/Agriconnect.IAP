<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Database;
use App\Config\MailService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'] ?? null;
    $token = bin2hex(random_bytes(16));
    $otp = rand(100000, 999999);
    $otpExpires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Check for duplicate email
    $stmt = $conn->prepare("SELECT id, is_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $msg = $existing['is_verified']
            ? "❌ This email is already verified. Please login."
            : "⚠️ Email already registered but not verified. Check your email or resend OTP.";
    } else {
        // Insert new user
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role, verify_token, otp_code, otp_expires_at, is_verified)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([$name, $email, $password, $role, $token, $otp, $otpExpires]);

        // Send verification email
        $mail = new MailService();
        $verifyLink = $_ENV['APP_URL'] . "views/auth/verify.php?token=" . $token;
        $body = "
            <h2>Welcome to AgriMarket, $name!</h2>
            <p>Click below to verify your email:</p>
            <a href='$verifyLink'>$verifyLink</a>
            <hr>
            <p>Or use this OTP:</p>
            <h2>$otp</h2>
            <small>Expires in 10 minutes</small>
        ";

        $msg = $mail->send($email, "Verify Your AgriMarket Account", $body)
            ? "✅ Registration successful! Check your email for the verification link and OTP."
            : "❌ Registration successful, but failed to send verification email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AgriMarket</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container">
    <h2>Create an Account</h2>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="farmer">Farmer</option>
            <option value="buyer">Buyer</option>
            <option value="driver">Driver</option>
            <option value="agronomist">Agronomist</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
