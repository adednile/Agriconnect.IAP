<?php
// Simple smoke test script to exercise core flows.
require_once __DIR__ . '/../vendor/autoload.php';
use App\Controllers\AuthController;
use App\Models\Sale;
use App\Models\Wallet;
use App\Services\MpesaService;

echo "Starting smoke test...\n";

// 1) Attempt to login (use a known test account)
$auth = new AuthController();
$testEmail = getenv('TEST_USER_EMAIL') ?: 'tester@example.com';
$testPass = getenv('TEST_USER_PASS') ?: 'password';
$login = $auth->login($testEmail, $testPass);
if ($login === 'invalid' || $login === 'not_verified') {
    echo "Login failed: " . json_encode($login) . "\n";
} else {
    echo "Logged in as: " . $login['name'] . " (role=" . $login['role'] . ")\n";
}

// 2) Initiate a simulated STK push (will fallback to simulator if not configured)
$phone = getenv('TEST_PHONE') ?: '0712345678';
$amount = 10.00;
$res = MpesaService::initiateStkPush($phone, $amount, 'SMOKETEST', null, null);
echo "STK result: " . json_encode($res) . "\n";

// 3) Optionally call mpesa_callback to simulate payment
if (isset($res['sale_id']) && $res['sale_id']) {
    $data = ['sale_id' => $res['sale_id'], 'amount' => $amount, 'mpesa_ref' => 'SIM-'.rand(100000,999999)];
    $ch = curl_init('http://localhost/routes/mpesa_callback.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $cb = curl_exec($ch);
    curl_close($ch);
    echo "Callback response: $cb\n";
}

echo "Smoke test finished.\n";
