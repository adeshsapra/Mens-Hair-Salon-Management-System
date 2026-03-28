<?php
include 'connect.php';
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_POST['payment_intent_id'])) {
    header('Location: checkout.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? (int) $_POST['product_id'] : null;
$payment_intent_id = trim($_POST['payment_intent_id']);

$delivery = [
    'full_name' => trim($_POST['full-name'] ?? ''),
    'contact_number' => trim($_POST['contact-number'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'city' => trim($_POST['city'] ?? ''),
    'state' => trim($_POST['state'] ?? ''),
    'postal_code' => trim($_POST['postal-code'] ?? '')
];

if ($payment_intent_id === '' || in_array('', $delivery, true)) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Invalid payment data.';
    header('Location: checkout.php');
    exit();
}

function getDiscountedPrice($price, $discountPercent) {
    if (empty($price)) return 0;
    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

function fetchStripeCheckoutItems($con, $userId, $productId = null) {
    $items = [];
    $grandTotal = 0.0;

    if ($productId) {
        $query = mysqli_query($con, "SELECT * FROM products WHERE p_id = {$productId} LIMIT 1");
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $lineTotal = getDiscountedPrice($row['p_price'], $row['p_discount'] ?? 0);
            $items[] = [
                'p_id' => (int) $row['p_id'],
                'p_img' => $row['p_img'],
                'p_name' => $row['p_name'],
                'p_price' => (float) $row['p_price'],
                'p_size' => $row['p_size'],
                'buy_quantity' => 1,
                'line_total' => $lineTotal
            ];
            $grandTotal = $lineTotal;
        }
    } else {
        $query = mysqli_query($con, "
            SELECT product_cart.*, products.*
            FROM product_cart
            INNER JOIN products ON product_cart.p_id = products.p_id
            WHERE product_cart.id = {$userId}
        ");
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $lineTotal = isset($row['c_total']) ? (float) $row['c_total'] : ((float) $row['c_price'] * (int) $row['c_quantity']);
                $items[] = [
                    'p_id' => (int) $row['p_id'],
                    'p_img' => $row['p_img'],
                    'p_name' => $row['p_name'],
                    'p_price' => (float) $row['p_price'],
                    'p_size' => $row['p_size'],
                    'buy_quantity' => (int) $row['c_quantity'],
                    'line_total' => $lineTotal
                ];
                $grandTotal += $lineTotal;
            }
        }
    }

    return ['items' => $items, 'grand_total' => round($grandTotal, 2)];
}

function stripeItemsHaveStock($con, $items) {
    foreach ($items as $item) {
        $pId = (int) $item['p_id'];
        $qty = (int) $item['buy_quantity'];
        if ($qty <= 0) {
            return false;
        }
        $stockResult = mysqli_query($con, "SELECT p_quantity FROM products WHERE p_id = {$pId} LIMIT 1");
        if (!$stockResult || !($stockRow = mysqli_fetch_assoc($stockResult)) || (int) $stockRow['p_quantity'] < $qty) {
            return false;
        }
    }
    return true;
}

$checkoutData = fetchStripeCheckoutItems($con, $user_id, $product_id);
$items = $checkoutData['items'];
$grand_total = $checkoutData['grand_total'];

if (empty($items) || $grand_total <= 0) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Cart is empty.';
    header('Location: checkout.php');
    exit();
}

if (!stripeItemsHaveStock($con, $items)) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Insufficient stock for one or more products.';
    header('Location: checkout.php');
    exit();
}

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

$fullName = mysqli_real_escape_string($con, $delivery['full_name']);
$contactNumber = mysqli_real_escape_string($con, $delivery['contact_number']);
$address = mysqli_real_escape_string($con, $delivery['address']);
$city = mysqli_real_escape_string($con, $delivery['city']);
$state = mysqli_real_escape_string($con, $delivery['state']);
$postalCode = mysqli_real_escape_string($con, $delivery['postal_code']);
$intentSafe = mysqli_real_escape_string($con, $payment_intent_id);

mysqli_begin_transaction($con);

try {
    foreach ($items as $item) {
        $pId = (int) $item['p_id'];
        $buyQty = (int) $item['buy_quantity'];
        $lineTotal = (float) $item['line_total'];
        $unitPrice = (float) $item['p_price'];
        $img = mysqli_real_escape_string($con, $item['p_img']);
        $name = mysqli_real_escape_string($con, $item['p_name']);
        $size = mysqli_real_escape_string($con, $item['p_size']);

        $updateStock = mysqli_query($con, "UPDATE products SET p_quantity = p_quantity - {$buyQty} WHERE p_id = {$pId} AND p_quantity >= {$buyQty}");
        if (!$updateStock || mysqli_affected_rows($con) === 0) {
            throw new Exception('Stock update failed.');
        }

        $insertSale = mysqli_query($con, "
            INSERT INTO product_sales (id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
            VALUES ({$user_id}, '{$img}', '{$name}', {$unitPrice}, '{$size}', {$buyQty}, {$lineTotal}, {$grand_total}, '{$currentDate}', 'confirmed', '{$currentTime}')
        ");
        if (!$insertSale) {
            throw new Exception('Order creation failed.');
        }

        $saleId = (int) mysqli_insert_id($con);

        $insertPayment = mysqli_query($con, "
            INSERT INTO payment (id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status, stripe_payment_intent_id, stripe_payment_status)
            VALUES ({$user_id}, {$saleId}, '{$fullName}', '{$contactNumber}', '{$address}', '{$city}', '{$state}', '{$postalCode}', 'stripe', '{$currentDate}', '{$currentTime}', 'paid', '{$intentSafe}', 'succeeded')
        ");
        if (!$insertPayment) {
            throw new Exception('Payment record creation failed.');
        }

        mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$saleId}, 'confirmed', '{$currentDate}', '{$currentTime}')");
    }

    if ($product_id) {
        mysqli_query($con, "DELETE FROM product_cart WHERE id = {$user_id} AND p_id = {$product_id}");
    } else {
        mysqli_query($con, "DELETE FROM product_cart WHERE id = {$user_id}");
    }

    mysqli_commit($con);
    $_SESSION['toast-type'] = 'success';
    $_SESSION['toast-msg'] = 'Payment successful! Order placed.';
    header('Location:thankyou_order.php');
    exit();
} catch (Exception $e) {
    mysqli_rollback($con);
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Error processing payment. Please contact support.';
    header('Location: checkout.php');
    exit();
}
?>
