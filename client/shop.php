<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");
include("../config.php");

function getBookedDays($con,$gown_id){

    $days = [];

    $q = mysqli_query($con,"
        SELECT date_from, date_to
        FROM bookings
        WHERE gown_id='$gown_id'
        AND status='approved'
    ");

    while($r=mysqli_fetch_assoc($q)){
        $start = strtotime($r['date_from']);
        $end = strtotime($r['date_to']);

        for($i=$start;$i<=$end;$i+=86400){
            $days[] = date("Y-m-d",$i);
        }
    }

    return $days;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Shop</title>

<style>
body{font-family:Arial;background:#f5f5f5;}
.container{width:1200px;margin:auto;}
.card{width:280px;background:#fff;padding:15px;margin:10px;float:left;}
.card img{width:100%;height:300px;object-fit:cover;}
.btn{background:#000;color:#fff;padding:8px;text-decoration:none;}
.red{background:red;color:#fff;}
table{font-size:10px;}
.clear{clear:both;}
</style>
</head>

<body>

<div class="container">

<h2>Gown Shop</h2>

<?php

$q = mysqli_query($con,"
SELECT
    gowns.id AS gown_id,
    gowns.name,
    gowns.description,
    gowns.image,
    gowns.base_price,
    gowns.category,

    gown_items.size,
    gown_items.color,
    gown_items.status
FROM gowns
LEFT JOIN gown_items
ON gowns.id = gown_items.gown_id
");

while($r=mysqli_fetch_assoc($q)){

    $booked = getBookedDays($con,$r['gown_id']);

    $month = date("m");
    $year = date("Y");
    $days = cal_days_in_month(CAL_GREGORIAN,$month,$year);

?>

<div class="card">

<img src="<?php echo $r['image']; ?>">

<h3><?php echo $r['name']; ?></h3>

<p>₱<?php echo $r['base_price']; ?></p>

<!-- CALENDAR -->
<table border="1">
<tr><th colspan="7"><?php echo date("F Y"); ?></th></tr>
<tr>

<?php
for($i=1;$i<=$days;$i++){

    $date = date("Y-m-d",strtotime("$year-$month-$i"));

    if(in_array($date,$booked)){
        echo "<td class='red'>$i</td>";
    }else{
        echo "<td>$i</td>";
    }

    if($i%7==0) echo "</tr><tr>";
}
?>

</tr>
</table>

<br>

<a class="btn" href="gown_details.php?id=<?php echo $r['gown_id']; ?>">
Book Now
</a>

</div>

<?php } ?>

<div class="clear"></div>

</div>

</body>
</html>