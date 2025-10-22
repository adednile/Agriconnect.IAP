<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Sale {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create($product_id, $buyer_id, $farmer_id, $price, $quantity) {
        $total = $price * $quantity;
        $stmt = $this->conn->prepare("INSERT INTO sales (product_id, buyer_id, farmer_id, price, quantity, total_amount, created_at, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'pending')");
        $res = $stmt->execute([$product_id, $buyer_id, $farmer_id, $price, $quantity, $total]);
        if ($res) return $this->conn->lastInsertId();
        return false;
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markPaid($sale_id, $mpesa_ref = null) {
        $stmt = $this->conn->prepare("UPDATE sales SET status = 'paid', mpesa_ref = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$mpesa_ref, $sale_id]);
    }

    public function getByFarmer($farmer_id) {
        $stmt = $this->conn->prepare("SELECT s.*, p.name AS product_name, u.name AS buyer_name FROM sales s JOIN products p ON p.id=s.product_id JOIN users u ON u.id=s.buyer_id WHERE s.farmer_id = ? ORDER BY s.created_at DESC");
        $stmt->execute([$farmer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
