<?php
session_start();
$con = mysqli_connect("localhost","root","","fashion");

$q = mysqli_query($con,"
    SELECT 
        packages.*, 
        gowns.name AS gown_name, 
        makeup_artists.name AS makeup_name 
    FROM packages 
    LEFT JOIN gowns ON packages.gown_id = gowns.id 
    LEFT JOIN makeup_artists ON packages.makeup_artist_id = makeup_artists.id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exclusive Packages | Luxury Atelier</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #D4AF37;
            --dark-gold: #996515;
            --black: #0a0a0a;
            --dark-grey: #1a1a1a;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--black);
            color: var(--white);
            margin: 0;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
        }

        .header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--gold);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .header p {
            color: #888;
            letter-spacing: 1px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 40px;
        }

        .package-card {
            background: var(--dark-grey);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: all 0.4s ease;
        }

        .package-card:hover {
            transform: translateY(-15px);
            border-color: var(--gold);
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.1);
        }

        .image-container {
            height: 400px;
            position: relative;
            overflow: hidden;
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .package-card:hover .image-container img {
            transform: scale(1.1);
        }

        /* Value Badge */
        .badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--gold);
            color: var(--black);
            padding: 5px 15px;
            font-weight: 700;
            font-size: 0.75rem;
            border-radius: 50px;
            text-transform: uppercase;
            z-index: 2;
        }

        .content {
            padding: 30px;
            text-align: center;
        }

        .content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--white);
            margin: 0 0 20px 0;
        }

        .inclusion-list {
            list-style: none;
            padding: 0;
            margin: 0 0 25px 0;
            text-align: left;
            display: inline-block;
        }

        .inclusion-list li {
            margin-bottom: 12px;
            font-size: 0.9rem;
            color: #bbb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .inclusion-list li::before {
            content: '✦';
            color: var(--gold);
        }

        .inclusion-list b {
            color: var(--gold);
            font-weight: 600;
        }

        .price-tag {
            font-size: 2rem;
            font-family: 'Playfair Display', serif;
            color: var(--white);
            margin-bottom: 25px;
            display: block;
        }

        .btn {
            display: block;
            background: transparent;
            color: var(--gold);
            border: 2px solid var(--gold);
            padding: 15px;
            text-decoration: none;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 2px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: var(--gold);
            color: var(--black);
        }

        .nav-link {
            text-align: center;
            display: block;
            margin-top: 50px;
            color: #666;
            text-decoration: none;
        }
        
        .nav-link:hover { color: var(--gold); }

    </style>
</head>
<body>

<div class="header">
    <h2>Special Packages</h2>
    <p>Curated bundles for your perfect look</p>
</div>

<div class="container">

<?php while($r=mysqli_fetch_assoc($q)){ ?>

<div class="package-card">
    <div class="badge">All Inclusive</div>
    
    <div class="image-container">
        <img src="<?php echo $r['image']; ?>" alt="Package Image">
    </div>

    <div class="content">
        <h3><?php echo $r['package_name']; ?></h3>

        <ul class="inclusion-list">
            <li>Gown: <b><?php echo $r['gown_name']; ?></b></li>
            <li>Makeup Artist: <b><?php echo $r['makeup_name']; ?></b></li>
            <li>Premium Styling & Fitting</li>
        </ul>

        <span class="price-tag">
            ₱<?php echo number_format($r['package_price'], 2); ?>
        </span>

        <a class="btn" href="package_details.php?id=<?php echo $r['id']; ?>">
            Book This Bundle
        </a>
    </div>
</div>

<?php } ?>

</div>

<a href="shop.php" class="nav-link">← Back to Gown Shop</a>

</body>
</html>