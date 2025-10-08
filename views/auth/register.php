<?php
$role = $_POST['role'];

require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;
use App\Config\MailerService;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $conn = $db->connect();

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(16));

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role, verify_token, is_verified)
                        VALUES (?, ?, ?, ?, ?, 0)");
$stmt->execute([$name, $email, $password, $role, $token]);


    $mail = new MailerService();
    $verifyLink = $_ENV['APP_URL'] . "verify.php?token=" . $token;

    $body = "
        <h2>Welcome to AgriMarket!</h2>
        <p>Click the link below to verify your email:</p>
        <a href='$verifyLink'>$verifyLink</a>
    ";

    if ($mail->send($email, "Verify Your AgriMarket Account", $body)) {
        $msg = "Registration successful! Please check your email to verify your account.";
    } else {
        $msg = "Error sending verification email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - AgriMarket</title>
    <link rel="stylesheet" href="css/style.css">
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
    </select>

    <button type="submit">Register</button>
</form>

    <p>Already have an account? <a href="login.php">Login</a></p>
</div>
</body>
</html>
