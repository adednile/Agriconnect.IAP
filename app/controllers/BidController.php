<?php
namespace App\Controllers;

use App\Models\Bid;
use App\Models\Sale;
use App\Models\Wallet;
use PDO;

class BidController {
    private $bidModel;
    private $saleModel;
    private $walletModel;

    public function __construct() {
        $this->bidModel = new Bid();
        $this->saleModel = new Sale();
        $this->walletModel = new Wallet();
    }

    // Render farmer bids page
    public function farmerBids() {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
            header('Location: ../../public/index.php');
            exit;
        }

        $farmerId = $_SESSION['user_id'];
        $bids = $this->bidModel->getByFarmer($farmerId);
        require_once __DIR__ . '/../../views/farmer/bids.php';
    }

    // Approve a bid (farmer only)
    public function approve($id) {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $bid = $this->bidModel->getById($id);
        if (!$bid) {
            http_response_code(404);
            echo json_encode(['error' => 'Bid not found']);
            exit;
        }

        // Ensure the bid belongs to this farmer
        $db = new \App\Config\Database();
        $conn = $db->connect();
        $pstmt = $conn->prepare("SELECT farmer_id FROM products WHERE id = ?");
        $pstmt->execute([$bid['product_id']]);
        $prod = $pstmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod || $prod['farmer_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }

        // Update bid status
        $res = $this->bidModel->updateStatus($id, 'approved');
        if ($res) {
            // create a sale record based on bid details
            $bid = $this->bidModel->getById($id);
            if ($bid) {
                // fields: product_id, buyer_id, amount, quantity
                $saleId = $this->saleModel->create($bid['product_id'], $bid['buyer_id'], /*farmer_id*/ null, $bid['amount'], $bid['quantity']);
                // Attempt to set farmer_id by querying the product owner
                // Since Sale::create requires farmer_id, we will fetch product->farmer_id here
                $db = new \App\Config\Database();
                $conn = $db->connect();
                $pstmt = $conn->prepare("SELECT farmer_id FROM products WHERE id = ?");
                $pstmt->execute([$bid['product_id']]);
                $p = $pstmt->fetch(PDO::FETCH_ASSOC);
                if ($p && $saleId) {
                    // update sale's farmer_id
                    $up = $conn->prepare("UPDATE sales SET farmer_id = ? WHERE id = ?");
                    $up->execute([$p['farmer_id'], $saleId]);
                }
            }
        }
        echo json_encode(['success' => (bool)$res]);
    }

    // Reject a bid
    public function reject($id) {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

            $bid = $this->bidModel->getById($id);
        if (!$bid) {
            http_response_code(404);
            echo json_encode(['error' => 'Bid not found']);
            exit;
        }
            // ensure ownership
            $db = new \App\Config\Database();
            $conn = $db->connect();
            $pstmt = $conn->prepare("SELECT farmer_id FROM products WHERE id = ?");
            $pstmt->execute([$bid['product_id']]);
            $prod = $pstmt->fetch(PDO::FETCH_ASSOC);
            if (!$prod || $prod['farmer_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                exit;
            }

            $res = $this->bidModel->updateStatus($id, 'rejected');
        echo json_encode(['success' => (bool)$res]);
    }
}

?>
