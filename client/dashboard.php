<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION["client_id"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account | Aura Luxury Rentals</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #D4AF37; --black: #0a0a0a; --dark-grey: #161616; --white: #ffffff; }
        body { font-family: 'Poppins', sans-serif; background: var(--black); color: #e0e0e0; margin: 0; padding-bottom: 50px; }
        
        .navbar { display: flex; justify-content: space-between; padding: 20px 10%; background: rgba(0,0,0,0.9); border-bottom: 1px solid rgba(212, 175, 55, 0.2); position: sticky; top: 0; z-index: 1000; align-items: center; }
        .navbar h1 { font-family: 'Playfair Display', serif; color: var(--gold); margin: 0; font-size: 1.5rem; letter-spacing: 2px; }
        .nav-links a { color: #fff; text-decoration: none; margin-left: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        
        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        h2 { font-family: 'Playfair Display', serif; color: var(--gold); font-size: 2rem; margin-bottom: 30px; }
        
        .section-title { font-family: 'Playfair Display', serif; color: var(--gold); border-bottom: 1px solid #333; padding-bottom: 10px; margin-top: 50px; text-transform: uppercase; letter-spacing: 2px; font-size: 1.2rem; }
        
        .card { background: var(--dark-grey); border: 1px solid #222; border-radius: 12px; padding: 20px; margin-bottom: 20px; display: flex; gap: 25px; transition: 0.3s; align-items: center; }
        .card:hover { border-color: var(--gold); }
        .card img { width: 120px; height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid #333; }
        
        .card-info { flex-grow: 1; }
        .card-info h3 { margin: 0 0 5px 0; font-family: 'Playfair Display', serif; color: var(--white); }
        
        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.65rem; font-weight: bold; text-transform: uppercase; display: inline-block; margin-bottom: 10px; }
        .status-pending { background: #3a2e00; color: #ffcc00; }
        .status-approved { background: #002e14; color: #00ff73; }
        .status-rejected { background: #3a0000; color: #ff4d4d; }

        .btn-pay { display: inline-block; background: var(--gold); color: #000; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; margin-top: 15px; transition: 0.3s; }
        .btn-pay:hover { background: #f1c40f; transform: translateY(-2px); }
        
        .details-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
        .data-item { background: rgba(255,255,255,0.05); padding: 5px 12px; border-radius: 4px; font-size: 0.8rem; color: #bbb; }
        .price { color: var(--gold); font-weight: 600; font-size: 1.1rem; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>AURA ATELIER</h1>
    <div class="nav-links">
        <a href="shop.php">Collection</a>
        <a href="dashboard.php" style="color: var(--gold);">My Bookings</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Client Dashboard</h2>

    <!-- GOWN RENTALS -->
    <h3 class="section-title">Gown Rentals</h3>
    <?php
    $q_gowns = mysqli_query($con,"SELECT b.*, g.name, g.image, g.base_price FROM bookings b JOIN gowns g ON g.id = b.gown_id WHERE b.client_id='$client_id' ORDER BY b.id DESC");
    if(mysqli_num_rows($q_gowns) == 0) echo "<p style='color:#666; margin-top:15px;'>No gown reservations found.</p>";
    while($r=mysqli_fetch_assoc($q_gowns)){ ?>
        <div class="card">
            <img src="<?php echo $r['image']; ?>">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">● <?php echo $r['status']; ?></span>
                <h3><?php echo $r['name']; ?></h3>
                <div class="price">₱<?php echo number_format($r['base_price'], 2); ?></div>
                <div class="details-row">
                    <span class="data-item">📅 <?php echo $r['date_from']; ?> to <?php echo $r['date_to']; ?></span>
                    <span class="data-item">💳 PAYMENT: <?php echo strtoupper($r['payment_status'] ?: 'unpaid'); ?></span>
                </div>
                <?php if($r['status'] == 'approved' && $r['payment_status'] !== 'paid'){ ?>
                    <a href="payment.php?booking_id=<?php echo $r['id']; ?>" class="btn-pay">Upload Payment Receipt</a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- MAKEUP APPOINTMENTS -->
    <h3 class="section-title">Makeup Appointments</h3>
    <?php
    $q_makeup = mysqli_query($con,"SELECT mb.*, ma.name, ma.price, ma.image FROM makeup_bookings mb JOIN makeup_artists ma ON ma.id = mb.makeup_artist_id WHERE mb.client_id='$client_id' ORDER BY mb.id DESC");
    if(mysqli_num_rows($q_makeup) == 0) echo "<p style='color:#666; margin-top:15px;'>No makeup appointments found.</p>";
    while($r=mysqli_fetch_assoc($q_makeup)){ ?>
        <div class="card">
            <img src="<?php echo $r['image']; ?>">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">● <?php echo $r['status']; ?></span>
                <h3>Artist: <?php echo $r['name']; ?></h3>
                <div class="price">₱<?php echo number_format($r['price'], 2); ?></div>
                <div class="details-row">
                    <span class="data-item">📅 <?php echo $r['booking_date']; ?> @ <?php echo $r['booking_time']; ?></span>
                    <span class="data-item">💳 PAYMENT: <?php echo strtoupper($r['payment_status'] ?: 'unpaid'); ?></span>
                </div>
                <?php if($r['status'] == 'approved' && $r['payment_status'] !== 'paid'){ ?>
                    <a href="payment.php?makeup_id=<?php echo $r['id']; ?>" class="btn-pay">Pay Appointment</a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- PACKAGES -->
    <h3 class="section-title">Luxury Packages</h3>
    <?php
    $q_packs = mysqli_query($con,"SELECT pb.*, p.package_name, p.package_price, p.image FROM package_bookings pb JOIN packages p ON p.id = pb.package_id WHERE pb.client_id='$client_id' ORDER BY pb.id DESC");
    if(mysqli_num_rows($q_packs) == 0) echo "<p style='color:#666; margin-top:15px;'>No package bookings found.</p>";
    while($r=mysqli_fetch_assoc($q_packs)){ ?>
        <div class="card">
            <img src="<?php echo $r['image']; ?>">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">● <?php echo $r['status']; ?></span>
                <h3><?php echo $r['package_name']; ?></h3>
                <div class="price">₱<?php echo number_format($r['package_price'], 2); ?></div>
                <div class="details-row">
                    <span class="data-item">📅 <?php echo $r['date_from']; ?> to <?php echo $r['date_to']; ?></span>
                    <span class="data-item">💳 PAYMENT: <?php echo strtoupper($r['payment_status'] ?: 'unpaid'); ?></span>
                </div>
                <?php if($r['status'] == 'approved' && $r['payment_status'] !== 'paid'){ ?>
                    <a href="payment.php?package_id=<?php echo $r['id']; ?>" class="btn-pay">Pay Package Bundle</a>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>

</body>
</html>