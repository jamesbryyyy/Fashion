<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

if(!isset($_SESSION["client_id"])){ header("Location: login.php"); exit(); }

$client_id = $_SESSION['client_id'];
$type = ""; $item_id = ""; $display_name = ""; $amount = 0; $data = null;

if(isset($_GET['booking_id'])){
    $type = "gown"; $item_id = mysqli_real_escape_string($con, $_GET['booking_id']);
    $res = mysqli_query($con, "SELECT b.*, g.name, g.base_price FROM bookings b JOIN gowns g ON g.id=b.gown_id WHERE b.id='$item_id' AND b.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    if($data){ $display_name = $data['name']; $amount = $data['base_price']; }
} elseif(isset($_GET['makeup_id'])){
    $type = "makeup"; $item_id = mysqli_real_escape_string($con, $_GET['makeup_id']);
    $res = mysqli_query($con, "SELECT mb.*, ma.name, ma.price FROM makeup_bookings mb JOIN makeup_artists ma ON ma.id=mb.makeup_artist_id WHERE mb.id='$item_id' AND mb.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    if($data){ $display_name = "Makeup: " . $data['name']; $amount = $data['price']; }
} elseif(isset($_GET['package_id'])){
    $type = "package"; $item_id = mysqli_real_escape_string($con, $_GET['package_id']);
    $res = mysqli_query($con, "SELECT pb.*, p.package_name, p.package_price FROM package_bookings pb JOIN packages p ON p.id=pb.package_id WHERE pb.id='$item_id' AND pb.client_id='$client_id'");
    $data = mysqli_fetch_assoc($res);
    if($data){ $display_name = $data['package_name']; $amount = $data['package_price']; }
}

if(!$data) { die("Invalid Selection. <a href='dashboard.php'>Back</a>"); }

// QR CODE PATH LOGIC
$qr_query = mysqli_query($con, "SELECT qr_code FROM settings WHERE id=1");
$qr_row = mysqli_fetch_assoc($qr_query);
$qr_filename = $qr_row['qr_code'] ?? '';
$qr_path = "../admin/uploads/" . $qr_filename;

if(isset($_POST['upload'])){
    $file_name = time() . "_" . $_FILES['receipt']['name'];
    if(!is_dir('receipts')) mkdir('receipts', 0777, true);
    
    if(move_uploaded_file($_FILES['receipt']['tmp_name'], "receipts/" . $file_name)){
        $db_path = "receipts/" . $file_name;
        
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
        body { background: #0a0a0a; color: white; font-family: 'Poppins', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .checkout-box { background: #1a1a1a; padding: 40px; border-radius: 20px; border: 1px solid #D4AF37; width: 400px; text-align: center; }
        .price { font-size: 2rem; color: #D4AF37; margin: 20px 0; }
        .qr-img { width: 220px; height: 220px; object-fit: contain; background: white; padding: 10px; border-radius: 10px; margin-bottom: 15px; }
        input[type="file"] { margin: 20px 0; display: block; width: 100%; color: #ccc; }
        .btn { background: #D4AF37; color: black; border: none; padding: 15px; width: 100%; border-radius: 10px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .btn:hover { background: #b8962d; }
    </style>
</head>
<body>
<div class="checkout-box">
    <h2>Secure Checkout</h2>
    <p><?php echo $display_name; ?></p>
    <div class="price">₱<?php echo number_format($amount, 2); ?></div>
    
    <?php if(!empty($qr_filename) && file_exists($qr_path)): ?>
        <img src="<?php echo $qr_path; ?>" class="qr-img" alt="QR Code">
    <?php else: ?>
        <div style="background:#333; padding: 20px; margin-bottom: 20px; border-radius: 10px;">QR Code Not Available</div>
    <?php endif; ?>
    
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