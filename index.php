<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura | Luxury Gown & Makeup Rentals</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
<style>
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Montserrat', sans-serif;
        scroll-behavior: smooth;
    }

    :root {
        --black: #0a0a0a;
        --gold: #D4AF37;
        --gold-light: #f1d592;
        --white: #ffffff;
        --gray: #1a1a1a;
    }

    body {
        background-color: var(--black);
        color: var(--white);
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* Navbar Styling */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 8%;
        background: rgba(10, 10, 10, 0.95);
        border-bottom: 1px solid rgba(212, 175, 55, 0.3);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .logo {
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        letter-spacing: 4px;
        color: var(--gold);
    }

    .nav-links {
        display: flex;
        list-style: none;
    }

    .nav-links li a {
        text-decoration: none;
        color: var(--white);
        margin: 0 20px;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: 0.3s;
    }

    .nav-links li a:hover {
        color: var(--gold);
    }

    .nav-auth .login {
        text-decoration: none;
        color: var(--white);
        margin-right: 20px;
        font-size: 0.9rem;
    }

    /* Buttons */
    .btn-gold {
        background: linear-gradient(45deg, var(--gold), var(--gold-light));
        color: var(--black);
        padding: 12px 28px;
        text-decoration: none;
        font-weight: 600;
        border-radius: 2px;
        transition: 0.3s;
        text-transform: uppercase;
        font-size: 0.8rem;
        border: none;
        cursor: pointer;
    }

    .btn-gold:hover {
        box-shadow: 0 0 15px rgba(212, 175, 55, 0.6);
        transform: translateY(-2px);
    }

    .btn-outline {
        border: 1px solid var(--gold);
        color: var(--gold);
        padding: 12px 28px;
        text-decoration: none;
        margin-left: 15px;
        transition: 0.3s;
    }
    
    .btn-outline:hover {
        background: var(--gold);
        color: var(--black);
    }

    /* Hero Section */
    .hero {
        height: 90vh;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                    url('https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&q=80&w=1600') center/cover;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 0 10%;
    }

    .hero h1 {
        font-family: 'Playfair Display', serif;
        font-size: 4rem;
        margin-bottom: 20px;
    }

    .gold-text {
        color: var(--gold);
    }

    .hero p {
        font-size: 1.2rem;
        max-width: 600px;
        margin: 0 auto 30px;
        color: #ccc;
    }

    /* Sections Shared Styling */
    .section-padding {
        padding: 100px 8%;
    }

    .section-title {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-title h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.8rem;
        margin-bottom: 10px;
    }

    .gold-line {
        width: 60px;
        height: 3px;
        background: var(--gold);
        margin: 0 auto;
    }

    /* About Section */
    .about {
        background-color: #050505;
        display: flex;
        align-items: center;
        gap: 50px;
        flex-wrap: wrap;
    }

    .about-image {
        flex: 1;
        min-width: 300px;
        position: relative;
    }

    .about-image img {
        width: 100%;
        border: 1px solid var(--gold);
        padding: 15px;
    }

    .about-content {
        flex: 1;
        min-width: 300px;
    }

    .about-content h3 {
        color: var(--gold);
        font-family: 'Playfair Display', serif;
        font-size: 1.8rem;
        margin-bottom: 20px;
    }

    .about-content p {
        margin-bottom: 20px;
        color: #aaa;
    }

    /* Services Section */
    .service-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .card {
        background: var(--gray);
        padding: 40px;
        border: 1px solid rgba(212, 175, 55, 0.1);
        transition: 0.4s;
        text-align: center;
    }

    .card:hover {
        border-color: var(--gold);
        transform: translateY(-10px);
    }

    .card-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
    }

    /* Contact Section */
    .contact {
        background: linear-gradient(rgba(10,10,10,0.9), rgba(10,10,10,0.9)), url('https://www.transparenttextures.com/patterns/black-linen.png');
    }

    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
    }

    .contact-info h3 {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        margin-bottom: 20px;
        color: var(--gold);
    }

    .contact-form {
        display: flex;
        flex-direction: column;
    }

    .contact-form input, .contact-form textarea {
        background: transparent;
        border: 1px solid #333;
        padding: 15px;
        margin-bottom: 20px;
        color: white;
        outline: none;
        transition: 0.3s;
    }

    .contact-form input:focus, .contact-form textarea:focus {
        border-color: var(--gold);
    }

    footer {
        text-align: center;
        padding: 40px;
        border-top: 1px solid rgba(212, 175, 55, 0.2);
        font-size: 0.8rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .contact-container { grid-template-columns: 1fr; }
        .hero h1 { font-size: 2.5rem; }
    }
</style>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">DAVE POWERS</div>
        <ul class="nav-links">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="client/shop.php">Shop</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="nav-auth">
            <a href="client/login.php" class="login">Login</a>
            <a href="client/register.php" class="btn-gold">Register</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero" id="home">
        <div class="hero-content">
            <h1>Dressed in <span class="gold-text">Grandeur</span></h1>
            <p>Premium gown rentals and professional makeup artistry for your most unforgettable nights.</p>
            <div class="hero-btns">
                <a href="client/shop.php" class="btn-gold">Explore Collection</a>
                <a href="client/register.php" class="btn-outline">Book Artistry</a>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section class="about section-padding" id="about">
        <div class="about-image">
            <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&q=80&w=800" alt="Luxury Fashion">
        </div>
        <div class="about-content">
            <div class="section-title" style="text-align: left;">
                <h2>Legacy of Elegance</h2>
                <div class="gold-line" style="margin: 0;"></div>
            </div>
            <h3>Exquisite Style for Every Occasion</h3>
            <p>Founded on the principle that luxury should be experienced, not just owned, Aura provides a curated library of designer gowns and bespoke makeup services.</p>
            <p>Whether it is a high-profile gala, a romantic wedding, or a red-carpet event, our team of stylists and artists ensure you radiate confidence from the moment you step out.</p>
            <a href="#" class="btn-gold">Learn More</a>
        </div>
    </section>

    <!-- Features/Shop Section -->
    <section class="services section-padding" id="services">
        <div class="section-title">
            <h2>Our Services</h2>
            <div class="gold-line"></div>
        </div>
        <div class="service-grid">
            <div class="card">
                <div class="card-icon">✨</div>
                <h3>Designer Gowns</h3>
                <p>Couture pieces curated for galas, weddings, and red-carpet events.</p>
            </div>
            <div class="card">
                <div class="card-icon">💄</div>
                <h3>Elite Makeup</h3>
                <p>HD & Airbrush services by certified luxury makeup artists.</p>
            </div>
            <div class="card">
                <div class="card-icon">👑</div>
                <h3>Full Styling</h3>
                <p>Personalized sessions to match your gown with the perfect look.</p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="contact section-padding" id="contact">
        <div class="section-title">
            <h2>Get In Touch</h2>
            <div class="gold-line"></div>
        </div>
        <div class="contact-container">
            <div class="contact-info">
                <h3>Let's create your <span class="gold-text">Signature Look</span></h3>
                <p>Book a private consultation or inquire about our seasonal gown collection.</p>
                <br>
                <p>📍 123 Luxury Lane, Beverly Hills</p>
                <p>📞 +1 (555) 012-3456</p>
                <p>✉️ concierge@aura-rentals.com</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; DAVE POWERS RENTALS. ALL RIGHTS RESERVED.</p>
    </footer>

</body>
</html>