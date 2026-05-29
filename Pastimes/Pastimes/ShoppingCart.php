<?php
// ShoppingCart.php - Complete Shopping Cart Class for POE
class ShoppingCart {
    private $cart;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $this->cart = &$_SESSION['cart'];
    }
    
    // AddItem - Adds item to cart, increases quantity if exists
    public function addItem($productId, $productName, $price, $quantity = 1, $imageUrl = '') {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += $quantity;
        } else {
            $this->cart[$productId] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $price,
                'quantity' => $quantity,
                'image' => $imageUrl
            ];
        }
        return true;
    }
    
    // RemoveItem - Removes item from cart
    public function removeItem($productId) {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            return true;
        }
        return false;
    }
    
    // UpdateQuantity - Updates quantity of specific item
    public function updateQuantity($productId, $quantity) {
        if (isset($this->cart[$productId])) {
            if ($quantity <= 0) {
                $this->removeItem($productId);
            } else {
                $this->cart[$productId]['quantity'] = $quantity;
            }
            return true;
        }
        return false;
    }
    
    // GetCartItems - Returns all cart items
    public function getCartItems() {
        return array_values($this->cart);
    }
    
    // GetTotalItems - Returns total number of items in cart
    public function getTotalItems() {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }
    
    // GetTotalPrice - Returns total price of all items
    public function getTotalPrice() {
        $total = 0;
        foreach ($this->cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    // EmptyCart - Empties the entire cart
    public function emptyCart() {
        $_SESSION['cart'] = [];
        $this->cart = &$_SESSION['cart'];
        return true;
    }
    
    // Checkout - Returns order data for processing
    public function checkout() {
        if ($this->getTotalItems() == 0) {
            return false;
        }
        return [
            'items' => $this->getCartItems(),
            'total' => $this->getTotalPrice(),
            'orderNumber' => 'ORD-' . date('Ymd') . '-' . uniqid(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    // ProcessInput - Processes form input actions
    public function processInput($action, $data) {
        switch ($action) {
            case 'add':
                return $this->addItem($data['id'], $data['name'], $data['price'], $data['quantity'] ?? 1, $data['image'] ?? '');
            case 'remove':
                return $this->removeItem($data['id']);
            case 'update':
                return $this->updateQuantity($data['id'], $data['quantity']);
            case 'empty':
                return $this->emptyCart();
            default:
                return false;
        }
    }
    
    // Login - Associates cart with user after login
    public function login($userId) {
        $_SESSION['user_id'] = $userId;
        return true;
    }
}
?>