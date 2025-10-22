<?php
// Simple CSRF helper
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token() {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['_csrf_token'];
}

function csrf_check($token) {
    if (empty($_SESSION['_csrf_token']) || !$token) return false;
    return hash_equals($_SESSION['_csrf_token'], $token);
}

?>
