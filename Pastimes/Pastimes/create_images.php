<?php
// create_images.php - Creates placeholder images

// Create folders if they don't exist
if (!is_dir('images')) {
    mkdir('images', 0777, true);
    echo "✅ Created 'images' folder<br>";
}

if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
    echo "✅ Created 'uploads' folder<br>";
}

// Function to create a colored image with text
function createImage($filename, $text, $color) {
    $width = 400;
    $height = 400;
    
    $image = imagecreate($width, $height);
    
    // Convert hex color to RGB
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    
    $bgColor = imagecolorallocate($image, $r, $g, $b);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Draw rectangle
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Add text
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $textHeight = imagefontheight($fontSize);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    imagestring($image, $fontSize, $x, $y, $text, $textColor);
    
    // Add a second line
    $line2 = "R 000.00";
    $line2Width = imagefontwidth($fontSize) * strlen($line2);
    $x2 = ($width - $line2Width) / 2;
    imagestring($image, $fontSize, $x2, $y + 20, $line2, $textColor);
    
    // Save image
    imagejpeg($image, $filename);
    imagedestroy($image);
    echo "✅ Created: $filename<br>";
}

// Product images
$products = [
    'images/jacket1.jpg' => ['Vintage Denim Jacket', '8B4513'],
    'images/dress1.jpg' => ['Floral Summer Dress', 'E75480'],
    'images/bag1.jpg' => ['Leather Handbag', '8B5E3C'],
    'images/shoes1.jpg' => ['White Sneakers', 'F5F5F5'],
    'images/sweater1.jpg' => ['Cashmere Sweater', 'D4A574'],
    'images/blouse1.jpg' => ['Silk Blouse', 'E8C4D4'],
    'images/jeans1.jpg' => ['Skinny Jeans', '3A6EA5'],
    'images/coat1.jpg' => ['Wool Coat', 'C4A86B'],
    'images/tshirt1.jpg' => ['Striped T-Shirt', '6B8E23'],
    'images/shorts1.jpg' => ['Running Shorts', '2C5F2D']
];

foreach ($products as $filename => $data) {
    createImage($filename, $data[0], $data[1]);
}

echo "<br><strong>All images created!</strong><br>";
echo "<a href='shop.php'>Go to Shop →</a>";
?>