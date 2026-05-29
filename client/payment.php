<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

// FIX: Check if booking_id exists in the URL
if(!isset($_GET['booking_id']) || empty($_GET['booking_id'])){
    // Redirect them back to dashboard if they access this page directly
    header("Location: dashboard.php");
    exit();
}

$booking_id = mysqli_real_escape_string($con, $_GET['booking_id']);
$client_id = $_SESSION['client_id'];

// Query with security check: Ensure the booking belongs to the logged-in client
$q = mysqli_query($con,"
    SELECT bookings.*, gowns.name, gowns.base_price, gowns.image
    FROM bookings
    INNER JOIN gowns ON gowns.id = bookings.gown_id
    WHERE bookings.id='$booking_id' AND bookings.client_id = '$client_id'
");

$r = mysqli_fetch_assoc($q);

// If no record is found in database
if(!$r) {
    die("<div style='color:white; background:black; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>
            <div>
                <h2>Booking Not Found</h2>
                <p>This booking doesn't exist or doesn't belong to you.</p>
                <a href='dashboard.php' style='color:#D4AF37;'>Return to Dashboard</a>
            </div>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment | Luxury Gowns</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark-gold: #996515;
            --black: #0a0a0a;
            --dark-grey: #1a1a1a;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--black);
            background-image: radial-gradient(circle at top right, #1a1a1a, #0a0a0a);
            color: var(--white);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
        }

        .checkout-container {
            width: 100%;
            max-width: 500px;
            background: var(--dark-grey);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            font-size: 2rem;
            margin: 0;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            height: 4px;
            width: 40px;
            background: #333;
            border-radius: 2px;
        }

        .step.active {
            background: var(--gold);
        }

        /* Order Summary Box */
        .order-summary {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 3px solid var(--gold);
        }

        .order-summary h4 {
            margin: 0;
            color: var(--gold);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        .order-summary p {
            margin: 10px 0 0 0;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
        }

        .price-tag {
            color: var(--gold);
            font-weight: 700;
        }

        /* QR Code Styling */
        .qr-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .qr-wrapper {
            background: white;
            padding: 15px;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.2);
            margin-bottom: 15px;
        }

        .qr-wrapper img {
            width: 220px;
            height: 220px;
            display: block;
        }

        .instruction {
            font-size: 0.9rem;
            color: #bbb;
            line-height: 1.6;
        }

        /* Form Styling */
        .upload-box {
            position: relative;
            margin-top: 20px;
        }

        input[type="file"] {
            background: rgba(255,255,255,0.05);
            border: 1px dashed var(--gold);
            padding: 20px;
            width: 100%;
            border-radius: 10px;
            color: #ccc;
            box-sizing: border-box;
            cursor: pointer;
            transition: all 0.3s;
        }

        input[type="file"]:hover {
            background: rgba(212, 175, 55, 0.05);
        }

        .submit-btn {
            background: linear-gradient(45deg, var(--dark-gold), var(--gold));
            color: var(--black);
            border: none;
            width: 100%;
            padding: 15px;
            font-size: 1rem;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 0.8rem;
        }

        .back-link:hover {
            color: var(--gold);
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="header">
        <h2>Secure Checkout</h2>
    </div>

    <div class="step-indicator">
        <div class="step active"></div>
        <div class="step active"></div>
        <div class="step"></div>
    </div>

    <div class="order-summary">
        <h4>Reservation for:</h4>
        <p>
            <span><?php echo $r['name']; ?></span>
            <span class="price-tag">₱<?php echo number_format($r['base_price'], 2); ?></span>
        </p>
    </div>

    <div class="qr-section">
        <div class="qr-wrapper">
            <!-- Replace with your actual GCash QR path -->
            <img src="gcash_qr.png" alt="GCash QR Code">
        </div>
        <p class="instruction">
            <strong>Scan the QR code via GCash</strong><br>
            Please ensure the amount matches the total above.
        </p>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="upload-box">
            <label style="display:block; margin-bottom:10px; font-size: 0.8rem; color: var(--gold);">UPLOAD PAYMENT RECEIPT</label>
            <input type="file" name="receipt" accept="image/*" required>
        </div>

        <button type="submit" name="upload" class="submit-btn">Verify Payment</button>
    </form>

    <a href="dashboard.php" class="back-link">Cancel and return to dashboard</a>
</div>

<?php
if(isset($_POST['upload'])){
    $file = $_FILES['receipt']['name'];
    $tmp = $_FILES['receipt']['tmp_name'];
    
    // Ensure directory exists
    if (!is_dir('receipts')) {
        mkdir('receipts', 0777, true);
    }

    $path = "receipts/" . time() . "_" . $file;

    if(move_uploaded_file($tmp, $path)){
        mysqli_query($con,"
            UPDATE bookings
            SET receipt_image='$path',
                payment_status='pending_verification'
            WHERE id='$booking_id'
        ");

        echo "<script>
            alert('Your receipt has been submitted successfully. We will verify your payment shortly.');
            window.location='dashboard.php';
        </script>";
    } else {
        echo "<script>alert('Error uploading file. Please try again.');</script>";
    }
}
?>

</body>
</html>