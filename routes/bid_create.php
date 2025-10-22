<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/csrf.php';

use App\Models\Bid;

header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid method']);
    exit;
}

$token = $_POST['_csrf'] ?? null;
if (!csrf_check($token)) {
    echo json_encode(['success'=>false,'error'=>'CSRF']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

$product_id = intval($_POST['product_id'] ?? 0);
$amount = floatval($_POST['amount'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if (!$product_id || $amount <= 0) {
    echo json_encode(['success'=>false,'error'=>'Invalid data']);
    exit;
}

$bidModel = new Bid();
$ok = $bidModel->create($product_id, $_SESSION['user_id'], $amount, $quantity);
echo json_encode(['success' => (bool)$ok]);

?>
