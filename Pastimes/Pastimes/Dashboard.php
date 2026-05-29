<?php
// dashboard.php - Fixed user dashboard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($userId == 0) {
    // If no user ID in session, redirect to login
    header("Location: login.php");
    exit();
}

// Fetch user data from database
$sql = "SELECT * FROM tbluser WHERE UserID = $userId LIMIT 1";
$result = mysqli_query($conn, $sql);

// Check if query was successful
if (!$result) {
    die("Database error: " . mysqli_error($conn));
}

// Check if user exists
if (mysqli_num_rows($result) == 0) {
    // User not found in database, logout
    session_destroy();
    header("Location: login.php?error=account_not_found");
    exit();
}

// Fetch user data
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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
            min-height: 100vh;
        }

        nav {
            background: white;
            border-bottom: 1px solid #E2DDD7;
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
            font-size: 0.9rem;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #1C1C1C;
        }

        .logged-in-banner {
            background: #1C1C1C;
            color: white;
            text-align: center;
            padding: 0.6rem;
            font-size: 0.88rem;
        }

        main {
            max-width: 860px;
            margin: 0 auto;
            padding: 2.5rem 1rem;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #1C1C1C;
            margin-bottom: 0.4rem;
        }
        
        .sub {
            color: #7A7065;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .data-card {
            background: white;
            border: 1px solid #E2DDD7;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        
        .data-card-header {
            padding: 1rem 1.5rem;
            background: #1C1C1C;
            color: white;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .info-table th,
        .info-table td {
            padding: 0.85rem 1.5rem;
            text-align: left;
            font-size: 0.9rem;
            border-bottom: 1px solid #E2DDD7;
        }
        
        .info-table th {
            color: #7A7065;
            font-weight: 500;
            width: 35%;
            background: #FAFAF8;
        }
        
        .info-table td {
            color: #1C1C1C;
        }
        
        .info-table tr:last-child th,
        .info-table tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
        }
        
        .badge-verified {
            background: #E8F5E9;
            color: #2E7D32;
        }
        
        .badge-pending {
            background: #FFF8E1;
            color: #E65100;
        }

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
        
        .btn-primary {
            background: #C0533A;
            color: white;
        }
        
        .btn-primary:hover {
            background: #A8432C;
        }
        
        .btn-outline {
            background: transparent;
            color: #1C1C1C;
            border: 1px solid #E2DDD7;
        }
        
        .btn-outline:hover {
            border-color: #1C1C1C;
        }

        .error-message {
            background: #FFEBEE;
            color: #C62828;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>

<nav>
    <a class="logo" href="index.php">Pastimes</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- "User X is logged in" string as required by assignment -->
<div class="logged-in-banner">
    User <?= htmlspecialchars($user['FullName'] ?? 'User') ?> is logged in
</div>

<main>
    <h1>My Account</h1>
    <p class="sub">Welcome back — here's your profile information.</p>

    <!-- Associative display of user data using column names -->
    <div class="data-card">
        <div class="data-card-header">Account Details</div>
        <table class="info-table">
            <tr>
                <th>User ID</th>
                <td><?= isset($user['UserID']) ? htmlspecialchars($user['UserID']) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Full Name</th>
                <td><?= isset($user['FullName']) ? htmlspecialchars($user['FullName']) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Email Address</th>
                <td><?= isset($user['Email']) ? htmlspecialchars($user['Email']) : 'N/A' ?></td>
            </tr>
            <tr>
                <th>Account Status</th>
                <td>
                    <?php if (isset($user['Status'])): ?>
                        <?php if ($user['Status'] === 'verified'): ?>
                            <span class="badge badge-verified">✔ Verified</span>
                        <?php else: ?>
                            <span class="badge badge-pending">⏳ Pending</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-pending">N/A</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Member Since</th>
                <td><?= isset($user['CreatedAt']) ? date('d F Y', strtotime($user['CreatedAt'])) : 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <div class="actions">
        <a href="shop.php" class="btn btn-primary">Browse Shop</a>
        <a href="sell-request.php" class="btn btn-outline">Sell an Item</a>
        <a href="logout.php" class="btn btn-outline">Logout</a>
    </div>
</main>

</body>
</html>