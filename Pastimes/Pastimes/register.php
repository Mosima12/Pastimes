<?php
// register.php - User registration page with pending status
require_once 'DBConn.php';

$error = '';
$success = '';
$stickyName = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    $stickyName = htmlspecialchars($fullName);
    $stickyEmail = htmlspecialchars($email);
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT UserID FROM tbluser WHERE Email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "An account with that email already exists.";
        } else {
            // Hash password with MD5
            $hash = md5($password);
            $sql = "INSERT INTO tbluser (FullName, Email, PasswordHash, Status) 
                    VALUES ('$fullName', '$email', '$hash', 'pending')";
            
            if (mysqli_query($conn, $sql)) {
                // Redirect to login page with success message
                header("Location: login.php?registered=1");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
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
    <title>Register - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            padding: 2rem;
        }
        
        .register-container {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 500px;
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
            margin-bottom: 1rem;
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 0.5rem;
            color: #1C1C1C;
        }
        
        .subtitle {
            text-align: center;
            color: #7A7065;
            margin-bottom: 2rem;
        }
        
        .pending-note {
            background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
            color: #E65100;
            padding: 0.8rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-left: 4px solid #E65100;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1C1C1C;
            font-size: 0.85rem;
        }
        
        label .required {
            color: #C0533A;
            margin-left: 0.2rem;
        }
        
        input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #E2DDD7;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #C0533A;
            box-shadow: 0 0 0 3px rgba(192,83,58,0.1);
        }
        
        .btn-register {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #C0533A 0%, #A8432C 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 0.5rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(192,83,58,0.3);
        }
        
        .error-message {
            background: #FFEBEE;
            color: #C62828;
            padding: 0.8rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            border-left: 4px solid #C62828;
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #7A7065;
            font-size: 0.9rem;
        }
        
        .login-link a {
            color: #C0533A;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: #7A7065;
            font-size: 0.8rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E2DDD7;
        }
        
        .password-hint {
            font-size: 0.7rem;
            color: #7A7065;
            margin-top: 0.3rem;
        }
        
        @media (max-width: 480px) {
            .register-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">Pastimes</div>
        <h1>Create Account</h1>
        <p class="subtitle">Join the sustainable fashion movement</p>
        
        <div class="pending-note">
            <i class="fas fa-clock"></i> New accounts require admin verification before login.
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="fullname" value="<?= $stickyName ?>" placeholder="e.g., John Doe" required>
            </div>
            
            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" name="email" value="<?= $stickyEmail ?>" placeholder="you@example.com" required>
            </div>
            
            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" placeholder="••••••••" required>
                <div class="password-hint">Password must be at least 6 characters</div>
            </div>
            
            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <div class="divider">Already have an account?</div>
        
        <p class="login-link">
            <a href="login.php">Sign in here</a>
        </p>
    </div>
</body>
</html>