<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$con = mysqli_connect("localhost", "root", "", "fashion");

$message = "";
if (isset($_POST['register'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    
    // Check if email exists first
    $checkEmail = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($checkEmail) > 0) {
        $message = "<div class='alert alert-error'>❌ Email already registered.</div>";
    } else {
        // Store data in session
        $_SESSION['temp_reg'] = [
            'email' => $email,
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'firstname' => mysqli_real_escape_string($con, $_POST['firstname']),
            'lastname' => mysqli_real_escape_string($con, $_POST['lastname']),
            'middlename' => mysqli_real_escape_string($con, $_POST['middlename']),
            'age' => mysqli_real_escape_string($con, $_POST['age']),
            'address' => mysqli_real_escape_string($con, $_POST['address']),
            'contactnumber' => mysqli_real_escape_string($con, $_POST['contactnumber'])
        ];

        // Generate OTP
        $otp = random_int(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_expiry'] = time() + 300; // 5 mins

        // Send Email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jamesmartinez24242@gmail.com'; // YOUR GMAIL
            $mail->Password   = 'rckytbdgjqiokxya';   // YOUR GMAIL APP PASSWORD
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('jamesmartinez24242@gmail.com', 'Aura Luxury');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Atelier Account';
            $mail->Body    = "<h1>Verification Code</h1><p>Your OTP is: <b>$otp</b></p><p>It expires in 5 minutes.</p>";

            $mail->send();
            header("Location: verify.php");
            exit();
        } catch (Exception $e) {
            $message = "<div class='alert alert-error'>Mail Error: {$mail->ErrorInfo}</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join The Atelier | Aura Luxury Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #D4AF37; --gold-hover: #f1c40f; --black: #0a0a0a; --grey: #161616; --white: #ffffff; --input-bg: #222; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--black); color: var(--white); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .reg-container { background: var(--grey); width: 100%; max-width: 600px; padding: 40px; border-radius: 15px; border: 1px solid #333; position: relative; }
        .reg-container::before { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 4px; background: linear-gradient(to right, transparent, var(--gold), transparent); }
        h2 { font-family: 'Playfair Display', serif; color: var(--gold); text-align: center; letter-spacing: 2px; text-transform: uppercase; }
        h3 { font-size: 0.9rem; color: var(--gold); text-transform: uppercase; margin: 25px 0 15px; border-bottom: 1px solid #333; padding-bottom: 5px; grid-column: span 2; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .full-width { grid-column: span 2; }
        input { background: var(--input-bg); border: 1px solid #444; padding: 12px; border-radius: 5px; color: white; width: 100%; box-sizing: border-box; }
        input:focus { outline: none; border-color: var(--gold); }
        .btn-register { background: var(--gold); color: black; border: none; padding: 15px; width: 100%; border-radius: 5px; font-weight: 700; text-transform: uppercase; cursor: pointer; margin-top: 30px; }
        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .alert-error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .login-link { text-align: center; margin-top: 20px; font-size: 0.85rem; color: #888; }
        .login-link a { color: var(--gold); text-decoration: none; }
    </style>
</head>
<body>
<div class="reg-container">
    <h2>The Atelier</h2>
    <?php echo $message; ?>
    <form method="POST">
        <div class="form-grid">
            <h3>Account Credentials</h3>
            <div class="input-group full-width"><input type="email" name="email" placeholder="Email Address" required></div>
            <div class="input-group full-width"><input type="password" name="password" placeholder="Password" required></div>
            <h3>Personal Profile</h3>
            <div class="input-group"><input type="text" name="firstname" placeholder="First Name" required></div>
            <div class="input-group"><input type="text" name="lastname" placeholder="Last Name" required></div>
            <div class="input-group"><input type="text" name="middlename" placeholder="Middle Name"></div>
            <div class="input-group"><input type="number" name="age" placeholder="Age" required></div>
            <div class="input-group full-width"><input type="text" name="contactnumber" placeholder="Contact Number" required></div>
            <div class="input-group full-width"><input type="text" name="address" placeholder="Full Home Address" required></div>
        </div>
        <button type="submit" name="register" class="btn-register">Send Verification Code</button>
        <div class="login-link">Already a member? <a href="login.php">Sign In</a></div>
    </form>
</div>
</body>
</html>