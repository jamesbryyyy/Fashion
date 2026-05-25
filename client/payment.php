<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

$booking_id = $_GET['booking_id'];

$q = mysqli_query($con,"
SELECT bookings.*, gowns.name, gowns.base_price
FROM bookings
INNER JOIN gowns ON gowns.id = bookings.gown_id
WHERE bookings.id='$booking_id'
");

$r = mysqli_fetch_assoc($q);
?>

<!DOCTYPE html>
<html>
<head>
<title>Payment</title>

<style>
body{font-family:Arial;background:#f5f5f5;}
.container{width:500px;margin:auto;background:white;padding:20px;margin-top:50px;}
.qr{width:100%;height:300px;border:2px dashed #ccc;display:flex;align-items:center;justify-content:center;}
</style>
</head>

<body>

<div class="container">

<h2>GCash Payment</h2>

<h3><?php echo $r['name']; ?></h3>

<p>Amount: ₱<?php echo number_format($r['base_price'],2); ?></p>

<!-- QR CODE BOX -->
<div class="qr">
    <img src="gcash_qr.png" style="max-width:100%;max-height:100%;">
</div>

<p>Send payment to QR above then upload receipt below.</p>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="receipt" required>

<br><br>

<button name="upload">Submit Receipt</button>

</form>

</div>

</body>
</html>

<?php

if(isset($_POST['upload'])){

    $file = $_FILES['receipt']['name'];
    $tmp = $_FILES['receipt']['tmp_name'];

    $path = "receipts/" . time() . "_" . $file;

    move_uploaded_file($tmp,$path);

    mysqli_query($con,"
        UPDATE bookings
        SET receipt_image='$path',
            payment_status='pending_verification'
        WHERE id='$booking_id'
    ");

    echo "<script>alert('Receipt sent for verification');</script>";
    echo "<script>window.location='dashboard.php';</script>";
}
?>