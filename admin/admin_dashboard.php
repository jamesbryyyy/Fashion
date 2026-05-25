<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");
include("../config.php");/*
COUNTS
*/
$pending = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) as c FROM bookings WHERE status='pending'"))['c'];
$booked = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) as c FROM bookings WHERE status='booked'"))['c'];
$finished = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) as c FROM bookings WHERE status='finished'"))['c'];
$cancelled = mysqli_fetch_assoc(mysqli_query($con,"SELECT COUNT(*) as c FROM bookings WHERE status='cancelled'"))['c'];

/*
REVENUE
*/
$revenue = mysqli_fetch_assoc(mysqli_query($con,"
SELECT SUM(g.base_price) as total
FROM bookings b
INNER JOIN gowns g ON g.id=b.gown_id
WHERE b.payment_status='paid'
"))['total'];


if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
?>

<h1>Admin Dashboard</h1>

<div style="display:flex;gap:10px;">

<div>Pending: <?php echo $pending; ?></div>
<div>Booked: <?php echo $booked; ?></div>
<div>Finished: <?php echo $finished; ?></div>
<div>Cancelled: <?php echo $cancelled; ?></div>

</div>

<h3>Total Revenue: ₱<?php echo number_format($revenue ?? 0,2); ?></h3>

<hr>

<a href="admin_panel.php">Bookings</a> |
<a href="admin_payments.php">Payments</a> |
<a href="gowns_create.php">Gowns</a>
<a href="reports.php">Report</a>
<a href="admin_logout.php">logout</a>