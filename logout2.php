<?php
session_start();
$wasAdmin = isset($_SESSION['admin_id']);
$_SESSION = array();


session_destroy();
header("Location: " . ($wasAdmin ? "admin_login.php" : "index2.php"));
exit();
?>