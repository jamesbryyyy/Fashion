<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION["client_id"];

/* --- HANDLE CANCEL (Works for Gowns) --- */
if(isset($_POST['cancel'])){
    $id = mysqli_real_escape_string($con, $_POST['id']);
    mysqli_query($con,"DELETE FROM bookings WHERE id='$id' AND client_id='$client_id' AND status='pending'");
    header("Location: dashboard.php");
    exit();
}

/* --- HANDLE UPDATE (Works for Gowns) --- */
if(isset($_POST['update'])){
    $id = mysqli_real_escape_string($con, $_POST['id']);
    $from = mysqli_real_escape_string($con, $_POST['date_from']);
    $to = mysqli_real_escape_string($con, $_POST['date_to']);

    // Check for availability conflicts before updating
    $check = mysqli_query($con,"
        SELECT * FROM bookings 
        WHERE gown_id = (SELECT gown_id FROM bookings WHERE id='$id') 
        AND status='approved' AND id != '$id'
        AND (('$from' BETWEEN date_from AND date_to) OR ('$to' BETWEEN date_from AND date_to))
    ");

    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('❌ These dates are already reserved by someone else.'); window.location='dashboard.php';</script>";
        exit();
    } else {
        mysqli_query($con,"UPDATE bookings SET date_from='$from', date_to='$to' WHERE id='$id' AND client_id='$client_id' AND status='pending'");
        header("Location: dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | Gown Atelier</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark-gold: #996515;
            --black: #0a0a0a;
            --dark-grey: #1a1a1a;
            --white: #ffffff;
            --status-pending: #ffcc00;
            --status-approved: #00ff73;
            --status-rejected: #ff4d4d;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--black);
            color: #e0e0e0;
            margin: 0;
            padding-bottom: 50px;
        }

        /* Navigation */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 10%;
            background: rgba(0,0,0,0.9);
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar h1 {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: 2px;
        }

        .nav-links a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .nav-links a:hover { color: var(--gold); }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            color: var(--white);
            margin-bottom: 10px;
            text-align: center;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            border-bottom: 1px solid #333;
            padding-bottom: 10px;
            margin-top: 50px;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        /* Booking Cards */
        .card {
            background: var(--dark-grey);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            display: flex;
            gap: 25px;
            transition: 0.3s;
            position: relative;
        }

        .card:hover { border-color: rgba(212, 175, 55, 0.4); }

        .card img {
            width: 150px;
            height: 180px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #333;
        }

        .card-info { flex-grow: 1; }

        .card-info h3 {
            font-family: 'Playfair Display', serif;
            margin: 0 0 5px 0;
            font-size: 1.6rem;
            color: var(--white);
        }

        .price {
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--gold);
            margin-bottom: 15px;
        }

        .details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .data-item {
            background: rgba(255,255,255,0.05);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
            color: #ccc;
        }

        /* Badges */
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .status-pending { background: #3a2e00; color: var(--status-pending); }
        .status-approved { background: #002e14; color: var(--status-approved); }
        .status-rejected { background: #330000; color: var(--status-rejected); }

        /* Action Buttons */
        .edit-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #333;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .edit-form label { font-size: 0.7rem; color: #888; display: block; margin-bottom: 5px; }

        input[type="date"], input[type="time"] {
            background: #000;
            border: 1px solid #444;
            color: #fff;
            padding: 8px;
            border-radius: 5px;
            font-family: inherit;
        }

        .btn-update {
            background: transparent;
            color: var(--gold);
            border: 1px solid var(--gold);
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-update:hover { background: var(--gold); color: #000; }

        .btn-cancel {
            background: transparent;
            color: #888;
            border: 1px solid #444;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-cancel:hover { background: #c0392b; color: #fff; border-color: #c0392b; }

        .btn-pay {
            display: inline-block;
            background: linear-gradient(45deg, var(--dark-gold), var(--gold));
            color: var(--black);
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.2);
        }

        @media (max-width: 768px) {
            .card { flex-direction: column; }
            .card img { width: 100%; height: 250px; }
            .navbar { padding: 20px 5%; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>GOWN ATELIER</h1>
    <div class="nav-links">
        <a href="shop.php">Collection</a>
        <a href="dashboard.php" style="color: var(--gold);">My Bookings</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>My Reservations</h2>
    <p style="text-align:center; color:#888;">Manage your upcoming fittings and glam sessions.</p>

    <!-- ================= GOWN SECTION ================= -->
    <h3 class="section-title">Gown Rentals</h3>
    <?php
    $q_gowns = mysqli_query($con,"
        SELECT bookings.*, gowns.name, gowns.image, gowns.base_price 
        FROM bookings 
        INNER JOIN gowns ON gowns.id = bookings.gown_id 
        WHERE bookings.client_id='$client_id' 
        ORDER BY bookings.id DESC
    ");

    if(mysqli_num_rows($q_gowns) == 0) echo "<p style='color:#666;'>No gown reservations found.</p>";

    while($r=mysqli_fetch_assoc($q_gowns)){ 
    ?>
        <div class="card">
            <img src="<?php echo $r['image']; ?>" alt="Gown">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">● <?php echo strtoupper($r['status']); ?></span>
                <h3><?php echo $r['name']; ?></h3>
                <div class="price">₱<?php echo number_format($r['base_price'], 2); ?></div>

                <div class="details-row">
                    <div class="data-item">📅 <?php echo date("M d", strtotime($r['date_from'])); ?> - <?php echo date("M d, Y", strtotime($r['date_to'])); ?></div>
                    <div class="data-item">💳 Payment: <span style="color:var(--gold)"><?php echo strtoupper($r['payment_status'] ?? 'UNPAID'); ?></span></div>
                </div>

                <?php if($r['status']=='approved' && $r['payment_status'] != 'paid'){ ?>
                    <a href="payment.php?booking_id=<?php echo $r['id']; ?>" class="btn-pay">Complete Payment</a>
                <?php } ?>

                <?php if($r['status']=='pending'){ ?>
                    <form method="POST" class="edit-form">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                        <div>
                            <label>START DATE</label>
                            <input type="date" name="date_from" value="<?php echo $r['date_from']; ?>">
                        </div>
                        <div>
                            <label>END DATE</label>
                            <input type="date" name="date_to" value="<?php echo $r['date_to']; ?>">
                        </div>
                        <button type="submit" name="update" class="btn-update">Update</button>
                        <button type="submit" name="cancel" class="btn-cancel" onclick="return confirm('Cancel this reservation?')">Cancel</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- ================= MAKEUP SECTION ================= -->
    <h3 class="section-title">Makeup Appointments</h3>
    <?php
    $q_makeup = mysqli_query($con,"
        SELECT makeup_bookings.*, makeup_artists.name
        FROM makeup_bookings
        INNER JOIN makeup_artists ON makeup_artists.id = makeup_bookings.makeup_artist_id
        WHERE makeup_bookings.client_id='$client_id'
        ORDER BY makeup_bookings.id DESC
    ");

    if(mysqli_num_rows($q_makeup) == 0) echo "<p style='color:#666;'>No makeup appointments found.</p>";

    while($r=mysqli_fetch_assoc($q_makeup)){
    ?>
        <div class="card">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">● <?php echo strtoupper($r['status']); ?></span>
                <h3>Artist: <?php echo $r['name']; ?></h3>
                <div class="details-row">
                    <div class="data-item">📅 Date: <?php echo date("M d, Y", strtotime($r['booking_date'])); ?></div>
                    <div class="data-item">⏰ Time: <?php echo date("h:i A", strtotime($r['booking_time'])); ?></div>
                </div>
            </div>
        </div>
    <?php } ?>

<!-- ================= PACKAGE SECTION ================= -->
    <h3 class="section-title">Exclusive Packages</h3>
    <?php
    // Removed 'packages.price' because it doesn't exist in your database
    $q_packs = mysqli_query($con,"
        SELECT package_bookings.*, packages.package_name
        FROM package_bookings
        INNER JOIN packages ON packages.id = package_bookings.package_id
        WHERE package_bookings.client_id='$client_id'
        ORDER BY package_bookings.id DESC
    ");

    if(mysqli_num_rows($q_packs) == 0) {
        echo "<p style='color:#666;'>No package bookings found.</p>";
    }

    while($r=mysqli_fetch_assoc($q_packs)){
    ?>
        <div class="card">
            <div class="card-info">
                <span class="badge status-<?php echo $r['status']; ?>">
                    ● <?php echo strtoupper($r['status']); ?>
                </span>
                
                <h3><?php echo $r['package_name']; ?></h3>
                
                <!-- We show dates and makeup time instead of price -->
                <div class="details-row">
                    <div class="data-item">
                        📅 Rental: <?php echo date("M d", strtotime($r['date_from'])); ?> - <?php echo date("M d, Y", strtotime($r['date_to'])); ?>
                    </div>
                    <div class="data-item">
                        💄 Makeup Time: <?php echo date("h:i A", strtotime($r['makeup_time'])); ?>
                    </div>
                </div>
                
                <div class="details-row">
                    <div class="data-item">
                        💳 Status: <span style="color:var(--gold)">RESERVATION ONLY</span>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>

</div>

</body>
</html>