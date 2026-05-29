<?php
session_start();

$con = mysqli_connect("localhost","root","","fashion");

$q = mysqli_query($con,"
    SELECT *
    FROM makeup_artists
");
?>

<!DOCTYPE html>
<html>

<head>

<style>

.card{
    width:250px;
    padding:15px;
    background:#fff;
    margin:10px;
    float:left;
}

.card img{
    width:100%;
    height:250px;
    object-fit:cover;
}

.btn{
    background:black;
    color:white;
    padding:10px;
    text-decoration:none;
    display:block;
    text-align:center;
}

</style>

</head>

<body>

<h2>Makeup Artists</h2>

<?php while($r=mysqli_fetch_assoc($q)){ ?>

<div class="card">

<img src="<?php echo $r['image']; ?>">

<h3><?php echo $r['name']; ?></h3>

<p><?php echo $r['specialty']; ?></p>

<p>₱<?php echo number_format($r['price'],2); ?></p>

<a
class="btn"
href="makeup_details.php?id=<?php echo $r['id']; ?>"
>
Book Makeup
</a>

</div>

<?php } ?>

</body>

</html>