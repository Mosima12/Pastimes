<?php
// shop.php - Fixed to show images from your folders
session_start();
require_once 'DBConn.php';

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name']) : '';

// Get filter values
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$size = isset($_GET['size']) ? trim($_GET['size']) : '';

// Build query
$where = ["1=1"];
if (!empty($search)) {
    $searchSafe = mysqli_real_escape_string($conn, $search);
    $where[] = "(ItemName LIKE '%$searchSafe%' OR Brand LIKE '%$searchSafe%')";
}
if (!empty($category)) {
    $categorySafe = mysqli_real_escape_string($conn, $category);
    $where[] = "Category = '$categorySafe'";
}
if (!empty($size)) {
    $sizeSafe = mysqli_real_escape_string($conn, $size);
    $where[] = "ClothesSize = '$sizeSafe'";
}

$whereSQL = implode(' AND ', $where);
$products = mysqli_query($conn, "SELECT * FROM tblclothes WHERE $whereSQL ORDER BY DateListed DESC");

// Get categories
$categories = [];
$catResult = mysqli_query($conn, "SELECT DISTINCT Category FROM tblclothes WHERE Category IS NOT NULL");
if ($catResult) {
    while ($row = mysqli_fetch_assoc($catResult)) {
        $categories[] = $row['Category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #F7F3EE; color: #1C1C1C; }
        
        nav {
            background: white;
            border-bottom: 1px solid #E2DDD7;
            padding: 0 5%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1C1C1C 0%, #C0533A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .nav-links a { color: #7A7065; text-decoration: none; font-size: 0.95rem; transition: color 0.3s; }
        .nav-links a:hover { color: #1C1C1C; }
        .btn-nav { background: #C0533A; color: white !important; padding: 0.5rem 1.2rem; border-radius: 50px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .page-header { text-align: center; margin-bottom: 2rem; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 2rem; margin-bottom: 0.5rem; }
        .page-header p { color: #7A7065; }
        
        .filter-panel { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .filter-group { display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; }
        .filter-group input, .filter-group select { padding: 0.6rem 1rem; border: 1px solid #E2DDD7; border-radius: 8px; background: #F7F3EE; font-family: 'DM Sans', sans-serif; }
        .btn-filter { background: #C0533A; color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 8px; cursor: pointer; }
        .clear-link { color: #C0533A; text-decoration: none; }
        .clear-link:hover { text-decoration: underline; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; }
        .product-card { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.3s; cursor: pointer; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .product-img { height: 260px; overflow: hidden; background: #E2DDD7; display: flex; align-items: center; justify-content: center; }
        .product-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .product-card:hover .product-img img { transform: scale(1.05); }
        .product-info { padding: 1rem; }
        .product-category-badge {
            display: inline-block;
            background: #F7F3EE;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            color: #C0533A;
            margin-bottom: 0.5rem;
        }
        .product-name { font-weight: 700; font-size: 1rem; margin-bottom: 0.3rem; }
        .product-brand { font-size: 0.8rem; color: #7A7065; margin-bottom: 0.5rem; }
        .product-price { font-size: 1.2rem; font-weight: 700; color: #C0533A; margin-bottom: 0.8rem; }
        .btn-add { width: 100%; padding: 0.6rem; background: #1C1C1C; color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.3s; }
        .btn-add:hover { background: #C0533A; }
        
        footer { background: #1C1C1C; color: #999; padding: 2rem; margin-top: 3rem; text-align: center; }
        
        @media (max-width: 768px) {
            .filter-group { flex-direction: column; }
            .filter-group input, .filter-group select { width: 100%; }
            .product-grid { grid-template-columns: 1fr; }
            .container { padding: 1rem; }
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">Pastimes</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="shop.php" style="color:#C0533A;">Shop</a>
        <a href="sell-request.php">Sell Request</a>
        <?php if ($loggedIn): ?>
            <a href="dashboard.php"><?= $userName ?></a>
            <a href="orders.php">Orders</a>
            <a href="cart.php">Cart</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="page-header">
        <h1>Shop Pre-loved Fashion</h1>
        <p>Discover unique second-hand clothing at affordable prices</p>
    </div>
    
    <div class="filter-panel">
        <form method="GET" class="filter-group">
            <input type="text" name="search" placeholder="Search items..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat ?>" <?= $category == $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="size">
                <option value="">All Sizes</option>
                <option value="XS">XS</option>
                <option value="S">S</option>
                <option value="M">M</option>
                <option value="L">L</option>
                <option value="XL">XL</option>
            </select>
            <button type="submit" class="btn-filter">Filter</button>
            <a href="shop.php" class="clear-link">Clear</a>
        </form>
    </div>
    
    <div class="product-grid">
        <?php if (mysqli_num_rows($products) == 0): ?>
            <div style="text-align:center; padding:3rem; grid-column:1/-1;">
                <i class="fas fa-box-open" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No products found</h3>
                <p><a href="sell-request.php">Be the first to list an item</a></p>
            </div>
        <?php else: ?>
            <?php while ($product = mysqli_fetch_assoc($products)): 
                // Get image path - try multiple locations
                $imageUrl = '';
                
                // Check if ImageURL is set in database
                if (!empty($product['ImageURL'])) {
                    // Check if file exists at that path
                    if (file_exists($product['ImageURL'])) {
                        $imageUrl = $product['ImageURL'];
                    }
                    // Check in images folder
                    elseif (file_exists('images/' . basename($product['ImageURL']))) {
                        $imageUrl = 'images/' . basename($product['ImageURL']);
                    }
                    // Check in uploads folder
                    elseif (file_exists('uploads/' . basename($product['ImageURL']))) {
                        $imageUrl = 'uploads/' . basename($product['ImageURL']);
                    }
                }
                
                // If still no image, try default product image
                if (empty($imageUrl)) {
                    $defaultPaths = [
                        'images/product' . $product['ClothesID'] . '.jpg',
                        'images/product' . $product['ClothesID'] . '.png',
                        'uploads/product' . $product['ClothesID'] . '.jpg',
                        'images/product1.jpg'
                    ];
                    foreach ($defaultPaths as $path) {
                        if (file_exists($path)) {
                            $imageUrl = $path;
                            break;
                        }
                    }
                }
            ?>
            <div class="product-card" onclick="window.location.href='product.php?id=<?= $product['ClothesID'] ?>'">
                <div class="product-img">
                    <?php if (!empty($imageUrl) && file_exists($imageUrl)): ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($product['ItemName']) ?>">
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-tshirt" style="font-size: 4rem; color: #999;"></i>
                            <p style="margin-top: 10px; font-size: 0.8rem; color: #999;"><?= htmlspecialchars($product['ItemName']) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <div class="product-category-badge"><?= strtoupper($product['Category'] ?? 'OTHER') ?></div>
                    <div class="product-name"><?= htmlspecialchars($product['ItemName']) ?></div>
                    <div class="product-brand"><?= htmlspecialchars($product['Brand'] ?? 'No Brand') ?></div>
                    <div class="product-price">R <?= number_format($product['Price'], 2) ?></div>
                    <button class="btn-add" onclick="event.stopPropagation(); addToCart(<?= $product['ClothesID'] ?>, '<?= addslashes($product['ItemName']) ?>', <?= $product['Price'] ?>, '<?= htmlspecialchars($imageUrl) ?>')">
                        <i class="fas fa-cart-plus"></i> Add to Cart
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>&copy; 2026 Pastimes - Sustainable Fashion Marketplace</p>
</footer>

<script>
function addToCart(id, name, price, image) {
    let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
    let existing = cart.find(item => item.id == id);
    if (existing) {
        existing.quantity++;
    } else {
        cart.push({id, name, price, image, quantity: 1});
    }
    localStorage.setItem('pastimes_cart', JSON.stringify(cart));
    alert(name + ' added to cart!');
    
    // Update cart count if badge exists
    let total = cart.reduce((sum, item) => sum + item.quantity, 0);
    let badges = document.querySelectorAll('.cart-count');
    badges.forEach(badge => {
        if (badge) badge.innerText = total;
    });
}
</script>

</body>
</html>