<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

$id = mysqli_real_escape_string($con, $_GET['id']);

$q = mysqli_query($con, "
    SELECT 
        packages.*, 
        gowns.name AS gown_name,
        gowns.image AS gown_image,
        makeup_artists.name AS makeup_name
    FROM packages
    LEFT JOIN gowns ON packages.gown_id = gowns.id
    LEFT JOIN makeup_artists ON packages.makeup_artist_id = makeup_artists.id
    WHERE packages.id='$id'
");

$r = mysqli_fetch_assoc($q);
$alert = "";

if (isset($_POST['book'])) {
    if (!isset($_SESSION['client_id'])) {
        header("Location: login.php?redirect=package_details.php?id=" . $id);
        exit();
    }

    $client = $_SESSION['client_id'];
    $df = mysqli_real_escape_string($con, $_POST['date_from']);
    $dt = mysqli_real_escape_string($con, $_POST['date_to']);
    $time = mysqli_real_escape_string($con, $_POST['makeup_time']);

    // Check Gown Availability
    $check_gown = mysqli_query($con, "
        SELECT * FROM bookings 
        WHERE gown_id='" . $r['gown_id'] . "' 
        AND status='approved' 
        AND (('$df' BETWEEN date_from AND date_to) OR ('$dt' BETWEEN date_from AND date_to) OR (date_from BETWEEN '$df' AND '$dt'))
    ");

    // Check Makeup Availability
    $check_makeup = mysqli_query($con, "
        SELECT * FROM makeup_bookings 
        WHERE makeup_artist_id='" . $r['makeup_artist_id'] . "' 
        AND booking_date='$df' AND booking_time='$time' AND status='approved'
    ");

    if (mysqli_num_rows($check_gown) > 0) {
        $alert = "<div class='alert error'>❌ The included gown is already reserved for these dates.</div>";
    } else if (mysqli_num_rows($check_makeup) > 0) {
        $alert = "<div class='alert error'>❌ The makeup artist is unavailable at the selected time.</div>";
    } else {
        mysqli_query($con, "
            INSERT INTO package_bookings (package_id, gown_id, makeup_artist_id, client_id, date_from, date_to, makeup_time, status)
            VALUES ('$id', '" . $r['gown_id'] . "', '" . $r['makeup_artist_id'] . "', '$client', '$df', '$dt', '$time', 'pending')
        ");
        $alert = "<div class='alert success'>✅ Booking request submitted! Please wait for approval.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $r['package_name']; ?> | Aura Luxury</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #D4AF37; --black: #0a0a0a; --grey: #161616; --white: #ffffff; }
        body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); margin: 0; }
        
        .container { max-width: 1100px; margin: 50px auto; padding: 20px; }
        .back-link { color: var(--gold); text-decoration: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; display: inline-block; margin-bottom: 20px; }
        
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; background: var(--grey); border-radius: 15px; overflow: hidden; border: 1px solid #222; }
        
        .img-box { height: 600px; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .info-box { padding: 40px; }
        h1 { font-family: 'Playfair Display', serif; color: var(--gold); font-size: 2.5rem; margin: 0 0 10px; }
        .price { font-size: 1.5rem; font-weight: 600; margin-bottom: 20px; color: var(--white); }
        
        .bundle-details { margin: 30px 0; border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 20px 0; }
        .bundle-item { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .bundle-item i { color: var(--gold); font-size: 1.2rem; }
        .label { color: var(--gold); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; display: block; }
        .val { font-size: 1rem; font-weight: 400; }

        form { display: grid; gap: 20px; }
        .input-group { display: flex; flex-direction: column; gap: 8px; }
        label { font-size: 0.75rem; text-transform: uppercase; color: #888; letter-spacing: 1px; }
        input, select { background: #222; border: 1px solid #444; padding: 12px; color: white; border-radius: 5px; font-family: inherit; }
        input:focus, select:focus { outline: none; border-color: var(--gold); }
        
        .btn-book { background: var(--gold); color: black; border: none; padding: 15px; border-radius: 5px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-book:hover { background: #f1c40f; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3); }

        .alert { padding: 15px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .error { background: rgba(231, 76, 60, 0.2); color: #e74c3c; border: 1px solid #e74c3c; }
        .success { background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; }

        @media (max-width: 850px) {
            .details-grid { grid-template-columns: 1fr; }
            .img-box { height: 400px; }
        }
    </style>
</head>
<body>

<div class="container">
    <a href="shop.php?view=packages" class="back-link">← Back to Packages</a>
    
    <?php echo $alert; ?>

    <div class="details-grid">
        <!-- LEFT: Image -->
        <div class="img-box">
            <img src="<?php echo $r['image']; ?>" alt="Package Image">
        </div>

        <!-- RIGHT: Details & Form -->
        <div class="info-box">
            <h1><?php echo $r['package_name']; ?></h1>
            <div class="price">₱<?php echo number_format($r['package_price'], 2); ?></div>
            
            <p style="color: #bbb; font-size: 0.9rem; line-height: 1.6;">
                <?php echo $r['description'] ?: "This exclusive luxury bundle includes a premium gown rental and professional makeup services for your special event."; ?>
            </p>

            <div class="bundle-details">
                <div class="bundle-item">
                    <div>
                        <span class="label">Curated Gown</span>
                        <span class="val"><?php echo $r['gown_name']; ?></span>
                    </div>
                </div>
                <div class="bundle-item">
                    <div>
                        <span class="label">Featured Artist</span>
                        <span class="val"><?php echo $r['makeup_name']; ?></span>
                    </div>
                </div>
            </div>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="input-group">
                        <label>Rental Starts</label>
                        <input type="date" name="date_from" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="input-group">
                        <label>Rental Ends</label>
                        <input type="date" name="date_to" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Preferred Makeup Time</label>
                    <select name="makeup_time" required>
                        <option value="" disabled selected>Select a time slot</option>
                        <option>08:00 AM</option>
                        <option>09:00 AM</option>
                        <option>10:00 AM</option>
                        <option>11:00 AM</option>
                        <option>01:00 PM</option>
                        <option>02:00 PM</option>
                    </select>
                </div>

                <button type="submit" name="book" class="btn-book">Reserve This Bundle</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>