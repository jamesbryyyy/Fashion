<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

$message = "";

if (!isset($_SESSION['otp'])) {
    header("Location: register.php");
    exit();
}

if (isset($_POST['verify'])) {
    $userOtp = $_POST['otp'];

    if (time() > $_SESSION['otp_expiry']) {
        $message = "<div class='alert alert-error'>❌ OTP Expired. Please register again.</div>";
    } elseif ($userOtp == $_SESSION['otp']) {
        $data = $_SESSION['temp_reg'];
        
        // 1. Insert User
        $sql1 = "INSERT INTO users (email, password) VALUES ('{$data['email']}', '{$data['password']}')";
        
        if (mysqli_query($con, $sql1)) {
            $user_id = mysqli_insert_id($con);
            
            // 2. Insert Profile
            $sql2 = "INSERT INTO user_profile (user_id, firstname, lastname, middlename, age, address, contactnumber) 
                     VALUES ('$user_id', '{$data['firstname']}', '{$data['lastname']}', '{$data['middlename']}', '{$data['age']}', '{$data['address']}', '{$data['contactnumber']}')";
            
            mysqli_query($con, $sql2);
            
            // Clear Session
            session_destroy();
            header("Location: login.php?registration=success");
            exit();
        }
    } else {
        $message = "<div class='alert alert-error'>❌ Invalid OTP. Please try again.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP | Aura Luxury</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* (Same CSS as above for consistency) */
        :root { --gold: #D4AF37; --black: #0a0a0a; --grey: #161616; --white: #ffffff; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--black); color: var(--white); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .verify-container { background: var(--grey); width: 100%; max-width: 400px; padding: 40px; border-radius: 15px; border: 1px solid #333; text-align: center; }
        h2 { font-family: 'Playfair Display', serif; color: var(--gold); }
        input { background: #222; border: 1px solid #444; padding: 15px; border-radius: 5px; color: white; width: 100%; text-align: center; font-size: 1.5rem; letter-spacing: 5px; margin-bottom: 20px; box-sizing: border-box; }
        .btn-verify { background: var(--gold); color: black; border: none; padding: 15px; width: 100%; border-radius: 5px; font-weight: 700; cursor: pointer; }
        .alert-error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; padding: 10px; margin-bottom: 10px; border-radius: 5px;}
    </style>
</head>
<body>
<div class="verify-container">
    <h2>Verify Email</h2>
    <p>Enter the 6-digit code sent to your email.</p>
    <?php echo $message; ?>
    <form method="POST">
        <input type="text" name="otp" maxlength="6" placeholder="000000" required>
        <button type="submit" name="verify" class="btn-verify">Verify & Create Account</button>
    </form>
</div>
</body>
</html>