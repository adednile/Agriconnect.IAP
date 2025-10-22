<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/csrf.php';
header('Content-Type: application/json');
session_start();
use App\Services\MpesaService;
use App\Models\Sale;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Invalid method']);
    exit;
}

$orderId = $_POST['order_id'] ?? null;
$saleId = $_POST['sale_id'] ?? null;
if (!$orderId) {
    echo json_encode(['success'=>false,'error'=>'Missing order_id']);
    exit;
}

// Call MpesaService which will either run real STK (if configured) or return a simulated response
$phone = $_POST['phone'] ?? null;
$amount = $_POST['amount'] ?? null;
if ($saleId) {
    $sModel = new Sale();
    $sale = $sModel->getById($saleId);
    if ($sale) {
        $amount = $amount ?: $sale['total_amount'];
    }
}
// build callback URL from env if available
$appUrl = getenv('APP_URL') ?: null;
$callback = $appUrl ? rtrim($appUrl, '/') . '/routes/mpesa_callback.php' : null;

if (empty($phone) || empty($amount)) {
    echo json_encode(['success' => false, 'error' => 'Missing phone or amount']);
    exit;
}

$res = MpesaService::initiateStkPush($phone, $amount, $orderId, $callback, $saleId);
echo json_encode($res);

?>
