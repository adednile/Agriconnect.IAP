<?php
// buyers.php: Buyer profile & bids CRUD
require_once 'config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: index.php?show=login');
    exit();
}
$buyer_id = $_SESSION['user_id'];
// Fetch buyer profile
$stmt = $pdo->prepare('SELECT * FROM buyers WHERE buyer_id = ?');
$stmt->execute([$buyer_id]);
$buyer = $stmt->fetch();
// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $business_type = trim($_POST['business_type']);
    $stmt = $pdo->prepare('UPDATE buyers SET name=?, business_type=? WHERE buyer_id=?');
    $stmt->execute([$name, $business_type, $buyer_id]);
    header('Location: buyers.php?updated=1');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h2>My Profile</h2>
    <form method="post" class="mb-4">
        <div class="mb-2">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($buyer['name']); ?>" required>
        </div>
        <div class="mb-2">
            <label>Business Type</label>
            <input type="text" name="business_type" class="form-control" value="<?php echo htmlspecialchars($buyer['business_type']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Update Profile</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</div>
</body>
</html>