<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'farmer') {
    die("Access denied!");
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$db = new Database();
$conn = $db->connect();

$msg = "";

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $farmer_id = $_SESSION['user_id'];

    // --- Handle image upload ---
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $image = $targetFilePath;
            } else {
                $msg = "Error uploading image file.";
            }
        } else {
            $msg = "Invalid image format. Only JPG, PNG, GIF allowed.";
        }
    }

    if (empty($msg)) {
        $stmt = $conn->prepare("INSERT INTO products (farmer_id, title, description, price, quantity, image)
                                VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$farmer_id, $title, $description, $price, $quantity, $image]);
        $msg = "✅ Product added successfully!";
    }
}

// --- Fetch all products for this farmer ---
$stmt = $conn->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - AgriMarket</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Manage My Products 🌾</h2>
    <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

    <?php if ($msg): ?>
        <p class="msg"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="product-form">
        <input type="text" name="title" placeholder="Product Title" required>
        <textarea name="description" placeholder="Product Description" rows="4"></textarea>
        <input type="number" name="price" placeholder="Price (Ksh)" step="0.01" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit">Add Product</button>
    </form>

    <h3>My Uploaded Products</h3>
    <div class="product-list">
        <?php if (count($products) > 0): ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php if ($p['image']): ?>
                        <img src="<?= htmlspecialchars($p['image']) ?>" alt="Product Image">
                    <?php endif; ?>
                    <h4><?= htmlspecialchars($p['title']) ?></h4>
                    <p><?= htmlspecialchars($p['description']) ?></p>
                    <p><strong>Ksh <?= htmlspecialchars($p['price']) ?></strong> | Qty: <?= htmlspecialchars($p['quantity']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products uploaded yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
