<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__. '/controllers/AuthController.php';

$auth = new AuthController();

// Test registration manually
echo $auth->register("Michael Doe", "mike@example.com", "mypassword123", "farmer");
?>
