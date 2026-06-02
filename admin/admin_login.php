<?php
// 1. Start session and output buffering
session_start();
ob_start();

// 2. Include database connection
// Make sure ../db.php correctly defines the $conn variable
include('../db.php');

$error = "";

if (isset($_POST['login'])) {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = $_POST['password'];

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // 3. Prepare statement
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($admin = $result->fetch_assoc()) {
        // 4. Verify Password
        // NOTE: This works with passwords hashed via password_hash()
        if (password_verify($pass, $admin['password'])) {
            
            // Regenerate session ID for security
            session_regenerate_id();
            
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['user_token'] = 'active';

            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Temporary debug: If your DB has MD5, this helps you identify the issue
            if (md5($pass) == $admin['password']) {
                $error = "Security Alert: Your DB uses MD5. Please update to PHP password_hash.";
            } else {
                $error = "Incorrect password.";
            }
        }
    } else {
        $error = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        h2 { color: #0f172a; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 1rem; }
        button { width: 100%; padding: 12px; background: #0f172a; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 1rem; transition: background 0.3s; }
        button:hover { background: #1e293b; }
        .error { color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 5px; font-size: 0.85rem; margin-bottom: 15px; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>
        
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>