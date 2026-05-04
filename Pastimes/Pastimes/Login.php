<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

$error       = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    $stickyEmail = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $error = "Both fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $hash      = md5($password);
        $safeEmail = mysqli_real_escape_string($conn, $email);

        $sql    = "SELECT * FROM tbluser WHERE Email = '$safeEmail' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if ($user['PasswordHash'] !== $hash) {
                $error = "Incorrect password. Please try again.";
            } elseif ($user['Status'] === 'pending') {
                $error = "Your account is pending admin verification. Please check back later.";
            } else {
                $_SESSION['user_id']   = $user['UserID'];
                $_SESSION['user_name'] = $user['FullName'];
                $_SESSION['user_email']= $user['Email'];
                $_SESSION['logged_in'] = true;

                header("Location: Dashboard.php");
                exit();
            }
        } else {
            $error = "No account found with that email. <a href='register.php'>Register here</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login – Pastimes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
    --cream: #F7F3EE;
    --charcoal: #1C1C1C;
    --rust: #C0533A;
    --rust-hover: #A8432C;
    --muted: #7A7065;
    --border: #E2DDD7;
    --card-bg: #FFFFFF;
    --error-bg: #FDF0ED;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

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

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    color: var(--charcoal);
    text-decoration: none;
}

nav a {
    color: var(--muted);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
}

nav a:hover {
    color: var(--charcoal);
}

main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1rem;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
}

.card-icon {
    font-size: 1.8rem;
    margin-bottom: 1rem;
    display: block;
}

h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.6rem;
    color: var(--charcoal);
    margin-bottom: 0.3rem;
}

.subtitle {
    color: var(--muted);
    font-size: 0.88rem;
    margin-bottom: 1.8rem;
}

.alert-error {
    background: var(--error-bg);
    color: var(--rust);
    border: 1px solid #F0C4BA;
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
    line-height: 1.5;
}

.success-banner {
    background: #E8F5E9;
    color: #2E7D32;
    border: 1px solid #C8E6C9;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
}

.field {
    margin-bottom: 1rem;
}

label {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--charcoal);
    margin-bottom: 0.4rem;
}

input {
    width: 100%;
    padding: 0.65rem 0.9rem;
    border: 1px solid var(--border);
    border-radius: 7px;
    font-size: 0.92rem;
    font-family: 'DM Sans', sans-serif;
    color: var(--charcoal);
    background: var(--cream);
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}

input:focus {
    border-color: var(--rust);
    box-shadow: 0 0 0 3px rgba(192,83,58,0.1);
    background: #fff;
}

.btn {
    width: 100%;
    padding: 0.75rem;
    background: var(--rust);
    color: #fff;
    border: none;
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s;
    margin-top: 0.5rem;
}

.btn:hover {
    background: var(--rust-hover);
}

.footer-link {
    text-align: center;
    margin-top: 1.4rem;
    font-size: 0.87rem;
    color: var(--muted);
}

.footer-link a {
    color: var(--rust);
    text-decoration: none;
    font-weight: 500;
}

.footer-link a:hover {
    text-decoration: underline;
}

.divider {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    margin: 1.2rem 0;
    color: var(--muted);
    font-size: 0.8rem;
}

.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

.admin-link {
    display: block;
    text-align: center;
    padding: 0.65rem;
    border: 1px solid var(--border);
    border-radius: 7px;
    color: var(--muted);
    text-decoration: none;
    font-size: 0.88rem;
    transition: border-color 0.2s, color 0.2s;
}

.admin-link:hover {
    border-color: var(--charcoal);
    color: var(--charcoal);
}
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div style="display:flex;gap:1.5rem;">
    <a href="index.php">Home</a>
    <a href="register.php">Register</a>
  </div>
</nav>

<main>
  <div class="card">
    <span class="card-icon">🪢</span>
    <h1>Welcome back</h1>
    <p class="subtitle">Sign in to continue to Pastimes</p>

    <?php if (isset($_GET['success'])): ?>
      <div class="success-banner">✔ Registration successful! Please wait for admin approval before logging in.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert-error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               value="<?= $stickyEmail ?>"
               placeholder="you@example.com" required>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn">Sign In</button>
    </form>

    <div class="divider">or</div>

    <a class="admin-link" href="Adminlogin.php">🔐 Admin Login</a>

    <p class="footer-link">No account? <a href="register.php">Register here</a></p>
  </div>
</main>

</body>
</html>