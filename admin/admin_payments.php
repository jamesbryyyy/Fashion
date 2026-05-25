<?php
$con = mysqli_connect("localhost","root","","fashion");

if(isset($_POST['approve'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET payment_status='paid'
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

if(isset($_POST['reject'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        UPDATE bookings
        SET payment_status='unpaid',
            receipt_image=NULL
        WHERE id='$id'
    ");

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$q = mysqli_query($con,"
SELECT bookings.*, gowns.name
FROM bookings
INNER JOIN gowns ON gowns.id = bookings.gown_id
WHERE receipt_image IS NOT NULL
ORDER BY bookings.id DESC
");
?>

<h2>Payment Verification</h2>

<table border="1" cellpadding="10">

<tr>
<th>Gown</th>
<th>Receipt</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php while($r=mysqli_fetch_assoc($q)){ ?>

<tr>

<td><?php echo $r['name']; ?></td>

<td>
    <img src="<?php echo $r['receipt_image']; ?>" width="100">
</td>

<td><?php echo $r['payment_status']; ?></td>

<td>

<form method="POST">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

    <button type="submit" name="approve">Mark Paid</button>
    <button type="submit" name="reject">Reject</button>
</form>

</td>

</tr>

<?php } ?>

</table>