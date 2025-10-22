<?php
// ✅ Correct autoload path (2 levels up)
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AuthController;

session_start();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $auth = new AuthController();
    $user = $auth->login($email, $password);

    if ($user === 'not_verified') {
        $msg = "⚠️ Your account is not verified. Check your email for the verification link.";
    } elseif ($user === 'invalid') {
        $msg = "❌ Invalid email or password.";
    } elseif (is_array($user)) {
        // ✅ Login successful, store session
        // Set consistent session values for the app
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role']; // legacy compatibility
    $_SESSION['is_admin'] = ($user['role'] === 'admin');

        // Redirect based on role
        switch ($user['role']) {
            case 'farmer':
                header('Location: ../../views/farmer/dashboard.php');
                break;
            case 'buyer':
                header('Location: ../../views/buyer/dashboard.php');
                break;
            case 'driver':
                header('Location: ../../views/driver/dashboard.php');
                break;
            case 'agronomist':
                header('Location: ../../views/agronomist/dashboard.php');
                break;
            case 'admin':
                header('Location: ../../views/admin/dashboard.php');
                break;
            default:
                header('Location: ../../index.php');
        }
        exit;
    } else {
        $msg = "⚠️ Unexpected error occurred. Try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - AgriConnect</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container">
    <h2>Login to Your Account</h2>

    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <p>Don’t have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
