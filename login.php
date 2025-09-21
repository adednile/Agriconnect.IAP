<?php
// login.php: Handles user login
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    // Try both tables
    $user = null;
    $role = null;
    foreach ([['farmers', 'farmer_id'], ['buyers', 'buyer_id']] as $info) {
        $table = $info[0];
        $id_col = $info[1];
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE phone = ?");
        $stmt->execute([$phone]);
        $row = $stmt->fetch();
        if ($row && isset($row['password']) && password_verify($password, $row['password'])) {
            $user = $row;
            $role = $table === 'farmers' ? 'farmer' : 'buyer';
            break;
        }
    }
    if ($user) {
        $_SESSION['user_id'] = $user[$role === 'farmer' ? 'farmer_id' : 'buyer_id'];
        $_SESSION['role'] = $role;
        $_SESSION['name'] = $user['name'];
        header('Location: dashboard.php');
        exit();
    } else {
        header('Location: index.php?show=login&error=invalid');
        exit();
    }
} else {
    header('Location: index.php?show=login');
    exit();
}
?>