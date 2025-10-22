<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Driver {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create($name, $phone, $vehicle) {
        $stmt = $this->conn->prepare("INSERT INTO drivers (name, phone, vehicle, created_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$name, $phone, $vehicle]);
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM drivers ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
