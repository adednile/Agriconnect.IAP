<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Sale;
use App\Models\Wallet;
use App\Services\Mailer;
use App\Config\Database;
use PDO;

header('Content-Type: application/json');
$body = file_get_contents('php://input');
$data = json_decode($body, true) ?: $_POST;

$saleId = isset($data['sale_id']) ? intval($data['sale_id']) : null;
$amount = isset($data['amount']) ? floatval($data['amount']) : null;
$mpesaRef = $data['mpesa_ref'] ?? null;

if (!$saleId || !$amount) {
    echo json_encode(['success' => false, 'error' => 'Missing sale_id or amount']);
    exit;
}

$saleModel = new Sale();
$sale = $saleModel->getById($saleId);
if (!$sale) {
    echo json_encode(['success' => false, 'error' => 'Sale not found']);
    exit;
}

// mark sale paid
$ok = $saleModel->markPaid($saleId, $mpesaRef);
if ($ok) {
    // credit wallet
    $wallet = new Wallet();
    $creditOk = $wallet->credit($sale['farmer_id'], $amount, 'mpesa', $mpesaRef);
    // ensure we have a payment reference
    if (empty($mpesaRef)) $mpesaRef = 'SIM-' . rand(100000, 999999);

    // send receipts to buyer and farmer (direct PDO lookup)
    $db = new Database(); $conn = $db->connect();
    $uStmt = $conn->prepare('SELECT id,name,email FROM users WHERE id = ?');
    $uStmt->execute([$sale['buyer_id']]);
    $buyer = $uStmt->fetch(PDO::FETCH_ASSOC);
    $uStmt->execute([$sale['farmer_id']]);
    $farmer = $uStmt->fetch(PDO::FETCH_ASSOC);
    $subject = 'Payment received - Sale #' . $saleId;
    $bodyHtml = "<p>Payment of <strong>KES " . number_format($amount,2) . "</strong> received for product ID <strong>" . htmlspecialchars($sale['product_id']) . "</strong>.</p><p>Sale ID: {$saleId}<br>MPesa Ref: {$mpesaRef}</p>";
    if ($buyer && !empty($buyer['email'])) Mailer::sendReceipt($buyer['email'], $buyer['name'] ?? '', 'Order payment confirmation', $bodyHtml);
    if ($farmer && !empty($farmer['email'])) Mailer::sendReceipt($farmer['email'], $farmer['name'] ?? '', $subject, $bodyHtml);
    error_log('Mpesa callback: sale ' . $saleId . ' credited ' . ($creditOk ? 'ok' : 'failed') . ' ref=' . $mpesaRef);
    echo json_encode(['success' => (bool)$creditOk, 'sale_id' => $saleId, 'mpesa_ref' => $mpesaRef]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to mark sale paid']);
}

?>
