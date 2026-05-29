<?php
session_start();
// Database connection - keep your existing logic
$con = mysqli_connect("localhost","root","","fashion");

function getBookedDays($con, $gown_id) {
    $days = [];
    // Approved Bookings
    $q1 = mysqli_query($con, "SELECT date_from, date_to FROM bookings WHERE gown_id='$gown_id' AND status='approved'");
    while($r = mysqli_fetch_assoc($q1)) {
        $start = strtotime($r['date_from']);
        $end = strtotime($r['date_to']);
        for($i=$start; $i<=$end; $i+=86400) { $days[] = date("Y-m-d", $i); }
    }
    // Blocked Dates
    $q2 = mysqli_query($con, "SELECT date_from, date_to FROM gown_items WHERE gown_id='$gown_id' AND (status IN ('maintenance', 'cleaning', 'retired', 'rented'))");
    while($r = mysqli_fetch_assoc($q2)) {
        $start = strtotime($r['date_from']);
        $end = strtotime($r['date_to']);
        for($i=$start; $i<=$end; $i+=86400) { $days[] = date("Y-m-d", $i); }
    }
    return $days;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxury Gown Collection</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark-gold: #996515;
            --black: #0a0a0a;
            --dark-grey: #1a1a1a;
            --light-grey: #e0e0e0;
            --glass: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--black);
            color: var(--light-grey);
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 50px 0;
        }

        .header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--gold);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 4px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        /* Card Styling */
        .card {
            background: var(--dark-grey);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease, border-color 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-10px);
            border-color: var(--gold);
        }

        .card-img-wrapper {
            position: relative;
            height: 350px;
            overflow: hidden;
        }

        .card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card:hover img {
            transform: scale(1.1);
        }

        .card-content {
            padding: 20px;
            flex-grow: 1;
        }

        .card h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gold);
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }

        .price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 15px;
        }

        .details {
            font-size: 0.9rem;
            color: #bbb;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }

        /* Calendar Styling */
        .calendar-section {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .calendar-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gold);
            text-align: center;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2px;
        }

        th {
            font-size: 0.7rem;
            color: #666;
            padding-bottom: 5px;
        }

        td {
            font-size: 0.75rem;
            text-align: center;
            padding: 5px 0;
            border-radius: 3px;
        }

        .date-available {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .date-booked {
            background: var(--dark-gold);
            color: var(--black);
            font-weight: bold;
            opacity: 0.6;
            text-decoration: line-through;
        }

        /* Legend */
        .legend {
            display: flex;
            justify-content: center;
            gap: 15px;
            font-size: 0.7rem;
            margin-top: 10px;
        }

        .legend-item { display: flex; align-items: center; gap: 5px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; }
        .dot.gold { background: var(--dark-gold); }
        .dot.white { background: rgba(255, 255, 255, 0.2); }

        /* Button */
        .btn {
            background: linear-gradient(45deg, var(--dark-gold), var(--gold));
            color: var(--black);
            padding: 12px;
            text-decoration: none;
            display: block;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: opacity 0.3s;
            border-radius: 0 0 14px 14px;
        }

        .btn:hover {
            opacity: 0.9;
        }

    </style>
</head>
<body>

<div class="header">
    <h2>The Gown Atelier</h2>
    <p style="color: #666;">Exquisite Elegance for Your Special Moments</p>
</div>

<div class="container">

<?php
$q = mysqli_query($con, "
    SELECT gowns.id AS gown_id, gowns.name, gowns.description, gowns.image, 
           gowns.base_price, gowns.category, gown_items.size, gown_items.color 
    FROM gowns 
    LEFT JOIN gown_items ON gowns.id = gown_items.gown_id
");

while($r = mysqli_fetch_assoc($q)){
    $booked = getBookedDays($con, $r['gown_id']);
    
    // Calendar Logic
    $month = date("m");
    $year = date("Y");
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $firstDayOfMonth = date("w", strtotime("$year-$month-01"));
?>

<div class="card">
    <div class="card-img-wrapper">
        <img src="<?php echo $r['image']; ?>" alt="Gown Image">
    </div>

    <div class="card-content">
        <h3><?php echo $r['name']; ?></h3>
        <div class="price">₱<?php echo number_format($r['base_price'], 2); ?></div>
        
        <div class="details">
            <span><strong>Size:</strong> <?php echo $r['size']; ?></span>
            <span><strong>Color:</strong> <?php echo $r['color']; ?></span>
        </div>

        <div class="calendar-section">
            <div class="calendar-title"><?php echo date("F Y"); ?> Availability</div>
            <table>
                <thead>
                    <tr><th>S</th><th>M</th><th>T</th><th>W</th><th>T</th><th>F</th><th>S</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        // Print empty slots before the first day of the month
                        for($x = 0; $x < $firstDayOfMonth; $x++) {
                            echo "<td></td>";
                        }

                        for($i = 1; $i <= $daysInMonth; $i++) {
                            $currentDate = date("Y-m-d", strtotime("$year-$month-$i"));
                            $isBooked = in_array($currentDate, $booked);
                            $class = $isBooked ? 'date-booked' : 'date-available';
                            
                            echo "<td class='$class'>$i</td>";

                            // New row every Saturday
                            if(($i + $firstDayOfMonth) % 7 == 0) {
                                echo "</tr><tr>";
                            }
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            
            <div class="legend">
                <div class="legend-item"><span class="dot white"></span> Available</div>
                <div class="legend-item"><span class="dot gold"></span> Unavailable</div>
            </div>
        </div>
    </div>

    <a class="btn" href="gown_details.php?id=<?php echo $r['gown_id']; ?>">
        Book Appointment
    </a>
</div>

<?php } ?>

</div>

</body>
</html>