<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

$id = $_GET['id'];

/*
GET GOWN
*/
$q = mysqli_query($con,"SELECT * FROM gowns WHERE id='$id'");
$r = mysqli_fetch_assoc($q);

/*
HANDLE BOOKING
*/
if(isset($_POST['book'])){

    // CHECK LOGIN FIRST
    if(!isset($_SESSION['client_id'])){
        header("Location: login.php?redirect=gown_details.php?id=$id");
        exit();
    }

    $df = $_POST['date_from'];
    $dt = $_POST['date_to'];
    $client = $_SESSION['client_id'];

    // OVERLAP CHECK (approved only)
    $check = mysqli_query($con,"
        SELECT * FROM bookings
        WHERE gown_id='$id'
        AND status='approved'
        AND (
            ('$df' BETWEEN date_from AND date_to)
            OR ('$dt' BETWEEN date_from AND date_to)
            OR (date_from BETWEEN '$df' AND '$dt')
        )
    ");

    if(mysqli_num_rows($check) > 0){
        echo "❌ Dates not available";
    } else {

        mysqli_query($con,"
            INSERT INTO bookings
            (gown_id,item_id,client_id,date_from,date_to,status)
            VALUES
            ('$id',1,'$client','$df','$dt','pending')
        ");

        echo "✅ Sent for admin approval";
    }
}
?>

<h2><?php echo $r['name']; ?></h2>

<img src="<?php echo $r['image']; ?>" width="200">

<p><?php echo $r['description']; ?></p>

<p>₱<?php echo $r['base_price']; ?></p>

<form method="POST">

<input type="date" name="date_from" required>
<input type="date" name="date_to" required>

<button name="book">Book</button>

</form>