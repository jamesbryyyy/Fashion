<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");
include 'config.php';


if(isset($_POST['approve'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET status='approved'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['reject'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET status='rejected'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>

<h2>Admin Booking Approval</h2>

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

    <button type="submit" name="approve">Approve</button>
    <button type="submit" name="reject">Reject</button>
</form>

</td>

</tr>

<?php } ?>

</table>