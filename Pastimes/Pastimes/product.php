<?php
session_start();
require_once 'DBConn.php';

$loggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$userName = $loggedIn ? htmlspecialchars($_SESSION['user_name']) : '';

$id = (int) ($_GET['id'] ?? 0);
if (!$id) { header("Location: shop.php"); exit(); }

$sql    = "SELECT c.*, u.FullName AS SellerName, u.Email AS SellerEmail
           FROM tblclothes c
           LEFT JOIN tbluser u ON c.SellerID = u.UserID
           WHERE c.ClothesID = $id LIMIT 1";
$result = mysqli_query($conn, $sql);
$p      = mysqli_fetch_assoc($result);

if (!$p) { header("Location: shop.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($p['ItemName']) ?> – Pastimes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #F7F3EE; --charcoal: #1C1C1C; --rust: #C0533A;
    --rust-hover: #A8432C; --muted: #7A7065; --border: #E2DDD7; --card-bg: #FFFFFF;
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--charcoal); }
  nav {
    background: var(--card-bg); border-bottom: 1px solid var(--border);
    padding: 0 2rem; height: 60px; display: flex; align-items: center;
    justify-content: space-between; position: sticky; top: 0; z-index: 100;
  }
  .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--charcoal); text-decoration: none; }
  .nav-links { display: flex; gap: 1.5rem; align-items: center; }
  .nav-links a { color: var(--muted); text-decoration: none; font-size: 0.9rem; }
  .nav-links a:hover { color: var(--charcoal); }

  main { max-width: 1000px; margin: 2.5rem auto; padding: 0 2rem 4rem; }
  .back { font-size: 0.88rem; color: var(--muted); text-decoration: none; display: inline-block; margin-bottom: 1.5rem; }
  .back:hover { color: var(--charcoal); }

  .product-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; }

  .product-image {
    border-radius: 14px; overflow: hidden;
    background: #EDE8E2;
    aspect-ratio: 1;
    display: flex; align-items: center; justify-content: center; font-size: 6rem;
  }
  .product-image img { width: 100%; height: 100%; object-fit: cover; display: block; }

  .brand-label { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--muted); margin-bottom: 0.5rem; }
  h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 0.7rem; line-height: 1.2; }
  .price { font-size: 1.8rem; font-weight: 600; color: var(--rust); margin-bottom: 1.2rem; }

  .tags { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
  .tag {
    padding: 0.3rem 0.75rem; border-radius: 20px; font-size: 0.8rem;
    background: var(--cream); border: 1px solid var(--border); color: var(--muted);
  }

  .desc-label { font-size: 0.78rem; font-weight: 500; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 0.5rem; }
  .desc { font-size: 0.92rem; line-height: 1.7; color: var(--charcoal); margin-bottom: 1.5rem; }

  .seller-box {
    border: 1px solid var(--border); border-radius: 10px; padding: 1rem;
    display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.5rem;
  }
  .seller-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: #EDE8E2; display: flex; align-items: center;
    justify-content: center; font-size: 1.1rem; flex-shrink: 0;
  }
  .seller-label { font-size: 0.78rem; color: var(--muted); }
  .seller-name  { font-size: 0.92rem; font-weight: 500; }

  .btn-cart {
    width: 100%; padding: 0.85rem; background: var(--rust); color: #fff;
    border: none; border-radius: 9px; font-family: 'DM Sans', sans-serif;
    font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s;
    text-align: center; text-decoration: none; display: block;
  }
  .btn-cart:hover { background: var(--rust-hover); }

  .login-note { font-size: 0.85rem; color: var(--muted); text-align: center; margin-top: 0.8rem; }
  .login-note a { color: var(--rust); text-decoration: none; }

  @media (max-width: 680px) {
    .product-layout { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>
<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div class="nav-links">
    <a href="shop.php">Shop</a>
    <a href="sell.php">Sell</a>
    <?php if ($loggedIn): ?>
      <a href="Dashboard.php">👤 <?= $userName ?></a>
      <a href="Logout.php">Logout</a>
    <?php else: ?>
      <a href="Login.php">Login</a>
    <?php endif; ?>
  </div>
</nav>
<!-- Image -->
<div class="product-image">
  <?php 
  $hasImage = !empty($p['ImageURL']) && file_exists($p['ImageURL']);
  if ($hasImage): 
  ?>
    <img src="<?= htmlspecialchars($p['ImageURL']) ?>" alt="<?= htmlspecialchars($p['ItemName']) ?>">
  <?php else: ?>
    <!-- FALLBACK: Show large emoji placeholder -->
    <?= match(strtolower($p['Category'] ?? '')) {
      'tops','shirts'  => '👚',
      'bottoms','jeans'=> '👖',
      'dresses'        => '👗',
      'shoes'          => '👟',
      'jackets','coats'=> '🧥',
      default          => '🛍️'
    } ?>
  <?php endif; ?>
</div>

    <!-- Details -->
    <div>
      <?php if ($p['Brand']): ?>
        <div class="brand-label"><?= htmlspecialchars($p['Brand']) ?></div>
      <?php endif; ?>

      <h1><?= htmlspecialchars($p['ItemName']) ?></h1>
      <div class="price">R<?= number_format($p['Price'], 2) ?></div>

      <div class="tags">
        <?php if ($p['ClothesSize']): ?><span class="tag"><?= htmlspecialchars($p['ClothesSize']) ?></span><?php endif; ?>
        <?php if ($p['Category']):    ?><span class="tag"><?= htmlspecialchars($p['Category']) ?></span><?php endif; ?>
        <?php if ($p['Condition']):   ?><span class="tag"><?= htmlspecialchars($p['Condition']) ?></span><?php endif; ?>
      </div>

      <?php if ($p['Description']): ?>
        <div class="desc-label">Description</div>
        <div class="desc"><?= nl2br(htmlspecialchars($p['Description'])) ?></div>
      <?php endif; ?>

      <div class="seller-box">
        <div class="seller-avatar">👤</div>
        <div>
          <div class="seller-label">Sold by</div>
          <div class="seller-name"><?= htmlspecialchars($p['SellerName'] ?? 'Unknown') ?></div>
        </div>
      </div>

      <?php if ($loggedIn): ?>
        <a href="checkout.php?id=<?= $p['ClothesID'] ?>" class="btn-cart">🛒 Add to Cart</a>
      <?php else: ?>
        <a href="Login.php" class="btn-cart">Login to Purchase</a>
        <p class="login-note">Don't have an account? <a href="register.php">Register here</a></p>
      <?php endif; ?>
    </div>
  </div>
</main>
</body>
</html>