<?php
// index.php – Pastimes homepage with IMAGES
session_start();
$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pastimes – Pre-loved Fashion</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #F7F3EE;
    --charcoal: #1C1C1C;
    --rust: #C0533A;
    --rust-hover: #A8432C;
    --muted: #7A7065;
    --border: #E2DDD7;
    --card-bg: #FFFFFF;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--charcoal); }

  nav {
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    padding: 0 2.5rem;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--charcoal); text-decoration: none; }
  .nav-links { display: flex; gap: 2rem; align-items: center; }
  .nav-links a { color: var(--muted); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
  .nav-links a:hover { color: var(--charcoal); }
  .nav-links a.active { color: var(--charcoal); font-weight: 500; }
  .btn-nav {
    padding: 0.5rem 1.1rem;
    background: var(--rust);
    color: #fff;
    border-radius: 7px;
    text-decoration: none;
    font-size: 0.88rem;
    font-weight: 500;
    transition: background 0.2s;
  }
  .btn-nav:hover { background: var(--rust-hover); color: #fff; }

  .hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: 520px;
    max-width: 1100px;
    margin: 3rem auto;
    padding: 0 2rem;
    gap: 3rem;
    align-items: center;
  }
  .hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: #F0EAE4;
    color: var(--muted);
    border-radius: 20px;
    padding: 0.3rem 0.85rem;
    font-size: 0.78rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 1.2rem;
  }
  .hero-text h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2.2rem, 4vw, 3.2rem);
    line-height: 1.15;
    color: var(--charcoal);
    margin-bottom: 1rem;
  }
  .hero-text h1 span { color: var(--rust); font-style: italic; }
  .hero-text p { color: var(--muted); line-height: 1.7; margin-bottom: 2rem; font-size: 1rem; }
  .hero-actions { display: flex; gap: 1rem; }
  .btn-primary {
    padding: 0.8rem 1.8rem;
    background: var(--rust);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: background 0.2s;
  }
  .btn-primary:hover { background: var(--rust-hover); }
  .btn-outline {
    padding: 0.8rem 1.8rem;
    border: 1px solid var(--border);
    color: var(--charcoal);
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: border-color 0.2s;
  }
  .btn-outline:hover { border-color: var(--charcoal); }

  .hero-visual {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.8rem;
    height: 400px;
  }
  .hero-img {
    background: #EDE8E2;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .hero-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .hero-img:first-child { grid-row: span 2; border-radius: 16px; }

  .how {
    background: var(--card-bg);
    padding: 4rem 2rem;
    text-align: center;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
  }
  .how h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 2.5rem; }
  .how-steps { display: flex; justify-content: center; gap: 3rem; flex-wrap: wrap; max-width: 700px; margin: 0 auto; }
  .step-num {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #F0EAE4;
    color: var(--rust);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
    margin: 0 auto 0.8rem;
  }
  .step h3 { font-size: 0.95rem; font-weight: 600; margin-bottom: 0.4rem; }
  .step p  { font-size: 0.82rem; color: var(--muted); max-width: 160px; line-height: 1.5; }

  .featured { max-width: 1100px; margin: 3rem auto; padding: 0 2rem; }
  .section-header {
    display: flex; justify-content: space-between; align-items: baseline;
    margin-bottom: 1.5rem;
  }
  .section-header h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; }
  .section-header a  { color: var(--rust); text-decoration: none; font-size: 0.88rem; }

  .product-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.2rem; }
  .product-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
  }
  .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
  .product-img {
    height: 200px;
    background: #EDE8E2;
    overflow: hidden;
  }
  .product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .product-info { padding: 0.9rem; }
  .product-name { font-size: 0.88rem; font-weight: 500; margin-bottom: 0.2rem; }
  .product-price { font-size: 0.88rem; color: var(--rust); font-weight: 600; }

  footer {
    background: var(--charcoal);
    color: #888;
    padding: 2.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 2rem;
    margin-top: 4rem;
  }
  .footer-brand p { font-family: 'Playfair Display', serif; color: #fff; font-size: 1.2rem; margin-bottom: 0.5rem; }
  .footer-brand span { font-size: 0.82rem; line-height: 1.6; }
  footer h4 { color: #fff; font-size: 0.85rem; margin-bottom: 0.8rem; }
  footer a  { display: block; color: #888; text-decoration: none; font-size: 0.82rem; margin-bottom: 0.4rem; }
  footer a:hover { color: #fff; }

  @media (max-width: 768px) {
    .hero { grid-template-columns: 1fr; }
    .hero-visual { display: none; }
    .product-grid { grid-template-columns: repeat(2, 1fr); }
    footer { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="shop.php">Shop</a>
    <a href="sell.php">Sell</a>
    <?php if ($loggedIn): ?>
      <a href="Dashboard.php">👤 <?= $userName ?></a>
      <a href="Logout.php">Logout</a>
    <?php else: ?>
      <a href="Login.php">Login</a>
      <a href="register.php" class="btn-nav">Register</a>
    <?php endif; ?>
  </div>
</nav>

<!-- HERO SECTION WITH IMAGES -->
<section class="hero">
  <div class="hero-text">
    <div class="hero-tag">🌱 Sustainable Fashion</div>
    <h1>Give Your Wardrobe a <span>Second Life</span></h1>
    <p>Buy and sell pre-loved clothing with confidence. Quality fashion at affordable prices — better for your wallet, better for the planet.</p>
    <div class="hero-actions">
      <a href="shop.php" class="btn-primary">Shop Now</a>
      <a href="register.php" class="btn-outline">Start Selling</a>
    </div>
  </div>
  
  <div class="hero-visual">
    <!-- HERO MAIN LARGE IMAGE - REPLACE 'hero-large.jpg' WITH YOUR IMAGE FILE NAME -->
    <div class="hero-img">
      <img src="images/hero-large.jpg" alt="Sustainable vintage fashion" onerror="this.src='https://placehold.co/600x600/EDE8E2/999?text=Add+hero-large.jpg'">
    </div>
    <!-- HERO SMALL IMAGE 1 - REPLACE 'hero-small-1.jpg' WITH YOUR IMAGE FILE NAME -->
    <div class="hero-img">
      <img src="images/hero-small-1.jpg" alt="Vintage handbag" onerror="this.src='https://placehold.co/300x300/EDE8E2/999?text=Add+hero-small-1.jpg'">
    </div>
    <!-- HERO SMALL IMAGE 2 - REPLACE 'hero-small-2.jpg' WITH YOUR IMAGE FILE NAME -->
    <div class="hero-img">
      <img src="images/hero-small-2.jpg" alt="Happy thrift shopping" onerror="this.src='https://placehold.co/300x300/EDE8E2/999?text=Add+hero-small-2.jpg'">
    </div>
  </div>
</section>

<!-- HOW IT WORKS SECTION -->
<section class="how">
  <h2>How It Works</h2>
  <div class="how-steps">
    <div class="step">
      <div class="step-num">01</div>
      <h3>List Your Item</h3>
      <p>Take a photo, add details, and set your price.</p>
    </div>
    <div class="step">
      <div class="step-num">02</div>
      <h3>Find &amp; Buy</h3>
      <p>Browse curated listings and add to your cart.</p>
    </div>
    <div class="step">
      <div class="step-num">03</div>
      <h3>Ship &amp; Enjoy</h3>
      <p>Secure checkout, fast shipping, happy wardrobe.</p>
    </div>
  </div>
</section>

<!-- FEATURED PRODUCTS SECTION WITH IMAGES -->
<section class="featured">
  <div class="section-header">
    <h2>Featured Items</h2>
    <a href="shop.php">View all →</a>
  </div>
  <div class="product-grid">
    
    <!-- PRODUCT 1: Vintage Jeans - REPLACE 'product1.jpg' WITH YOUR IMAGE -->
    <div class="product-card" onclick="window.location.href='shop.php'">
      <div class="product-img">
        <img src="images/product1.jpg" alt="Vintage Levi's 501 Jeans" onerror="this.src='https://placehold.co/400x400/EDE8E2/999?text=👖+Jeans'">
      </div>
      <div class="product-info">
        <div class="product-name">Vintage Levi's 501 Jeans</div>
        <div class="product-price">R450.00</div>
      </div>
    </div>
    
    <!-- PRODUCT 2: Floral Blouse - REPLACE 'product2.jpg' WITH YOUR IMAGE -->
    <div class="product-card" onclick="window.location.href='shop.php'">
      <div class="product-img">
        <img src="images/product2.jpg" alt="Silk Floral Blouse" onerror="this.src='https://placehold.co/400x400/EDE8E2/999?text=👚+Blouse'">
      </div>
      <div class="product-info">
        <div class="product-name">Silk Floral Blouse</div>
        <div class="product-price">R280.00</div>
      </div>
    </div>
    
    <!-- PRODUCT 3: Wool Coat - REPLACE 'product3.jpg' WITH YOUR IMAGE -->
    <div class="product-card" onclick="window.location.href='shop.php'">
      <div class="product-img">
        <img src="images/product3.jpg" alt="Wool Overcoat" onerror="this.src='https://placehold.co/400x400/EDE8E2/999?text=🧥+Coat'">
      </div>
      <div class="product-info">
        <div class="product-name">Wool Overcoat – Camel</div>
        <div class="product-price">R890.00</div>
      </div>
    </div>
    
    <!-- PRODUCT 4: Black Dress - REPLACE 'product4.jpg' WITH YOUR IMAGE -->
    <div class="product-card" onclick="window.location.href='shop.php'">
      <div class="product-img">
        <img src="images/product4.jpg" alt="Black Midi Dress" onerror="this.src='https://placehold.co/400x400/EDE8E2/999?text=👗+Dress'">
      </div>
      <div class="product-info">
        <div class="product-name">Black Midi Dress</div>
        <div class="product-price">R350.00</div>
      </div>
    </div>
    
  </div>
</section>

<footer>
  <div class="footer-brand">
    <p>Pastimes</p>
    <span>Give your clothes a second life.<br>Buy and sell pre-loved fashion with ease.</span>
  </div>
  <div>
    <h4>Quick Links</h4>
    <a href="shop.php">Shop</a>
    <a href="sell.php">Sell</a>
    <a href="Dashboard.php">My Account</a>
  </div>
  <div>
    <h4>Support</h4>
    <span style="font-size:0.82rem">help@pastimes.co.za</span><br>
    <span style="font-size:0.82rem">© 2026 Pastimes</span>
  </div>
</footer>

</body>
</html>