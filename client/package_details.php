<?php
session_start();

$con = mysqli_connect("localhost","root","","fashion");

$id = $_GET['id'];

$q = mysqli_query($con,"

    SELECT

        packages.*,

        gowns.name AS gown_name,
        gowns.image AS gown_image,

        makeup_artists.name AS makeup_name

    FROM packages

    LEFT JOIN gowns
    ON packages.gown_id = gowns.id

    LEFT JOIN makeup_artists
    ON packages.makeup_artist_id = makeup_artists.id

    WHERE packages.id='$id'

");

$r = mysqli_fetch_assoc($q);

if(isset($_POST['book'])){

    if(!isset($_SESSION['client_id'])){

        header("Location: login.php");
        exit();

    }

    $client = $_SESSION['client_id'];

    $df = $_POST['date_from'];
    $dt = $_POST['date_to'];

    $time = $_POST['makeup_time'];

    /*
    CHECK GOWN AVAILABILITY
    */
    $check_gown = mysqli_query($con,"

        SELECT *

        FROM bookings

        WHERE gown_id='".$r['gown_id']."'

        AND status='approved'

        AND (

            ('$df' BETWEEN date_from AND date_to)

            OR

            ('$dt' BETWEEN date_from AND date_to)

            OR

            (date_from BETWEEN '$df' AND '$dt')

        )

    ");

    /*
    CHECK MAKEUP AVAILABILITY
    */
    $check_makeup = mysqli_query($con,"

        SELECT *

        FROM makeup_bookings

        WHERE makeup_artist_id='".$r['makeup_artist_id']."'

        AND booking_date='$df'

        AND booking_time='$time'

        AND status='approved'

    ");

    if(mysqli_num_rows($check_gown)>0){

        echo "❌ Gown unavailable";

    }
    else if(mysqli_num_rows($check_makeup)>0){

        echo "❌ Makeup artist unavailable";

    }
    else{

        mysqli_query($con,"

            INSERT INTO package_bookings
            (
                package_id,
                gown_id,
                makeup_artist_id,
                client_id,
                date_from,
                date_to,
                makeup_time,
                status
            )

            VALUES
            (
                '$id',
                '".$r['gown_id']."',
                '".$r['makeup_artist_id']."',
                '$client',
                '$df',
                '$dt',
                '$time',
                'pending'
            )

        ");

        echo "✅ Package booking submitted";
    }
}
?>

<h2>
<?php echo $r['package_name']; ?>
</h2>

<img
src="<?php echo $r['image']; ?>"
width="300">

<p>
Gown:
<?php echo $r['gown_name']; ?>
</p>

<p>
Makeup:
<?php echo $r['makeup_name']; ?>
</p>

<p>
₱<?php echo number_format($r['package_price'],2); ?>
</p>

<form method="POST">

<label>Date From</label>
<br>

<input
type="date"
name="date_from"
required>

<br><br>

<label>Date To</label>
<br>

<input
type="date"
name="date_to"
required>

<br><br>

<label>Makeup Time</label>
<br>

<select name="makeup_time" required>

<option>8:00 AM</option>
<option>9:00 AM</option>
<option>10:00 AM</option>
<option>11:00 AM</option>
<option>1:00 PM</option>
<option>2:00 PM</option>

</select>

<br><br>

<button name="book">
Book Package
</button>

</form>