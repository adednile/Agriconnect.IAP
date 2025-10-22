<?php
namespace App\Controllers;

use App\Models\Wallet;

class WalletController {
    private $walletModel;

    public function __construct() {
        $this->walletModel = new Wallet();
    }

    public function farmerWallet() {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
            header('Location: ../../public/index.php');
            exit;
        }

        $farmerId = $_SESSION['user_id'];
        $balance = $this->walletModel->getBalance($farmerId);
        $transactions = $this->walletModel->getTransactions($farmerId);
        require_once __DIR__ . '/../../views/farmer/wallet.php';
    }
}

?>
