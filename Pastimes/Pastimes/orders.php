<?php
// orders.php - Purchase history with grand total
session_start();
require_once 'DBConn.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$sql = "SELECT o.*, 
        (SELECT SUM(ol.Quantity * ol.Price) FROM tblorderline ol WHERE ol.OrderID = o.OrderID) as OrderTotal
        FROM tblaorder o 
        WHERE o.BuyerID = $userId 
        ORDER BY o.OrderDate DESC";
$orders = mysqli_query($conn, $sql);

$grandTotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #F7F3EE; color: #1C1C1C; }
        
        nav {
            background: white;
            border-bottom: 1px solid #E2DDD7;
            padding: 0 5%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1C1C1C 0%, #C0533A 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        .nav-links { display: flex; gap: 1.5rem; align-items: center; }
        .nav-links a { color: #7A7065; text-decoration: none; font-size: 0.9rem; }
        .nav-links a:hover { color: #C0533A; }
        
        .orders-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #E2DDD7;
        }
        
        .data-table th {
            background: #F7F3EE;
            font-weight: 600;
        }
        
        .data-table tr:hover td {
            background: rgba(192,83,58,0.03);
        }
        
        .status {
            display: inline-block;
            padding: 0.25rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-delivered { background: #E8F5E9; color: #2E7D32; }
        .status-shipped { background: #E3F2FD; color: #1565C0; }
        .status-pending { background: #FFF3E0; color: #E65100; }
        
        .grand-total-row {
            background: linear-gradient(135deg, #C0533A 0%, #A8432C 100%);
            color: white;
        }
        
        .grand-total-row td {
            border-bottom: none;
            font-weight: 700;
        }
        
        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #7A7065;
        }
        
        .btn-primary {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            background: #C0533A;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            margin-top: 1rem;
        }
        
        footer {
            background: #1C1C1C;
            color: #999;
            padding: 2rem;
            margin-top: 3rem;
            text-align: center;
        }
    </style>
</head>
<body>

<nav>
    <a href="index.php" class="logo">Pastimes</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="shop.php">Shop</a>
        <a href="sell-request.php">Sell</a>
        <a href="dashboard.php">Account</a>
        <a href="cart.php">Cart</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="orders-container">
    <h1><i class="fas fa-box"></i> My Purchase History</h1>
    <p>View all your past orders and track deliveries</p>
    
    <?php if (mysqli_num_rows($orders) == 0): ?>
        <div class="no-orders">
            <i class="fas fa-shopping-bag" style="font-size: 3rem; margin-bottom: 1rem;"></i>
            <h3>No orders yet</h3>
            <p>Start shopping to see your orders here!</p>
            <a href="shop.php" class="btn-primary">Start Shopping</a>
        </div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Delivery</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): 
                    $grandTotal += $order['TotalPrice'];
                    // Get items for this order
                    $items = mysqli_query($conn, "SELECT ol.*, c.ItemName FROM tblorderline ol JOIN tblclothes c ON ol.ProductID = c.ClothesID WHERE ol.OrderID = " . $order['OrderID']);
                    $itemList = '';
                    while ($item = mysqli_fetch_assoc($items)) {
                        $itemList .= $item['ItemName'] . ' x ' . $item['Quantity'] . '<br>';
                    }
                ?>
                <tr>
                    <td><strong><?= $order['OrderNumber'] ?? 'ORD-' . $order['OrderID'] ?></strong></td>
                    <td><?= date('d M Y', strtotime($order['OrderDate'])) ?></td>
                    <td><?= $itemList ?></td>
                    <td>R <?= number_format($order['TotalPrice'], 2) ?></td>
                    <td><span class="status status-<?= strtolower($order['OrderStatus']) ?>"><?= $order['OrderStatus'] ?></span></td>
                    <td><span class="status status-<?= strtolower($order['DeliveryStatus'] ?? 'Pending') ?>"><?= $order['DeliveryStatus'] ?? 'Pending' ?></span></td>
                </tr>
                <?php endwhile; ?>
                <tr class="grand-total-row">
                    <td colspan="3"><strong>GRAND TOTAL OF ALL PURCHASES</strong></td>
                    <td colspan="3"><strong>R <?= number_format($grandTotal, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2026 Pastimes - Sustainable Fashion Marketplace</p>
</footer>

</body>
</html>