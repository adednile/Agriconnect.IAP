<?php
if (session_status() === PHP_SESSION_NONE) session_start();
use App\Config\Database;

// ✅ Ensure farmer is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../vendor/autoload.php';
$db = new Database();
$conn = $db->connect();

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmerId = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    // Handle image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . '/../../public/uploads/';
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $imagePath = 'public/uploads/' . $imageName;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (farmer_id, name, description, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$farmerId, $name, $description, $price, $quantity, $imagePath]);

    $msg = "✅ Product added successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post New Product - AgriConnect</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body {
            background-color: #f4f6f8;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 30px;
            margin: 40px auto;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2e7d32;
            text-align: center;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input[type="file"] {
            padding: 6px;
        }

        button {
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #1b5e20;
        }

        .msg {
            text-align: center;
            font-weight: bold;
        }

        a.back {
            display: inline-block;
            text-decoration: none;
            color: #2e7d32;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back">← Back to Dashboard</a>
        <h2>🛒 Post New Product</h2>

        <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="name" placeholder="Enter product name" required>

            <label>Description</label>
            <textarea name="description" rows="4" placeholder="Describe your product (optional)"></textarea>

            <label>Price (Ksh)</label>
            <input type="number" name="price" min="1" step="0.01" required>

            <label>Quantity</label>
            <input type="number" name="quantity" min="1" value="1" required>

            <label>Upload Image</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
