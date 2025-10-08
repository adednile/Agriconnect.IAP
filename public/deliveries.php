<?php
session_start();
if ($_SESSION['role'] !== 'driver') {
    die("Access denied!");
}
echo "<h2>Driver Deliveries Page</h2>";
