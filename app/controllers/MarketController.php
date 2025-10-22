<?php
namespace App\Controllers;

use App\Config\Database;
use PDO;

class MarketController {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function listProducts() {
        $stmt = $this->conn->prepare("SELECT p.*, u.name AS farmer_name FROM products p JOIN users u ON u.id=p.farmer_id WHERE p.quantity > 0 ORDER BY p.created_at DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../../views/market/list.php';
    }

    public function productDetail($id) {
        $stmt = $this->conn->prepare("SELECT p.*, u.name AS farmer_name FROM products p JOIN users u ON u.id=p.farmer_id WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../../views/market/detail.php';
    }
}
