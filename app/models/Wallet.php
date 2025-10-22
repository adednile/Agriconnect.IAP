<?php
namespace App\Models;

use App\Config\Database;
use PDO;

class Wallet {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getBalance($farmer_id) {
        $stmt = $this->conn->prepare("SELECT balance FROM wallets WHERE farmer_id = ?");
        $stmt->execute([$farmer_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['balance'] : 0.0;
    }

    public function credit($farmer_id, $amount, $source = 'mpesa', $reference = null) {
        // ensure wallet exists
        $this->ensureWallet($farmer_id);
        $this->conn->beginTransaction();
        $stmt = $this->conn->prepare("UPDATE wallets SET balance = balance + ? WHERE farmer_id = ?");
        $ok = $stmt->execute([$amount, $farmer_id]);
        $tx = $this->conn->prepare("INSERT INTO wallet_transactions (farmer_id, type, amount, source, reference, created_at) VALUES (?, 'credit', ?, ?, ?, NOW())");
        $txOk = $tx->execute([$farmer_id, $amount, $source, $reference]);
        if ($ok && $txOk) {
            $this->conn->commit();
            return true;
        }
        $this->conn->rollBack();
        return false;
    }

    private function ensureWallet($farmer_id) {
        $stmt = $this->conn->prepare("INSERT IGNORE INTO wallets (farmer_id, balance) VALUES (?, 0.00)");
        return $stmt->execute([$farmer_id]);
    }

    public function getTransactions($farmer_id) {
        $stmt = $this->conn->prepare("SELECT * FROM wallet_transactions WHERE farmer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$farmer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
