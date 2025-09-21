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
    $stmt = $mysqli->prepare("SELECT * FROM $table WHERE phone = ?");
    $stmt->bind_param('s', $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()) {
        header('Location: index.php?show=register&error=exists');
        exit();
    }

    // Insert user
    if ($role === 'farmer') {
        $stmt = $mysqli->prepare("INSERT INTO farmers (name, phone, county, language, password) VALUES (?, ?, '', 'en', ?)");
        $stmt->bind_param('sss', $name, $phone, $hashed);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO buyers (name, phone, business_type, password) VALUES (?, ?, '', ?)");
        $stmt->bind_param('sss', $name, $phone, $hashed);
        $stmt->execute();
    }
    // Send welcome email to the registered user
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