<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. CONFIGURATION
$magic_token = "ACCESS_ME_ANYTIME"; // Change this to your secret
$login_page = "admin_login.php"; 

// 2. MAGIC LINK CHECK
// If someone uses ?token=... on ANY page, log them in
if (isset($_GET['token']) && $_GET['token'] === $magic_token) {
    $_SESSION['user_token'] = 'bypass_active';
    $_SESSION['is_admin'] = true;
}

// 3. THE "LOCK"
// If the session variable doesn't exist, stop EVERYTHING.
if (!isset($_SESSION['user_token'])) {
    // If it's an AJAX request or direct file access, just kill the script
    header("HTTP/1.1 403 Forbidden");
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h1 style='color:red;'>Access Denied</h1>
            <p>You cannot access this file directly.</p>
            <a href='$login_page'>Go to Login</a>
         </div>");
}
?>