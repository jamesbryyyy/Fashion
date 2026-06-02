<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

$id = mysqli_real_escape_string($con, $_GET['id']);

// Fetch Gown Data
$q = mysqli_query($con, "SELECT * FROM gowns WHERE id='$id'");
$r = mysqli_fetch_assoc($q);

$msg = "";
$msg_class = "";

/* HANDLE BOOKING */
if(isset($_POST['book'])){
    if(!isset($_SESSION['client_id'])){
        header("Location: login.php?redirect=gown_details.php?id=$id");
        exit();
    }

    $df = $_POST['date_from'];
    $dt = $_POST['date_to'];
    $client = $_SESSION['client_id'];

    if($df > $dt){
        $msg = "❌ Invalid date range selected.";
        $msg_class = "error";
    } else {
        // Check for approved bookings
        $check_booking = mysqli_query($con, "SELECT * FROM bookings WHERE gown_id='$id' AND status='approved' AND (('$df' BETWEEN date_from AND date_to) OR ('$dt' BETWEEN date_from AND date_to) OR (date_from BETWEEN '$df' AND '$dt'))");

        if(mysqli_num_rows($check_booking) > 0){
            $msg = "❌ Sorry, this gown is already booked for these dates.";
            $msg_class = "error";
        } else {
            mysqli_query($con, "INSERT INTO bookings (gown_id, item_id, client_id, date_from, date_to, status) VALUES ('$id', 1, '$client', '$df', '$dt', 'pending')");
            $msg = "✅ Booking request sent! Please wait for admin approval.";
            $msg_class = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $r['name']; ?> | Aura Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #D4AF37; --black: #0a0a0a; --grey: #161616; --white: #ffffff; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--black); 
            color: var(--white); 
            margin: 0; 
            padding: 0;
        }

        .container {
            max-width: 1100px;
            margin: 80px auto;
            display: flex;
            gap: 50px;
            padding: 20px;
            background: var(--grey);
            border: 1px solid rgba(212, 175, 55, 0.1);
        }

        /* LEFT SIDE: IMAGE */
        .image-section { flex: 1; }
        .image-section img { 
            width: 100%; 
            height: 700px; 
            object-fit: cover; 
            border: 1px solid rgba(212, 175, 55, 0.2);
        }

        /* RIGHT SIDE: DETAILS */
        .details-section { flex: 1; display: flex; flex-direction: column; justify-content: center; }
        
        .details-section h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 3rem; 
            color: var(--gold); 
            margin: 0 0 10px; 
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .price { 
            font-size: 1.8rem; 
            font-weight: 600; 
            margin-bottom: 20px; 
            color: var(--white);
        }

        .description { 
            font-size: 0.95rem; 
            line-height: 1.8; 
            color: #bbb; 
            margin-bottom: 30px; 
            border-top: 1px solid #333;
            padding-top: 20px;
        }

        /* FORM STYLING */
        .booking-form { 
            background: rgba(0,0,0,0.3); 
            padding: 25px; 
            border-radius: 4px; 
            border: 1px solid #222;
        }

        .form-group { margin-bottom: 15px; }
        label { 
            display: block; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: var(--gold); 
            margin-bottom: 5px; 
            letter-spacing: 1px;
        }

        input[type="date"] {
            width: 100%;
            background: #000;
            border: 1px solid #333;
            padding: 12px;
            color: var(--white);
            font-family: 'Poppins', sans-serif;
            outline: none;
            box-sizing: border-box;
        }

        input[type="date"]:focus { border-color: var(--gold); }

        .btn-book {
            width: 100%;
            background: var(--gold);
            color: #000;
            border: none;
            padding: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-book:hover { background: var(--white); }

        /* ALERTS */
        .alert { 
            padding: 15px; 
            margin-bottom: 20px; 
            font-size: 0.85rem; 
            text-align: center;
            border-radius: 4px;
        }
        .error { background: #441111; color: #ffcccc; border: 1px solid #662222; }
        .success { background: #113311; color: #ccffcc; border: 1px solid #225522; }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .back-link:hover { color: var(--gold); }

        @media (max-width: 850px) {
            .container { flex-direction: column; margin: 20px; }
            .image-section img { height: 400px; }
        }
    </style>
</head>
<body>

<?php include '../header.php'; ?>

<div class="container">
    
    <!-- LEFT: GOWN IMAGE -->
    <div class="image-section">
        <img src="<?php echo $r['image']; ?>" alt="<?php echo $r['name']; ?>">
    </div>

    <!-- RIGHT: DETAILS & FORM -->
    <div class="details-section">
        
        <?php if($msg != ""): ?>
            <div class="alert <?php echo $msg_class; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <h1><?php echo $r['name']; ?></h1>
        <div class="price">₱<?php echo number_format($r['base_price'], 2); ?></div>
        
        <div class="description">
            <?php echo $r['description']; ?>
            <p style="margin-top:15px; font-style: italic; font-size: 0.8rem;">
                *All rentals are subject to a standard 3-day duration unless otherwise coordinated with our stylists.
            </p>
        </div>

        <div class="booking-form">
            <form method="POST">
                <div class="form-group">
                    <label>Pickup Date</label>
                    <input type="date" name="date_from" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label>Return Date</label>
                    <input type="date" name="date_to" required min="<?php echo date('Y-m-d'); ?>">
                </div>

                <button type="submit" name="book" class="btn-book">Request Reservation</button>
            </form>
        </div>

        <a href="shop.php?view=gowns" class="back-link">← Back to Collection</a>
    </div>

</div>

<?php include '../footer.php'; ?>

</body>
</html>