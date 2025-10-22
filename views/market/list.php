<?php
// $products provided by MarketController::listProducts
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Market - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <style>
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
    .card{background:white;border-radius:12px;padding:12px;box-shadow:0 6px 20px rgba(0,0,0,0.06)}
    .price{color:#2e7d32;font-weight:700}
  </style>
</head>
<body>
  <div class="container">
    <h2>Marketplace</h2>
    <div class="grid">
      <?php foreach($products as $p): ?>
        <div class="card">
          <h3><?php echo htmlspecialchars($p['name']); ?></h3>
          <p><?php echo htmlspecialchars(substr($p['description'] ?? '',0,80)); ?></p>
          <p class="price">Ksh <?php echo number_format($p['price'],2); ?></p>
          <p>Seller: <?php echo htmlspecialchars($p['farmer_name']); ?></p>
          <a href="detail.php?id=<?php echo $p['id']; ?>">View</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
