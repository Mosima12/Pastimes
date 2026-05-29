<?php
// AdminLogin.php - Admin login page
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: AdminDashboard.php");
    exit();
}
require_once 'DBConn.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $sql = "SELECT * FROM tbladmin WHERE Username = '$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) === 1) {
        $admin = mysqli_fetch_assoc($result);
        if (md5($password) === $admin['PasswordHash']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['AdminID'];
            $_SESSION['admin_name'] = $admin['Username'];
            header("Location: AdminDashboard.php");
            exit();
        } else { $error = "Invalid password."; }
    } else { $error = "Invalid admin credentials."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'DM Sans',sans-serif;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .admin-card{background:white;border-radius:20px;padding:40px;width:100%;max-width:400px;box-shadow:0 20px 40px rgba(0,0,0,0.2);}
        .admin-icon{font-size:3rem;text-align:center;margin-bottom:20px;}
        h1{text-align:center;margin-bottom:10px;}
        input{width:100%;padding:12px;margin:10px 0;border:2px solid #e0e0e0;border-radius:10px;}
        .btn{width:100%;padding:14px;background:#C0533A;color:white;border:none;border-radius:10px;cursor:pointer;}
        .error{color:#c00;margin-bottom:15px;padding:10px;background:#fee;border-radius:8px;}
        .hint{background:#f0f0f0;padding:12px;border-radius:8px;margin-bottom:20px;font-size:0.85rem;}
        .back-link{display:block;text-align:center;margin-top:20px;color:#666;text-decoration:none;}
        .back-link:hover{color:#C0533A;}
    </style>
</head>
<body>
    <div class="admin-card">
        <div class="admin-icon">[ADMIN]</div>
        <h1>Admin Portal</h1>
        <div class="hint">
            <strong>Demo Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
        <?php if($error):?><div class="error"><?=$error?></div><?php endif;?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login as Admin</button>
        </form>
        <a href="login.php" class="back-link">Back to User Login</a>
    </div>
</body>
</html>