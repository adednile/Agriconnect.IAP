<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Farmer {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllFarmers() {
        $stmt = $this->conn->prepare("SELECT id, name, email, phone, location FROM users WHERE role = 'farmer'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFarmerById($id) {
        $stmt = $this->conn->prepare("SELECT id, name, email, phone, location FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateFarmer($id, $name, $email, $phone, $location) {
        $stmt = $this->conn->prepare("
            UPDATE users SET name=?, email=?, phone=?, location=? WHERE id=?
        ");
        return $stmt->execute([$name, $email, $phone, $location, $id]);
    }
}
