<?php
require_once 'config/Database.php';

$db = new Database();
$conn = $db->connect();

echo "✅ Database connected as per .env settings!<br>";
echo "DB Host: " . getenv('DB_HOST') . "<br>";
echo "DB Name: " . getenv('DB_NAME') . "<br>";
?>
