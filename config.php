session_start();
<?php
// Supported languages for the site
$conf['languages'] = ['en' => 'English', 'sw' => 'Kiswahili'];

// Default language code
$conf['default_lang'] = 'en';

// Language selection logic: uses query string or session
session_start(); // Start session to store language preference
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $conf['languages'])) {
    $_SESSION['lang'] = $_GET['lang']; // Set language from URL if valid
}
$conf['lang'] = $_SESSION['lang'] ?? $conf['default_lang']; // Use session or default

// Site timezone setting
$conf['site_timezone'] = 'Africa/Nairobi';
date_default_timezone_set($conf['site_timezone']);

// Site information
$conf['site_name'] = 'AgriconnectKE'; // Name of the site
$conf['site_url'] = 'http://localhost/internet_application_programming_project/'; // Base URL
$conf['site_email'] = 'agriconnectKE@gmail.com'; // Contact email

// Site language (legacy, use $conf['lang'] for current language)
$conf['site_lang'] = 'en';

// Database configuration
$conf['DB_TYPE'] = 'mysqli'; // Database type
$conf['DB_HOST'] = 'localhost'; // Database host
$conf['DB_USER'] = 'root'; // Database username
$conf['DB_PASS'] = 'ndeda'; // Database password
$conf['DB_NAME'] = 'agriwebapp'; // Database name

// Email configuration
$conf['mail_type'] = 'smtp'; // Email sending method: 'mail' or 'smtp'
$conf['smtp_host'] = 'smtp.gmail.com'; // SMTP Host Address
$conf['smtp_user'] = 'elindeda1@gmail.com'; // SMTP Username
$conf['smtp_pass'] = ''; // SMTP Password
$conf['smtp_port'] = 465; // SMTP Port - 587 for tls, 465 for ssl
$conf['smtp_secure'] = 'ssl'; // Encryption - 'ssl' or 'tls'

// MySQLi connection
$mysqli = new mysqli(
    $conf['DB_HOST'],
    $conf['DB_USER'],
    $conf['DB_PASS'],
    $conf['DB_NAME']
);
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>