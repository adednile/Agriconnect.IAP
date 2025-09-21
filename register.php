<?php
// register.php: Handles user registration

require_once 'config.php';
require_once __DIR__ . '/plugins/phpmailer_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check for duplicate phone
    $table = $role === 'farmer' ? 'farmers' : 'buyers';
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        header('Location: index.php?show=register&error=exists');
        exit();
    }

    // Insert user
    if ($role === 'farmer') {
        $stmt = $pdo->prepare("INSERT INTO farmers (name, phone, county, language, password) VALUES (?, ?, '', 'en', ?)");
        $stmt->execute([$name, $phone, $hashed]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO buyers (name, phone, business_type, password) VALUES (?, ?, '', ?)");
        $stmt->execute([$name, $phone, $hashed]);
    }
    // Send welcome email (optional: add email field to registration form and DB)
    // For demo, use phone as email if valid, or set a default
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        send_welcome_email($email, $name);
    }
    header('Location: index.php?show=login&success=registered');
    exit();
} else {
    header('Location: index.php?show=register');
    exit();
}
?>