<?php
if (session_status() === PHP_SESSION_NONE) session_start();

use App\Config\Database;

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$db = new Database();
$conn = $db->connect();

$farmerId = $_SESSION['user_id'];
$msg = "";

// 🧩 Handle product deletion
if (isset($_GET['delete'])) {
    $productId = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? AND farmer_id = ?");
    $stmt->execute([$productId, $farmerId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Delete image file if exists
        if (!empty($product['image']) && file_exists(__DIR__ . '/../../' . $product['image'])) {
            unlink(__DIR__ . '/../../' . $product['image']);
        }

        $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ? AND farmer_id = ?");
        $deleteStmt->execute([$productId, $farmerId]);
        $msg = "🗑️ Product deleted successfully.";
    } else {
        $msg = "⚠️ Product not found or unauthorized.";
    }
}

// 🧩 Fetch all farmer products
$stmt = $conn->prepare("SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
$stmt->execute([$farmerId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products - AgriConnect</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2e7d32;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        tr:hover {
            background-color: #f1f8e9;
        }

        img {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
        }

        .btn {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn-edit {
            background-color: #1976d2;
            color: white;
        }

        .btn-delete {
            background-color: #c62828;
            color: white;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .msg {
            text-align: center;
            font-weight: bold;
            color: #333;
        }

        a.back {
            color: #2e7d32;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <a href="post_product.php" class="btn" style="background:#2e7d32;color:white;">+ Add New Product</a>
    </div>

    <h2>🌾 My Products</h2>

    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <?php if (count($products) > 0): ?>
        <table>
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Description</th>
                <th>Price (Ksh)</th>
                <th>Qty</th>
                <th>Actions</th>
            </tr>

            <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="../../<?php echo htmlspecialchars($p['image']); ?>" alt="Product">
                        <?php else: ?>
                            <img src="../../public/images/no-image.png" alt="No Image">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($p['description'], 0, 50)); ?>...</td>
                    <td><?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo $p['quantity']; ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-edit">Edit</a>
                        <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No products yet. <a href="post_product.php">Add one</a>.</p>
    <?php endif; ?>
</div>
</body>
</html>
