<?php
// createTable.php
// Drops tblUser if it exists, recreates it, and loads data from userData.txt

require_once 'DBConn.php';

// ── Step 1: Drop tblUser if it exists ────────────────────────────────────────
$dropSQL = "DROP TABLE IF EXISTS tbluser";
if (mysqli_query($conn, $dropSQL)) {
    echo "✔ tbluser dropped (if it existed).<br>";
} else {
    die("Error dropping table: " . mysqli_error($conn));
}

// ── Step 2: Create tblUser ────────────────────────────────────────────────────
$createSQL = "
CREATE TABLE IF NOT EXISTS tbluser (
    UserID       INT AUTO_INCREMENT PRIMARY KEY,
    FullName     VARCHAR(100)        NOT NULL,
    Email        VARCHAR(150)        NOT NULL UNIQUE,
    PasswordHash VARCHAR(255)        NOT NULL,
    Status       ENUM('pending','verified') DEFAULT 'pending',
    CreatedAt    TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $createSQL)) {
    echo "✔ tbluser created successfully.<br>";
} else {
    die("Error creating table: " . mysqli_error($conn));
}

// ── Step 3: Load data from userData.txt ──────────────────────────────────────
$filePath = __DIR__ . '/userData.txt';

if (!file_exists($filePath)) {
    die("userData.txt not found at: $filePath");
}

$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$inserted = 0;

foreach ($lines as $line) {
    // Each line: FullName \t Email \t PasswordHash \t Status
    $parts = explode("\t", $line);

    if (count($parts) < 3) continue; // skip malformed lines

    $fullName = mysqli_real_escape_string($conn, trim($parts[0]));
    $email    = mysqli_real_escape_string($conn, trim($parts[1]));
    $hash     = mysqli_real_escape_string($conn, trim($parts[2]));
    $status   = isset($parts[3]) ? mysqli_real_escape_string($conn, trim($parts[3])) : 'pending';

    $insertSQL = "INSERT INTO tblUser (FullName, Email, PasswordHash, Status)
                  VALUES ('$fullName', '$email', '$hash', '$status')";

    if (mysqli_query($conn, $insertSQL)) {
        $inserted++;
    } else {
        echo "⚠ Skipped '$fullName': " . mysqli_error($conn) . "<br>";
    }
}

echo "✔ $inserted record(s) loaded into tblUser from userData.txt.<br>";
echo "<br><strong>Done!</strong> Table is ready.";

mysqli_close($conn);
?>