<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $order_id = mysqli_real_escape_string($con, $_GET['id']);

    // Fetch the order details
    $order_query = "SELECT s_total, s_name, s_id, s_status FROM product_sales WHERE s_id = '$order_id' AND id = '$user_id'";
    $order_result = mysqli_query($con, $order_query);

    if ($order_result && mysqli_num_rows($order_result) > 0) {
        $order = mysqli_fetch_assoc($order_result);
        $current_status = strtolower($order['s_status']);
        $can_cancel = in_array($current_status, ['pending', 'confirmed', 'processing'], true);

        if (!$can_cancel) {
            header("Location: order.php?toast=error&msg=This+order+cannot+be+cancelled+now.");
            exit;
        }

        $amount = $order['s_total'];
        $sale_id = $order['s_id'];

        // Prevent duplicate refund entries for the same order
        $existing_refund = mysqli_query($con, "SELECT id FROM wallet_transactions WHERE user_id = '$user_id' AND sale_id = '$sale_id' LIMIT 1");
        if ($existing_refund && mysqli_num_rows($existing_refund) > 0) {
            header("Location: order.php?toast=info&msg=Order+already+cancelled+and+refunded.");
            exit;
        }

        // Insert refund into wallet with sale ID reference
        $insert_wallet_query = "INSERT INTO wallet_transactions (user_id, amount, sale_id) 
                                 VALUES ('$user_id', '$amount', '$sale_id')";

        if (mysqli_query($con, $insert_wallet_query)) {
            // Mark the order as canceled
            $currentDate = date('Y-m-d');
            $currentTime = date('H:i:s');
            $cancel_order_query = "UPDATE product_sales SET s_status = 'cancelled' WHERE s_id = '$order_id' AND id = '$user_id'";
            if(mysqli_query($con, $cancel_order_query)) {
                mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ('$order_id', 'cancelled', '$currentDate', '$currentTime')");
                header("Location: order.php?toast=success&msg=Order+cancelled+successfully.+Refunded+to+wallet.");
                exit;
            } else {
                die("Error updating order status: " . mysqli_error($con));
            }
        } else {
            die("Error inserting into wallet: " . mysqli_error($con));
        }
    } else {
        die("Order not found or unauthorized. Order ID: " . $order_id . ", User ID: " . $user_id . ". Please check if this order belongs to you.");
    }
} else {
    header("Location: order.php?toast=error&msg=Action+failed.");
}
?>

