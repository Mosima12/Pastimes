<?php
// dashboard.php – Shown after successful login
// Displays user data using associative fetch (as per assignment spec)
session_start();
require_once 'DBConn.php';

// Guard: must be logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: Login.php");
    exit();
}

// Fetch full user row using associative approach
$userId = (int) $_SESSION['user_id']; 
$sql    = "SELECT * FROM tbluser WHERE UserID = $userId LIMIT 1";
$result = mysqli_query($conn, $sql);
$user   = mysqli_fetch_assoc($result); // associative read using column names
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – Pastimes</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --cream: #F7F3EE;
    --charcoal: #1C1C1C;
    --rust: #C0533A;
    --muted: #7A7065;
    --border: #E2DDD7;
    --card-bg: #FFFFFF;
  }

  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--cream);
    min-height: 100vh;
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
  nav a { color: var(--muted); text-decoration: none; font-size: 0.9rem; }
  nav a:hover { color: var(--charcoal); }

  .logged-in-banner {
    background: var(--charcoal);
    color: #fff;
    text-align: center;
    padding: 0.6rem;
    font-size: 0.88rem;
    letter-spacing: 0.03em;
  }

  main { max-width: 860px; margin: 0 auto; padding: 2.5rem 1rem; }

  h1 {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    color: var(--charcoal);
    margin-bottom: 0.4rem;
  }
  .sub { color: var(--muted); font-size: 0.9rem; margin-bottom: 2rem; }

  /* ── User data table (associative display) ── */
  .data-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
  }
  .data-card-header {
    padding: 1rem 1.5rem;
    background: var(--charcoal);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  th, td {
    padding: 0.85rem 1.5rem;
    text-align: left;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border);
  }
  th {
    color: var(--muted);
    font-weight: 500;
    width: 35%;
    background: #FAFAF8;
  }
  td { color: var(--charcoal); }
  tr:last-child th, tr:last-child td { border-bottom: none; }

  .badge {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 500;
  }
  .badge-verified { background: #E8F5E9; color: #2E7D32; }
  .badge-pending  { background: #FFF8E1; color: #F57F17; }

  .actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .btn {
    padding: 0.65rem 1.4rem;
    border-radius: 7px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: background 0.2s;
  }
  .btn-primary { background: var(--rust); color: #fff; }
  .btn-primary:hover { background: #A8432C; }
  .btn-outline { background: transparent; color: var(--charcoal); border: 1px solid var(--border); }
  .btn-outline:hover { border-color: var(--charcoal); }
</style>
</head>
<body>

<nav>
  <a class="logo" href="index.php">Pastimes</a>
  <div style="display:flex;gap:1.5rem;align-items:center;">
    <a href="index.php">Home</a>
    <a href="shop.php">Shop</a>
    <a href="Logout.php">Logout</a>
  </div>
</nav>

<!-- "User X is logged in" string as required by assignment -->
<div class="logged-in-banner">
  User <?= htmlspecialchars($user['FullName']) ?> is logged in
</div>

<main>
  <h1>My Account</h1>
  <p class="sub">Welcome back — here's your profile information.</p>

  <!-- Associative display of user data using column names -->
  <div class="data-card">
    <div class="data-card-header">Account Details</div>
    <table>
      <tr>
        <th>User ID</th>
        <td><?= htmlspecialchars($user['UserID']) ?></td>
      </tr>
      <tr>
        <th>Full Name</th>
        <td><?= htmlspecialchars($user['FullName']) ?></td>
      </tr>
      <tr>
        <th>Email Address</th>
        <td><?= htmlspecialchars($user['Email']) ?></td>
      </tr>
      <tr>
        <th>Account Status</th>
        <td>
          <?php if ($user['Status'] === 'verified'): ?>
            <span class="badge badge-verified">✔ Verified</span>
          <?php else: ?>
            <span class="badge badge-pending">⏳ Pending</span>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <th>Member Since</th>
        <td><?= date('d F Y', strtotime($user['CreatedAt'])) ?></td>
      </tr>
    </table>
  </div>

  <div class="actions">
    <a href="shop.php"   class="btn btn-primary">Browse Shop</a>
    <a href="sell.php"   class="btn btn-outline">Sell an Item</a>
    <a href="Logout.php" class="btn btn-outline">Logout</a>
  </div>
</main>

</body>
</html>