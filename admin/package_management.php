<?php
$con = mysqli_connect("localhost","root","","fashion");
include('auth_check.php');
?>

<h2>Package Management</h2>

<form method="POST" enctype="multipart/form-data">

<label>Package Name</label>
<br>
<input type="text" name="package_name" required>
<br><br>

<label>Select Gown</label>
<br>

<select name="gown_id" required>

<?php

$g = mysqli_query($con,"SELECT * FROM gowns");

while($gr=mysqli_fetch_assoc($g)){

    echo "
    <option value='".$gr['id']."'>
        ".$gr['name']."
    </option>
    ";

}

?>

</select>

<br><br>

<label>Select Makeup Artist</label>
<br>

<select name="makeup_artist_id" required>

<?php

$m = mysqli_query($con,"
    SELECT * FROM makeup_artists
");

while($mr=mysqli_fetch_assoc($m)){

    echo "
    <option value='".$mr['id']."'>
        ".$mr['name']."
    </option>
    ";

}

?>

</select>

<br><br>

<label>Package Price</label>
<br>

<input
type="number"
step="0.01"
name="package_price"
required>

<br><br>

<label>Description</label>
<br>

<textarea name="description"></textarea>

<br><br>

<label>Image</label>
<br>

<input type="file" name="image" required>

<br><br>

<button name="save">
Save Package
</button>

</form>

<?php

if(isset($_POST['save'])){

    $package_name = $_POST['package_name'];
    $gown_id = $_POST['gown_id'];
    $makeup_artist_id = $_POST['makeup_artist_id'];
    $package_price = $_POST['package_price'];
    $description = $_POST['description'];

    $image = "../asset/" . basename($_FILES['image']['name']);

    move_uploaded_file(
        $_FILES['image']['tmp_name'],
        $image
    );

    mysqli_query($con,"

        INSERT INTO packages
        (
            package_name,
            gown_id,
            makeup_artist_id,
            package_price,
            description,
            image
        )

        VALUES
        (
            '$package_name',
            '$gown_id',
            '$makeup_artist_id',
            '$package_price',
            '$description',
            '$image'
        )

    ");

    echo "Package Saved";
}
?>