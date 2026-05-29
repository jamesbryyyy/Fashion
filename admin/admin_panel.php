<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");
include("../config.php");
include('auth_check.php');

/*
APPROVE / REJECT GOWN BOOKINGS
*/
if(isset($_POST['approve_gown'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET status='approved'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['reject_gown'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET status='rejected'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/*
APPROVE / REJECT MAKEUP BOOKINGS
*/
if(isset($_POST['approve_makeup'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE makeup_bookings
        SET status='approved'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['reject_makeup'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE makeup_bookings
        SET status='rejected'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

/*
APPROVE / REJECT PACKAGE BOOKINGS
*/
if(isset($_POST['approve_package'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE package_bookings
        SET status='approved'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['reject_package'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE package_bookings
        SET status='rejected'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<h2>Admin Booking Management</h2>

<!-- ================= GOWN BOOKINGS ================= -->
<h3>Gown Bookings</h3>

<table border="1" cellpadding="10">

<tr>
    <th>Gown</th>
    <th>Date From</th>
    <th>Date To</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php
$q = mysqli_query($con,"
SELECT bookings.*, gowns.name
FROM bookings
INNER JOIN gowns ON gowns.id = bookings.gown_id
ORDER BY bookings.id DESC
");

while($r=mysqli_fetch_assoc($q)){
?>

<tr>

<td><?php echo $r['name']; ?></td>
<td><?php echo $r['date_from']; ?></td>
<td><?php echo $r['date_to']; ?></td>
<td><?php echo $r['status']; ?></td>

<td>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    <button name="approve_gown">Approve</button>
    <button name="reject_gown">Reject</button>
</form>
</td>

</tr>

<?php } ?>

</table>

<hr>

<!-- ================= MAKEUP BOOKINGS ================= -->
<h3>Makeup Bookings</h3>

<table border="1" cellpadding="10">

<tr>
    <th>Artist ID</th>
    <th>Date</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php
$q = mysqli_query($con,"
SELECT *
FROM makeup_bookings
ORDER BY id DESC
");

while($r=mysqli_fetch_assoc($q)){
?>

<tr>

<td><?php echo $r['makeup_artist_id']; ?></td>
<td><?php echo $r['booking_date']; ?></td>
<td><?php echo $r['booking_time']; ?></td>
<td><?php echo $r['status']; ?></td>

<td>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    <button name="approve_makeup">Approve</button>
    <button name="reject_makeup">Reject</button>
</form>
</td>

</tr>

<?php } ?>

</table>

<hr>

<!-- ================= PACKAGE BOOKINGS ================= -->
<h3>Package Bookings</h3>

<table border="1" cellpadding="10">

<tr>
    <th>Package ID</th>
    <th>Gown ID</th>
    <th>Makeup ID</th>
    <th>Date From</th>
    <th>Date To</th>
    <th>Time</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php
$q = mysqli_query($con,"
SELECT *
FROM package_bookings
ORDER BY id DESC
");

while($r=mysqli_fetch_assoc($q)){
?>

<tr>

<td><?php echo $r['package_id']; ?></td>
<td><?php echo $r['gown_id']; ?></td>
<td><?php echo $r['makeup_artist_id']; ?></td>
<td><?php echo $r['date_from']; ?></td>
<td><?php echo $r['date_to']; ?></td>
<td><?php echo $r['makeup_time']; ?></td>
<td><?php echo $r['status']; ?></td>

<td>
<form method="POST">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    <button name="approve_package">Approve</button>
    <button name="reject_package">Reject</button>
</form>
</td>

</tr>

<?php } ?>

</table>