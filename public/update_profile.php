<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\FarmerController;

$controller = new FarmerController();
// Expect POST data for updating
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$controller->updateFarmer($_POST);
}
