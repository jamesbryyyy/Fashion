<?php
$con = mysqli_connect("localhost", "root", "", "fashion");
include('auth_check.php');

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

?>

<h3>Gown Management</h3>

<!-- ADD FORM -->
<form method="POST" enctype="multipart/form-data">

<table border="1" cellpadding="10">

<tr>
    <td>Gown Code</td>
    <td><input type="text" name="gown_code" required></td>
</tr>

<tr>
    <td>Name</td>
    <td><input type="text" name="name" required></td>
</tr>

<tr>
    <td>Description</td>
    <td><input type="text" name="description"></td>
</tr>

<tr>
    <td>Category</td>
    <td><input type="text" name="category"></td>
</tr>

<tr>
    <td>Color</td>
    <td><input type="text" name="color"></td>
</tr>

<tr>
    <td>Size</td>
    <td><input type="text" name="size"></td>
</tr>

<tr>
    <td>Base Price</td>
    <td><input type="number" name="base_price" step="0.01"></td>
</tr>

<tr>
    <td>Image</td>
    <td><input type="file" name="image" required></td>
</tr>

<tr>
    <td colspan="2">
        <input type="submit" name="btnsubmit" value="Add Gown">
    </td>
</tr>

</table>

</form>

<?php

// ADD GOWN
if (isset($_POST["btnsubmit"])) {

    $gown_code = $_POST["gown_code"];
    $name = $_POST["name"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $color = $_POST["color"];
    $size = $_POST["size"];
    $base_price = $_POST["base_price"];

    $image = "../asset/" . basename($_FILES["image"]["name"]);

    move_uploaded_file($_FILES["image"]["tmp_name"], $image);

    // INSERT GOWN
    mysqli_query($con, "
        INSERT INTO gowns
        (
            name,
            description,
            image,
            base_price,
            category,
            created_at
        )
        VALUES
        (
            '$name',
            '$description',
            '$image',
            '$base_price',
            '$category',
            NOW()
        )
    ");

    $gown_id = mysqli_insert_id($con);

    // INSERT ITEM
    mysqli_query($con, "
        INSERT INTO gown_items
        (
            gown_id,
            size,
            sku,
            color,
            status,
            created_at
        )
        VALUES
        (
            '$gown_id',
            '$size',
            '$gown_code',
            '$color',
            'available',
            NOW()
        )
    ");

    echo "<script>alert('Gown Added');</script>";
    echo "<script>window.location='';</script>";
}
?>

<hr>

<h3>Gown List</h3>

<table border="1" cellpadding="10">

<tr>
    <th>Image</th>
    <th>SKU</th>
    <th>Name</th>
    <th>Description</th>
    <th>Category</th>
    <th>Color</th>
    <th>Size</th>
    <th>Base Price</th>
    <th>Status</th>
    <th>Date From</th>
    <th>Date To</th>
    <th>Action</th>
</tr>

<?php

$q = mysqli_query($con, "

    SELECT

        gowns.id AS gown_id,
        gowns.name,
        gowns.description,
        gowns.image,
        gowns.base_price,
        gowns.category,

        gown_items.id AS item_id,
        gown_items.size,
        gown_items.sku,
        gown_items.color,
        gown_items.status,
        gown_items.date_from,
        gown_items.date_to

    FROM gowns

    LEFT JOIN gown_items
    ON gowns.id = gown_items.gown_id

    ORDER BY gowns.id DESC

");

while ($r = mysqli_fetch_array($q)) {

?>

<tr>

<form method="POST">

    <td>
        <img src="<?php echo $r["image"]; ?>" width="80">
    </td>

    <td><?php echo $r["sku"]; ?></td>

    <td><?php echo $r["name"]; ?></td>

    <td><?php echo $r["description"]; ?></td>

    <td><?php echo $r["category"]; ?></td>

    <td><?php echo $r["color"]; ?></td>

    <td><?php echo $r["size"]; ?></td>

    <td>
        ₱<?php echo number_format($r["base_price"], 2); ?>
    </td>

    <td>

        <input type="hidden" name="item_id"
        value="<?php echo $r["item_id"]; ?>">

        <select name="status">

            <option value="available"
            <?php if($r["status"]=="available") echo "selected"; ?>>
                Available
            </option>

            <option value="cleaning"
            <?php if($r["status"]=="cleaning") echo "selected"; ?>>
                Cleaning
            </option>

            <option value="maintenance"
            <?php if($r["status"]=="maintenance") echo "selected"; ?>>
                Maintenance
            </option>

            <option value="retired"
            <?php if($r["status"]=="retired") echo "selected"; ?>>
                Retired
            </option>

            <option value="rented"
            <?php if($r["status"]=="rented") echo "selected"; ?>>
                Rented
            </option>

        </select>

    </td>

    <td>

        <input
            type="date"
            name="date_from"
            value="<?php echo $r["date_from"]; ?>"
        >

    </td>

    <td>

        <input
            type="date"
            name="date_to"
            value="<?php echo $r["date_to"]; ?>"
        >

    </td>

    <td>

        <button type="submit" name="btnupdate">
            Update
        </button>

    </td>

</form>

</tr>

<?php } ?>

</table>

<?php

// UPDATE STATUS
if(isset($_POST["btnupdate"])){

    $item_id = $_POST["item_id"];
    $status = $_POST["status"];
    $date_from = $_POST["date_from"];
    $date_to = $_POST["date_to"];

    // AVAILABLE = REMOVE BLOCK DATES
    if($status == "available"){

        mysqli_query($con, "

            UPDATE gown_items

            SET
                status='available',
                date_from=NULL,
                date_to=NULL

            WHERE id='$item_id'

        ");

    } else {

        // REQUIRE DATES
        if(empty($date_from) || empty($date_to)){

            echo "<script>alert('Please select Date From and Date To');</script>";

        } else {

            mysqli_query($con, "

                UPDATE gown_items

                SET
                    status='$status',
                    date_from='$date_from',
                    date_to='$date_to'

                WHERE id='$item_id'

            ");

            echo "<script>alert('Updated Successfully');</script>";
            echo "<script>window.location='';</script>";
        }
    }
}
?>

<hr>

<h3>Booking Validation Example</h3>

<?php
/*
USE THIS IN YOUR BOOKING PAGE

$item_id = 1;
$rent_from = '2026-05-28';
$rent_to = '2026-05-30';

$check = mysqli_query($con, "

    SELECT *

    FROM gown_items

    WHERE id='$item_id'

    AND (
        status='maintenance'
        OR status='cleaning'
        OR status='retired'
        OR status='rented'
    )

    AND (

        '$rent_from' BETWEEN date_from AND date_to

        OR

        '$rent_to' BETWEEN date_from AND date_to

        OR

        date_from BETWEEN '$rent_from' AND '$rent_to'

    )

");

if(mysqli_num_rows($check) > 0){

    echo "This gown is unavailable.";

} else {

    echo "Gown is available.";

}
*/
?>