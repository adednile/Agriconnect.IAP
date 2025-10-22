<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
    header("Location: login.php");
    exit;
}

$controller = new \App\Controllers\FarmerController();
$controller->dashboard();
