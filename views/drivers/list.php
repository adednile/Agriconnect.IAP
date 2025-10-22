<?php
// $drivers provided by DriverController::listDrivers
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Drivers - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
  <div class="container">
    <h2>Drivers</h2>
    <?php if (empty($drivers)) echo '<p>No drivers added yet.</p>'; ?>
    <?php foreach($drivers as $d): ?>
      <div style="background:white;padding:12px;border-radius:8px;margin-bottom:8px">
        <strong><?php echo htmlspecialchars($d['name']); ?></strong>
        <p><?php echo htmlspecialchars($d['vehicle']); ?> — <?php echo htmlspecialchars($d['phone']); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
