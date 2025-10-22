<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Order {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create($product_id, $buyer_id, $farmer_id, $driver_id, $price, $quantity, $total, $status = 'pending') {
        $stmt = $this->conn->prepare("INSERT INTO orders (product_id, buyer_id, farmer_id, driver_id, price, quantity, total_amount, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $res = $stmt->execute([$product_id, $buyer_id, $farmer_id, $driver_id, $price, $quantity, $total, $status]);
        if ($res) return $this->conn->lastInsertId();
        return false;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT o.*, p.name AS product_name, u.name AS buyer_name, d.name AS driver_name FROM orders o LEFT JOIN products p ON p.id=o.product_id LEFT JOIN users u ON u.id=o.buyer_id LEFT JOIN drivers d ON d.id=o.driver_id WHERE o.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByBuyer($buyer_id) {
        $stmt = $this->conn->prepare("SELECT o.*, p.name AS product_name FROM orders o JOIN products p ON p.id=o.product_id WHERE o.buyer_id = ? ORDER BY o.created_at DESC");
        $stmt->execute([$buyer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}

?>
