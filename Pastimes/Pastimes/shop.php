<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name']) : '';


$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$size     = trim($_GET['size']     ?? '');
$sort     = trim($_GET['sort']     ?? 'newest');


$tableCheck = mysqli_query($conn, "SHOW TABLES LIKE 'tblclothes'");
if (mysqli_num_rows($tableCheck) == 0) {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS tblclothes (
        ClothesID INT AUTO_INCREMENT PRIMARY KEY,
        SellerID INT,
        ItemName VARCHAR(100) NOT NULL,
        Brand VARCHAR(50),
        Category VARCHAR(50),
        ClothesSize VARCHAR(10),
        `Condition` VARCHAR(20),
        Price DECIMAL(10,2),
        Description TEXT,
        ImageURL VARCHAR(255),
        DateListed DATE,
        FOREIGN KEY (SellerID) REFERENCES tbluser(UserID) ON DELETE SET NULL
    )";
    mysqli_query($conn, $createTableSQL);
}

// ── Build query ───────────────────────────────────────────────────────────────
$where  = ["1=1"];

if ($search) {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(c.ItemName LIKE '%$s%' OR c.Brand LIKE '%$s%' OR c.Description LIKE '%$s%')";
}
if ($category) {
    $cat = mysqli_real_escape_string($conn, $category);
    $where[] = "c.Category = '$cat'";
}
if ($size) {
    $sz = mysqli_real_escape_string($conn, $size);
    $where[] = "c.ClothesSize = '$sz'";
}

$orderBy = match($sort) {
    'price_asc'  => 'c.Price ASC',
    'price_desc' => 'c.Price DESC',
    default      => 'c.DateListed DESC',
};

$whereSQL = implode(' AND ', $where);
$sql = "SELECT c.*, u.FullName AS SellerName 
        FROM tblclothes c 
        LEFT JOIN tbluser u ON c.SellerID = u.UserID
        WHERE $whereSQL
        ORDER BY $orderBy";

$result = mysqli_query($conn, $sql);

if (!$result) {
    $products = [];
} else {
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

// ── Category list for filter ──────────────────────────────────────────────────
$categories = [];
$catResult = mysqli_query($conn, "SELECT DISTINCT Category FROM tblclothes WHERE Category IS NOT NULL AND Category != '' ORDER BY Category");
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
<title>Shop – Pastimes</title>
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
  body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--charcoal); min-height: 100vh; }

  nav {
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    padding: 0 2rem;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--charcoal); text-decoration: none; }
  .nav-links { display: flex; gap: 1.5rem; align-items: center; }
  .nav-links a { color: var(--muted); text-decoration: none; font-size: 0.9rem; transition: color 0.2s; }
  .nav-links a:hover, .nav-links a.active { color: var(--charcoal); }
  .btn-nav { padding: 0.45rem 1rem; background: var(--rust); color: #fff !important; border-radius: 7px; font-size: 0.85rem !important; }
  .btn-nav:hover { background: var(--rust-hover); }

  .page-header {
    max-width: 1100px;
    margin: 2.5rem auto 0;
    padding: 0 2rem;
  }
  .page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    margin-bottom: 0.3rem;
  }
  .page-header p { color: var(--muted); font-size: 0.9rem; }

  .search-bar {
    max-width: 1100px;
    margin: 1.5rem auto 0;
    padding: 0 2rem;
  }
  .search-wrap {
    display: flex;
    gap: 0;
    border: 1px solid var(--border);
    border-radius: 9px;
    overflow: hidden;
    background: var(--card-bg);
  }
  .search-wrap input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    color: var(--charcoal);
    background: transparent;
    outline: none;
  }
  .search-wrap button {
    padding: 0 1.2rem;
    background: var(--rust);
    color: #fff;
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
  }
  .search-wrap button:hover { background: var(--rust-hover); }

  .filters {
    max-width: 1100px;
    margin: 1rem auto 0;
    padding: 0 2rem;
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
  }
  .filters select {
    padding: 0.5rem 0.85rem;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    color: var(--charcoal);
    background: var(--card-bg);
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
  }
  .filters select:focus { border-color: var(--rust); }
  .filter-count {
    margin-left: auto;
    font-size: 0.85rem;
    color: var(--muted);
  }
  .clear-link {
    font-size: 0.85rem;
    color: var(--rust);
    text-decoration: none;
  }
  .clear-link:hover { text-decoration: underline; }

  .grid-wrap {
    max-width: 1100px;
    margin: 1.5rem auto 4rem;
    padding: 0 2rem;
  }
  .product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
  }

  .product-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
    display: block;
  }
  .product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.09);
  }
  .product-img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
    background: #EDE8E2;
  }
  .product-info { padding: 1rem; }
  .product-meta {
    display: flex;
    gap: 0.4rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
  }
  .tag {
    font-size: 0.72rem;
    padding: 0.15rem 0.5rem;
    border-radius: 20px;
    background: var(--cream);
    color: var(--muted);
    border: 1px solid var(--border);
  }
  .product-name {
    font-size: 0.95rem;
    font-weight: 600;
    margin-bottom: 0.2rem;
  }
  .product-brand {
    font-size: 0.8rem;
    color: var(--muted);
    margin-bottom: 0.6rem;
  }
  .product-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .product-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--rust);
  }
  .seller-name {
    font-size: 0.7rem;
    color: var(--muted);
  }

  .empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 4rem 2rem;
    color: var(--muted);
  }
  .empty h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    margin-bottom: 0.5rem;
    color: var(--charcoal);
  }
  .empty a {
    display: inline-block;
    margin-top: 1.2rem;
    padding: 0.65rem 1.4rem;
    background: var(--rust);
    color: #fff;
    border-radius: 7px;
    text-decoration: none;
    font-size: 0.9rem;
  }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="shop.php" class="active">Shop</a>
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

<div class="page-header">
  <h1>Shop</h1>
  <p>Discover unique pre-loved fashion</p>
</div>

<!-- Search -->
<div class="search-bar">
  <form method="GET" action="shop.php">
    <div class="search-wrap">
      <input type="text" name="search"
             placeholder="Search items, brands..."
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit">Search</button>
    </div>
  </form>
</div>

<!-- Filters -->
<form method="GET" action="shop.php" id="filter-form">
  <?php if ($search): ?>
    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
  <?php endif; ?>
  <div class="filters">
    <select name="category" onchange="document.getElementById('filter-form').submit()">
      <option value="">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
          <?= htmlspecialchars(ucfirst($cat)) ?>
        </option>
      <?php endforeach; ?>
      <?php if (empty($categories)): ?>
        <option value="tops"    <?= $category==='tops'    ? 'selected':'' ?>>Tops</option>
        <option value="bottoms" <?= $category==='bottoms' ? 'selected':'' ?>>Bottoms</option>
        <option value="dresses" <?= $category==='dresses' ? 'selected':'' ?>>Dresses</option>
        <option value="shoes"   <?= $category==='shoes'   ? 'selected':'' ?>>Shoes</option>
        <option value="jackets" <?= $category==='jackets' ? 'selected':'' ?>>Jackets</option>
      <?php endif; ?>
    </select>

    <select name="size" onchange="document.getElementById('filter-form').submit()">
      <option value="">All Sizes</option>
      <?php foreach (['XS','S','M','L','XL','XXL'] as $sz): ?>
        <option value="<?= $sz ?>" <?= $size === $sz ? 'selected' : '' ?>><?= $sz ?></option>
      <?php endforeach; ?>
    </select>

    <select name="sort" onchange="document.getElementById('filter-form').submit()">
      <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Newest</option>
      <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Price: Low to High</option>
      <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Price: High to Low</option>
    </select>

    <span class="filter-count"><?= count($products) ?> item<?= count($products) !== 1 ? 's' : '' ?></span>

    <?php if ($search || $category || $size): ?>
      <a href="shop.php" class="clear-link">Clear filters</a>
    <?php endif; ?>
  </div>
</form>

<!-- PRODUCT GRID - SHOWS ACTUAL IMAGES FROM DATABASE -->
<div class="grid-wrap">
  <div class="product-grid">
    <?php if (empty($products)): ?>
      <div class="empty">
        <h2>No items found</h2>
        <p>Try adjusting your search or filters.</p>
        <p style="margin-top: 0.5rem; font-size: 0.8rem;">📷 <strong>Tip:</strong> Go to <a href="sell.php">Sell</a> to add your first item!</p>
        <?php if ($loggedIn): ?>
          <a href="sell.php">Sell an Item</a>
        <?php else: ?>
          <a href="register.php">Join to start selling</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <?php foreach ($products as $p): ?>
        <a class="product-card" href="product.php?id=<?= $p['ClothesID'] ?>">
          <?php 
          // CHECK IF PRODUCT HAS AN IMAGE IN DATABASE
          $hasImage = !empty($p['ImageURL']) && file_exists($p['ImageURL']);
          
          if ($hasImage): 
          ?>
            <!-- SHOW ACTUAL IMAGE FROM DATABASE -->
            <img class="product-img" src="<?= htmlspecialchars($p['ImageURL']) ?>" alt="<?= htmlspecialchars($p['ItemName']) ?>">
          <?php else: ?>
            <!-- NO IMAGE - SHOW COLORED PLACEHOLDER WITH PRODUCT NAME -->
            <div class="product-img" style="background: #EDE8E2; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #999;">
              <?= strtoupper(substr($p['ItemName'], 0, 2)) ?>
            </div>
          <?php endif; ?>
          <div class="product-info">
            <div class="product-meta">
              <?php if ($p['ClothesSize']): ?>
                <span class="tag"><?= htmlspecialchars($p['ClothesSize']) ?></span>
              <?php endif; ?>
              <?php if ($p['Condition']): ?>
                <span class="tag"><?= htmlspecialchars($p['Condition']) ?></span>
              <?php endif; ?>
            </div>
            <div class="product-name"><?= htmlspecialchars($p['ItemName']) ?></div>
            <div class="product-brand"><?= htmlspecialchars($p['Brand'] ?? '') ?></div>
            <div class="product-footer">
              <span class="product-price">R<?= number_format($p['Price'], 2) ?></span>
              <span class="seller-name">by <?= htmlspecialchars($p['SellerName'] ?? 'Unknown') ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
