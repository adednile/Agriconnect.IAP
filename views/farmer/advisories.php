<?php
// $advisories provided by AdvisoryController::listForFarmers
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Advisories - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
  <div class="container">
    <h2>Advisories for Farmers</h2>
    <?php if (empty($advisories)) echo '<p>No advisories yet.</p>'; ?>
    <?php foreach($advisories as $a): ?>
      <div style="background:white;padding:12px;border-radius:8px;margin-bottom:8px">
        <h3><?php echo htmlspecialchars($a['title']); ?></h3>
        <p><?php echo nl2br(htmlspecialchars($a['body'])); ?></p>
        <small>By <?php echo htmlspecialchars($a['author_name']); ?> — <?php echo htmlspecialchars($a['created_at']); ?></small>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>
