<?php
$con = mysqli_connect("localhost","root","","fashion");

$revenue = mysqli_query($con,"
SELECT 
    SUM(g.base_price) as total_sales,
    COUNT(b.id) as total_bookings
FROM bookings b
INNER JOIN gowns g ON g.id=b.gown_id
WHERE b.payment_status='paid'
");

$r = mysqli_fetch_assoc($revenue);
?>

<h2>Sales Report</h2>

<p>Total Sales: ₱<?php echo number_format($r['total_sales'] ?? 0,2); ?></p>
<p>Total Bookings: <?php echo $r['total_bookings']; ?></p>