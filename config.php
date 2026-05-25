mysqli_query($con,"
    UPDATE bookings
    SET status='finished'
    WHERE status='approved'
    AND date_to < CURDATE()
");