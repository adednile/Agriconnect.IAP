<?php
namespace App\Controllers;

use App\Models\Sale;

class SaleController {
    private $saleModel;

    public function __construct() {
        $this->saleModel = new Sale();
    }

    public function farmerSales() {
        session_start();
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
            header('Location: ../../public/index.php');
            exit;
        }
        $farmerId = $_SESSION['user_id'];
        $sales = $this->saleModel->getByFarmer($farmerId);
        require_once __DIR__ . '/../../views/farmer/sales.php';
    }
}

?>
