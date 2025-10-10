<?php
require_once __DIR__.'/../../vendor/autoload.php';
use App\Controllers\AuthController;

$msg = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    if($auth->verifyOTP($email,$otp)) {
        $msg = "✅ Account verified successfully! You can now login.";
    } else {
        $msg = "❌ Invalid OTP or expired. Try resending OTP.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Account</title>
</head>
<body>
<h2>Verify Your Account</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Registered Email" required>
    <input type="text" name="otp" placeholder="6-digit OTP" required>
    <button type="submit">Verify</button>
</form>

<p>Didn't get OTP? <a href="resend.php">Resend OTP</a></p>
</body>
</html>
