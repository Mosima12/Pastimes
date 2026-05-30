<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?redirect=sell-request.php");
    exit();
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemName = mysqli_real_escape_string($conn, trim($_POST['item_name'] ?? ''));
    $brand = mysqli_real_escape_string($conn, trim($_POST['brand'] ?? ''));
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $size = mysqli_real_escape_string($conn, $_POST['size'] ?? '');
    $condition = mysqli_real_escape_string($conn, $_POST['condition'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $sellerId = $_SESSION['user_id'];
    
    // Validate
    if (empty($itemName) || empty($category) || $price <= 0) {
        $error = "Please fill in all required fields (Item Name, Category, Price)";
    } else {
        // Handle image upload
        $imagePath = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
            $uploadDir = 'uploads/';
            
            // Create uploads folder if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $itemName) . '.' . $ext;
                $destination = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destination)) {
                    $imagePath = $destination;
                } else {
                    $error = "Failed to upload image. Please check folder permissions.";
                }
            } else {
                $error = "Invalid file type. Please upload JPG, PNG, WEBP, or GIF.";
            }
        }
        
        if (empty($error)) {
            // Insert into database
            $sql = "INSERT INTO tblclothes (SellerID, ItemName, Brand, Category, ClothesSize, `Condition`, Price, Description, ImageURL, Status, DateListed) 
                    VALUES ($sellerId, '$itemName', '$brand', '$category', '$size', '$condition', $price, '$description', '$imagePath', 'pending_approval', CURDATE())";
            
            if (mysqli_query($conn, $sql)) {
                $message = "Your listing request has been submitted for admin approval!";
                // Clear form on success via redirect to prevent resubmission
                echo "<script>setTimeout(function(){ window.location.href = 'sell-request.php?success=1'; }, 2000);</script>";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Your listing request has been submitted for admin approval!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Sell - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: #F7F3EE;
            color: #1C1C1C;
        }
        
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
            color: #1C1C1C;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #7A7065;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #1C1C1C;
        }
        
        .container {
            max-width: 700px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .sell-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #7A7065;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.85rem;
        }
        
        .required {
            color: #C0533A;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #E2DDD7;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #C0533A;
        }
        
        .row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
        
        .upload-zone {
            border: 2px dashed #E2DDD7;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #F7F3EE;
        }
        
        .upload-zone:hover {
            border-color: #C0533A;
            background: #FAF7F4;
        }
        
        .upload-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: #C0533A;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }
        
        .btn-submit:hover {
            background: #A8432C;
        }
        
        .alert-success {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #2E7D32;
        }
        
        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #C62828;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #7A7065;
            text-decoration: none;
        }
        
        .back-link:hover {
            color: #C0533A;
        }
        
        @media (max-width: 640px) {
            .row-2, .row-3 {
                grid-template-columns: 1fr;
            }
            
            .sell-card {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">Pastimes</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="sell-request.php" style="color:#C0533A;">Sell Request</a>
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <a href="Dashboard.php">Account: <?= htmlspecialchars($_SESSION['user_name']) ?></a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <div class="sell-card">
        <h1><i class="fas fa-plus-circle"></i> Request to Sell</h1>
        <p class="subtitle">Fill in your item details. Admin will review and approve your listing.</p>
        
        <?php if ($message): ?>
            <div class="alert-success">
                <i class="fas fa-check-circle"></i> <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row-2">
                <div class="form-group">
                    <label>Item Name <span class="required">*</span></label>
                    <input type="text" name="item_name" required placeholder="e.g., Vintage Denim Jacket">
                </div>
                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" name="brand" placeholder="e.g., Levi's, Zara, Nike">
                </div>
            </div>
            
            <div class="row-3">
                <div class="form-group">
                    <label>Category <span class="required">*</span></label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <option value="tops">Tops</option>
                        <option value="bottoms">Bottoms</option>
                        <option value="dresses">Dresses</option>
                        <option value="jackets">Jackets</option>
                        <option value="shoes">Shoes</option>
                        <option value="accessories">Accessories</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Size</label>
                    <select name="size">
                        <option value="">Select Size</option>
                        <option value="XS">XS</option>
                        <option value="S">S</option>
                        <option value="M">M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Condition</label>
                    <select name="condition">
                        <option value="">Select Condition</option>
                        <option value="New">New with Tags</option>
                        <option value="Like new">Like New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                    </select>
                </div>
            </div>
            
            <div class="row-2">
                <div class="form-group">
                    <label>Price (R) <span class="required">*</span></label>
                    <input type="number" name="price" step="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="1" min="1">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Describe your item - condition, measurements, material, reason for selling..."></textarea>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <div class="upload-zone" onclick="document.getElementById('imageInput').click()">
                    <i class="fas fa-cloud-upload-alt upload-icon"></i>
                    <p>Click to upload or drag and drop</p>
                    <p style="font-size: 0.8rem; color: #7A7065;">JPG, PNG, WEBP up to 5MB</p>
                    <input type="file" id="imageInput" name="product_image" accept="image/*" style="display: none;">
                </div>
                <div id="imagePreview" style="margin-top: 0.5rem; display: none;">
                    <img id="previewImg" src="#" alt="Preview" style="max-width: 100%; max-height: 150px; border-radius: 8px;">
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Submit Listing Request
            </button>
        </form>
        
        <a href="shop.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Shop</a>
    </div>
</div>

<script>
    document.getElementById('imageInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('previewImg');
                preview.src = event.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Drag and drop functionality
    const dropZone = document.querySelector('.upload-zone');
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#C0533A';
    });
    
    dropZone.addEventListener('dragleave', function(e) {
        this.style.borderColor = '#E2DDD7';
    });
    
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        const file = e.dataTransfer.files[0];
        if (file) {
            document.getElementById('imageInput').files = e.dataTransfer.files;
            const reader = new FileReader();
            reader.onload = function(event) {
                document.getElementById('previewImg').src = event.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
        this.style.borderColor = '#E2DDD7';
    });
</script>

</body>
</html>