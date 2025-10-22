<?php
// $orders provided by OrderController::buyerOrders
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Orders - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
  <div class="container">
    <h2>My Orders</h2>
    <?php if (empty($orders)) echo '<p>No orders yet.</p>'; ?>
    <?php foreach($orders as $o): ?>
      <div style="background:white;padding:12px;border-radius:8px;margin-bottom:8px">
        <strong><?php echo htmlspecialchars($o['product_name']); ?></strong>
        <p>Qty: <?php echo (int)$o['quantity']; ?> — Total: Ksh <?php echo number_format($o['total_amount'],2); ?></p>
        <small>Status: <?php echo htmlspecialchars($o['status']); ?> — <?php echo htmlspecialchars($o['created_at']); ?></small>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
