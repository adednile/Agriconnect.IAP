<?php
namespace App\Controllers;

use App\Models\Farmer;
use App\Controllers\AuthController;

class FarmerController {
    private $farmerModel;

    public function __construct() {
        $this->farmerModel = new Farmer();
    }

    public function updateFarmer($data) {
        $id = $data['id'];
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $phone = $data['phone'] ?? '';
        $location = $data['location'] ?? '';

        $result = $this->farmerModel->updateFarmer($id, $name, $email, $phone, $location);

        // Redirect back to public profile page with status
        if ($result) {
            header('Location: ../../public/farmer_profile.php?id=' . urlencode($id) . '&updated=1');
            exit;
        } else {
            header('Location: ../../public/farmer_profile.php?id=' . urlencode($id) . '&error=1');
            exit;
        }
    }

    // Render profile edit form for a farmer
    public function profile($id) {
        $farmer = $this->farmerModel->getFarmerById($id);
        if (!$farmer) {
            die('Farmer not found');
        }
        // Make $farmer available to the view
        require_once __DIR__ . '/../../views/farmer/profile.php';
    }

    // Minimal wrapper to expose register/login/dashboard for routes
    public function register() {
        // Expect POST data
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = 'farmer';
            $auth = new AuthController();
            $res = $auth->register($name, $email, $password, $role);
            echo json_encode($res);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $auth = new AuthController();
            $res = $auth->login($email, $password);
            echo json_encode($res);
        }
    }

    public function dashboard() {
        // Prepare data for the dashboard view
        // If admin, show all farmers; otherwise show only the logged-in farmer
        $farmers = [];
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
            $farmers = $this->farmerModel->getAllFarmers();
        } else {
            if (isset($_SESSION['user_id'])) {
                $farmer = $this->farmerModel->getFarmerById($_SESSION['user_id']);
                if ($farmer) {
                    $farmers = [$farmer];
                }
            }
        }

        // Basic dashboard view (can be expanded)
        require_once __DIR__ . '/../../views/farmer/dashboard.php';
    }
}
?>
