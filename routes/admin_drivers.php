<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Models\Driver;

$d = new Driver();
session_start();
if (empty($_SESSION['is_admin'])) {
    echo json_encode(['success'=>false,'error'=>'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_once __DIR__ . '/../helpers/csrf.php';
    $token = $_POST['_csrf'] ?? null;
    if (!csrf_check($token)) { echo json_encode(['success'=>false,'error'=>'CSRF']); exit; }
    $name = $_POST['name'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $vehicle = $_POST['vehicle'] ?? null;
    if (!$name) { echo json_encode(['success'=>false,'error'=>'Missing name']); exit; }
    $res = $d->create($name,$phone,$vehicle);
    // Driver::create returns boolean; fetch last insert id for convenience
    $db = new \App\Config\Database(); $conn = $db->connect();
    $id = $conn->lastInsertId();
    echo json_encode(['success'=>true,'id'=>$id]);
    exit;
}

require_once __DIR__ . '/../views/admin/drivers.php';

?>
