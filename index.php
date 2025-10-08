<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AgriMarket - Welcome</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to AgriMarket 🌾</h1>
        <p>A digital marketplace connecting farmers, buyers, drivers, and agronomists.</p>
        <a href="register.php" class="btn">Register</a>
        <a href="login.php" class="btn">Login</a>
    </div>
</body>
</html>
