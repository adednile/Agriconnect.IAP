<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$name = htmlspecialchars($_SESSION['name']);
$role = htmlspecialchars($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AgriMarket</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">
    <h2>Welcome, <?= $name; ?> 👋</h2>
    <h3>Role: <?= ucfirst($role); ?></h3>

    <?php if ($role === 'farmer'): ?>
        <p>You can post products, view bids, and check weather advisories.</p>
        <a href="farmer_products.php" class="btn">Manage My Products</a>
        <a href="bids.php" class="btn">View Bids</a>

    <?php elseif ($role === 'buyer'): ?>
        <p>Browse available farm products and place bids.</p>
        <a href="market.php" class="btn">View Market</a>

    <?php elseif ($role === 'driver'): ?>
        <p>View delivery requests and confirm deliveries.</p>
        <a href="deliveries.php" class="btn">My Deliveries</a>

    <?php elseif ($role === 'agronomist'): ?>
        <p>Share advisories and view farmers’ activity.</p>
        <a href="advisories.php" class="btn">Post Advisory</a>

    <?php elseif ($role === 'admin'): ?>
        <p>Manage users and oversee system activities.</p>
        <a href="admin_panel.php" class="btn">Admin Dashboard</a>

    <?php else: ?>
        <p>Unknown role. Contact support.</p>
    <?php endif; ?>

    <br><br>
    <a href="logout.php" class="btn">Logout</a>
</div>
</body>
</html>
