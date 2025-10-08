<?php
session_start();
if ($_SESSION['role'] !== 'farmer') {
    die("Access denied!");
}
echo "<h2>Farmer Product Management Page</h2>";
