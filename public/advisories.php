<?php
session_start();
if ($_SESSION['role'] !== 'agronomist') {
    die("Access denied!");
}
echo "<h2>Agronomist Advisories Page</h2>";
