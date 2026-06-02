<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

$view = isset($_GET['view']) ? $_GET['view'] : 'gowns';

// --- AVAILABILITY LOGIC (ONLY APPROVED) ---

function getGownBookedDays($con, $gown_id) {
    $days = [];
    
    // 1. Check standard gown bookings (Approved only)
    $q1 = mysqli_query($con, "SELECT date_from, date_to FROM bookings WHERE gown_id='$gown_id' AND status='approved'");
    while($r = mysqli_fetch_assoc($q1)) {
        $start = strtotime($r['date_from']); $end = strtotime($r['date_to']);
        for($i=$start; $i<=$end; $i+=86400) { $days[] = date("Y-m-d", $i); }
    }

    // 2. Check if gown is part of a booked package (Approved only)
    $q2 = mysqli_query($con, "SELECT date_from, date_to FROM package_bookings WHERE gown_id='$gown_id' AND status='approved'");
    while($r = mysqli_fetch_assoc($q2)) {
        $start = strtotime($r['date_from']); $end = strtotime($r['date_to']);
        for($i=$start; $i<=$end; $i+=86400) { $days[] = date("Y-m-d", $i); }
    }
    
    return array_values(array_unique($days));
}

function getArtistBookedDays($con, $artist_id) {
    $days = [];
    
    // 1. Check individual artist bookings (Approved only)
    $q1 = mysqli_query($con, "SELECT booking_date FROM makeup_bookings WHERE makeup_artist_id='$artist_id' AND status='approved'");
    while($r = mysqli_fetch_assoc($q1)) { $days[] = $r['booking_date']; }

    // 2. Check if artist is busy with a package (Approved only)
    $q2 = mysqli_query($con, "SELECT date_from, date_to FROM package_bookings WHERE makeup_artist_id='$artist_id' AND status='approved'");
    while($r = mysqli_fetch_assoc($q2)) {
        $start = strtotime($r['date_from']); $end = strtotime($r['date_to']);
        for($i=$start; $i<=$end; $i+=86400) { $days[] = date("Y-m-d", $i); }
    }
    
    return array_values(array_unique($days));
}

// Helper Function for Card UI
function renderCard($title, $img, $price, $sub, $link, $bookedDays) {
    $bookedJson = json_encode($bookedDays);
    ?>
    <div class="card">
        <div class="img-box"><img src="<?php echo $img; ?>"></div>
        <div class="card-body">
            <h3><?php echo $title; ?></h3>
            <span class="sub-text"><?php echo $sub; ?></span>
            <div class="price"><?php echo $price; ?></div>
            <!-- Calendar target with data -->
            <div class="js-calendar" data-booked='<?php echo $bookedJson; ?>'></div>
        </div>
        <a href="<?php echo $link; ?>" class="btn">View Details</a>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> DAVE POWERS
    </title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #D4AF37; --black: #0a0a0a; --grey: #161616; --white: #ffffff; }
        body { font-family: 'Poppins', sans-serif; background: var(--black); color: var(--white); margin: 0; }
        .atelier-header { text-align: center; padding: 40px 20px 10px; }
        .atelier-header h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--gold); letter-spacing: 4px; margin: 0; }
        .atelier-nav { display: flex; justify-content: center; margin: 20px 0; gap: 20px; }
        .atelier-nav a { text-decoration: none; color: #666; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; transition: 0.3s; padding-bottom: 5px; }
        .atelier-nav a.active { color: var(--gold); border-bottom: 2px solid var(--gold); }
        .container { max-width: 1200px; margin: 0 auto 50px; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; padding: 0 15px; }
        .card { background: var(--grey); border: 1px solid #222; transition: 0.3s; display: flex; flex-direction: column; overflow: hidden; }
        .card:hover { border-color: var(--gold); transform: translateY(-3px); }
        .img-box { height: 250px; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .card-body { padding: 15px; flex-grow: 1; text-align: center; }
        .card-body h3 { font-family: 'Playfair Display', serif; color: var(--gold); margin: 0 0 5px; font-size: 1.1rem; }
        .sub-text { font-size: 0.65rem; color: #888; display: block; margin-bottom: 8px; min-height: 15px; }
        .price { font-weight: 600; font-size: 1rem; margin-bottom: 5px; }
        .cal-box { background: rgba(0,0,0,0.4); padding: 8px; border-radius: 4px; margin-top: 10px; border: 1px solid #333; }
        .cal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .cal-month { font-size: 0.55rem; text-transform: uppercase; color: var(--gold); font-weight: bold; }
        .cal-btn { background: none; border: none; color: var(--gold); cursor: pointer; font-size: 0.8rem; }
        table { width: 100%; border-spacing: 1px; }
        td { font-size: 0.55rem; padding: 2px; text-align: center; width: 14%; }
        .date-free { background: rgba(255,255,255,0.03); color: #555; }
        .date-booked { background: var(--gold) !important; color: #000 !important; font-weight: bold; text-decoration: line-through; }
        .btn { background: var(--gold); color: #000; text-align: center; padding: 12px; text-decoration: none; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; }
    </style>
</head>
<body>

<?php include '../header.php'; ?>

<section class="atelier-header">
    <h1>SHOP SERVICES</h1>
    <nav class="atelier-nav">
        <a href="?view=gowns" class="<?php echo $view == 'gowns' ? 'active' : ''; ?>">Gowns</a>
        <a href="?view=makeup" class="<?php echo $view == 'makeup' ? 'active' : ''; ?>">Artists</a>
        <a href="?view=packages" class="<?php echo $view == 'packages' ? 'active' : ''; ?>">Packages</a>
    </nav>
</section>

<div class="container">
    <?php 
    if($view == 'gowns') {
        $q = mysqli_query($con, "SELECT * FROM gowns");
        while($r = mysqli_fetch_assoc($q)) {
            $booked = getGownBookedDays($con, $r['id']);
            renderCard($r['name'], $r['image'], "₱".number_format($r['base_price'], 2), "Premium Collection", "gown_details.php?id=".$r['id'], $booked);
        }
    } 
    elseif($view == 'makeup') {
        $q = mysqli_query($con, "SELECT * FROM makeup_artists");
        while($r = mysqli_fetch_assoc($q)) {
            $booked = getArtistBookedDays($con, $r['id']);
            renderCard($r['name'], $r['image'], "₱".number_format($r['price'], 2), $r['specialty'], "makeup_details.php?id=".$r['id'], $booked);
        }
    }
    elseif($view == 'packages') {
        $q = mysqli_query($con, "SELECT packages.*, gowns.name as gname, makeup_artists.name as mname FROM packages JOIN gowns ON packages.gown_id = gowns.id JOIN makeup_artists ON packages.makeup_artist_id = makeup_artists.id");
        while($r = mysqli_fetch_assoc($q)) {
            // Packages check availability for BOTH components
            $gBooked = getGownBookedDays($con, $r['gown_id']);
            $mBooked = getArtistBookedDays($con, $r['makeup_artist_id']);
            $booked = array_values(array_unique(array_merge($gBooked, $mBooked)));
            renderCard($r['package_name'], $r['image'], "₱".number_format($r['package_price'], 2), $r['gname']." + ".$r['mname'], "package_details.php?id=".$r['id'], $booked);
        }
    }
    ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const calendars = document.querySelectorAll('.js-calendar');
    const now = new Date();
    
    calendars.forEach(cal => {
        let currentMonth = now.getMonth();
        let currentYear = now.getFullYear();
        let bookedDays = [];

        try {
            let rawData = cal.getAttribute('data-booked');
            bookedDays = JSON.parse(rawData);
            if (!Array.isArray(bookedDays)) bookedDays = Object.values(bookedDays);
        } catch(e) { bookedDays = []; }

        function render(month, year) {
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            let firstDay = new Date(year, month, 1).getDay();
            let daysInMonth = new Date(year, month + 1, 0).getDate();

            let html = `
                <div class="cal-box">
                    <div class="cal-header">
                        <button class="cal-btn prev">❮</button>
                        <div class="cal-month">${monthNames[month]} ${year}</div>
                        <button class="cal-btn next">❯</button>
                    </div>
                    <table><tr>`;

            for (let i = 0; i < firstDay; i++) { html += '<td></td>'; }

            for (let i = 1; i <= daysInMonth; i++) {
                let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
                let isBooked = bookedDays.includes(dateStr) ? 'date-booked' : 'date-free';
                html += `<td class="${isBooked}">${i}</td>`;
                if ((i + firstDay) % 7 === 0) html += '</tr><tr>';
            }

            html += '</tr></table></div>';
            cal.innerHTML = html;

            cal.querySelector('.prev').onclick = (e) => {
                e.preventDefault();
                currentMonth--;
                if(currentMonth < 0) { currentMonth = 11; currentYear--; }
                render(currentMonth, currentYear);
            };

            cal.querySelector('.next').onclick = (e) => {
                e.preventDefault();
                currentMonth++;
                if(currentMonth > 11) { currentMonth = 0; currentYear++; }
                render(currentMonth, currentYear);
            };
        }
        render(currentMonth, currentYear);
    });
});
</script>
</body>
</html>