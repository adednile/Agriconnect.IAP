<?php
namespace App\Services;

use App\Config\Database;

class MpesaService {
    // Attempts a sandbox STK push; if credentials missing, returns simulated response
    public static function initiateStkPush($phone, $amount, $accountRef = null, $callbackUrl = null, $saleId = null) {
        // load env (allow environment or .env file)
        $envPath = __DIR__ . '/../../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || strpos($line, '#') === 0) continue;
                if (stripos($line, 'export ') === 0) $line = trim(substr($line,7));
                if (strpos($line, '=') === false) continue;
                list($name, $value) = explode('=', $line, 2);
                putenv(trim($name) . '=' . trim($value));
            }
        }

        $consumerKey = getenv('MPESA_CONSUMER_KEY');
        $consumerSecret = getenv('MPESA_CONSUMER_SECRET');
        $shortcode = getenv('MPESA_SHORTCODE');
        $passkey = getenv('MPESA_PASSKEY');
        $env = getenv('MPESA_ENV') ?: 'sandbox';
        $callback = $callbackUrl ?: getenv('MPESA_CALLBACK_URL') ?: null;

        if (!$consumerKey || !$consumerSecret || !$shortcode || !$passkey) {
            // return simulated response
            return ['success' => true, 'simulated' => true, 'message' => 'STK push simulated. Use mpesa_callback.php to mark sale paid.', 'sale_id' => $saleId];
        }

        // Determine endpoints
        $base = ($env === 'production') ? 'https://api.safaricom.co.ke' : 'https://sandbox.safaricom.co.ke';

        // Obtain OAuth token
        $tokenData = self::getAccessToken($consumerKey, $consumerSecret, $base);
        if (!$tokenData['success']) {
            return ['success' => false, 'error' => 'Failed to obtain access token', 'details' => $tokenData];
        }
        $accessToken = $tokenData['access_token'];

        // Build STK push payload
        $timestamp = date('YmdHis');
        $password = base64_encode($shortcode . $passkey . $timestamp);
        $accountRef = $accountRef ?: ('SALE-' . ($saleId ?? uniqid()));
        $phone = self::normalizePhone($phone);
        if (!$phone) return ['success' => false, 'error' => 'Invalid phone number'];

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int)ceil($amount),
            'PartyA' => $phone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callback ?: ($base . '/mpesa/callback'),
            'AccountReference' => $accountRef,
            'TransactionDesc' => 'Payment for order ' . ($saleId ?? $accountRef),
        ];

        $url = $base . '/mpesa/stkpush/v1/processrequest';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            error_log('Mpesa STK curl error: ' . $err);
            return ['success' => false, 'error' => 'Curl error', 'details' => $err];
        }

        $decoded = json_decode($resp, true);
        if ($httpcode >= 200 && $httpcode < 300 && isset($decoded['ResponseCode'])) {
            return ['success' => true, 'response' => $decoded, 'sale_id' => $saleId];
        }

        return ['success' => false, 'http_code' => $httpcode, 'response' => $decoded];
    }

    private static function getAccessToken($consumerKey, $consumerSecret, $base)
    {
        $url = $base . '/oauth/v1/generate?grant_type=client_credentials';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            error_log('Mpesa token curl error: ' . $err);
            return ['success' => false, 'error' => $err];
        }

        $data = json_decode($resp, true);
        if ($httpcode >= 200 && $httpcode < 300 && isset($data['access_token'])) {
            return ['success' => true, 'access_token' => $data['access_token']];
        }
        return ['success' => false, 'http_code' => $httpcode, 'response' => $data];
    }

    private static function normalizePhone($phone)
    {
        $p = preg_replace('/[^0-9+]/', '', $phone);
        // Convert local 07... to 2547...
        if (preg_match('/^0([0-9]{9})$/', $p, $m)) {
            return '254' . $m[1];
        }
        // Leading +254
        if (preg_match('/^\+?(254[0-9]{9})$/', $p, $m)) return $m[1];
        // Already 254...
        if (preg_match('/^(254[0-9]{9})$/', $p, $m)) return $m[1];
        return null;
    }
}
