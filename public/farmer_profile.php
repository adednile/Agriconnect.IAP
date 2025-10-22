<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\FarmerController;

if (!isset($_GET['id'])) {
    die("Farmer ID is required.");
}

$controller = new FarmerController();
$controller->profile($_GET['id']);
