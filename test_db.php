<?php
require_once 'config/Database.php';

$db = new Database();
$conn = $db->connect();

if ($conn) {
    echo "✅ Database connected successfully!";
} else {
    echo "❌ Database connection failed!";
}
?>
