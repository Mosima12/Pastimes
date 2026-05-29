<?php
// login.php - User login page with redirect functionality
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

// Get redirect URL from query parameter
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';

$error = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stickyEmail = htmlspecialchars($email);
    
    // Get redirect from POST if not in GET
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : $redirect;
    
    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } else {
        $safeEmail = mysqli_real_escape_string($conn, $email);
        $result = mysqli_query($conn, "SELECT * FROM tbluser WHERE Email = '$safeEmail' LIMIT 1");
        
        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            if (md5($password) !== $user['PasswordHash']) {
                $error = "Incorrect password. Please try again.";
            } elseif ($user['Status'] === 'pending') {
                $error = "Account pending admin verification. Please check back later.";
            } else {
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['user_name'] = $user['FullName'];
                $_SESSION['user_email'] = $user['Email'];
                $_SESSION['logged_in'] = true;
                
                // Redirect to original page or dashboard
                header("Location: $redirect");
                exit();
            }
        } else {
            $error = "No account found with that email. <a href='register.php'>Register here</a>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pastimes</title>
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
            background: linear-gradient(135deg, #F7F3EE 0%, #E8E0D8 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            animation: fadeInUp 0.6s ease;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .logo {
            text-align: center;
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1C1C1C 0%, #C0533A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 0.5rem;
        }
        .subtitle {
            text-align: center;
            color: #7A7065;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #1C1C1C;
        }
        input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #E2DDD7;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 1rem;
            transition: all 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #C0533A;
            box-shadow: 0 0 0 3px rgba(192,83,58,0.1);
        }
        .btn-login {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, #C0533A 0%, #A8432C 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(192,83,58,0.3);
        }
        .error-message {
            background: #FFEBEE;
            color: #C62828;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            border-left: 4px solid #C62828;
        }
        .success-message {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            border-left: 4px solid #2E7D32;
        }
        .admin-link {
            display: block;
            text-align: center;
            margin-top: 1.5rem;
            color: #7A7065;
            text-decoration: none;
            padding: 0.5rem;
            border: 1px solid #E2DDD7;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .admin-link:hover {
            border-color: #C0533A;
            color: #C0533A;
            background: rgba(192,83,58,0.05);
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #7A7065;
        }
        .register-link a {
            color: #C0533A;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #7A7065;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E2DDD7;
        }
        .redirect-notice {
            background: #E3F2FD;
            color: #1565C0;
            padding: 0.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">Pastimes</div>
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to continue</p>
        
        <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> Registration successful! Please wait for admin approval.
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['checkout']) && $_GET['checkout'] == 1): ?>
            <div class="redirect-notice">
                <i class="fas fa-info-circle"></i> Please login to complete your checkout.
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $stickyEmail ?>" placeholder="your@email.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
        
        <div class="divider">or</div>
        
        <a href="AdminLogin.php" class="admin-link">
            <i class="fas fa-lock"></i> Admin Login
        </a>
        
        <p class="register-link">No account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>