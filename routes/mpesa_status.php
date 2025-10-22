<?php
require_once __DIR__ . '/../vendor/autoload.php';
session_start();
if (empty($_SESSION['is_admin'])) { echo 'Unauthorized'; exit; }

require_once __DIR__ . '/../views/admin/mpesa_status.php';

?>
