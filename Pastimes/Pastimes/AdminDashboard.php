<?php
// AdminDashboard.php - Complete admin with Add, Delete, Update for clothing and users
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'DBConn.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: AdminLogin.php");
    exit();
}

$message = '';
$error = '';

// Handle Product Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ADD PRODUCT
    if (isset($_POST['add_product'])) {
        $itemName = mysqli_real_escape_string($conn, $_POST['item_name']);
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $size = mysqli_real_escape_string($conn, $_POST['size']);
        $condition = mysqli_real_escape_string($conn, $_POST['condition']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        $sql = "INSERT INTO tblclothes (SellerID, ItemName, Brand, Category, ClothesSize, `Condition`, Price, Quantity, Description, Status) 
                VALUES (1, '$itemName', '$brand', '$category', '$size', '$condition', $price, $quantity, '$description', 'active')";
        if (mysqli_query($conn, $sql)) { $message = "Product added successfully!"; }
        else { $error = "Error: " . mysqli_error($conn); }
    }
    
    // UPDATE PRODUCT
    if (isset($_POST['update_product'])) {
        $id = intval($_POST['product_id']);
        $itemName = mysqli_real_escape_string($conn, $_POST['item_name']);
        $brand = mysqli_real_escape_string($conn, $_POST['brand']);
        $price = floatval($_POST['price']);
        $quantity = intval($_POST['quantity']);
        
        $sql = "UPDATE tblclothes SET ItemName='$itemName', Brand='$brand', Price=$price, Quantity=$quantity WHERE ClothesID=$id";
        if (mysqli_query($conn, $sql)) { $message = "Product updated successfully!"; }
    }
    
    // DELETE PRODUCT
    if (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        $sql = "DELETE FROM tblclothes WHERE ClothesID=$id";
        if (mysqli_query($conn, $sql)) { $message = "Product deleted successfully!"; }
    }
    
    // VERIFY USER
    if (isset($_POST['action']) && $_POST['action'] === 'verify') {
        $userId = (int) $_POST['user_id'];
        $sql = "UPDATE tbluser SET Status = 'verified' WHERE UserID = $userId";
        if (mysqli_query($conn, $sql)) { $message = "User verified successfully!"; }
        else { $error = "Failed to verify user."; }
    }
    
    // DELETE USER
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $userId = (int) $_POST['user_id'];
        if ($userId != $_SESSION['admin_id']) {
            $sql = "DELETE FROM tbluser WHERE UserID = $userId";
            if (mysqli_query($conn, $sql)) { $message = "User deleted successfully!"; }
        } else { $error = "You cannot delete your own admin account!"; }
    }
    
    // ADD USER
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $password = $_POST['password'];
        $accountType = mysqli_real_escape_string($conn, $_POST['account_type']);
        
        if (!empty($fullname) && !empty($email) && !empty($password)) {
            $hash = md5($password);
            $sql = "INSERT INTO tbluser (FullName, Email, PasswordHash, Status, AccountType) VALUES ('$fullname', '$email', '$hash', 'verified', '$accountType')";
            if (mysqli_query($conn, $sql)) { $message = "User added successfully!"; }
        } else { $error = "All fields required!"; }
    }
    
    // UPDATE USER
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $userId = (int) $_POST['user_id'];
        $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
        $email = mysqli_real_escape_string($conn, trim($_POST['email']));
        $accountType = mysqli_real_escape_string($conn, $_POST['account_type']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        $sql = "UPDATE tbluser SET FullName='$fullname', Email='$email', AccountType='$accountType', Status='$status' WHERE UserID=$userId";
        if (mysqli_query($conn, $sql)) { $message = "User updated successfully!"; }
    }
}

$products = mysqli_query($conn, "SELECT * FROM tblclothes ORDER BY DateListed DESC");
$users = mysqli_query($conn, "SELECT * FROM tbluser ORDER BY CreatedAt DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DM Sans',sans-serif; background:#f5f5f5; }
        .admin-nav { background:#1a1a2e; color:white; padding:0 30px; height:60px; display:flex; align-items:center; justify-content:space-between; }
        .logo { font-family:'Playfair Display',serif; font-size:1.4rem; color:white; text-decoration:none; }
        .admin-container { max-width:1400px; margin:0 auto; padding:30px; }
        .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:30px; }
        .stat-card { background:white; border-radius:15px; padding:25px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .stat-number { font-size:2.5rem; font-weight:bold; color:#C0533A; }
        .card { background:white; border-radius:15px; padding:25px; margin-bottom:30px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
        .card h3 { margin-bottom:20px; color:#1a1a2e; }
        .form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:15px; margin-bottom:15px; }
        input, select, textarea { padding:10px; border:1px solid #ddd; border-radius:8px; width:100%; }
        .btn { padding:10px 20px; border:none; border-radius:8px; cursor:pointer; }
        .btn-primary { background:#C0533A; color:white; }
        .btn-edit { background:#4CAF50; color:white; }
        .btn-delete { background:#f44336; color:white; }
        table { width:100%; border-collapse:collapse; }
        th, td { padding:12px; text-align:left; border-bottom:1px solid #ddd; }
        th { background:#f8f8f8; }
        .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:0.75rem; }
        .badge-verified { background:#e8f5e9; color:#2e7d32; }
        .badge-pending { background:#fff3e0; color:#e65100; }
        .inline-input { padding:5px; border:1px solid #ddd; border-radius:5px; width:100%; }
        .alert { padding:15px; border-radius:10px; margin-bottom:20px; }
        .alert-success { background:#e8f5e9; color:#2e7d32; border-left:4px solid #2e7d32; }
        .alert-error { background:#ffebee; color:#c62828; border-left:4px solid #c62828; }
        .tabs { display:flex; gap:10px; margin-bottom:20px; }
        .tab-btn { padding:10px 20px; background:#ddd; border:none; border-radius:8px; cursor:pointer; }
        .tab-btn.active { background:#C0533A; color:white; }
        .tab-content { display:none; }
        .tab-content.active { display:block; }
    </style>
</head>
<body>
    <nav class="admin-nav"><a href="index.php" class="logo">Pastimes Admin</a><div><a href="Adminlogout.php" style="color:white;">Logout</a></div></nav>
    
    <div class="admin-container">
        <?php if ($message): ?><div class="alert alert-success">Success: <?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error">Error: <?= $error ?></div><?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?= mysqli_num_rows($users) ?></div><div class="stat-label">Total Users</div></div>
            <div class="stat-card"><div class="stat-number" style="color:#e65100;"><?= mysqli_num_rows(mysqli_query($conn, "SELECT * FROM tbluser WHERE Status='pending'")) ?></div><div class="stat-label">Pending Verification</div></div>
            <div class="stat-card"><div class="stat-number" style="color:#2e7d32;"><?= mysqli_num_rows($products) ?></div><div class="stat-label">Total Products</div></div>
        </div>
        
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('products')">Products</button>
            <button class="tab-btn" onclick="showTab('users')">Users</button>
        </div>
        
        <!-- Products Tab -->
        <div id="productsTab" class="tab-content active">
            <div class="card">
                <h3>Add New Product</h3>
                <form method="POST">
                    <div class="form-row">
                        <input type="text" name="item_name" placeholder="Item Name" required>
                        <input type="text" name="brand" placeholder="Brand">
                        <select name="category" required><option value="tops">Tops</option><option value="bottoms">Bottoms</option><option value="dresses">Dresses</option><option value="jackets">Jackets</option><option value="shoes">Shoes</option><option value="accessories">Accessories</option></select>
                        <select name="size"><option value="XS">XS</option><option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option></select>
                        <select name="condition"><option value="New">New</option><option value="Like new">Like new</option><option value="Good">Good</option><option value="Fair">Fair</option></select>
                        <input type="number" name="price" placeholder="Price" step="0.01" required>
                        <input type="number" name="quantity" placeholder="Quantity" value="1">
                        <textarea name="description" placeholder="Description" rows="2"></textarea>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </form>
            </div>
            
            <div class="card">
                <h3>All Products (Edit, Delete)</h3>
                <table>
                    <thead><tr><th>ID</th><th>Item Name</th><th>Brand</th><th>Price</th><th>Quantity</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?= $p['ClothesID'] ?>">
                            <tr>
                                <td><?= $p['ClothesID'] ?></td>
                                <td><input type="text" name="item_name" value="<?= htmlspecialchars($p['ItemName']) ?>" class="inline-input" required></td>
                                <td><input type="text" name="brand" value="<?= htmlspecialchars($p['Brand'] ?? '') ?>" class="inline-input"></td>
                                <td><input type="number" name="price" value="<?= $p['Price'] ?>" step="0.01" class="inline-input" style="width:80px;"></td>
                                <td><input type="number" name="quantity" value="<?= $p['Quantity'] ?? 1 ?>" class="inline-input" style="width:60px;"></td>
                                <td><button type="submit" name="update_product" class="btn btn-edit">Edit</button> <button type="submit" name="delete_product" class="btn btn-delete" onclick="return confirm('Delete this product?')">Delete</button></td>
                            </tr>
                        </form>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Users Tab -->
        <div id="usersTab" class="tab-content">
            <div class="card">
                <h3>Add New Customer</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <input type="text" name="fullname" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <select name="account_type"><option value="customer">Customer</option><option value="admin">Admin</option></select>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <h3>All Users (Update, Verify, Delete)</h3>
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php mysqli_data_seek($users, 0); while ($u = mysqli_fetch_assoc($users)): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= $u['UserID'] ?>">
                            <tr>
                                <td><?= $u['UserID'] ?></td>
                                <td><input type="text" name="fullname" value="<?= htmlspecialchars($u['FullName']) ?>" class="inline-input" required></td>
                                <td><input type="email" name="email" value="<?= htmlspecialchars($u['Email']) ?>" class="inline-input" required></td>
                                <td><select name="account_type"><option value="customer" <?= $u['AccountType']=='customer'?'selected':'' ?>>Customer</option><option value="admin" <?= $u['AccountType']=='admin'?'selected':'' ?>>Admin</option></select></td>
                                <td><select name="status"><option value="pending" <?= $u['Status']=='pending'?'selected':'' ?>>Pending</option><option value="verified" <?= $u['Status']=='verified'?'selected':'' ?>>Verified</option></select></td>
                                <td><button type="submit" class="btn btn-edit">Update</button></form>
                                <?php if ($u['Status'] == 'pending'): ?>
                                <form method="POST" style="display:inline;"><input type="hidden" name="action" value="verify"><input type="hidden" name="user_id" value="<?= $u['UserID'] ?>"><button type="submit" class="btn btn-edit">Verify</button></form>
                                <?php endif; ?>
                                <?php if ($u['UserID'] != $_SESSION['admin_id']): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')"><input type="hidden" name="action" value="delete"><input type="hidden"name="user_id" value="<?= $u['UserID'] ?>"><button type="submit" class="btn btn-delete">Delete</button></form>
                                <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    function showTab(tab) {
        document.getElementById('productsTab').classList.remove('active');
        document.getElementById('usersTab').classList.remove('active');
        document.getElementById(tab + 'Tab').classList.add('active');
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
    }
    </script>
</body>
</html>S