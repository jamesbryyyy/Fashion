<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");
include("../config.php");

if(!isset($_SESSION["client_id"])){
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION["client_id"];

/*
HANDLE CANCEL
*/
if(isset($_POST['cancel'])){
    $id = $_POST['id'];

    mysqli_query($con,"
        DELETE FROM bookings
        WHERE id='$id'
        AND client_id='$client_id'
        AND status='pending'
    ");

    header("Location: dashboard.php");
    exit();
}

/*
HANDLE EDIT
*/
if(isset($_POST['update'])){
    $id = $_POST['id'];
    $from = $_POST['date_from'];
    $to = $_POST['date_to'];

    // check overlap (approved only)
    $check = mysqli_query($con,"
        SELECT * FROM bookings
        WHERE gown_id = (SELECT gown_id FROM bookings WHERE id='$id')
        AND status='approved'
        AND (
            ('$from' BETWEEN date_from AND date_to)
            OR ('$to' BETWEEN date_from AND date_to)
            OR (date_from BETWEEN '$from' AND '$to')
        )
    ");

    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('❌ Dates not available');</script>";
    } else {

        mysqli_query($con,"
            UPDATE bookings
            SET date_from='$from', date_to='$to'
            WHERE id='$id'
            AND client_id='$client_id'
            AND status='pending'
        ");

        header("Location: dashboard.php");
        exit();
    }
}

/*
FETCH BOOKINGS
*/
$q = mysqli_query($con,"
SELECT 
    bookings.*,
    gowns.name,
    gowns.image,
    gowns.base_price
FROM bookings
INNER JOIN gowns ON gowns.id = bookings.gown_id
WHERE bookings.client_id='$client_id'
ORDER BY bookings.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>My Dashboard</title>

<style>
body{font-family:Arial;background:#f5f5f5;}
.container{width:1000px;margin:auto;}

.card{
    background:white;
    padding:15px;
    margin:10px 0;
    border-radius:10px;
    display:flex;
    gap:15px;
}

img{
    width:120px;
    height:120px;
    object-fit:cover;
    border-radius:10px;
}

.status{
    padding:5px 10px;
    color:#fff;
    border-radius:5px;
}

.pending{background:orange;}
.approved{background:green;}
.rejected{background:red;}

input[type=date]{
    padding:5px;
}

button{
    padding:5px 10px;
    margin-top:5px;
}

.actions{
    margin-top:10px;
}

</style>
</head>

<body>

<div class="container">

<h2>My Bookings</h2>

<a href="shop.php">← Back to Shop</a>
<a href="logout.php">logout</a>

<br><br>

<?php while($r=mysqli_fetch_assoc($q)){ ?>

<div class="card">

    <img src="<?php echo $r['image']; ?>">

    <div>

        <h3><?php echo $r['name']; ?></h3>

        <p>₱<?php echo number_format($r['base_price'],2); ?></p>

        <p>
            <b>From:</b> <?php echo $r['date_from']; ?> <br>
            <b>To:</b> <?php echo $r['date_to']; ?>
        </p>

        <!-- STATUS -->
        <span class="status <?php echo $r['status']; ?>">
            <?php echo strtoupper($r['status']); ?>
        </span>

        <br><br>

        <!-- PAYMENT STATUS -->
        <p>
            Payment:
            <span style="color:
            <?php
                if($r['payment_status']=='paid') echo 'green';
                else if($r['payment_status']=='pending_verification') echo 'orange';
                else echo 'red';
            ?>
            ">
            <?php echo strtoupper($r['payment_status'] ?? 'UNPAID'); ?>
            </span>
        </p>

        <!-- EDIT + CANCEL (ONLY PENDING) -->
        <?php if($r['status']=='pending'){ ?>

            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                <div class="actions">

                    <label>From:</label>
                    <input type="date" name="date_from" value="<?php echo $r['date_from']; ?>">

                    <label>To:</label>
                    <input type="date" name="date_to" value="<?php echo $r['date_to']; ?>">

                    <br>

                    <button type="submit" name="update">Update</button>
                    <button type="submit" name="cancel" style="background:red;color:white;">
                        Cancel
                    </button>

                </div>
            </form>

        <?php } ?>

        <!-- PAY BUTTON (ONLY APPROVED) -->
        <?php if($r['status']=='approved' && $r['payment_status']!='paid'){ ?>

            <br>

            <a href="payment.php?booking_id=<?php echo $r['id']; ?>"
               style="background:blue;color:white;padding:5px 10px;text-decoration:none;">
               Pay Now (GCash)
            </a>

        <?php } ?>

    </div>

</div>

<?php } ?>

</div>

</body>
</html>