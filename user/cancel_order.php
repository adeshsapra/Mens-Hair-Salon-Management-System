<?php
include('connect.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['id'];

// Fetch the order details
$order_query = "SELECT s_total, s_name, s_id FROM product_sales WHERE s_id = '$order_id' AND id = '$user_id'";
$order_result = mysqli_query($con, $order_query);

if ($order_result && mysqli_num_rows($order_result) > 0) {
    $order = mysqli_fetch_assoc($order_result);
    
    // Move to wallet
    $amount = $order['s_total'];
    $sale_id = $order['s_id']; // Get the sale ID

    // Insert into wallet with sale ID
    $insert_wallet_query = "INSERT INTO wallet_transactions (user_id, amount, product_id) 
                             VALUES ('$user_id', '$amount', '$sale_id')"; // Using s_id as product_id

    if (mysqli_query($con, $insert_wallet_query)) {
        // Mark the order as canceled
        $cancel_order_query = "UPDATE product_sales SET s_status = 'Cancelled' WHERE s_id = '$order_id'";
        mysqli_query($con, $cancel_order_query);

        // Redirect back to orders page with success message
        header("Location: user_wallet.php");
        exit;
    } else {
        die("Error inserting into wallet: " . mysqli_error($con));
    }
} else {
    die("Order not found.");
}
?>