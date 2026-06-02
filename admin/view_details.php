<?php
$con = mysqli_connect("localhost", "root", "", "fashion");
include('auth_check.php');

if(!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: admin_panel.php");
    exit();
}

$id = mysqli_real_escape_string($con, $_GET['id']);
$type = $_GET['type'];

// SELECTING DATA BASED ON TYPE (Joins the user_profile table using client_id)
if($type == 'gown') {
    $sql = "SELECT b.*, u.*, g.name as item_name 
            FROM bookings b 
            JOIN user_profile u ON b.client_id = u.user_id 
            JOIN gowns g ON b.gown_id = g.id 
            WHERE b.id = '$id'";
    $title = "Gown Rental Details";
} elseif($type == 'makeup') {
    $sql = "SELECT mb.*, u.*, ma.name as item_name 
            FROM makeup_bookings mb 
            JOIN user_profile u ON mb.client_id = u.user_id 
            JOIN makeup_artists ma ON mb.makeup_artist_id = ma.id 
            WHERE mb.id = '$id'";
    $title = "Makeup Artist Details";
} else {
    $sql = "SELECT pb.*, u.*, p.package_name as item_name 
            FROM package_bookings pb 
            JOIN user_profile u ON pb.client_id = u.user_id 
            JOIN packages p ON pb.package_id = p.id 
            WHERE pb.id = '$id'";
    $title = "Package Details";
}

$result = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($result);

if(!$data) { die("Record not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f3f4f6; padding: 40px; }
        .card { background: white; max-width: 600px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .back-link { text-decoration: none; color: #3b82f6; font-size: 0.9rem; font-weight: 600; margin-bottom: 20px; display: block; }
        h2 { color: #1e3a8a; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .detail-row { margin-bottom: 15px; border-bottom: 1px solid #f9fafb; padding-bottom: 10px; }
        .label { font-size: 0.75rem; color: #6b7280; text-transform: uppercase; font-weight: 600; margin: 0; }
        .value { font-size: 1.1rem; color: #1f2937; margin: 5px 0 0 0; }
        .status { display: inline-block; padding: 4px 12px; background: #eee; border-radius: 20px; font-weight: 600; font-size: 0.8rem; }
    </style>
</head>
<body>

<div class="card">
    <a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    <h2><?php echo $title; ?></h2>

    <div class="detail-row">
        <p class="label">Customer Name</p>
        <p class="value"><?php echo $data['firstname'] . " " . $data['middlename'] . " " . $data['lastname']; ?></p>
    </div>

    <div class="detail-row">
        <p class="label">Contact Number</p>
        <p class="value"><?php echo $data['contactnumber']; ?></p>
    </div>

    <div class="detail-row">
        <p class="label">Shipping/Booking Address</p>
        <p class="value"><?php echo $data['address']; ?></p>
    </div>

    <div class="detail-row">
        <p class="label">Age</p>
        <p class="value"><?php echo $data['age']; ?></p>
    </div>

    <div class="detail-row" style="background: #f0f7ff; padding: 15px; border-radius: 8px;">
        <p class="label">Ordered Service</p>
        <p class="value" style="color: #1e3a8a; font-weight: 600;"><?php echo $data['item_name']; ?></p>
        
        <p class="label" style="margin-top:15px;">Status</p>
        <p class="status"><?php echo strtoupper($data['status']); ?></p>
    </div>

    <?php if($type != 'makeup'): ?>
    <div class="detail-row">
        <p class="label">Rental Dates</p>
        <p class="value"><?php echo date('F d, Y', strtotime($data['date_from'])); ?> to <?php echo date('F d, Y', strtotime($data['date_to'])); ?></p>
    </div>
    <?php else: ?>
    <div class="detail-row">
        <p class="label">Appointment</p>
        <p class="value"><?php echo date('F d, Y', strtotime($data['booking_date'])); ?> @ <?php echo $data['booking_time']; ?></p>
    </div>
    <?php endif; ?>
</div>

</body>
</html>