<?php
namespace App\Controllers;

use App\Models\Order;
use App\Models\Driver;
use App\Models\Sale;

class OrderController {
    private $orderModel;
    private $driverModel;
    private $saleModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->driverModel = new Driver();
        $this->saleModel = new Sale();
    }

    public function buyerOrders() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../../public/index.php');
            exit;
        }
        $buyerId = $_SESSION['user_id'];
        $orders = $this->orderModel->getByBuyer($buyerId);
        require_once __DIR__ . '/../../views/buyer/orders.php';
    }

    // Create order (buyer sends product_id, quantity, driver_id)
    public function createOrder($data) {
        session_start();
        if (!isset($_SESSION['user_id'])) return ['error' => 'Unauthorized'];
        $buyerId = $_SESSION['user_id'];
        $productId = intval($data['product_id']);
        $quantity = intval($data['quantity']);
        $driverId = !empty($data['driver_id']) ? intval($data['driver_id']) : null;

        // fetch product price and farmer
        $db = new \App\Config\Database();
        $conn = $db->connect();
        $pstmt = $conn->prepare("SELECT price, farmer_id, quantity FROM products WHERE id = ?");
        $pstmt->execute([$productId]);
        $p = $pstmt->fetch(\PDO::FETCH_ASSOC);
        if (!$p) return ['error' => 'Product not found'];
        if ($quantity > $p['quantity']) return ['error' => 'Insufficient stock'];

        $price = $p['price'];
        $farmerId = $p['farmer_id'];
        $total = $price * $quantity;

        $orderId = $this->orderModel->create($productId, $buyerId, $farmerId, $driverId, $price, $quantity, $total);
        if ($orderId) {
            // reduce product quantity
            $up = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $up->execute([$quantity, $productId]);
            // create a sale record tied to this order (pending)
            $saleId = $this->saleModel->create($productId, $buyerId, $farmerId, $price, $quantity);
            return ['success' => true, 'order_id' => $orderId, 'sale_id' => $saleId];
        }
        return ['error' => 'Failed to create order'];
    }

    // MPesa checkout initiation skeleton
    public function initiateCheckout($orderId) {
        // This is a placeholder skeleton; real implementation requires Safaricom credentials
        // For local testing, return a dummy token and redirect URL or instruct to call mpesa_callback.php
        return ['checkout' => 'simulated', 'order_id' => $orderId];
    }
}
