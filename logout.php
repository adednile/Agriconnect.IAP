<?php
// logout.php: Destroys session and redirects to login
session_start();
session_unset();
session_destroy();
header('Location: index.php?show=login&logout=1');
exit();
?>