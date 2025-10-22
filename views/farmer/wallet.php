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
  <title>Wallet - AgriConnect</title>
  <link rel="stylesheet" href="../../public/css/style.css">
  <style>
    .container{max-width:900px;margin:40px auto;background:white;padding:20px;border-radius:12px}
    h2{color:#2e7d32}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid #eee}
    .balance{font-size:1.5rem;font-weight:700;color:#2e7d32}
  </style>
</head>
<body>
  <div class="container">
    <a href="dashboard.php">← Back</a>
    <h2>💼 Wallet</h2>
    <p>Balance: <span class="balance">Ksh <?php echo number_format($balance,2); ?></span></p>

    <h3>Transactions</h3>
    <?php if (empty($transactions)): ?>
      <p>No transactions yet.</p>
    <?php else: ?>
      <table>
        <tr><th>Date</th><th>Type</th><th>Amount</th><th>Source</th><th>Reference</th></tr>
        <?php foreach($transactions as $t): ?>
          <tr>
            <td><?php echo htmlspecialchars($t['created_at']); ?></td>
            <td><?php echo htmlspecialchars($t['type']); ?></td>
            <td><?php echo number_format($t['amount'],2); ?></td>
            <td><?php echo htmlspecialchars($t['source']); ?></td>
            <td><?php echo htmlspecialchars($t['reference']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
