<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../helpers/csrf.php';

use App\Controllers\BidController;

$body = file_get_contents('php://input');
$data = json_decode($body, true) ?: $_POST;

$action = $data['action'] ?? null;
$id = isset($data['id']) ? intval($data['id']) : null;

$controller = new BidController();

// CSRF token can be sent in body as _csrf or in header X-CSRF-Token
$token = $data['_csrf'] ?? null;
if (!$token) {
    $hdr = getallheaders();
    $token = $hdr['X-CSRF-Token'] ?? $hdr['x-csrf-token'] ?? null;
}

if (!csrf_check($token)) {
    echo json_encode(['error' => 'CSRF', 'success' => false]);
    exit;
}

header('Content-Type: application/json');

if (!$action || !$id) {
    echo json_encode(['error' => 'Invalid request', 'success' => false]);
    exit;
}

if ($action === 'approve') {
    $controller->approve($id);
    exit;
} elseif ($action === 'reject') {
    $controller->reject($id);
    exit;
} else {
    echo json_encode(['error' => 'Unknown action', 'success' => false]);
    exit;
}

?>
