<?php
// setup.php - Database initialization script
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS anshu_loyalty_reward";
    $conn->exec($sql);
    echo "Database created successfully<br>";
    
    // Use the database
    $conn->exec("USE anshu_loyalty_reward");
    // In setup.php, modify the users table creation:
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    phone VARCHAR(20),
    join_date DATE,
    points INT(10) DEFAULT 0,
    membership_level VARCHAR(20) DEFAULT 'Basic',
    profile_pic VARCHAR(255) DEFAULT 'default.jpg',
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (username),
    UNIQUE (email)
)";
    $conn->exec($sql);
    echo "Users table created successfully<br>";
    
    // Create rewards table
    $sql = "CREATE TABLE IF NOT EXISTS rewards (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        points_required INT(10) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Rewards table created successfully<br>";
    
    // Create transactions table
    $sql = "CREATE TABLE IF NOT EXISTS transactions (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED NOT NULL,
        points INT(10) NOT NULL,
        type ENUM('earned', 'redeemed') NOT NULL,
        description VARCHAR(255),
        transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";
    $conn->exec($sql);
    echo "Transactions table created successfully<br>";
    
    // Insert sample rewards if they don't exist
    $sql = "INSERT IGNORE INTO rewards (name, description, points_required) VALUES 
        ('10% Discount Coupon', 'Get 10% off on your next purchase', 100),
        ('Free Coffee', 'Redeem for a free coffee at any location', 50),
        ('VIP Membership', 'Upgrade to VIP status for exclusive benefits', 500),
        ('Free Shipping', 'Free shipping on your next order', 75),
        ('Birthday Gift', 'Special gift on your birthday', 200)";
    $conn->exec($sql);
    echo "Sample rewards inserted successfully<br>";
    
    echo "<p>Setup completed successfully! <a href='index.php'>Go to Home Page</a></p>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>