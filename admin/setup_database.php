<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "salon_db";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error;
}

// Select the database
$conn->select_db($dbname);

// Create products table
$sql = "CREATE TABLE IF NOT EXISTS products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'products' created successfully<br>";
} else {
    echo "Error creating table: " . $conn->error;
}

// Insert a sample product
$sql = "INSERT INTO products (name, description, image) VALUES ('Sample Product', 'This is a description of the sample product.', 'uploads/sample-product.jpg')";

if ($conn->query($sql) === TRUE) {
    echo "Sample product inserted successfully<br>";
} else {
    echo "Error inserting sample product: " . $conn->error;
}

// Close connection
$conn->close();
?>
