<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

if (!$con) {
    die("Connection failed");
}

$error = "";

if (isset($_POST["btnlogin"])) {
    // Sanitize inputs
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $password = $_POST["password"];

    // 1. Fetch user by email
    $q = mysqli_query($con, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);

        // 2. Verify Hashed Password
        if (password_verify($password, $row['password'])) {
            
            // 3. Get name from profile to set in session
            $uid = $row['id'];
            $profile_q = mysqli_query($con, "SELECT firstname FROM user_profile WHERE user_id='$uid'");
            $p_row = mysqli_fetch_assoc($profile_q);

            $_SESSION["client_id"] = $row["id"];
            $_SESSION["client_name"] = $p_row["firstname"] ?? "Client";

            /* REDIRECT */
            if (isset($_GET["redirect"])) {
                header("Location: " . $_GET["redirect"]);
            } else {
                header("Location: shop.php");
            }
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    } else {
        $error = "Account not found with that email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Aura Luxury Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --gold-bright: #f1c40f;
            --black: #0a0a0a;
            --dark-grey: #161616;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--black);
            background-image: radial-gradient(circle at center, #1a1a1a 0%, #0a0a0a 100%);
            color: var(--white);
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: var(--dark-grey);
            padding: 40px;
            border-radius: 15px;
            border: 1px solid #333;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            text-align: center;
            position: relative;
        }

        /* Gold accent line at top */
        .login-container::before {
            content: "";
            position: absolute;
            top: 0; left: 15%; width: 70%; height: 3px;
            background: var(--gold);
            box-shadow: 0 0 15px var(--gold);
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            font-size: 2.2rem;
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        p.subtitle {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 30px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .error-msg {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border: 1px solid #e74c3c;
            padding: 10px;
            border-radius: 5px;
            font-size: 0.8rem;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            font-size: 0.75rem;
            color: var(--gold);
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
            letter-spacing: 1px;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            background: #222;
            border: 1px solid #444;
            border-radius: 5px;
            color: white;
            font-family: inherit;
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            outline: none;
            border-color: var(--gold);
            background: #2a2a2a;
        }

        button {
            width: 100%;
            padding: 15px;
            background: var(--gold);
            color: #000;
            border: none;
            border-radius: 5px;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: var(--gold-bright);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .footer-links {
            margin-top: 25px;
            font-size: 0.8rem;
            color: #666;
        }

        .footer-links a {
            color: var(--gold);
            text-decoration: none;
            transition: 0.2s;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>The Atelier</h2>
    <p class="subtitle">Aura Luxury Rentals</p>

    <?php if ($error != "") { ?>
        <div class="error-msg">
            <?php echo $error; ?>
        </div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="e.g. client@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" name="btnlogin">Sign In</button>
    </form>

    <div class="footer-links">
        Don't have an account? <a href="register.php">Create one</a><br><br>
        <a href="../index.php">← Back to Home</a>
    </div>
</div>

</body>
</html>