<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    die("Access denied!");
}
echo "<h2>Admin Control Panel</h2>";
