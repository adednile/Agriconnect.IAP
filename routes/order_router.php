<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/csrf.php';
use App\Controllers\OrderController;

$controller = new OrderController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_check($token)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'CSRF']);
        exit;
    }

    $res = $controller->createOrder($_POST);
    header('Content-Type: application/json');
        if (!empty($res['success']) && !empty($res['order_id'])) {
        // call simulated STK initiate for dev
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, dirname($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . '/stk_initiate.php');
        curl_setopt($ch, CURLOPT_POST, true);
            // include sale_id when available so the simulator and callback can reference it
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['order_id' => $res['order_id'], 'sale_id' => $res['sale_id'] ?? null]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $stkResp = curl_exec($ch);
        curl_close($ch);
        $res['stk'] = $stkResp ? json_decode($stkResp, true) : null;
    }
    echo json_encode($res);
    exit;
}

?>
