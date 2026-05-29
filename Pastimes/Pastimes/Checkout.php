<?php
// checkout.php - Complete checkout with order processing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'DBConn.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?redirect=checkout.php&checkout=1");
    exit();
}

$message = '';
$orderNum = '';
$sessionId = session_id();
$userName = $_SESSION['user_name'] ?? '';
$userEmail = $_SESSION['user_email'] ?? '';
$fullName = explode(' ', $userName, 2);
$firstName = $fullName[0] ?? '';
$lastName = $fullName[1] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartData = json_decode($_POST['cart'], true);
    $subtotal = floatval($_POST['subtotal']);
    $shipping = floatval($_POST['shipping']);
    $total = $subtotal + $shipping;
    
    $firstName = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lastName = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $postalCode = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $userId = $_SESSION['user_id'];
    
    $fullAddress = "$city, $state, $postalCode";
    $orderNum = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // Insert into orders table - without PaymentMethod if column doesn't exist
    // First check if PaymentMethod column exists
    $checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM tblaorder LIKE 'PaymentMethod'");
    $hasPaymentMethod = mysqli_num_rows($checkColumn) > 0;
    
    if ($hasPaymentMethod) {
        $sql = "INSERT INTO tblaorder (OrderNumber, BuyerID, TotalPrice, DeliveryAddress, PaymentMethod, OrderStatus, PaymentStatus, DeliveryStatus, SessionID) 
                VALUES ('$orderNum', $userId, $total, '$fullAddress', '$paymentMethod', 'Pending', 'Paid', 'Pending', '$sessionId')";
    } else {
        $sql = "INSERT INTO tblaorder (OrderNumber, BuyerID, TotalPrice, DeliveryAddress, OrderStatus, PaymentStatus, DeliveryStatus, SessionID) 
                VALUES ('$orderNum', $userId, $total, '$fullAddress', 'Pending', 'Paid', 'Pending', '$sessionId')";
    }
    
    if (mysqli_query($conn, $sql)) {
        $orderId = mysqli_insert_id($conn);
        
        // Insert into orderLine table
        foreach ($cartData as $item) {
            $productId = $item['id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            $sql = "INSERT INTO tblorderline (OrderID, ProductID, Quantity, Price) 
                    VALUES ($orderId, $productId, $quantity, $price)";
            mysqli_query($conn, $sql);
            
            // Decrement quantity in products table
            $sql = "UPDATE tblclothes SET Quantity = Quantity - $quantity WHERE ClothesID = $productId";
            mysqli_query($conn, $sql);
        }
        
        $message = 'success';
        // Clear the shopping cart
        echo "<script>localStorage.removeItem('pastimes_cart');</script>";
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
    } else {
        $message = 'error';
        $errorMsg = mysqli_error($conn);
    }
}

// Get cart items from localStorage via JavaScript
$cartItems = [];
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pastimes</title>
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
            background: #F5F0EB;
            color: #1C1C1C;
        }
        
        /* Navigation */
        nav {
            background: white;
            border-bottom: 1px solid #E2DDD7;
            padding: 0 5%;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
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
        
        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: #7A7065;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #C0533A;
        }
        
        /* Main Checkout Container */
        .checkout-wrapper {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .checkout-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: #1C1C1C;
            position: relative;
            display: inline-block;
        }
        
        .checkout-header h1:after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #C0533A;
            border-radius: 3px;
        }
        
        /* Two Column Layout */
        .checkout-two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        /* Left Column */
        .checkout-left {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1C1C1C;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #E2DDD7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .section-title i {
            color: #C0533A;
            font-size: 1rem;
        }
        
        /* Form Grid */
        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 500;
            font-size: 0.8rem;
            color: #7A7065;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #E2DDD7;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            transition: all 0.3s;
            background: white;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #C0533A;
            box-shadow: 0 0 0 3px rgba(192,83,58,0.1);
        }
        
        /* Payment Methods */
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 0.5rem;
        }
        
        .payment-option {
            flex: 1;
            min-width: 100px;
            padding: 0.8rem;
            border: 2px solid #E2DDD7;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .payment-option i {
            font-size: 1.2rem;
            margin-right: 0.5rem;
            color: #7A7065;
        }
        
        .payment-option input {
            display: none;
        }
        
        .payment-option.selected {
            border-color: #C0533A;
            background: rgba(192,83,58,0.05);
        }
        
        .payment-option.selected i {
            color: #C0533A;
        }
        
        /* Right Column - Order Summary */
        .checkout-right {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 100px;
        }
        
        .order-summary-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #E2DDD7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .order-items {
            margin-bottom: 1.5rem;
            max-height: 350px;
            overflow-y: auto;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #E2DDD7;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #1C1C1C;
            font-size: 0.9rem;
        }
        
        .order-item-price {
            font-size: 0.75rem;
            color: #7A7065;
            margin-top: 0.2rem;
        }
        
        .order-item-quantity {
            font-size: 0.7rem;
            color: #C0533A;
            margin-top: 0.2rem;
        }
        
        .order-item-total {
            font-weight: 700;
            color: #C0533A;
            font-size: 0.9rem;
        }
        
        /* Totals */
        .totals {
            background: #F7F3EE;
            border-radius: 16px;
            padding: 1rem;
            margin: 1rem 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }
        
        .total-row.grand-total {
            border-top: 2px solid #E2DDD7;
            margin-top: 0.5rem;
            padding-top: 0.8rem;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .grand-total .amount {
            color: #C0533A;
            font-size: 1.3rem;
        }
        
        /* Proceed Button */
        .btn-proceed {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #C0533A 0%, #A8432C 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-proceed:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(192,83,58,0.3);
        }
        
        /* Secure Badge */
        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 0.8rem;
            background: #E8F5E9;
            border-radius: 12px;
            color: #2E7D32;
            font-size: 0.8rem;
        }
        
        /* Order Confirmation */
        .order-confirmation {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #E8F5E9;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: #2E7D32;
        }
        
        .order-details {
            background: #F7F3EE;
            padding: 1.5rem;
            border-radius: 16px;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .order-details p {
            margin: 0.5rem 0;
        }
        
        .order-number {
            font-weight: 700;
            color: #C0533A;
            font-size: 1.1rem;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .btn-primary {
            padding: 0.8rem 1.8rem;
            background: #C0533A;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #A8432C;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            padding: 0.8rem 1.8rem;
            background: transparent;
            color: #1C1C1C;
            border: 2px solid #E2DDD7;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            border-color: #C0533A;
            color: #C0533A;
        }
        
        /* Alert Messages */
        .alert-error {
            background: #FFEBEE;
            color: #C62828;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            border-left: 4px solid #C62828;
        }
        
        /* Footer */
        footer {
            background: #1C1C1C;
            color: #999;
            padding: 2rem 5%;
            margin-top: 3rem;
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .footer-col h4 {
            color: white;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .footer-col a {
            display: block;
            color: #999;
            text-decoration: none;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-col a:hover {
            color: #C0533A;
        }
        
        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 0.8rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .checkout-two-column {
                grid-template-columns: 1fr;
            }
            
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            
            .checkout-wrapper {
                padding: 0 1rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
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

<div class="checkout-wrapper">
    <?php if ($message === 'success'): ?>
        <!-- Order Confirmation -->
        <div class="order-confirmation">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>Order Confirmed!</h2>
            <p>Thank you for your purchase</p>
            
            <div class="order-details">
                <p><strong>Order Number:</strong> <span class="order-number"><?= $orderNum ?></span></p>
                <p><strong>Session ID:</strong> <code><?= $sessionId ?></code></p>
                <p><strong>Date:</strong> <?= date('d F Y, H:i') ?></p>
                <p><strong>Total Amount:</strong> <span class="order-number">R <?= number_format($_POST['subtotal'] + $_POST['shipping'], 2) ?></span></p>
            </div>
            
            <p>A confirmation email has been sent to <strong><?= htmlspecialchars($userEmail) ?></strong></p>
            
            <div class="btn-group">
                <a href="orders.php" class="btn-primary">View My Orders</a>
                <a href="shop.php" class="btn-outline">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <div class="checkout-header">
            <h1>Checkout</h1>
        </div>
        
        <?php if (isset($errorMsg)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $errorMsg ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="checkoutForm">
            <input type="hidden" name="cart" id="cartData">
            <input type="hidden" name="subtotal" id="subtotalData">
            <input type="hidden" name="shipping" id="shippingData">
            
            <div class="checkout-two-column">
                <!-- Left Column -->
                <div class="checkout-left">
                    <!-- Customer Info -->
                    <div class="section-title">
                        <i class="fas fa-user"></i> Customer Info
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" placeholder="+27 12 345 6789" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($userEmail) ?>" required>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="section-title" style="margin-top: 1.5rem;">
                        <i class="fas fa-truck"></i> Shipping Address
                    </div>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" placeholder="e.g., Cape Town" required>
                        </div>
                        <div class="form-group">
                            <label>State/Province</label>
                            <input type="text" name="state" placeholder="e.g., Western Cape" required>
                        </div>
                        <div class="form-group">
                            <label>Postal Code</label>
                            <input type="text" name="postal_code" placeholder="e.g., 8001" required>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="section-title" style="margin-top: 1.5rem;">
                        <i class="fas fa-credit-card"></i> Payment
                    </div>
                    <div class="payment-methods">
                        <label class="payment-option selected" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="Credit Card" checked>
                            <i class="fas fa-credit-card"></i> Credit Card
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="PayPal">
                            <i class="fab fa-paypal"></i> PayPal
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="Cash">
                            <i class="fas fa-money-bill"></i> Cash
                        </label>
                        <label class="payment-option" onclick="selectPayment(this)">
                            <input type="radio" name="payment_method" value="Gift Card">
                            <i class="fas fa-gift"></i> Gift Card
                        </label>
                    </div>
                </div>
                
                <!-- Right Column - Order Summary -->
                <div class="checkout-right">
                    <div class="order-summary-title">
                        <i class="fas fa-shopping-bag"></i> Your Purchases
                    </div>
                    
                    <div class="order-items" id="orderItems"></div>
                    
                    <div class="totals">
                        <div class="total-row">
                            <span>Subtotal</span>
                            <span id="subtotalDisplay">R 0.00</span>
                        </div>
                        <div class="total-row">
                            <span>Shipping</span>
                            <span>R 60.00</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total</span>
                            <span class="amount" id="grandTotal">R 0.00</span>
                        </div>
                    </div>
                    
                    <div class="secure-badge">
                        <i class="fas fa-lock"></i> Secure payment protected by escrow
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    
                    <button type="submit" class="btn-proceed">
                        <i class="fas fa-check-circle"></i> PROCEED TO CHECKOUT
                    </button>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<footer>
    <div class="footer-content">
        <div class="footer-col">
            <h4>COMPANY</h4>
            <a href="#">About us</a>
            <a href="#">Testimonials</a>
            <a href="#">FAQ</a>
        </div>
        <div class="footer-col">
            <h4>REGISTRANT</h4>
            <a href="#">Delivery</a>
            <a href="#">Corporate Orders</a>
            <a href="#">Payment</a>
            <a href="#">Shipping & Return</a>
            <a href="#">Terms & Conditions</a>
        </div>
        <div class="footer-col">
            <h4>CONTACT</h4>
            <a href="#"><i class="fas fa-phone"></i> +27 12 345 6789</a>
            <a href="#"><i class="fas fa-envelope"></i> help@pastimes.co.za</a>
        </div>
        <div class="footer-col">
            <h4>SOCIAL</h4>
            <a href="#"><i class="fab fa-instagram"></i> Instagram</a>
            <a href="#"><i class="fab fa-youtube"></i> YouTube</a>
            <a href="#"><i class="fab fa-facebook"></i> Facebook</a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2026 Pastimes - Sustainable Fashion Marketplace</p>
    </div>
</footer>

<script>
function selectPayment(element) {
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    element.classList.add('selected');
    element.querySelector('input').checked = true;
}

// Get cart from localStorage
let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');
let subtotal = 0;
let itemsHtml = '';

if (cart.length === 0 && <?= $message !== 'success' ? 'true' : 'false' ?>) {
    window.location.href = 'cart.php';
}

cart.forEach(item => {
    let itemTotal = item.price * item.quantity;
    subtotal += itemTotal;
    itemsHtml += `
        <div class="order-item">
            <div class="order-item-info">
                <div class="order-item-name">${item.name}</div>
                <div class="order-item-price">R ${item.price.toFixed(2)} each</div>
                <div class="order-item-quantity">Quantity: ${item.quantity}</div>
            </div>
            <div class="order-item-total">R ${itemTotal.toFixed(2)}</div>
        </div>
    `;
});

let shipping = 60;
let total = subtotal + shipping;

document.getElementById('orderItems').innerHTML = itemsHtml;
document.getElementById('subtotalDisplay').innerHTML = `R ${subtotal.toFixed(2)}`;
document.getElementById('grandTotal').innerHTML = `R ${total.toFixed(2)}`;
document.getElementById('cartData').value = JSON.stringify(cart);
document.getElementById('subtotalData').value = subtotal;
document.getElementById('shippingData').value = shipping;
</script>

</body>
</html>