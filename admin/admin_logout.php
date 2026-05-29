<?php
session_start();

// 1. Clear all session variables
session_unset();

// 2. Destroy the session
session_destroy();

// 3. Optional: Clear the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Redirect to the login page
header("Location: admin_login.php");
exit();
?>