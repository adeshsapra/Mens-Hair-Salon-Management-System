<?php
require_once __DIR__ . '/../connect.php';

if (!$con) {
    die('Database connection failed: ' . mysqli_connect_error());
}

$column_check = mysqli_query($con, "SHOW COLUMNS FROM products LIKE 'p_discount'");

if (!$column_check) {
    die('Failed to check column: ' . mysqli_error($con));
}

if (mysqli_num_rows($column_check) > 0) {
    echo 'Column p_discount already exists. No changes made.';
    exit;
}

$add_column = mysqli_query(
    $con,
    "ALTER TABLE products ADD COLUMN p_discount DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER p_price"
);

if (!$add_column) {
    die('Failed to add p_discount column: ' . mysqli_error($con));
}

echo 'Column p_discount created successfully.';
?>
