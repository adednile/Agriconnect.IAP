<?php
// $bids is provided by controller
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'farmer') {
    header('Location: ../../public/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Bids - AgriConnect</title>
    <link rel="stylesheet" href="../../public/css/style.css">
    <style>
        body { background:#f4f6f8; font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
        .container { max-width:1000px; margin:40px auto; background:white; padding:24px; border-radius:12px; box-shadow:0 6px 24px rgba(0,0,0,0.08);} 
        h2 { color:#2e7d32; margin-bottom:12px }
        table { width:100%; border-collapse:collapse; margin-top:12px }
        th, td { padding:12px; text-align:left; border-bottom:1px solid #eee }
        th { background:#e8f5e9; color:#1b5e20 }
        .status { padding:6px 10px; border-radius:8px; display:inline-block; font-weight:600 }
        .status.pending { background:#fff3cd; color:#856404 }
        .status.approved { background:#d4edda; color:#155724 }
        .status.rejected { background:#f8d7da; color:#721c24 }
        .btn { padding:8px 12px; border-radius:8px; text-decoration:none; color:white; display:inline-block; }
        .btn-approve { background:#2e7d32 }
        .btn-reject { background:#c62828 }
        .top { display:flex; justify-content:space-between; align-items:center }
    </style>
</head>
<body>
<div class="container">
    <div class="top">
        <h2>🧾 Bids on Your Products</h2>
        <a href="dashboard.php" style="color:#2e7d32;text-decoration:none">← Back to Dashboard</a>
    </div>

    <?php if (empty($bids)): ?>
        <p>No bids yet. Your products will receive offers from buyers here.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Buyer</th>
                    <th>Amount (Ksh)</th>
                    <th>Qty</th>
                    <th>Placed</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($bids as $b): ?>
                <tr id="bid-<?php echo htmlspecialchars($b['id']); ?>">
                    <td><?php echo htmlspecialchars($b['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($b['buyer_name']) . '<br><small>' . htmlspecialchars($b['buyer_email']) . '</small>'; ?></td>
                    <td><?php echo number_format($b['amount'], 2); ?></td>
                    <td><?php echo (int)$b['quantity']; ?></td>
                    <td><?php echo htmlspecialchars($b['created_at']); ?></td>
                    <td><span class="status <?php echo htmlspecialchars($b['status']); ?>"><?php echo htmlspecialchars(ucfirst($b['status'])); ?></span></td>
                    <td>
                        <?php if ($b['status'] === 'pending'): ?>
                            <button class="btn btn-approve" onclick="updateBid(<?php echo $b['id']; ?>,'approve', this)">Approve</button>
                            <button class="btn btn-reject" onclick="updateBid(<?php echo $b['id']; ?>,'reject', this)">Reject</button>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
function updateBid(id, action, btn) {
    if (!confirm('Are you sure?')) return;
    btn.disabled = true;
    fetch('../../routes/bid_router.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: action, id: id })
    }).then(r => r.json()).then(js => {
        if (js.success) {
            const row = document.getElementById('bid-' + id);
            const statusEl = row.querySelector('.status');
            statusEl.textContent = action === 'approve' ? 'Approved' : 'Rejected';
            statusEl.className = 'status ' + (action === 'approve' ? 'approved' : 'rejected');
            // remove actions
            row.querySelector('td:last-child').innerHTML = '—';
        } else {
            alert('Failed: ' + (js.error || 'unknown'));
            btn.disabled = false;
        }
    }).catch(err => {
        alert('Request failed');
        btn.disabled = false;
    });
}
</script>
</body>
</html>
