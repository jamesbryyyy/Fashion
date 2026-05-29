<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

$id = $_GET['id'];

/*
GET GOWN
*/
$q = mysqli_query($con,"
    SELECT * FROM gowns
    WHERE id='$id'
");

$r = mysqli_fetch_assoc($q);

/*
HANDLE BOOKING
*/
if(isset($_POST['book'])){

    // CHECK LOGIN
    if(!isset($_SESSION['client_id'])){

        header("Location: login.php?redirect=gown_details.php?id=$id");
        exit();

    }

    $df = $_POST['date_from'];
    $dt = $_POST['date_to'];

    $client = $_SESSION['client_id'];

    // VALIDATE DATES
    if($df > $dt){

        echo "❌ Invalid date range";

    } else {

        /*
        CHECK APPROVED BOOKINGS
        */
        $check_booking = mysqli_query($con,"

            SELECT *

            FROM bookings

            WHERE gown_id='$id'

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
        CHECK BLOCKED GOWN STATUS
        */
        $check_status = mysqli_query($con,"

            SELECT *

            FROM gown_items

            WHERE gown_id='$id'

            AND (

                status='maintenance'

                OR status='cleaning'

                OR status='retired'

                OR status='rented'

            )

            AND (

                ('$df' BETWEEN date_from AND date_to)

                OR

                ('$dt' BETWEEN date_from AND date_to)

                OR

                (date_from BETWEEN '$df' AND '$dt')

            )

        ");

        /*
        IF ALREADY BOOKED
        */
        if(mysqli_num_rows($check_booking) > 0){

            echo "❌ Dates already booked";

        }

        /*
        IF BLOCKED
        */
        else if(mysqli_num_rows($check_status) > 0){

            echo "❌ Gown unavailable due to maintenance/cleaning";

        }

        /*
        SAVE BOOKING
        */
        else{

            mysqli_query($con,"

                INSERT INTO bookings
                (
                    gown_id,
                    item_id,
                    client_id,
                    date_from,
                    date_to,
                    status
                )

                VALUES
                (
                    '$id',
                    1,
                    '$client',
                    '$df',
                    '$dt',
                    'pending'
                )

            ");

            echo "✅ Sent for admin approval";

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Gown Details</title>

<style>

body{
    font-family:Arial;
    background:#f5f5f5;
    padding:30px;
}

.box{
    background:#fff;
    width:500px;
    margin:auto;
    padding:20px;
    border-radius:10px;
}

img{
    width:100%;
    height:500px;
    object-fit:cover;
}

input{
    width:100%;
    padding:10px;
    margin-top:10px;
}

button{
    width:100%;
    padding:10px;
    margin-top:10px;
    background:black;
    color:white;
    border:none;
}

</style>

</head>

<body>

<div class="box">

<h2><?php echo $r['name']; ?></h2>

<img src="<?php echo $r['image']; ?>">

<p>
<?php echo $r['description']; ?>
</p>

<h3>
₱<?php echo number_format($r['base_price'],2); ?>
</h3>

<form method="POST">

<label>Date From</label>

<input
    type="date"
    name="date_from"
    required
>

<label>Date To</label>

<input
    type="date"
    name="date_to"
    required
>

<button name="book">
    Book Now
</button>

</form>

</div>

</body>

</html>