<?php
require_once __DIR__ . '/../app/Controllers/FarmerController.php';
require_once __DIR__ . '/../helpers/csrf.php';

use App\Controllers\FarmerController;

$controller = new FarmerController();
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_check($token)) { echo json_encode(['success'=>false,'error'=>'CSRF']); exit; }
    if (empty($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Unauthorized']); exit; }
    // ensure the session user is the owner or an admin
    $farmerId = $_POST['id'] ?? null;
    if ($farmerId && $_SESSION['user_id'] != $farmerId && empty($_SESSION['is_admin'])) { echo json_encode(['success'=>false,'error'=>'Forbidden']); exit; }
    $controller->updateFarmer($_POST);
} else {
    echo "Invalid request method.";
}
?>
