<?php
include 'connect.php';
require_once '../vendor/autoload.php';
session_start();

if (!isset($_POST['payment_intent_id'])) {
    header('Location: checkout.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_intent_id = mysqli_real_escape_string($con, $_POST['payment_intent_id']);
$product_id = isset($_POST['product_id']) && $_POST['product_id'] !== "" ? intval($_POST['product_id']) : null;

$fullName = mysqli_real_escape_string($con, $_POST['full-name']);
$contactNumber = mysqli_real_escape_string($con, $_POST['contact-number']);
$address = mysqli_real_escape_string($con, $_POST['address']);
$city = mysqli_real_escape_string($con, $_POST['city']);
$state = mysqli_real_escape_string($con, $_POST['state']);
$postalCode = mysqli_real_escape_string($con, $_POST['postal-code']);

$currentDate = date('Y-m-d');
$currentTime = date('H:i:s');

$pay_grand_total = 0;
$quantityUpdateQueries = [];

function getDiscountedPrice($price, $discountPercent) {
    if (empty($price)) return 0;
    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

if ($product_id) {
    $pay_product = mysqli_query($con, "SELECT * FROM products WHERE p_id = '$product_id'");
    $fetch_pay_product = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $fetch_pay_product ? getDiscountedPrice($fetch_pay_product['p_price'], $fetch_pay_product['p_discount'] ?? 0) : 0;
} else {
    $pay_product = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '$user_id'");
    $total_row = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $total_row && $total_row['grand_total'] ? $total_row['grand_total'] : 0;
}

if ($product_id) {
    if ($fetch_pay_product) {
        $discounted_unit_price = getDiscountedPrice($fetch_pay_product['p_price'], $fetch_pay_product['p_discount'] ?? 0);
        $insertSale = mysqli_query($con, "
            INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
            VALUES ('$user_id', '{$fetch_pay_product['p_img']}', '{$fetch_pay_product['p_name']}', '{$fetch_pay_product['p_price']}', '{$fetch_pay_product['p_size']}', 1, '{$discounted_unit_price}', '{$pay_grand_total}', '$currentDate', 'confirmed', '$currentTime')");

        if ($insertSale) {
            $s_id = mysqli_insert_id($con);
            $insertPayment = mysqli_query($con, "
                INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status, stripe_payment_intent_id, stripe_payment_status)
                VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'stripe', '$currentDate', '$currentTime', 'paid', '$payment_intent_id', 'succeeded')");

            if ($insertPayment) {
                mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ('$s_id', 'confirmed', '$currentDate', '$currentTime')");
                $new_quantity = $fetch_pay_product['p_quantity'] - 1;
                if ($new_quantity >= 0) {
                    mysqli_query($con, "UPDATE products SET p_quantity = '$new_quantity' WHERE p_id = '$product_id'");
                }
                mysqli_query($con, "DELETE FROM product_cart WHERE id='$user_id' AND p_id='$product_id'");
                $_SESSION['toast-type'] = 'success';
                $_SESSION['toast-msg'] = 'Payment successful! Order placed.';
                header('Location:thankyou_order.php');
                exit();
            }
        }
    }
} else {
    $all_products = mysqli_query($con, "
        SELECT product_cart.*, products.*
        FROM product_cart
        JOIN products ON product_cart.p_id = products.p_id
        WHERE product_cart.id = '$user_id'");

    $insertSaleSuccess = true;
    $s_ids = [];

    while ($p = mysqli_fetch_assoc($all_products)) {
        $insertSale = mysqli_query($con, "
            INSERT INTO product_sales(id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
            VALUES ('$user_id', '{$p['p_img']}', '{$p['p_name']}', '{$p['p_price']}', '{$p['p_size']}', '{$p['c_quantity']}', '{$p['c_total']}', '{$pay_grand_total}', '$currentDate', 'confirmed', '$currentTime')");

        if ($insertSale) {
            $s_ids[] = mysqli_insert_id($con);
            $new_quantity = $p['p_quantity'] - $p['c_quantity'];
            if ($new_quantity >= 0) {
                $quantityUpdateQueries[] = "UPDATE products SET p_quantity = '$new_quantity' WHERE p_id = '{$p['p_id']}'";
            }
        } else {
            $insertSaleSuccess = false;
        }
    }

    if ($insertSaleSuccess && !empty($s_ids)) {
        $s_id = $s_ids[0]; 
        
        $insertPayment = mysqli_query($con, "
            INSERT INTO payment(id, s_id, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_date, p_time, p_status, stripe_payment_intent_id, stripe_payment_status)
            VALUES ('$user_id', '$s_id', '$fullName', '$contactNumber', '$address', '$city', '$state', '$postalCode', 'stripe', '$currentDate', '$currentTime', 'paid', '$payment_intent_id', 'succeeded')");

        if ($insertPayment) {
            mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ('$s_id', 'confirmed', '$currentDate', '$currentTime')");
            foreach ($quantityUpdateQueries as $query) {
                mysqli_query($con, $query);
            }
            mysqli_query($con, "DELETE FROM product_cart WHERE id='$user_id'");
            $_SESSION['toast-type'] = 'success';
            $_SESSION['toast-msg'] = 'Payment successful! Order placed.';
            header('Location:thankyou_order.php');
            exit();
        }
    }
}

// If something fails
$_SESSION['toast-type'] = 'error';
$_SESSION['toast-msg'] = 'Error processing payment. Please contact support.';
header('Location: checkout.php');
exit();
?>
