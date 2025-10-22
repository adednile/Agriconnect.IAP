<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
    header('Location: ../../public/index.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sales - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <style>
    .container{max-width:1000px;margin:40px auto;background:white;padding:20px;border-radius:12px}
    h2{color:#2e7d32}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee}
    th{background:#e8f5e9;color:#1b5e20}
  </style>
</head>
<body>
  <div class="container">
    <a href="dashboard.php">← Back</a>
    <h2>📦 Sales</h2>
    <?php if (empty($sales)): ?>
      <p>No sales yet.</p>
    <?php else: ?>
      <table>
        <tr><th>Product</th><th>Buyer</th><th>Qty</th><th>Price</th><th>Total</th><th>Status</th><th>Date</th></tr>
        <?php foreach($sales as $s): ?>
          <tr>
            <td><?php echo htmlspecialchars($s['product_name']); ?></td>
            <td><?php echo htmlspecialchars($s['buyer_name']); ?></td>
            <td><?php echo (int)$s['quantity']; ?></td>
            <td><?php echo number_format($s['price'],2); ?></td>
            <td><?php echo number_format($s['total_amount'],2); ?></td>
            <td><?php echo htmlspecialchars($s['status']); ?></td>
            <td><?php echo htmlspecialchars($s['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
