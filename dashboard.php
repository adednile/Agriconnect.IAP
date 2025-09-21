<?php
// dashboard.php: Role-based dashboard
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?show=login');
    exit();
}

$role = $_SESSION['role'];
$name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Agri-WebApp</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Agri-WebApp</a>
        <div class="d-flex">
            <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($name); ?> (<?php echo $role; ?>)</span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>
<div class="container mt-4">
    <?php if ($role === 'farmer'): ?>
        <h2>Farmer Dashboard</h2>
        <ul>
            <li><a href="farmers.php">My Profile & Listings</a></li>
            <li><a href="listings.php">Create/View Listings</a></li>
            <li><a href="bids.php">View Bids</a></li>
        </ul>
    <?php elseif ($role === 'buyer'): ?>
        <h2>Buyer Dashboard</h2>
        <ul>
            <li><a href="buyers.php">My Profile & Bids</a></li>
            <li><a href="listings.php">Browse Listings</a></li>
        </ul>
    <?php else: ?>
        <h2>Admin Dashboard</h2>
        <ul>
            <li><a href="advisories.php">Manage Advisories</a></li>
            <li><a href="alerts.php">Manage Alerts</a></li>
        </ul>
    <?php endif; ?>
</div>
</body>
</html>