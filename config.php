<?php
$con = mysqli_connect("localhost","root","","fashion");
$sql = "UPDATE bookings 
        SET status='finished' 
        WHERE status='approved' 
        AND date_to < CURDATE()";

mysqli_query($con, $sql);

?>