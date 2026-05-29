<?php
require_once 'DBConn.php';

echo "<h2>Image Checker</h2>";

// Get all products
$result = mysqli_query($conn, "SELECT ClothesID, ItemName, ImageURL FROM tblclothes");

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Product</th><th>Image URL in DB</th><th>File Exists?</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    $imagePath = $row['ImageURL'];
    $fullPath = __DIR__ . '/' . $imagePath;
    $exists = file_exists($fullPath);
    
    echo "<tr>";
    echo "<td>" . $row['ClothesID'] . "</td>";
    echo "<td>" . $row['ItemName'] . "</td>";
    echo "<td><code>" . htmlspecialchars($imagePath) . "</code></td>";
    echo "<td style='color: " . ($exists ? 'green' : 'red') . "'>" . ($exists ? 'YES' : 'NO - Missing!') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Folder Contents:</h3>";

// Check images folder
echo "<h4>/images/ folder:</h4>";
if (is_dir('images')) {
    $files = scandir('images');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ images folder does NOT exist!</p>";
}

// Check uploads folder
echo "<h4>/uploads/ folder:</h4>";
if (is_dir('uploads')) {
    $files = scandir('uploads');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color:red'>❌ uploads folder does NOT exist!</p>";
}

mysqli_close($conn);
?>