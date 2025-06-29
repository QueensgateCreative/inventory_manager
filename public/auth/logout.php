<?php
require_once '../../config.php'; // Ensures session is started

session_unset();   // Unset all of the session variables
session_destroy(); // Destroy the session

header('Location: /inventory_manager/public/auth/login.php');
exit;
?>