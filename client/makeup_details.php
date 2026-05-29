<?php
session_start();

$con = mysqli_connect("localhost","root","","fashion");

$id = $_GET['id'];

$q = mysqli_query($con,"
    SELECT *
    FROM makeup_artists
    WHERE id='$id'
");

$r = mysqli_fetch_assoc($q);

if(isset($_POST['book'])){

    if(!isset($_SESSION['client_id'])){

        header("Location: login.php");
        exit();

    }

    $client = $_SESSION['client_id'];

    $date = $_POST['booking_date'];
    $time = $_POST['booking_time'];

    /*
    CHECK IF ALREADY BOOKED
    */
    $check = mysqli_query($con,"
        SELECT *
        FROM makeup_bookings
        WHERE makeup_artist_id='$id'
        AND booking_date='$date'
        AND booking_time='$time'
        AND status='approved'
    ");

    if(mysqli_num_rows($check)>0){

        echo "❌ Artist already booked";

    }else{

        mysqli_query($con,"
            INSERT INTO makeup_bookings
            (
                makeup_artist_id,
                client_id,
                booking_date,
                booking_time,
                status
            )
            VALUES
            (
                '$id',
                '$client',
                '$date',
                '$time',
                'pending'
            )
        ");

        echo "✅ Booking Sent";
    }
}
?>

<h2><?php echo $r['name']; ?></h2>

<img src="<?php echo $r['image']; ?>" width="300">

<p><?php echo $r['specialty']; ?></p>

<p>₱<?php echo number_format($r['price'],2); ?></p>

<form method="POST">

<input
type="date"
name="booking_date"
required>

<br><br>

<select name="booking_time" required>

<option value="">Select Time</option>

<option>8:00 AM</option>
<option>9:00 AM</option>
<option>10:00 AM</option>
<option>11:00 AM</option>
<option>1:00 PM</option>
<option>2:00 PM</option>
<option>3:00 PM</option>
<option>4:00 PM</option>

</select>

<br><br>

<button name="book">
Book Makeup
</button>

</form>