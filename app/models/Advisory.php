<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Advisory {
    private $conn;
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function create($author_id, $title, $body, $audience = 'farmers') {
        $stmt = $this->conn->prepare("INSERT INTO advisories (author_id, title, body, audience, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$author_id, $title, $body, $audience]);
    }

    public function getForFarmers() {
        $stmt = $this->conn->prepare("SELECT a.*, u.name as author_name FROM advisories a JOIN users u ON u.id=a.author_id WHERE a.audience IN ('all','farmers') ORDER BY a.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
