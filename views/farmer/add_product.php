<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'farmer') {
    header("Location: ../../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
use App\Controllers\FarmerController;

$farmerCtrl = new FarmerController();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $quantity = trim($_POST['quantity']);

    if ($farmerCtrl->addProduct($_SESSION['user']['id'], $name, $price, $quantity)) {
        $msg = "✅ Product added successfully!";
    } else {
        $msg = "❌ Failed to add product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
<div class="container">
    <h2>Add a New Product</h2>
    <?php if ($msg) echo "<p>$msg</p>"; ?>
    <form method="POST">
        <input type="text" name="name" placeholder="Product Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price (Ksh)" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <button type="submit">Save</button>
    </form>
    <a href="dashboard.php">⬅ Back to Dashboard</a>
</div>
</body>
</html>
