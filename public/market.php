<?php
session_start();
if ($_SESSION['role'] !== 'buyer') {
    die("Access denied!");
}
echo "<h2>Marketplace Page</h2>";
