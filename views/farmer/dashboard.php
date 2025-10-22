<!DOCTYPE html>
<html>
<head>
    <title>Farmer Dashboard</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: auto;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 10px;
            color: #2e7d32;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
        }
        .activities-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .activities-table th, .activities-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .activities-table th {
            background: #f5f5f5;
            color: #2e7d32;
        }
        nav a {
            display: inline-block;
            padding: 8px 16px;
            background: #2e7d32;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }
        nav a:hover {
            background: #1b5e20;
        }
        .profile-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-summary h3 {
            color: #2e7d32;
            margin-top: 0;
        }
        .msg.success {
            color: #2e7d32;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
        <h2>Farmer Dashboard</h2>
        <nav>
            <a href="my_products.php">My Products</a>
            <a href="bids.php" style="margin-left:8px">Bids</a>
            <a href="sales.php" style="margin-left:8px">Sales</a>
            <a href="wallet.php" style="margin-left:8px">Wallet</a>
            <a href="../auth/logout.php" style="margin-left:8px;background:#d32f2f">Logout</a>
        </nav>
    </div>

    <?php
    require_once __DIR__ . '/../../vendor/autoload.php';
    use App\Config\Database;

    if (session_status() === PHP_SESSION_NONE) session_start();

    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) {
        header('Location: ../auth/login.php');
        exit;
    }

    try {
        $db = new Database();
        $pdo = $db->connect();

        // Get farmer details
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND role = "farmer"');
        $stmt->execute([$user_id]);
        $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$farmer) {
            header('Location: ../auth/login.php');
            exit;
        }

        // Dashboard statistics
        $stats = [
            'products' => 0,
            'active_bids' => 0,
            'total_sales' => 0,
            'wallet_balance' => 0
        ];

        // Count products
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE farmer_id = ?');
        $stmt->execute([$user_id]);
        $stats['products'] = $stmt->fetchColumn();

        // Active bids
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM bids b 
            JOIN products p ON b.product_id = p.id 
            WHERE p.farmer_id = ? AND b.status = "pending"
        ');
        $stmt->execute([$user_id]);
        $stats['active_bids'] = $stmt->fetchColumn();

        // Total sales
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM sales s 
            JOIN products p ON s.product_id = p.id 
            WHERE p.farmer_id = ?
        ');
        $stmt->execute([$user_id]);
        $stats['total_sales'] = $stmt->fetchColumn();

        // Wallet
        $stmt = $pdo->prepare('SELECT balance FROM wallets WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $stats['wallet_balance'] = $stmt->fetchColumn() ?? 0;

        // Recent activities
        $stmt = $pdo->prepare('
            SELECT "Bid" AS type, b.amount, b.created_at AS date, p.name AS product_name, u.name AS buyer_name
            FROM bids b
            JOIN products p ON b.product_id = p.id
            JOIN users u ON b.buyer_id = u.id
            WHERE p.farmer_id = ? AND b.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            UNION ALL
            SELECT "Sale" AS type, s.amount, s.created_at AS date, p.name AS product_name, u.name AS buyer_name
            FROM sales s
            JOIN products p ON s.product_id = p.id
            JOIN users u ON s.buyer_id = u.id
            WHERE p.farmer_id = ? AND s.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY date DESC
            LIMIT 5
        ');
        $stmt->execute([$user_id, $user_id]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Dashboard error: " . $e->getMessage());
        $farmer = ['name' => 'Error loading profile'];
        $stats = ['products' => 0, 'active_bids' => 0, 'total_sales' => 0, 'wallet_balance' => 0];
        $activities = [];
    }
    ?>

    <div class="profile-summary">
        <h3>Welcome, <?= htmlspecialchars($farmer['name']) ?>!</h3>
        <p>Email: <?= htmlspecialchars($farmer['email']) ?></p>
        <p>Phone: <?= htmlspecialchars($farmer['phone'] ?? 'Not set') ?></p>
        <p>Location: <?= htmlspecialchars($farmer['location'] ?? 'Not set') ?></p>
        <a href="profile.php?id=<?= $user_id ?>" style="color:#2e7d32">Update Profile</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Products</h3>
            <div class="stat-value"><?= number_format($stats['products']) ?></div>
        </div>
        <div class="stat-card">
            <h3>Active Bids</h3>
            <div class="stat-value"><?= number_format($stats['active_bids']) ?></div>
        </div>
        <div class="stat-card">
            <h3>Total Sales</h3>
            <div class="stat-value"><?= number_format($stats['total_sales']) ?></div>
        </div>
        <div class="stat-card">
            <h3>Wallet Balance</h3>
            <div class="stat-value">KES <?= number_format($stats['wallet_balance'], 2) ?></div>
        </div>
    </div>

    <h3>Recent Activities</h3>
    <table class="activities-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Product</th>
                <th>Buyer</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($activities)): ?>
            <tr><td colspan="5" style="text-align:center">No recent activities</td></tr>
        <?php else: ?>
            <?php foreach ($activities as $a): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($a['date'])) ?></td>
                    <td><?= htmlspecialchars($a['type']) ?></td>
                    <td><?= htmlspecialchars($a['product_name']) ?></td>
                    <td><?= htmlspecialchars($a['buyer_name']) ?></td>
                    <td>KES <?= number_format($a['amount'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
