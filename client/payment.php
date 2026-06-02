<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['client_id'];
$type = "";
$item_id = "";
$display_name = "";
$amount = 0;

// Identify what is being paid for
if(isset($_GET['booking_id'])){
    $type = "gown";
    $item_id = mysqli_real_escape_string($con, $_GET['booking_id']);
    $res = mysqli_query($con, "SELECT b.*, g.name, g.base_price FROM bookings b JOIN gowns g ON g.id=b.gown_id WHERE b.id='$item_id' AND b.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    $display_name = $data['name'];
    $amount = $data['base_price'];
} elseif(isset($_GET['makeup_id'])){
    $type = "makeup";
    $item_id = mysqli_real_escape_string($con, $_GET['makeup_id']);
    $res = mysqli_query($con, "SELECT mb.*, ma.name, ma.price FROM makeup_bookings mb JOIN makeup_artists ma ON ma.id=mb.makeup_artist_id WHERE mb.id='$item_id' AND mb.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    $display_name = "Makeup Artist: " . $data['name'];
    $amount = $data['price'];
} elseif(isset($_GET['package_id'])){
    $type = "package";
    $item_id = mysqli_real_escape_string($con, $_GET['package_id']);
    $res = mysqli_query($con, "SELECT pb.*, p.package_name, p.package_price FROM package_bookings pb JOIN packages p ON p.id=pb.package_id WHERE pb.id='$item_id' AND pb.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    $display_name = $data['package_name'];
    $amount = $data['package_price'];
}

if(!$data) { die("Invalid Selection. <a href='dashboard.php'>Back</a>"); }

// Fetch QR Code
$qr_query = mysqli_query($con, "SELECT qr_code FROM settings WHERE id=1");
$qr_path = mysqli_fetch_assoc($qr_query)['qr_code'] ?? 'gcash_qr.png';

// Handle Receipt Upload
if(isset($_POST['upload'])){
    $file = time() . "_" . $_FILES['receipt']['name'];
    if(!is_dir('receipts')) mkdir('receipts');
    
    if(move_uploaded_file($_FILES['receipt']['tmp_name'], "receipts/" . $file)){
        $db_path = "receipts/" . $file;
        
        // Insert into payments table using the correct column
        if($type == "gown") {
            mysqli_query($con, "INSERT INTO payments (booking_id, amount, receipt_image, status) VALUES ('$item_id', '$amount', '$db_path', 'pending')");
            mysqli_query($con, "UPDATE bookings SET payment_status='pending_verification' WHERE id='$item_id'");
        } elseif($type == "makeup") {
            mysqli_query($con, "INSERT INTO payments (makeup_bookings_id, amount, receipt_image, status) VALUES ('$item_id', '$amount', '$db_path', 'pending')");
            mysqli_query($con, "UPDATE makeup_bookings SET payment_status='pending_verification' WHERE id='$item_id'");
        } elseif($type == "package") {
            mysqli_query($con, "INSERT INTO payments (package_bookings_id, amount, receipt_image, status) VALUES ('$item_id', '$amount', '$db_path', 'pending')");
            mysqli_query($con, "UPDATE package_bookings SET payment_status='pending_verification' WHERE id='$item_id'");
        }

        echo "<script>alert('Receipt submitted!'); window.location='dashboard.php';</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Complete Payment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; padding: 50px; }
        .checkout-box { background: #1a1a1a; padding: 40px; border-radius: 20px; border: 1px solid #D4AF37; width: 400px; text-align: center; }
        .price { font-size: 2rem; color: #D4AF37; margin: 20px 0; }
        .qr-img { width: 200px; background: white; padding: 10px; border-radius: 10px; }
        input[type="file"] { margin: 20px 0; display: block; width: 100%; }
        .btn { background: #D4AF37; color: black; border: none; padding: 15px; width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
<div class="checkout-box">
    <h2>Secure Checkout</h2>
    <p><?php echo $display_name; ?></p>
    <div class="price">₱<?php echo number_format($amount, 2); ?></div>
    
    <img src="../admin/<?php echo $qr_path; ?>" class="qr-img" alt="QR Code">
    <p>Scan to pay via GCash</p>

    <form method="POST" enctype="multipart/form-data">
        <label>Upload Receipt Image</label>
        <input type="file" name="receipt" required>
        <button type="submit" name="upload" class="btn">Submit Payment</button>
    </form>
    <br>
    <a href="dashboard.php" style="color:#666; text-decoration:none;">Cancel</a>
</div>
</body>
</html>