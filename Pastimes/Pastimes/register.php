<?php
// register.php – New user registration (status = pending until admin verifies)
require_once 'DBConn.php';

$error   = '';
$success = '';


$stickyName  = '';
$stickyEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';
    $confirm  = $_POST['confirm']       ?? '';

    $stickyName  = htmlspecialchars($fullName);
    $stickyEmail = htmlspecialchars($email);

    // ── Validation ────────────────────────────────────────────────────────────
    if (empty($fullName) || empty($email) || empty($password) || empty($confirm)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $checkEmail = mysqli_real_escape_string($conn, $email);
        $checkSQL   = "SELECT UserID FROM tbluser WHERE Email = '$checkEmail'";
        $result     = mysqli_query($conn, $checkSQL);

        if (mysqli_num_rows($result) > 0) {
            $error = "An account with that email already exists.";
        } else {
            // Hash password with MD5 (as per assignment spec)
            $hash = md5($password);

            $safeName  = mysqli_real_escape_string($conn, $fullName);
            $safeEmail = mysqli_real_escape_string($conn, $email);

            $insertSQL = "INSERT INTO tbluser (FullName, Email, PasswordHash, Status)
                          VALUES ('$safeName', '$safeEmail', '$hash', 'pending')";

            if (mysqli_query($conn, $insertSQL)) {
                $success = "Registration successful! Your account is pending admin verification. You will be able to login once approved.";
                $stickyName  = '';
                $stickyEmail = '';
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
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
<title>Register – Pastimes</title>
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
    --success-bg: #EDF7F0;
    --success-color: #2D7A4F;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }

  /* ── Navbar ── */
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
    letter-spacing: 0.02em;
  }
  nav a {
    color: var(--muted);
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.2s;
  }
  nav a:hover { color: var(--charcoal); }

  /* ── Main ── */
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
    max-width: 420px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
  }

  .card-icon {
    width: 48px;
    height: 48px;
    background: var(--cream);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.2rem;
    font-size: 1.4rem;
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

 
  .alert {
    padding: 0.8rem 1rem;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 1.2rem;
    line-height: 1.5;
  }
  .alert-error   { background: var(--error-bg);   color: var(--rust);          border: 1px solid #F0C4BA; }
  .alert-success { background: var(--success-bg);  color: var(--success-color); border: 1px solid #B8DFC8; }

  /* ── Form ── */
  .field { margin-bottom: 1rem; }
  label {
    display: block;
    font-size: 0.82rem;
    font-weight: 500;
    color: var(--charcoal);
    margin-bottom: 0.4rem;
    letter-spacing: 0.02em;
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
    transition: background 0.2s, transform 0.1s;
    margin-top: 0.5rem;
  }
  .btn:hover  { background: var(--rust-hover); }
  .btn:active { transform: scale(0.99); }

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
  .footer-link a:hover { text-decoration: underline; }

  .pending-note {
    background: #FFF8F0;
    border: 1px solid #F5D9B5;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.82rem;
    color: #8C5E1A;
    margin-bottom: 1.5rem;
    line-height: 1.5;
  }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div style="display:flex;gap:1.5rem;">
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
  </div>
</nav>

<main>
  <div class="card">
    <div class="card-icon">🪡</div>
    <h1>Create Account</h1>
    <p class="subtitle">Join Pastimes to buy &amp; sell pre-loved fashion</p>

    <div class="pending-note">
      ⏳ New accounts require <strong>admin verification</strong> before login is granted.
    </div>

    <?php if ($error):   ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

    <?php if (!$success): ?>
    <form method="POST" novalidate>
      <div class="field">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname"
               value="<?= $stickyName ?>"
               placeholder="e.g. Thabo Nkosi" required>
      </div>
      <div class="field">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               value="<?= $stickyEmail ?>"
               placeholder="you@example.com" required>
      </div>
      <div class="field">
        <label for="password">Password <span style="color:var(--muted);font-weight:300">(min 6 chars)</span></label>
        <input type="password" id="password" name="password"
               placeholder="••••••••" required>
      </div>
      <div class="field">
        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm"
               placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn">Create Account</button>
    </form>
    <?php else: ?>
      <a href="login.php" class="btn" style="display:block;text-align:center;text-decoration:none;">Go to Login</a>
    <?php endif; ?>

    <p class="footer-link">Already have an account? <a href="login.php">Sign in</a></p>
  </div>
</main>

</body>
</html>