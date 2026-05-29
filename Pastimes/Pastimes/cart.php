<?php
// cart.php - Shopping cart page with full CSS
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Pastimes</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ============================================ */
        /* CART PAGE SPECIFIC STYLES
        /* ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DM Sans', sans-serif;
            background: #F7F3EE;
            color: #1C1C1C;
            line-height: 1.6;
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
            background-clip: text;
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
            font-size: 0.95rem;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #1C1C1C;
        }
        
        .btn-nav {
            background: #C0533A;
            color: white !important;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
        }
        
        /* Cart Container */
        .cart-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .cart-container h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #E2DDD7;
        }
        
        .cart-container h1 i {
            color: #C0533A;
            margin-right: 0.5rem;
        }
        
        /* Cart Items */
        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 1.5rem;
            align-items: center;
            padding: 1.2rem;
            border-bottom: 1px solid #E2DDD7;
            transition: background 0.3s;
        }
        
        .cart-item:hover {
            background: #F7F3EE;
        }
        
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            background: #E2DDD7;
        }
        
        .cart-item-details h3 {
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }
        
        .cart-item-details p {
            color: #7A7065;
            font-size: 0.85rem;
            margin-bottom: 0;
        }
        
        .cart-item-price {
            color: #C0533A;
            font-weight: 600;
        }
        
        /* Quantity Controls */
        .cart-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .cart-quantity button {
            width: 32px;
            height: 32px;
            border: 1px solid #E2DDD7;
            background: #F7F3EE;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .cart-quantity button:hover {
            background: #C0533A;
            color: white;
            border-color: #C0533A;
        }
        
        .cart-quantity span {
            min-width: 30px;
            text-align: center;
            font-weight: 500;
        }
        
        .cart-remove {
            background: none;
            border: none;
            cursor: pointer;
            color: #C62828;
            font-size: 1.2rem;
            transition: all 0.3s;
            padding: 0.5rem;
        }
        
        .cart-remove:hover {
            transform: scale(1.1);
        }
        
        /* Cart Total */
        .cart-total {
            text-align: right;
            padding: 1.2rem;
            font-size: 1.3rem;
            font-weight: 700;
            border-top: 2px solid #E2DDD7;
            margin-top: 1rem;
            background: #F7F3EE;
            border-radius: 10px;
        }
        
        /* Cart Actions */
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            gap: 1rem;
        }
        
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.8rem;
            background: linear-gradient(135deg, #C0533A 0%, #A8432C 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(192,83,58,0.3);
        }
        
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.8rem;
            background: transparent;
            color: #1C1C1C;
            border: 2px solid #E2DDD7;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            border-color: #C0533A;
            color: #C0533A;
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #7A7065;
        }
        
        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #E2DDD7;
        }
        
        .empty-cart h3 {
            margin-bottom: 0.5rem;
        }
        
        /* Footer */
        footer {
            background: #1C1C1C;
            color: #999;
            padding: 2rem 5%;
            margin-top: 3rem;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 0.8rem;
            }
            
            .cart-item-img {
                margin: 0 auto;
            }
            
            .cart-quantity {
                justify-content: center;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .cart-container {
                margin: 1rem;
                padding: 1rem;
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
        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
            <a href="dashboard.php">Account</a>
            <a href="cart.php" style="color:#C0533A;">Cart</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-nav">Register</a>
        <?php endif; ?>
    </div>
</nav>

<div class="cart-container">
    <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
    <div id="cart-items"></div>
    <div id="cart-total" class="cart-total"></div>
    <div class="cart-actions">
        <a href="shop.php" class="btn-outline"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
        <a href="checkout.php" class="btn-primary" id="checkoutBtn"><i class="fas fa-credit-card"></i> Proceed to Checkout</a>
    </div>
</div>

<footer>
    <p>&copy; 2026 Pastimes - Sustainable Fashion Marketplace</p>
</footer>

<script>
let cart = JSON.parse(localStorage.getItem('pastimes_cart') || '[]');

function loadCart() {
    let container = document.getElementById('cart-items');
    let totalContainer = document.getElementById('cart-total');
    let checkoutBtn = document.getElementById('checkoutBtn');
    let total = 0;
    
    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <i class="fas fa-shopping-bag"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items yet</p>
                <a href="shop.php" class="btn-primary" style="display: inline-block; margin-top: 1rem;">Start Shopping</a>
            </div>
        `;
        totalContainer.innerHTML = '';
        if (checkoutBtn) checkoutBtn.style.display = 'none';
        return;
    }
    
    let html = '';
    cart.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        total += itemTotal;
        html += `
            <div class="cart-item">
                <img class="cart-item-img" src="${item.image || 'images/placeholder.jpg'}" alt="${item.name}">
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <p>R ${item.price.toFixed(2)} each</p>
                </div>
                <div class="cart-quantity">
                    <button onclick="updateQuantity(${index}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQuantity(${index}, 1)">+</button>
                </div>
                <div>
                    <strong class="cart-item-price">R ${itemTotal.toFixed(2)}</strong>
                    <button class="cart-remove" onclick="removeItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    totalContainer.innerHTML = `<strong>Total: R ${total.toFixed(2)}</strong>`;
    if (checkoutBtn) checkoutBtn.style.display = 'inline-flex';
}

function updateQuantity(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    localStorage.setItem('pastimes_cart', JSON.stringify(cart));
    loadCart();
}

function removeItem(index) {
    cart.splice(index, 1);
    localStorage.setItem('pastimes_cart', JSON.stringify(cart));
    loadCart();
}

loadCart();
</script>

</body>
</html>