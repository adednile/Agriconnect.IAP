<?php
require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../helpers/csrf.php';

$controller = new ProfileController();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_check($token)) { echo json_encode(['success'=>false,'error'=>'CSRF']); exit; }
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
        $controller->updateProfile($_POST, $_FILES);
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Invalid request method.";
}
?>
