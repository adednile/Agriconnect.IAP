<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\FarmerController;

$action = $_GET['action'] ?? null;
$controller = new FarmerController();

switch ($action) {
    case 'register_farmer':
        $controller->register();
        break;
    case 'login_farmer':
        $controller->login();
        break;
    case 'dashboard_farmer':
        $controller->dashboard();
        break;
    default:
        require_once __DIR__ . '/../views/home.php';
        break;
}
