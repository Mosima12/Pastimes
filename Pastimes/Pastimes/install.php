<?php
// install.php - Run this once to set up the database
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'clothingstore';

$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database
mysqli_query($conn, "DROP DATABASE IF EXISTS $dbname");
mysqli_query($conn, "CREATE DATABASE $dbname");
mysqli_select_db($conn, $dbname);
echo "✅ Database created<br>";

// Create tbluser
mysqli_query($conn, "CREATE TABLE tbluser (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    Status ENUM('pending', 'verified') DEFAULT 'pending',
    AccountType ENUM('customer', 'admin') DEFAULT 'customer',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ tbluser created<br>";

// Create tblclothes
mysqli_query($conn, "CREATE TABLE tblclothes (
    ClothesID INT AUTO_INCREMENT PRIMARY KEY,
    SellerID INT,
    ItemName VARCHAR(150) NOT NULL,
    Brand VARCHAR(100),
    Category ENUM('tops', 'bottoms', 'dresses', 'jackets', 'shoes', 'accessories', 'other') NOT NULL,
    ClothesSize ENUM('XS', 'S', 'M', 'L', 'XL', 'XXL') DEFAULT NULL,
    `Condition` ENUM('New', 'Like new', 'Good', 'Fair') DEFAULT NULL,
    Price DECIMAL(10,2) NOT NULL,
    Quantity INT DEFAULT 1,
    Description TEXT,
    ImageURL VARCHAR(300),
    Status VARCHAR(20) DEFAULT 'active',
    DateListed DATE DEFAULT (CURDATE())
)");
echo "✅ tblclothes created<br>";

// Create tblaorder
mysqli_query($conn, "CREATE TABLE tblaorder (
    OrderID INT AUTO_INCREMENT PRIMARY KEY,
    OrderNumber VARCHAR(50) UNIQUE,
    BuyerID INT,
    TotalPrice DECIMAL(10,2),
    DeliveryAddress TEXT,
    OrderStatus ENUM('Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    PaymentStatus ENUM('Pending', 'Paid', 'Failed', 'Refunded') DEFAULT 'Pending',
    DeliveryStatus ENUM('Pending', 'In Transit', 'Delivered', 'Failed') DEFAULT 'Pending',
    SessionID VARCHAR(100),
    OrderDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ tblaorder created<br>";

// Create tblorderline
mysqli_query($conn, "CREATE TABLE tblorderline (
    OrderLineID INT AUTO_INCREMENT PRIMARY KEY,
    OrderID INT,
    ProductID INT,
    Quantity INT,
    Price DECIMAL(10,2)
)");
echo "✅ tblorderline created<br>";

// Create tbladmin
mysqli_query($conn, "CREATE TABLE tbladmin (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(80) NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "✅ tbladmin created<br>";

// Insert admin
mysqli_query($conn, "INSERT INTO tbladmin (Username, PasswordHash) VALUES ('admin', '0192023a7bbd73250516f069df18b500')");
mysqli_query($conn, "INSERT INTO tbluser (FullName, Email, PasswordHash, Status, AccountType) VALUES ('Administrator', 'admin@pastimes.co.za', '0192023a7bbd73250516f069df18b500', 'verified', 'admin')");
echo "✅ Admin added<br>";

// Insert customers
mysqli_query($conn, "INSERT INTO tbluser (FullName, Email, PasswordHash, Status, AccountType) VALUES 
    ('John Doe', 'john.doe@example.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'verified', 'customer'),
    ('Jane Smith', 'jane.smith@example.com', '5f4dcc3b5aa765d61d8327deb882cf99', 'verified', 'customer')");
echo "✅ Customers added<br>";

// Insert products
mysqli_query($conn, "INSERT INTO tblclothes (SellerID, ItemName, Brand, Category, ClothesSize, `Condition`, Price, Quantity, Description, Status) VALUES 
    (2, 'Vintage Denim Jacket', 'Levi\'s', 'jackets', 'M', 'Good', 450.00, 1, 'Classic vintage denim jacket', 'active'),
    (3, 'Floral Summer Dress', 'Zara', 'dresses', 'S', 'Like new', 299.00, 1, 'Beautiful floral dress', 'active'),
    (2, 'Leather Handbag', 'Coach', 'accessories', NULL, NULL, 1200.00, 1, 'Genuine leather handbag', 'active'),
    (3, 'White Sneakers', 'Nike', 'shoes', 'L', 'Very Good', 650.00, 2, 'Classic white sneakers', 'active')");
echo "✅ Products added<br>";

// Create folders
if (!is_dir('images')) mkdir('images', 0777);
if (!is_dir('uploads')) mkdir('uploads', 0777);
echo "✅ Folders created<br>";

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<strong>Login:</strong><br>";
echo "Admin: admin@pastimes.co.za / admin123<br>";
echo "Customer: john.doe@example.com / password<br>";
echo "<a href='index.php'>Go to Homepage</a>";

mysqli_close($conn);
?>