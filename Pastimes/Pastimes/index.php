<?php
// index.php - Complete Pastimes Homepage with Modern Design
session_start();
require_once 'DBConn.php';

// Check if tables exist
$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'tblclothes'");
$dbReady = (mysqli_num_rows($tableCheck) > 0);

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name']) : '';
$cartCount = 0;

// Get cart count from localStorage via JS, default to 0
if ($loggedIn && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

// Get featured products
$featuredProducts = [];
if ($dbReady) {
    $featured = mysqli_query($conn, "SELECT * FROM tblclothes WHERE Status = 'active' ORDER BY DateListed DESC LIMIT 4");
    if ($featured) {
        while ($row = mysqli_fetch_assoc($featured)) {
            $featuredProducts[] = $row;
        }
    }
}

// Get statistics for counters
$productCount = 0;
$userCount = 0;
if ($dbReady) {
    $productResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM tblclothes");
    $productCount = $productResult ? mysqli_fetch_assoc($productResult)['count'] : 0;
    
    $userResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM tbluser WHERE AccountType = 'customer'");
    $userCount = $userResult ? mysqli_fetch_assoc($userResult)['count'] : 0;
}
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pastimes – Sustainable Second-Hand Fashion Marketplace</title>
    <meta name="description" content="Pastimes is South Africa's trusted marketplace for pre-loved fashion. Buy quality second-hand clothing, sell items you no longer wear, and join the circular fashion movement.">
    <meta name="keywords" content="second hand fashion, pre-loved clothes, sustainable fashion, thrift store, vintage clothing">
    <meta name="author" content="Pastimes">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        /* ============================================ */
        /* ROOT VARIABLES - Modern Color Palette
        /* ============================================ */
        :root {
            --cream: #F9F6F2;
            --charcoal: #1A1A1A;
            --rust: #C8653A;
            --rust-light: #E28A62;
            --rust-dark: #A84E28;
            --muted: #6B6B6B;
            --muted-light: #9B9B9B;
            --border: #E5E0DB;
            --border-dark: #D1C9C1;
            --card-bg: #FFFFFF;
            --success: #2E7D32;
            --success-light: #E8F5E9;
            --error: #C62828;
            --error-light: #FFEBEE;
            --warning: #F57C00;
            --info: #1976D2;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 5px 20px rgba(0,0,0,0.08);
            --shadow-lg: 0 15px 40px rgba(0,0,0,0.12);
            --shadow-xl: 0 25px 50px rgba(0,0,0,0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html {
            scroll-behavior: smooth;
            scroll-padding-top: 80px;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--charcoal);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--border);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--rust);
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--rust-dark);
        }
        
        ::selection {
            background: var(--rust);
            color: white;
        }
        
        /* ============================================ */
        /* NAVIGATION - Modern Sticky Nav
        /* ============================================ */
        nav {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 0 5%;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        nav.scrolled {
            height: 65px;
            box-shadow: var(--shadow-md);
        }
        
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--charcoal) 0%, var(--rust) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--muted);
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 0.5rem 0;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--rust);
            transition: width 0.3s ease;
        }
        
        .nav-links a:hover::after,
        .nav-links a.active::after {
            width: 100%;
        }
        
        .nav-links a:hover,
        .nav-links a.active {
            color: var(--charcoal);
        }
        
        .btn-nav {
            background: var(--rust);
            color: white !important;
            padding: 0.6rem 1.3rem !important;
            border-radius: 50px;
            transition: all 0.3s ease !important;
        }
        
        .btn-nav:hover {
            background: var(--rust-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(200, 101, 58, 0.3);
        }
        
        .btn-nav::after {
            display: none !important;
        }
        
        .cart-link {
            position: relative;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -12px;
            background: var(--rust);
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.4rem;
            border-radius: 50%;
            min-width: 18px;
            text-align: center;
        }
        
        .menu-toggle {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }
        
        .menu-toggle span {
            width: 25px;
            height: 2px;
            background: var(--charcoal);
            transition: all 0.3s ease;
        }
        
        /* ============================================ */
        /* HERO SECTION - Animated
        /* ============================================ */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1400px;
            margin: 4rem auto;
            padding: 0 5%;
            gap: 4rem;
            align-items: center;
        }
        
        .hero-content {
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-tag {
            background: linear-gradient(135deg, var(--rust-light), var(--rust));
            color: white;
            border-radius: 50px;
            padding: 0.3rem 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }
        
        .hero h1 span {
            background: linear-gradient(135deg, var(--rust) 0%, var(--rust-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero p {
            font-size: 1.1rem;
            color: var(--muted);
            margin-bottom: 2rem;
            line-height: 1.7;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 2rem;
            background: linear-gradient(135deg, var(--rust) 0%, var(--rust-dark) 100%);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(200, 101, 58, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(200, 101, 58, 0.4);
        }
        
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 2rem;
            background: transparent;
            color: var(--charcoal);
            border: 2px solid var(--border);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline:hover {
            border-color: var(--rust);
            color: var(--rust);
            transform: translateY(-3px);
        }
        
        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--rust);
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--muted);
        }
        
        .hero-visual {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            height: 500px;
            animation: fadeInRight 0.8s ease-out;
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .hero-img {
            background: linear-gradient(135deg, var(--border) 0%, var(--border-dark) 100%);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-lg);
        }
        
        .hero-img:first-child {
            grid-row: span 2;
        }
        
        .hero-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .hero-img:hover img {
            transform: scale(1.08);
        }
        
        .hero-img-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 1rem;
            color: white;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }
        
        .hero-img:hover .hero-img-overlay {
            transform: translateY(0);
        }
        
        /* ============================================ */
        /* FEATURED SECTION
        /* ============================================ */
        .featured {
            max-width: 1400px;
            margin: 5rem auto;
            padding: 0 5%;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }
        
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            font-weight: 700;
            position: relative;
            display: inline-block;
        }
        
        .section-header h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--rust);
            border-radius: 3px;
        }
        
        .section-header a {
            color: var(--rust);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .section-header a:hover {
            color: var(--rust-dark);
            transform: translateX(5px);
            display: inline-block;
        }
        
        /* ============================================ */
        /* PRODUCT GRID
        /* ============================================ */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }
        
        .product-card {
            background: var(--card-bg);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }
        
        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--rust), var(--rust-light));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }
        
        .product-card:hover::before {
            transform: scaleX(1);
        }
        
        .product-img {
            height: 280px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--border) 0%, var(--border-dark) 100%);
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.08);
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-category {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--rust);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }
        
        .product-brand {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 0.8rem;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--rust);
            margin-bottom: 1rem;
        }
        
        .product-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .product-actions .btn-add {
            flex: 1;
            padding: 0.7rem;
            background: var(--charcoal);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .product-actions .btn-add:hover {
            background: var(--rust);
        }
        
        /* ============================================ */
        /* CATEGORIES SECTION
        /* ============================================ */
        .categories {
            background: linear-gradient(135deg, var(--card-bg) 0%, var(--cream) 100%);
            padding: 5rem 5%;
            margin: 3rem 0;
        }
        
        .categories h2 {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .category-card {
            background: var(--card-bg);
            padding: 2rem 1rem;
            text-align: center;
            border-radius: 16px;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }
        
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
            background: var(--rust);
            color: white;
        }
        
        .category-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .category-name {
            font-weight: 600;
        }
        
        /* ============================================ */
        /* TESTIMONIALS SECTION
        /* ============================================ */
        .testimonials {
            max-width: 1200px;
            margin: 5rem auto;
            padding: 0 5%;
        }
        
        .testimonials h2 {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }
        
        .testimonial-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .testimonial-stars {
            color: #FFD700;
            margin-bottom: 1rem;
        }
        
        .testimonial-text {
            font-style: italic;
            margin-bottom: 1rem;
            color: var(--muted);
        }
        
        .testimonial-author {
            font-weight: 700;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }
        
        /* ============================================ */
        /* NEWSLETTER SECTION
        /* ============================================ */
        .newsletter {
            background: linear-gradient(135deg, var(--charcoal) 0%, #2a2a2a 100%);
            padding: 4rem 5%;
            margin: 3rem 0;
            text-align: center;
        }
        
        .newsletter h2 {
            color: white;
            margin-bottom: 1rem;
        }
        
        .newsletter p {
            color: rgba(255,255,255,0.7);
            margin-bottom: 2rem;
        }
        
        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            gap: 0.5rem;
        }
        
        .newsletter-form input {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
        }
        
        .newsletter-form button {
            padding: 1rem 2rem;
            background: var(--rust);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .newsletter-form button:hover {
            background: var(--rust-dark);
        }
        
        /* ============================================ */
        /* FOOTER
        /* ============================================ */
        footer {
            background: var(--charcoal);
            color: #888;
            padding: 4rem 5% 2rem;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-brand p {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, white, var(--rust));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }
        
        .footer-brand span {
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .footer-col h4 {
            color: white;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }
        
        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--rust);
        }
        
        .footer-col a {
            display: block;
            color: #999;
            text-decoration: none;
            margin-bottom: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .footer-col a:hover {
            color: var(--rust);
            transform: translateX(5px);
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--rust);
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
        }
        
        /* ============================================ */
        /* RESPONSIVE DESIGN
        /* ============================================ */
        @media (max-width: 1024px) {
            .hero {
                gap: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }
            
            .nav-links {
                position: fixed;
                top: 80px;
                left: -100%;
                width: 80%;
                height: calc(100vh - 80px);
                background: var(--card-bg);
                flex-direction: column;
                align-items: flex-start;
                padding: 2rem;
                transition: left 0.3s ease;
                box-shadow: var(--shadow-lg);
                z-index: 999;
            }
            
            .nav-links.open {
                left: 0;
            }
            
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-buttons {
                justify-content: center;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .hero-visual {
                display: none;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .newsletter-form {
                flex-direction: column;
            }
            
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }
        }
        
        @media (max-width: 480px) {
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn-primary, .btn-outline {
                justify-content: center;
            }
            
            .product-grid {
                grid-template-columns: 1fr;
            }
            
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Loading Animation */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--border);
            border-top-color: var(--rust);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--success);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.error {
            background: var(--error);
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <a href="index.php" class="logo">Pastimes</a>
    <div class="menu-toggle" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="nav-links">
        <a href="index.php" class="active">Home</a>
        <a href="shop.php">Shop</a>
        <a href="sell-request.php">Sell Request</a>
        <?php if ($loggedIn): ?>
            <a href="Dashboard.php"><i class="fas fa-user"></i> <?= $userName ?></a>
            <a href="orders.php"><i class="fas fa-box"></i> Orders</a>
            <a href="cart.php" class="cart-link"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-count" id="cartCount"><?= $cartCount ?></span></a>
            <a href="logout.php" class="btn-nav"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <div class="hero-tag">✨ Sustainable Fashion Movement</div>
        <h1>Give Your Wardrobe a <span>Second Life</span></h1>
        <p>Pastimes is South Africa's trusted marketplace for pre-loved fashion. Buy quality second-hand clothing, sell items you no longer wear, and join thousands of conscious consumers making a difference.</p>
        <div class="hero-buttons">
            <a href="shop.php" class="btn-primary"><i class="fas fa-shopping-bag"></i> Shop Now →</a>
            <a href="register.php" class="btn-outline"><i class="fas fa-plus-circle"></i> Start Selling →</a>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <div class="stat-number"><?= number_format($productCount) ?>+</div>
                <div class="stat-label">Items Available</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= number_format($userCount) ?>+</div>
                <div class="stat-label">Happy Customers</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">5000+</div>
                <div class="stat-label">Items Sold</div>
            </div>
        </div>
    </div>
    <div class="hero-visual">
        <div class="hero-img">
            <img src="images/hero-large.jpg" alt="Sustainable Fashion" onerror="this.src='https://placehold.co/600x600/EDE8E2/999?text=Sustainable+Fashion'">
            <div class="hero-img-overlay">Vintage Denim Collection</div>
        </div>
        <div class="hero-img">
            <img src="images/hero-small-1.jpg" alt="Fashion Accessories" onerror="this.src='https://placehold.co/300x300/EDE8E2/999?text=Accessories'">
        </div>
        <div class="hero-img">
            <img src="images/hero-small-2.jpg" alt="Happy Customer" onerror="this.src='https://placehold.co/300x300/EDE8E2/999?text=Happy+Customer'">
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories">
    <h2>Shop by Category</h2>
    <div class="categories-grid">
        <div class="category-card" onclick="window.location.href='shop.php?category=tops'">
            <div class="category-icon">👕</div>
            <div class="category-name">Tops</div>
        </div>
        <div class="category-card" onclick="window.location.href='shop.php?category=bottoms'">
            <div class="category-icon">👖</div>
            <div class="category-name">Bottoms</div>
        </div>
        <div class="category-card" onclick="window.location.href='shop.php?category=dresses'">
            <div class="category-icon">👗</div>
            <div class="category-name">Dresses</div>
        </div>
        <div class="category-card" onclick="window.location.href='shop.php?category=jackets'">
            <div class="category-icon">🧥</div>
            <div class="category-name">Jackets & Coats</div>
        </div>
        <div class="category-card" onclick="window.location.href='shop.php?category=shoes'">
            <div class="category-icon">👟</div>
            <div class="category-name">Shoes</div>
        </div>
        <div class="category-card" onclick="window.location.href='shop.php?category=accessories'">
            <div class="category-icon">👜</div>
            <div class="category-name">Accessories</div>
        </div>
    </div>
</section>

<!-- Featured Products -->
<section class="featured">
    <div class="section-header">
        <h2>Featured Items</h2>
        <a href="shop.php">View all →</a>
    </div>
    
    <?php if (empty($featuredProducts)): ?>
        <div style="text-align: center; padding: 3rem; background: var(--card-bg); border-radius: 20px;">
            <i class="fas fa-box-open" style="font-size: 3rem; color: var(--muted);"></i>
            <h3 style="margin-top: 1rem;">No products yet</h3>
            <p>Be the first to <a href="sell-request.php">list an item</a> in our marketplace!</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-img" onclick="window.location.href='product.php?id=<?= $product['ClothesID'] ?>'">
                    <img src="<?= htmlspecialchars($product['ImageURL'] ?? 'images/placeholder.jpg') ?>" alt="<?= htmlspecialchars($product['ItemName']) ?>">
                </div>
                <div class="product-info">
                    <div class="product-category"><?= ucfirst($product['Category'] ?? 'Uncategorized') ?></div>
                    <div class="product-name"><?= htmlspecialchars($product['ItemName']) ?></div>
                    <div class="product-brand"><?= htmlspecialchars($product['Brand'] ?? 'Unknown Brand') ?></div>
                    <div class="product-price">R <?= number_format($product['Price'], 2) ?></div>
                    <div class="product-actions">
                        <button class="btn-add" onclick="addToCart(<?= $product['ClothesID'] ?>, '<?= addslashes($product['ItemName']) ?>', <?= $product['Price'] ?>, '<?= $product['ImageURL'] ?>')">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- How It Works -->
<section class="categories" style="background: var(--cream);">
    <h2>How Pastimes Works</h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: var(--rust); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">1</span>
            </div>
            <h3>Find & Buy</h3>
            <p style="color: var(--muted);">Browse thousands of pre-loved items from trusted sellers across South Africa.</p>
        </div>
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: var(--rust); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">2</span>
            </div>
            <h3>Request to Sell</h3>
            <p style="color: var(--muted);">Submit your items with photos and description. Admin reviews your listing.</p>
        </div>
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: var(--rust); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">3</span>
            </div>
            <h3>Admin Approval</h3>
            <p style="color: var(--muted);">Admin reviews and approves your listing to ensure quality.</p>
        </div>
        <div style="text-align: center;">
            <div style="width: 80px; height: 80px; background: var(--rust); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                <span style="color: white; font-size: 2rem; font-weight: bold;">4</span>
            </div>
            <h3>Ship & Enjoy</h3>
            <p style="color: var(--muted);">Secure checkout, fast delivery, and enjoy your sustainable fashion!</p>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials">
    <h2>What Our Community Says</h2>
    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
            </div>
            <p class="testimonial-text">"Pastimes made it so easy to find unique vintage pieces. I've saved so much money and my wardrobe looks amazing!"</p>
            <div class="testimonial-author">— Sarah M., Cape Town</div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
            </div>
            <p class="testimonial-text">"Selling my old clothes was effortless. The admin team was helpful and my items sold within a week!"</p>
            <div class="testimonial-author">— Thabo N., Johannesburg</div>
        </div>
        <div class="testimonial-card">
            <div class="testimonial-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
            </div>
            <p class="testimonial-text">"Love the sustainable mission! Great quality items and secure checkout. Highly recommend!"</p>
            <div class="testimonial-author">— Lisa K., Durban</div>
        </div>
    </div>
</section>

<!-- Newsletter -->
<section class="newsletter">
    <h2>Join the Sustainable Fashion Movement</h2>
    <p>Subscribe to get exclusive offers, new arrivals, and sustainable fashion tips.</p>
    <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
        <input type="email" id="newsletterEmail" placeholder="Enter your email address" required>
        <button type="submit">Subscribe</button>
    </form>
</section>

<!-- Footer -->
<footer>
    <div class="footer-grid">
        <div class="footer-brand">
            <p>Pastimes</p>
            <span>Give your clothes a second life. Buy and sell pre-loved fashion with ease.</span>
        </div>
        <div class="footer-col">
            <h4>Quick Links</h4>
            <a href="shop.php">Shop</a>
            <a href="sell-request.php">Sell Request</a>
            <a href="Dashboard.php">My Account</a>
            <a href="orders.php">My Orders</a>
        </div>
        <div class="footer-col">
            <h4>Categories</h4>
            <a href="shop.php?category=tops">Tops</a>
            <a href="shop.php?category=bottoms">Bottoms</a>
            <a href="shop.php?category=dresses">Dresses</a>
            <a href="shop.php?category=jackets">Jackets</a>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <a href="#">Help Center</a>
            <a href="#">Shipping Info</a>
            <a href="#">Returns Policy</a>
            <a href="#">Contact Us</a>
        </div>
        <div class="footer-col">
            <h4>Follow Us</h4>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Pastimes. All rights reserved. | Sustainable Fashion Marketplace</p>
    </div>
</footer>

<!-- Toast Notification -->
<div id="toast" class="toast"></div>

<script>
// ============================================
// CART FUNCTIONS
// ============================================
let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');

function addToCart(id, name, price, image) {
    let existing = cart.find(item => item.id == id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({id, name, price, image, quantity: 1});
    }
    localStorage.setItem('pastimes_cart', JSON.stringify(cart));
    updateCartDisplay();
    showToast(name + ' added to cart!', 'success');
}

function updateCartDisplay() {
    let total = cart.reduce((sum, item) => sum + item.quantity, 0);
    let cartBadges = document.querySelectorAll('.cart-count');
    cartBadges.forEach(badge => {
        if (badge) badge.innerText = total;
    });
}

function showToast(message, type = 'success') {
    let toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast show ' + type;
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function subscribeNewsletter(event) {
    event.preventDefault();
    let email = document.getElementById('newsletterEmail').value;
    showToast('Thanks for subscribing! Check your email for updates.', 'success');
    document.getElementById('newsletterEmail').value = '';
}

// ============================================
// NAVIGATION FUNCTIONS
// ============================================
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    navLinks.classList.toggle('open');
}

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const nav = document.querySelector('nav');
    if (window.scrollY > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// Close mobile menu when clicking a link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', () => {
        document.querySelector('.nav-links').classList.remove('open');
    });
});

// Update cart display on page load
updateCartDisplay();

// ============================================
// ANIMATIONS ON SCROLL
// ============================================
const animateElements = document.querySelectorAll('.product-card, .category-card, .testimonial-card');
const observerOptions = { threshold: 0.1, rootMargin: '0px 0px -50px 0px' };

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

animateElements.forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'all 0.6s ease';
    observer.observe(el);
});
</script>

<!-- AOS Animation Library -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });
</script>

</body>
</html>