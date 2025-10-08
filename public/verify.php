<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Config\Database;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT * FROM users WHERE verify_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?");
        $update->execute([$user['id']]);
        echo "<h3>Email verified successfully! You can now <a href='login.php'>login</a>.</h3>";
    } else {
        echo "<h3>Invalid or expired token.</h3>";
    }
} else {
    echo "<h3>Missing verification token.</h3>";
}
?>
