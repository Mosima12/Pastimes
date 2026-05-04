<?php
// AdminLogin.php - Fixed admin login page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: AdminDashboard.php");
    exit();
}

require_once 'DBConn.php';

$error = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stickyEmail = htmlspecialchars($email);
    
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } else {
        // Check in database for admin user (AccountType = 'admin')
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $sql = "SELECT * FROM tbluser WHERE Email = '$safeEmail' AND AccountType = 'admin' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            
            // Verify password (MD5 hash)
            if (md5($password) === $admin['PasswordHash']) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['UserID'];
                $_SESSION['admin_name'] = $admin['FullName'];
                $_SESSION['admin_email'] = $admin['Email'];
                header("Location: AdminDashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid admin credentials.";
        }
    }
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .admin-icon { font-size: 3rem; text-align: center; margin-bottom: 20px; }
        h1 { text-align: center; color: #1a1a2e; margin-bottom: 10px; font-family: 'Playfair Display', serif; }
        .subtitle { text-align: center; color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        input:focus { outline: none; border-color: #C0533A; }
        .btn-admin {
            width: 100%;
            padding: 14px;
            background: #C0533A;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-admin:hover { background: #A8432C; }
        .error-message {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c00;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover { color: #C0533A; }
        .hint {
            background: #f0f0f0;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        .hint code { background: #fff; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="admin-card">
        <div class="admin-icon">🔐</div>
        <h1>Admin Portal</h1>
        <p class="subtitle">Enter your credentials to continue</p>
        
        <div class="hint">
            <strong>Demo Credentials:</strong><br>
            Email: <code>admin@pastimes.co.za</code><br>
            Password: <code>admin123</code>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $stickyEmail ?>" required placeholder="admin@pastimes.co.za">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-admin">Login as Admin</button>
        </form>
        <a href="login.php" class="back-link">← Back to User Login</a>
    </div>
</body>
</html>