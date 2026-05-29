<?php
$con = mysqli_connect("localhost","root","","fashion");
include('auth_check.php');
?>

<h2>Makeup Artist Management</h2>

<form method="POST" enctype="multipart/form-data">

<input type="text" name="name" placeholder="Artist Name" required>
<br><br>

<input type="text" name="specialty" placeholder="Specialty">
<br><br>

<input type="text" name="contact" placeholder="Contact">
<br><br>

<input type="number" step="0.01" name="price" placeholder="Price">
<br><br>

<input type="file" name="image" required>
<br><br>

<button name="save">Save</button>

</form>

<?php

if(isset($_POST['save'])){

    $name = $_POST['name'];
    $specialty = $_POST['specialty'];
    $contact = $_POST['contact'];
    $price = $_POST['price'];

    $image = "../asset/" . basename($_FILES['image']['name']);

    move_uploaded_file($_FILES['image']['tmp_name'],$image);

    mysqli_query($con,"
        INSERT INTO makeup_artists
        (
            name,
            specialty,
            contact,
            price,
            image
        )
        VALUES
        (
            '$name',
            '$specialty',
            '$contact',
            '$price',
            '$image'
        )
    ");

    echo "Saved";
}
?>