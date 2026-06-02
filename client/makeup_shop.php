<?php
session_start();
$con = mysqli_connect("localhost", "root", "", "fashion");

// Fetch Makeup Artists
$q = mysqli_query($con, "SELECT * FROM makeup_artists");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Makeup Artists | Aura</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --gold: #D4AF37; 
            --black: #0a0a0a; 
            --grey: #161616; 
            --white: #ffffff; 
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--black); 
            color: var(--white); 
            margin: 0; 
            padding-bottom: 50px;
        }

        /* HEADER STYLE */
        .section-header {
            text-align: center;
            padding: 60px 20px 40px;
            background: linear-gradient(to bottom, #111, #0a0a0a);
        }
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 6px;
            margin: 0;
        }
        .section-header p { color: #888; letter-spacing: 2px; text-transform: uppercase; font-size: 0.8rem; }

        /* GRID CONTAINER (Matches the previous small card grid) */
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); 
            gap: 20px; 
            padding: 0 20px; 
        }

        /* SMALL CARD STYLING */
        .card { 
            background: var(--grey); 
            border: 1px solid rgba(212, 175, 55, 0.1); 
            transition: 0.4s; 
            overflow: hidden; 
            display: flex; 
            flex-direction: column; 
        }
        .card:hover { 
            transform: translateY(-5px); 
            border-color: var(--gold); 
        }

        /* IMAGE BOX (Small height) */
        .img-box { 
            height: 280px; 
            overflow: hidden; 
        }
        .img-box img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: 0.5s; 
        }
        .card:hover .img-box img { 
            transform: scale(1.05); 
        }

        /* CARD CONTENT */
        .card-body { 
            padding: 15px; 
            flex-grow: 1; 
        }
        .card-body h3 { 
            font-family: 'Playfair Display', serif; 
            color: var(--gold); 
            font-size: 1.1rem; 
            margin: 0 0 5px; 
        }
        .card-body .specialty { 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: #888; 
            margin-bottom: 10px; 
            display: block;
        }
        .price { 
            font-size: 1rem; 
            font-weight: 600; 
            color: var(--white);
            margin-bottom: 10px; 
        }

        /* BUTTON STYLE */
        .btn {
            background: var(--gold); 
            color: #000; 
            text-align: center; 
            padding: 12px;
            text-decoration: none; 
            font-weight: 700; 
            text-transform: uppercase; 
            font-size: 0.75rem;
            transition: 0.3s;
        }
        .btn:hover {
            background: #fff;
        }
    </style>
</head>
<body>

<section class="section-header">
    <h2>Makeup Artists</h2>
    <p>Professional Artistry for your Special Day</p>
</section>

<div class="container">

    <?php while($r = mysqli_fetch_assoc($q)): ?>
    <div class="card">
        <div class="img-box">
            <img src="<?php echo $r['image']; ?>" alt="<?php echo $r['name']; ?>">
        </div>
        <div class="card-body">
            <h3><?php echo $r['name']; ?></h3>
            <span class="specialty"><?php echo $r['specialty']; ?></span>
            <div class="price">₱<?php echo number_format($r['price'], 2); ?></div>
        </div>
        <a href="makeup_details.php?id=<?php echo $r['id']; ?>" class="btn">Book Artist</a>
    </div>
    <?php endwhile; ?>

</div>

</body>
</html>