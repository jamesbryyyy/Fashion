<?php
// Start session only if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura | Luxury Gown Collection</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --black: #0a0a0a;
            --gold: #D4AF37;
            --gold-light: #f1d592;
            --white: #ffffff;
            --gray: #1a1a1a;
            --dark-gold: #996515;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; scroll-behavior: smooth; }
        body { background-color: var(--black); color: var(--white); line-height: 1.6; }

        /* Navbar Styling */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 8%;
            background: rgba(10, 10, 10, 0.95);
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.8rem; letter-spacing: 4px; color: var(--gold); text-decoration: none; }
        .nav-links { display: flex; list-style: none; align-items: center; }
        .nav-links li a { text-decoration: none; color: var(--white); margin: 0 15px; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
        .nav-links li a:hover { color: var(--gold); }
        
        .nav-auth { display: flex; align-items: center; gap: 10px; margin-left: 10px; }

        .btn-gold {
            background: linear-gradient(45deg, var(--gold), var(--gold-light));
            color: var(--black);
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 600;
            border-radius: 2px;
            text-transform: uppercase;
            font-size: 0.75rem;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Outline version for Logout to distinguish from Checkout */
        .btn-outline {
            background: transparent;
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .btn-gold:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
            color: var(--black);
        }

        .btn-outline:hover {
            background: var(--gold);
            color: var(--black);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo">DAVE POWERS</a>
        <ul class="nav-links">
            <li><a href="../index.php">Home</a></li>
            <li><a href="../index.php#about">About</a></li>
            <li><a href="client/shop.php">Shop</a></li>
            <li><a href="../index.php#contact">Contact</a></li>
            
            <div class="nav-auth">
                <?php if(isset($_SESSION['client_id'])): ?>
                    <!-- Checkout / Cart Button -->
                    <a href="dashboard.php" class="btn-gold">
                        <i class="fas fa-shopping-bag"></i> My Bookings
                    </a>
                    
                    <!-- Logout Button -->
                    <a href="logout.php" class="btn-gold btn-outline">Logout</a>
                <?php else: ?>
                    <!-- Login Button -->
                    <a href="login.php" class="btn-gold">Login</a>
                <?php endif; ?>
            </div>
        </ul>
    </nav>
</body>
</html>