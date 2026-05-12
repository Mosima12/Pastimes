<?php
// AdminDashboard.php - Complete admin management system
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: AdminLogin.php");
    exit();
}

require_once 'DBConn.php';

$message = '';
$error = '';


// 1. VERIFY NEW CUSTOMER (pending -> verified)
if (isset($_POST['action']) && $_POST['action'] === 'verify') {
    $userId = (int) $_POST['user_id'];
    $sql = "UPDATE tbluser SET Status = 'verified' WHERE UserID = $userId";
    if (mysqli_query($conn, $sql)) {
        $message = "User has been verified successfully!";
    } else {
        $error = "Failed to verify user: " . mysqli_error($conn);
    }
}

// 2. DELETE USER
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $userId = (int) $_POST['user_id'];
    // Don't allow admin to delete themselves
    if ($userId != $_SESSION['admin_id']) {
        $sql = "DELETE FROM tbluser WHERE UserID = $userId";
        if (mysqli_query($conn, $sql)) {
            $message = "User has been deleted successfully!";
        } else {
            $error = "Failed to delete user: " . mysqli_error($conn);
        }
    } else {
        $error = "You cannot delete your own admin account!";
    }
}

// 3. ADD NEW USER
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $accountType = mysqli_real_escape_string($conn, $_POST['account_type'] ?? 'customer');
    
    if (empty($fullname) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address!";
    } else {
        $hash = md5($password);
        $sql = "INSERT INTO tbluser (FullName, Email, PasswordHash, Status, AccountType) 
                VALUES ('$fullname', '$email', '$hash', 'verified', '$accountType')";
        if (mysqli_query($conn, $sql)) {
            $message = "New user '$fullname' has been added successfully!";
        } else {
            $error = "Failed to add user: " . mysqli_error($conn);
        }
    }
}

// 4. UPDATE USER
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $userId = (int) $_POST['user_id'];
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
    $accountType = mysqli_real_escape_string($conn, $_POST['account_type'] ?? 'customer');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'pending');
    
    if (empty($fullname) || empty($email)) {
        $error = "Name and email are required!";
    } else {
        $sql = "UPDATE tbluser SET FullName='$fullname', Email='$email', AccountType='$accountType', Status='$status' 
                WHERE UserID = $userId";
        if (mysqli_query($conn, $sql)) {
            $message = "User has been updated successfully!";
        } else {
            $error = "Failed to update user: " . mysqli_error($conn);
        }
    }
}

// Load all users
$sql = "SELECT * FROM tbluser ORDER BY 
        CASE WHEN Status = 'pending' THEN 0 ELSE 1 END, 
        UserID DESC";
$result = mysqli_query($conn, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

$totalUsers = count($users);
$pendingUsers = count(array_filter($users, fn($u) => $u['Status'] === 'pending'));
$verifiedUsers = $totalUsers - $pendingUsers;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #f5f5f5; }
        
        .admin-nav {
            background: #1a1a2e;
            color: white;
            padding: 0 30px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .logo { font-family: 'Playfair Display', serif; font-size: 1.4rem; color: white; text-decoration: none; }
        .admin-badge {
            background: #C0533A;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .logout-btn {
            color: #ccc;
            text-decoration: none;
            margin-left: 20px;
        }
        .logout-btn:hover { color: white; }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #C0533A; }
        .stat-label { color: #666; margin-top: 8px; }
        
        .add-user-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .add-user-card h2 {
            margin-bottom: 20px;
            color: #1a1a2e;
            font-size: 1.3rem;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .form-row input, .form-row select {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
        }
        .btn-add {
            background: #C0533A;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .users-table-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px 20px; text-align: left; border-bottom: 1px solid #e0e0e0; }
        th { background: #f8f8f8; font-weight: 600; color: #333; }
        tr:hover { background: #fafafa; }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .badge-verified { background: #e8f5e9; color: #2e7d32; }
        .badge-pending { background: #fff3e0; color: #e65100; }
        .badge-admin { background: #e3f2fd; color: #1565c0; }
        .badge-customer { background: #e8eaf6; color: #283593; }
        
        .action-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn-verify, .btn-update, .btn-delete {
            padding: 5px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .btn-verify { background: #e8f5e9; color: #2e7d32; }
        .btn-update { background: #e3f2fd; color: #1565c0; }
        .btn-delete { background: #ffebee; color: #c62828; }
        
        .alert { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border-left: 4px solid #2e7d32; }
        .alert-error { background: #ffebee; color: #c62828; border-left: 4px solid #c62828; }
        
        .inline-input, .inline-select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <a href="index.php" class="logo">Pastimes Admin</a>
        <div>
            <span class="admin-badge">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="admin-container">
        <?php if ($message): ?>
            <div class="alert alert-success">✓ <?= $message ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error">✗ <?= $error ?></div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #e65100;"><?= $pendingUsers ?></div>
                <div class="stat-label">Pending Verification</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #2e7d32;"><?= $verifiedUsers ?></div>
                <div class="stat-label">Verified Users</div>
            </div>
        </div>
        
        <div class="add-user-card">
            <h2>➕ Add New Customer</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <select name="account_type" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Administrator</option>
                    </select>
                    <button type="submit" class="btn-add">Add User</button>
                </div>
            </form>
        </div>
        
        <div class="users-table-card">
            <div class="table-header">
                <h2>All Users</h2>
                <span><?= $totalUsers ?> records found</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Account Type</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                            <td><?= $user['UserID'] ?></td>
                            <td><input type="text" name="fullname" value="<?= htmlspecialchars($user['FullName']) ?>" class="inline-input" required></td>
                            <td><input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" class="inline-input" required></td>
                            <td>
                                <select name="account_type" class="inline-select">
                                    <option value="customer" <?= $user['AccountType'] == 'customer' ? 'selected' : '' ?>>Customer</option>
                                    <option value="admin" <?= $user['AccountType'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <select name="status" class="inline-select">
                                    <option value="pending" <?= $user['Status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="verified" <?= $user['Status'] == 'verified' ? 'selected' : '' ?>>Verified</option>
                                </select>
                            </td>
                            <td><?= date('d M Y', strtotime($user['CreatedAt'])) ?></td>
                            <td class="action-buttons">
                                <button type="submit" class="btn-update">💾 Update</button>
                        </form>
                        <?php if ($user['Status'] == 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="verify">
                                <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                                <button type="submit" class="btn-verify">✓ Verify</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($user['UserID'] != $_SESSION['admin_id'] && $user['AccountType'] != 'admin'): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $user['UserID'] ?>">
                                <button type="submit" class="btn-delete">🗑 Delete</button>
                            </form>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>