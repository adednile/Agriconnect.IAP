<?php
session_start();
if (empty($_SESSION['is_admin'])) { echo 'Unauthorized'; exit; }
require_once __DIR__ . '/../../vendor/autoload.php';
use App\Config\Database;

$db = new Database(); $conn = $db->connect();
$sales = $conn->query('SELECT s.*, p.name AS product_name, u.email AS buyer_email FROM sales s LEFT JOIN products p ON p.id=s.product_id LEFT JOIN users u ON u.id=s.buyer_id ORDER BY s.created_at DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC);
$wallets = $conn->query('SELECT w.*, u.email AS user_email FROM wallets w LEFT JOIN users u ON u.id=w.user_id ORDER BY w.updated_at DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC);
?>
<h2>MPesa Status / Dev</h2>
<h3>Recent Sales</h3>
<table border="1"><tr><th>ID</th><th>Product</th><th>Buyer</th><th>Farmer</th><th>Amount</th><th>Status</th><th>Action</th></tr>
<?php foreach($sales as $s){ echo '<tr><td>'.$s['id'].'</td><td>'.htmlspecialchars($s['product_name']).'</td><td>'.htmlspecialchars($s['buyer_email']).'</td><td>'.$s['farmer_id'].'</td><td>'.$s['total_amount'].'</td><td>'.$s['status'].'</td><td>' . ($s['status']==='paid' ? 'Paid' : '<button onclick="(function(id,amt){const fd=new FormData();fd.append(\'sale_id\',id);fd.append(\'amount\',amt);fd.append(\'mpesa_ref\',\'SIM-'+rand(100000,999999)+'\'); fetch(\'../routes/mpesa_callback.php\',{method:\'POST\',body:fd}).then(r=>r.json()).then(js=>alert(JSON.stringify(js)));})(\''.$s['id'].'\','.$s['total_amount'].')">Simulate</button>') . '</td></tr>';} ?>
</table>
<h3>Wallets</h3>
<table border="1"><tr><th>User</th><th>Balance</th></tr>
<?php foreach($wallets as $w) echo '<tr><td>'.htmlspecialchars($w['user_email']).'</td><td>'.$w['balance'].'</td></tr>'; ?>
</table>
