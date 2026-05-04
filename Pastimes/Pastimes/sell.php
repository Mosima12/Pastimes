<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

// Must be logged in to sell
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: Login.php");
    exit();
}

$loggedIn = true;
$userName = htmlspecialchars($_SESSION['user_name']);
$sellerID = (int) $_SESSION['user_id'];

$checkSeller = mysqli_query($conn, "SELECT UserID FROM tbluser WHERE UserID = $sellerID");
if (mysqli_num_rows($checkSeller) == 0) {
    die("Error: Your user account (ID: $sellerID) does not exist in the database. Please contact admin.");
}

$error   = '';
$success = '';


$sticky_itemname = '';
$sticky_brand = '';
$sticky_category = '';
$sticky_size = '';
$sticky_condition = '';
$sticky_price = '';
$sticky_description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName    = trim($_POST['itemname']    ?? '');
    $brand       = trim($_POST['brand']       ?? '');
    $category    = trim($_POST['category']    ?? '');
    $size        = trim($_POST['size']        ?? '');
    $condition   = trim($_POST['condition']   ?? '');
    $price       = trim($_POST['price']       ?? '');
    $description = trim($_POST['description'] ?? '');

    
    $sticky_itemname = htmlspecialchars($itemName);
    $sticky_brand = htmlspecialchars($brand);
    $sticky_category = $category;
    $sticky_size = $size;
    $sticky_condition = $condition;
    $sticky_price = htmlspecialchars($price);
    $sticky_description = htmlspecialchars($description);

    if (empty($itemName) || empty($category) || empty($price)) {
        $error = "Item name, category, and price are required.";
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = "Please enter a valid price.";
    } else {
      
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg','jpeg','png','webp','gif'];
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $error = "Image must be JPG, PNG, WEBP, or GIF.";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $error = "Image must be under 5MB.";
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $filename  = uniqid('img_') . '.' . $ext;
                $dest      = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imagePath = 'uploads/' . $filename;
                } else {
                    $error = "Failed to upload image.";
                }
            }
        }

        if (!$error) {
            // ── Insert into tblclothes ────────────────────────────────────────
            $safeItem  = mysqli_real_escape_string($conn, $itemName);
            $safeBrand = mysqli_real_escape_string($conn, $brand);
            $safeCat   = mysqli_real_escape_string($conn, $category);
            $safeSize  = mysqli_real_escape_string($conn, $size);
            $safeCond  = mysqli_real_escape_string($conn, $condition);
            $safeDesc  = mysqli_real_escape_string($conn, $description);
            $safeImg   = mysqli_real_escape_string($conn, $imagePath);
            $safePrice = (float) $price;
            $todayDate = date('Y-m-d');

            $sql = "INSERT INTO tblclothes 
                        (SellerID, ItemName, Brand, Category, ClothesSize, `Condition`, Price, Description, ImageURL, DateListed)
                    VALUES
                        ($sellerID, '$safeItem', '$safeBrand', '$safeCat', '$safeSize', '$safeCond', $safePrice, '$safeDesc', '$safeImg', '$todayDate')";

            if (mysqli_query($conn, $sql)) {
                $success = "Your item has been listed successfully!";
                // Clear sticky values after success
                $sticky_itemname = $sticky_brand = $sticky_category = $sticky_size = $sticky_condition = $sticky_price = $sticky_description = '';
            } else {
                $error = "Failed to list item: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sell an Item – Pastimes</title>
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

  main {
    max-width: 700px;
    margin: 3rem auto;
    padding: 0 1.5rem 4rem;
  }

  h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    margin-bottom: 0.3rem;
  }
  .subtitle { color: var(--muted); font-size: 0.9rem; margin-bottom: 2rem; }

  .alert {
    padding: 0.9rem 1.1rem;
    border-radius: 9px;
    font-size: 0.9rem;
    margin-bottom: 1.5rem;
    line-height: 1.5;
  }
  .alert-error   { background: #FDF0ED; color: var(--rust);  border: 1px solid #F0C4BA; }
  .alert-success { background: #EDF7F0; color: #2D7A4F;      border: 1px solid #B8DFC8; }

  .form-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 2px 16px rgba(0,0,0,0.04);
  }

  .upload-zone {
    border: 2px dashed var(--border);
    border-radius: 10px;
    padding: 2.5rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    position: relative;
    margin-bottom: 1.5rem;
    background: var(--cream);
  }
  .upload-zone:hover { border-color: var(--rust); background: #FAF7F4; }
  .upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
  }
  .upload-icon { font-size: 2rem; margin-bottom: 0.6rem; }
  .upload-text { font-size: 0.9rem; color: var(--muted); }
  .upload-text strong { color: var(--rust); }
  #preview {
    width: 100%;
    max-height: 260px;
    object-fit: cover;
    border-radius: 8px;
    display: none;
    margin-bottom: 0.5rem;
  }

  .section-label {
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
    margin: 1.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border);
  }

  .field { margin-bottom: 1.1rem; }
  .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
  .row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }

  label {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--charcoal);
    margin-bottom: 0.4rem;
  }
  label .req { color: var(--rust); }

  input[type="text"],
  input[type="number"],
  textarea,
  select {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.92rem;
    color: var(--charcoal);
    background: var(--cream);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  input:focus, textarea:focus, select:focus {
    border-color: var(--rust);
    box-shadow: 0 0 0 3px rgba(192,83,58,0.1);
    background: #fff;
  }
  textarea { resize: vertical; min-height: 100px; line-height: 1.6; }

  .price-wrap { position: relative; }
  .price-wrap input { padding-left: 2rem; }
  .price-prefix {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 0.9rem;
    pointer-events: none;
  }

  .btn-submit {
    width: 100%;
    padding: 0.85rem;
    background: var(--rust);
    color: #fff;
    border: none;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 1.5rem;
  }
  .btn-submit:hover { background: var(--rust-hover); }

  .back-link {
    display: inline-block;
    margin-top: 1.2rem;
    font-size: 0.88rem;
    color: var(--muted);
    text-decoration: none;
  }
  .back-link:hover { color: var(--charcoal); }

  @media (max-width: 560px) {
    .row-2, .row-3 { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="shop.php">Shop</a>
    <a href="sell.php" class="active">Sell</a>
    <a href="Dashboard.php">👤 <?= $userName ?></a>
    <a href="Logout.php">Logout</a>
  </div>
</nav>

<main>
  <h1>Sell an Item</h1>
  <p class="subtitle">Fill in the details below to list your item for sale.</p>

  <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">
      ✔ <?= $success ?>
      <br><a href="shop.php" style="color:#2D7A4F;font-weight:500">View your listing in the shop →</a>
    </div>
  <?php endif; ?>

  <div class="form-card">
    <form method="POST" enctype="multipart/form-data">

      <div class="section-label">Photo</div>
      <div class="upload-zone" id="upload-zone">
        <input type="file" name="image" accept="image/*" id="image-input" onchange="previewImage(this)">
        <img id="preview" alt="Preview">
        <div id="upload-placeholder">
          <div class="upload-icon">📷</div>
          <div class="upload-text">
            <strong>Click to upload</strong> or drag and drop<br>
            JPG, PNG, WEBP up to 5MB
          </div>
        </div>
      </div>

      <div class="section-label">Item Details</div>

      <div class="row-2">
        <div class="field">
          <label>Item Name <span class="req">*</span></label>
          <input type="text" name="itemname" placeholder="e.g. Vintage Denim Jacket" value="<?= htmlspecialchars($sticky_itemname) ?>" required>
        </div>
        <div class="field">
          <label>Brand</label>
          <input type="text" name="brand" placeholder="e.g. Levi's" value="<?= htmlspecialchars($sticky_brand) ?>">
        </div>
      </div>

      <div class="row-3">
        <div class="field">
          <label>Category <span class="req">*</span></label>
          <select name="category" required>
            <option value="">Select</option>
            <?php foreach (['tops','bottoms','dresses','jackets','shoes','accessories','other'] as $cat): ?>
              <option value="<?= $cat ?>" <?= $sticky_category === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Size</label>
          <select name="size">
            <option value="">Select</option>
            <?php foreach (['XS','S','M','L','XL','XXL'] as $sz): ?>
              <option value="<?= $sz ?>" <?= $sticky_size === $sz ? 'selected' : '' ?>><?= $sz ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Condition</label>
          <select name="condition">
            <option value="">Select</option>
            <?php foreach (['New','Like new','Excellent','Good','Fair'] as $cond): ?>
              <option value="<?= $cond ?>" <?= $sticky_condition === $cond ? 'selected' : '' ?>><?= $cond ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="section-label">Pricing</div>
      <div class="field" style="max-width:200px">
        <label>Price (ZAR) <span class="req">*</span></label>
        <div class="price-wrap">
          <span class="price-prefix">R</span>
          <input type="number" name="price" step="0.01" min="1" placeholder="0.00" value="<?= htmlspecialchars($sticky_price) ?>" required>
        </div>
      </div>

      <div class="section-label">Description</div>
      <div class="field">
        <label>Description</label>
        <textarea name="description" placeholder="Describe the item — measurements, flaws, fabric, why you're selling it..."><?= htmlspecialchars($sticky_description) ?></textarea>
      </div>

      <button type="submit" class="btn-submit">List Item for Sale</button>
    </form>
  </div>

  <a class="back-link" href="shop.php">← Back to shop</a>
</main>

<script>
function previewImage(input) {
  const preview = document.getElementById('preview');
  const placeholder = document.getElementById('upload-placeholder');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>