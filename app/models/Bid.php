<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Bid {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create($product_id, $buyer_id, $amount, $quantity) {
        $stmt = $this->conn->prepare("INSERT INTO bids (product_id, buyer_id, amount, quantity, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        return $stmt->execute([$product_id, $buyer_id, $amount, $quantity]);
    }

    public function getByFarmer($farmer_id) {
        $stmt = $this->conn->prepare(
            "SELECT b.*, p.name AS product_name, u.name AS buyer_name, u.email AS buyer_email
             FROM bids b
             JOIN products p ON p.id = b.product_id
             JOIN users u ON u.id = b.buyer_id
             WHERE p.farmer_id = ?
             ORDER BY b.created_at DESC"
        );
        $stmt->execute([$farmer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM bids WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->conn->prepare("UPDATE bids SET status = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}

?>
