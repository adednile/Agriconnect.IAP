<?php
require_once __DIR__.'/../../vendor/autoload.php';
use App\Controllers\AuthController;

$msg = "";

if($_SERVER['REQUEST_METHOD']==='POST'){
    $auth = new AuthController();
    $res = $auth->resendOTP($_POST['email']);
    $msg = $res['msg'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resend OTP</title>
</head>
<body>
<h2>Resend OTP</h2>
<?php if($msg) echo "<p>$msg</p>"; ?>

<form method="POST">
    <input type="email" name="email" placeholder="Registered Email" required>
    <button type="submit">Resend OTP</button>
</form>
</body>
</html>
