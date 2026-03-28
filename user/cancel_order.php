<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: order.php?toast=error&msg=Invalid+order+ID.');
    exit;
}

$order_result = mysqli_query(
    $con,
    "SELECT s_id, s_total, s_status, s_date, s_time FROM product_sales WHERE s_id = {$order_id} AND id = {$user_id} LIMIT 1"
);

if (!$order_result || mysqli_num_rows($order_result) === 0) {
    header('Location: order.php?toast=error&msg=Order+not+found.');
    exit;
}

$order = mysqli_fetch_assoc($order_result);
$current_status = strtolower(trim($order['s_status']));

if (in_array($current_status, ['shipped', 'delivered', 'cancelled', 'refunded'], true)) {
    header("Location: order.php?toast=error&msg=This+order+cannot+be+cancelled+now.");
    exit;
}

// Prevent duplicate refund entries for the same order (from adesh branch)
$amount = $order['s_total'];
$sale_id = $order['s_id'];
$existing_refund = mysqli_query($con, "SELECT id FROM wallet_transactions WHERE user_id = '$user_id' AND sale_id = '$sale_id' LIMIT 1");
if ($existing_refund && mysqli_num_rows($existing_refund) > 0) {
    header("Location: order.php?toast=info&msg=Order+already+cancelled+and+refunded.");
    exit;
}

// Look up payment record (from main branch)
$payment = null;
$paymentResult = mysqli_query(
    $con,
    "SELECT * FROM payment WHERE id = {$user_id} AND s_id = {$order_id} ORDER BY pay_id DESC LIMIT 1"
);
if ($paymentResult && mysqli_num_rows($paymentResult) > 0) {
    $payment = mysqli_fetch_assoc($paymentResult);
} else {
    $fallbackResult = mysqli_query(
        $con,
        "SELECT * FROM payment WHERE id = {$user_id} AND p_date = '{$order['s_date']}' AND p_time = '{$order['s_time']}' ORDER BY pay_id DESC LIMIT 1"
    );
    if ($fallbackResult && mysqli_num_rows($fallbackResult) > 0) {
        $payment = mysqli_fetch_assoc($fallbackResult);
    }
}

$payment_method = $payment ? strtolower(trim($payment['p_method'])) : 'cod';
$payment_status = $payment ? strtolower(trim($payment['p_status'])) : 'pending';
$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

mysqli_begin_transaction($con);

try {
    if ($payment_method === 'cod') {
        $cancelOrder = mysqli_query($con, "UPDATE product_sales SET s_status = 'cancelled' WHERE s_id = {$order_id} AND id = {$user_id}");
        if (!$cancelOrder) {
            throw new Exception('Unable to cancel order.');
        }

        if ($payment && isset($payment['pay_id'])) {
            mysqli_query($con, "UPDATE payment SET p_status = 'cancelled' WHERE pay_id = " . (int) $payment['pay_id']);
        }

        $insertStatus = mysqli_query(
            $con,
            "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$order_id}, 'cancelled', '{$currentDate}', '{$currentTime}')"
        );
        if (!$insertStatus) {
            throw new Exception('Unable to update order history.');
        }

        mysqli_commit($con);
        header('Location: order.php?toast=success&msg=Order+cancelled+successfully.');
        exit;
    }

    if (!in_array($payment_method, ['stripe', 'wallet'], true)) {
        throw new Exception('Unsupported payment method for cancellation.');
    }

    if ($payment_status === 'refunded') {
        throw new Exception('Order already refunded.');
    }

    $updateOrder = mysqli_query($con, "UPDATE product_sales SET s_status = 'cancelled' WHERE s_id = {$order_id} AND id = {$user_id}");
    if (!$updateOrder) {
        throw new Exception('Failed to cancel order.');
    }

    if ($payment && isset($payment['pay_id'])) {
        $updatePayment = mysqli_query($con, "UPDATE payment SET p_status = 'refund_pending' WHERE pay_id = " . (int) $payment['pay_id']);
        if (!$updatePayment) {
            throw new Exception('Failed to update refund status.');
        }
    } else {
        mysqli_query(
            $con,
            "UPDATE payment SET p_status = 'refund_pending' WHERE id = {$user_id} AND p_date = '{$order['s_date']}' AND p_time = '{$order['s_time']}' AND LOWER(p_method) IN ('stripe', 'wallet')"
        );
    }

    // Wallet refund: credit back to wallet (from adesh branch)
    $insert_wallet_query = "INSERT INTO wallet_transactions (user_id, sale_id, type, amount, source, created_at) VALUES ('$user_id', '$order_id', 'credit', '{$order['s_total']}', 'order_refund', NOW())";
    mysqli_query($con, $insert_wallet_query);

    $insertStatus = mysqli_query(
        $con,
        "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$order_id}, 'cancelled', '{$currentDate}', '{$currentTime}')"
    );
    if (!$insertStatus) {
        throw new Exception('Failed to add cancellation history.');
    }

    mysqli_commit($con);
    header('Location: order.php?toast=success&msg=Order+cancelled+successfully.+Refunded+to+wallet.');
    exit;
} catch (Exception $e) {
    mysqli_rollback($con);
    header('Location: order.php?toast=error&msg=' . urlencode($e->getMessage()));
    exit;
}
?>
