<?php
include 'connect.php';
require_once 'wallet_helpers.php';
require_once '../stripe_config.php';
require_once '../notification_helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

function tableExists(mysqli $con, string $table): bool
{
    $table_safe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function getDiscountedPrice($price, $discountPercent)
{
    if (empty($price)) {
        return 0;
    }
    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

function fetchCheckoutItems($con, $userId, $productId = null, $comboId = null, $comboCartReady = false)
{
    $items = [];
    $grandTotal = 0.0;

    if ($productId) {
        $query = mysqli_query($con, "SELECT * FROM products WHERE p_id = {$productId} LIMIT 1");
        if ($query && $product = mysqli_fetch_assoc($query)) {
            $lineTotal = getDiscountedPrice($product['p_price'], $product['p_discount'] ?? 0);
            $items[] = [
                'item_type' => 'product',
                'p_id' => (int) $product['p_id'],
                'p_img' => $product['p_img'],
                'p_name' => $product['p_name'],
                'p_price' => $lineTotal,
                'p_size' => $product['p_size'],
                'buy_quantity' => 1,
                'available_quantity' => (int) $product['p_quantity'],
                'line_total' => $lineTotal,
                'meta' => '',
                'original_unit_total' => (float) $product['p_price'],
                'components' => []
            ];
            $grandTotal = $lineTotal;
        }
    } elseif ($comboId && $comboCartReady) {
        $combo_query = mysqli_query($con, "SELECT * FROM combos WHERE id = {$comboId} AND status = 1 LIMIT 1");
        if ($combo_query && $combo = mysqli_fetch_assoc($combo_query)) {
            $components = [];
            $parts = [];
            $originalUnitTotal = 0.0;

            $components_query = mysqli_query(
                $con,
                "SELECT cp.product_id, cp.quantity, p.p_name, p.p_price, p.p_discount, p.p_quantity
                 FROM combo_products cp
                 INNER JOIN products p ON p.p_id = cp.product_id
                 WHERE cp.combo_id = {$comboId}"
            );

            if ($components_query) {
                while ($component_row = mysqli_fetch_assoc($components_query)) {
                    $component_qty = (int) $component_row['quantity'];
                    $component_price = getDiscountedPrice((float) $component_row['p_price'], (float) ($component_row['p_discount'] ?? 0));
                    $originalUnitTotal += ($component_price * $component_qty);
                    $parts[] = $component_row['p_name'] . ' x' . $component_qty;
                    $components[] = [
                        'product_id' => (int) $component_row['product_id'],
                        'component_qty' => $component_qty,
                        'available_qty' => (int) $component_row['p_quantity']
                    ];
                }
            }

            $combo_price = (float) $combo['price'];
            $items[] = [
                'item_type' => 'combo',
                'combo_id' => (int) $combo['id'],
                'p_id' => 0,
                'p_img' => !empty($combo['image']) ? $combo['image'] : 'default.jpeg',
                'p_name' => $combo['name'],
                'p_price' => $combo_price,
                'p_size' => 'Combo Pack',
                'buy_quantity' => 1,
                'available_quantity' => 1,
                'line_total' => $combo_price,
                'meta' => implode(', ', $parts),
                'original_unit_total' => $originalUnitTotal,
                'components' => $components,
                'status' => (int) $combo['status']
            ];
            $grandTotal = $combo_price;
        }
    } else {
        $query = mysqli_query(
            $con,
            "SELECT product_cart.*, products.*
             FROM product_cart
             INNER JOIN products ON product_cart.p_id = products.p_id
             WHERE product_cart.id = {$userId}"
        );
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $lineTotal = isset($row['c_total']) ? (float) $row['c_total'] : ((float) $row['c_price'] * (int) $row['c_quantity']);
                $items[] = [
                    'item_type' => 'product',
                    'p_id' => (int) $row['p_id'],
                    'p_img' => $row['p_img'],
                    'p_name' => $row['p_name'],
                    'p_price' => (float) $row['c_price'],
                    'p_size' => $row['p_size'],
                    'buy_quantity' => (int) $row['c_quantity'],
                    'available_quantity' => (int) $row['p_quantity'],
                    'line_total' => $lineTotal,
                    'meta' => '',
                    'original_unit_total' => (float) $row['p_price'],
                    'components' => []
                ];
                $grandTotal += $lineTotal;
            }
        }
        if ($comboCartReady) {
            $combo_cart_rows = [];
            $combo_ids = [];
            $combo_cart_query = mysqli_query(
                $con,
                "SELECT cc.*, c.status
                 FROM combo_cart cc
                 LEFT JOIN combos c ON c.id = cc.combo_id
                 WHERE cc.id = {$userId}"
            );

            if ($combo_cart_query) {
                while ($combo_row = mysqli_fetch_assoc($combo_cart_query)) {
                    $combo_cart_rows[] = $combo_row;
                    $combo_ids[] = (int) $combo_row['combo_id'];
                }
            }

            $combo_components_map = [];
            if (!empty($combo_ids)) {
                $combo_id_sql = implode(',', array_unique($combo_ids));
                $components_query = mysqli_query(
                    $con,
                    "SELECT cp.combo_id, cp.product_id, cp.quantity, p.p_name, p.p_price, p.p_discount, p.p_quantity
                     FROM combo_products cp
                     INNER JOIN products p ON p.p_id = cp.product_id
                     WHERE cp.combo_id IN ({$combo_id_sql})
                     ORDER BY cp.combo_id ASC, p.p_name ASC"
                );

                if ($components_query) {
                    while ($row = mysqli_fetch_assoc($components_query)) {
                        $combo_id = (int) $row['combo_id'];
                        if (!isset($combo_components_map[$combo_id])) {
                            $combo_components_map[$combo_id] = [
                                'components' => [],
                                'parts' => [],
                                'original_unit_total' => 0.0
                            ];
                        }

                        $component_qty = (int) $row['quantity'];
                        $component_price = getDiscountedPrice((float) $row['p_price'], (float) ($row['p_discount'] ?? 0));
                        $combo_components_map[$combo_id]['original_unit_total'] += ($component_price * $component_qty);
                        $combo_components_map[$combo_id]['parts'][] = $row['p_name'] . ' x' . $component_qty;
                        $combo_components_map[$combo_id]['components'][] = [
                            'product_id' => (int) $row['product_id'],
                            'component_qty' => $component_qty,
                            'available_qty' => (int) $row['p_quantity']
                        ];
                    }
                }
            }

            foreach ($combo_cart_rows as $combo_row) {
                $combo_id = (int) $combo_row['combo_id'];
                $unit_price = (float) $combo_row['cc_price'];
                $qty = (int) $combo_row['cc_quantity'];
                $line_total = (float) $combo_row['cc_total'];
                if ($line_total <= 0) {
                    $line_total = $unit_price * $qty;
                }

                $meta_parts = isset($combo_components_map[$combo_id]) ? $combo_components_map[$combo_id]['parts'] : [];
                $components = isset($combo_components_map[$combo_id]) ? $combo_components_map[$combo_id]['components'] : [];
                $original_unit_total = isset($combo_components_map[$combo_id]) ? (float) $combo_components_map[$combo_id]['original_unit_total'] : $unit_price;

                $items[] = [
                    'item_type' => 'combo',
                    'combo_id' => $combo_id,
                    'p_id' => 0,
                    'p_img' => $combo_row['cc_img'] ?: 'default.jpeg',
                    'p_name' => $combo_row['cc_name'],
                    'p_price' => $unit_price,
                    'p_size' => 'Combo Pack',
                    'buy_quantity' => $qty,
                    'available_quantity' => 1,
                    'line_total' => $line_total,
                    'meta' => implode(', ', $meta_parts),
                    'original_unit_total' => $original_unit_total,
                    'components' => $components,
                    'status' => isset($combo_row['status']) ? (int) $combo_row['status'] : 1
                ];
                $grandTotal += $line_total;
            }
        }
    }

    return ['items' => $items, 'grand_total' => round($grandTotal, 2)];
}

function checkoutItemsHaveStock($items)
{
    foreach ($items as $item) {
        $buyQty = (int) $item['buy_quantity'];
        if ($buyQty <= 0) {
            return false;
        }

        if (($item['item_type'] ?? 'product') === 'combo') {
            if (isset($item['status']) && (int) $item['status'] !== 1) {
                return false;
            }
            if (empty($item['components'])) {
                return false;
            }
            foreach ($item['components'] as $component) {
                $required = (int) $component['component_qty'] * $buyQty;
                if ((int) $component['available_qty'] < $required) {
                    return false;
                }
            }
        } else {
            if ((int) $item['available_quantity'] < $buyQty) {
                return false;
            }
        }
    }
    return true;
}

function createOrderFromItems($con, $userId, $items, $grandTotal, $productId, $comboId, $comboCartReady, $delivery, $paymentMethod, $orderStatus, $paymentStatus, $stripeIntentId = null, $stripePaymentStatus = null)
{
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    $userId = (int) $userId;
    $grandTotal = (float) $grandTotal;

    $fullName = mysqli_real_escape_string($con, $delivery['full_name']);
    $contactNumber = mysqli_real_escape_string($con, $delivery['contact_number']);
    $address = mysqli_real_escape_string($con, $delivery['address']);
    $city = mysqli_real_escape_string($con, $delivery['city']);
    $state = mysqli_real_escape_string($con, $delivery['state']);
    $postalCode = mysqli_real_escape_string($con, $delivery['postal_code']);
    $paymentMethod = mysqli_real_escape_string($con, strtolower($paymentMethod));
    $paymentStatus = mysqli_real_escape_string($con, strtolower($paymentStatus));
    $orderStatus = mysqli_real_escape_string($con, strtolower($orderStatus));

    $stripeIntentValue = $stripeIntentId ? "'" . mysqli_real_escape_string($con, $stripeIntentId) . "'" : "NULL";
    $stripeStatusValue = $stripePaymentStatus ? "'" . mysqli_real_escape_string($con, $stripePaymentStatus) . "'" : "NULL";

    mysqli_begin_transaction($con);

    try {
        if ($paymentMethod === 'wallet') {
            $walletDebitOk = debitWalletBalance($con, $userId, $grandTotal, 'order_payment', null);
            if (!$walletDebitOk) {
                throw new Exception('Insufficient wallet balance.');
            }
        }

        $createdSaleIds = [];

        foreach ($items as $item) {
            $itemType = $item['item_type'] ?? 'product';
            $buyQty = (int) $item['buy_quantity'];
            $lineTotal = (float) $item['line_total'];
            $unitPrice = (float) $item['p_price'];
            $pImg = mysqli_real_escape_string($con, $item['p_img']);
            $pName = mysqli_real_escape_string($con, $item['p_name']);
            $pSize = mysqli_real_escape_string($con, $item['p_size']);

            if ($itemType === 'combo') {
                foreach ($item['components'] as $component) {
                    $componentProductId = (int) $component['product_id'];
                    $required = (int) $component['component_qty'] * $buyQty;
                    $updateStock = mysqli_query($con, "UPDATE products SET p_quantity = p_quantity - {$required} WHERE p_id = {$componentProductId} AND p_quantity >= {$required}");
                    if (!$updateStock || mysqli_affected_rows($con) === 0) {
                        throw new Exception('Insufficient stock while placing combo order.');
                    }
                }
            } else {
                $pId = (int) $item['p_id'];
                $updateStock = mysqli_query($con, "UPDATE products SET p_quantity = p_quantity - {$buyQty} WHERE p_id = {$pId} AND p_quantity >= {$buyQty}");
                if (!$updateStock || mysqli_affected_rows($con) === 0) {
                    throw new Exception('Insufficient stock while placing order.');
                }
            }

            $insertSale = mysqli_query(
                $con,
                "INSERT INTO product_sales (id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
                 VALUES ({$userId}, '{$pImg}', '{$pName}', {$unitPrice}, '{$pSize}', {$buyQty}, {$lineTotal}, {$grandTotal}, '{$currentDate}', '{$orderStatus}', '{$currentTime}')"
            );
            if (!$insertSale) {
                throw new Exception('Failed to create order record.');
            }

            $saleId = (int) mysqli_insert_id($con);
            $createdSaleIds[] = $saleId;

            $insertPayment = mysqli_query(
                $con,
                "INSERT INTO payment (id, s_id, m_id, payment_for, payment_note, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_amount, p_date, p_time, p_status, stripe_payment_intent_id, stripe_payment_status)
                 VALUES ({$userId}, {$saleId}, NULL, 'product', 'Product order #{$saleId}', '{$fullName}', '{$contactNumber}', '{$address}', '{$city}', '{$state}', '{$postalCode}', '{$paymentMethod}', {$grandTotal}, '{$currentDate}', '{$currentTime}', '{$paymentStatus}', {$stripeIntentValue}, {$stripeStatusValue})"
            );
            if (!$insertPayment) {
                throw new Exception('Failed to create payment record.');
            }

            mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$saleId}, '{$orderStatus}', '{$currentDate}', '{$currentTime}')");
        }

        if ($paymentMethod === 'wallet' && !empty($createdSaleIds)) {
            $firstSaleId = (int) $createdSaleIds[0];
            mysqli_query($con, "UPDATE wallet_transactions SET order_id = {$firstSaleId}, sale_id = {$firstSaleId} WHERE user_id = {$userId} AND type = 'debit' AND source = 'order_payment' AND order_id IS NULL ORDER BY id DESC LIMIT 1");
        }

        if ($productId) {
            mysqli_query($con, "DELETE FROM product_cart WHERE id = {$userId} AND p_id = " . (int) $productId);
        } elseif ($comboId && $comboCartReady) {
            mysqli_query($con, "DELETE FROM combo_cart WHERE id = {$userId} AND combo_id = " . (int) $comboId);
        } else {
            mysqli_query($con, "DELETE FROM product_cart WHERE id = {$userId}");
            if ($comboCartReady) {
                mysqli_query($con, "DELETE FROM combo_cart WHERE id = {$userId}");
            }
        }

        mysqli_commit($con);

        if (!empty($createdSaleIds)) {
            $firstSaleId = (int) $createdSaleIds[0];
            $itemCount = 0;
            foreach ($items as $item) {
                $itemCount += (int) ($item['buy_quantity'] ?? 0);
            }
            $amountLabel = number_format($grandTotal, 2);
            notificationCreateForUser(
                $con,
                $userId,
                'order_placed',
                'Order Placed Successfully',
                "Your order #{$firstSaleId} for {$itemCount} item(s) worth ₹{$amountLabel} was placed.",
                'user/order.php',
                'user',
                $userId,
                'order',
                $firstSaleId
            );
            notificationCreateForAllAdmins(
                $con,
                'order_placed',
                'New Product Order',
                "User #{$userId} placed order #{$firstSaleId} for ₹{$amountLabel}.",
                'admin/manage_orders.php',
                'user',
                $userId,
                'order',
                $firstSaleId
            );
        }

        return ['success' => true, 'message' => 'Order placed successfully'];
    } catch (Exception $e) {
        mysqli_rollback($con);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

$user_id = (int) $_SESSION['user_id'];
$product_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$combo_id = isset($_GET['combo_id']) ? (int) $_GET['combo_id'] : null;
if ($product_id && $combo_id) {
    $combo_id = null;
}
$combo_cart_ready = tableExists($con, 'combo_cart') && tableExists($con, 'combos') && tableExists($con, 'combo_products');
$error_message = '';

$checkoutData = fetchCheckoutItems($con, $user_id, $product_id, $combo_id, $combo_cart_ready);
$checkout_items = $checkoutData['items'];
$pay_grand_total = $checkoutData['grand_total'];

if (isset($_POST['cod-btn']) || isset($_POST['wallet-btn'])) {
    $delivery = [
        'full_name' => trim($_POST['full-name'] ?? ''),
        'contact_number' => trim($_POST['contact-number'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'state' => trim($_POST['state'] ?? ''),
        'postal_code' => trim($_POST['postal-code'] ?? '')
    ];

    if (in_array('', $delivery, true)) {
        $error_message = 'Please fill all delivery details.';
    } elseif (empty($checkout_items) || $pay_grand_total <= 0) {
        $error_message = 'Your cart is empty.';
    } elseif (!checkoutItemsHaveStock($checkout_items)) {
        $error_message = 'Insufficient stock for one or more items.';
    } else {
        if (isset($_POST['wallet-btn'])) {
            $walletBalance = getUserWalletBalance($con, $user_id);
            if ($walletBalance < $pay_grand_total) {
                $error_message = 'Wallet balance is not enough for this order.';
            } else {
                $result = createOrderFromItems($con, $user_id, $checkout_items, $pay_grand_total, $product_id, $combo_id, $combo_cart_ready, $delivery, 'wallet', 'confirmed', 'paid');
                if ($result['success']) {
                    $_SESSION['toast-type'] = 'success';
                    $_SESSION['toast-msg'] = 'Order placed successfully!';
                    header('Location:thankyou_order.php');
                    exit();
                }
                $error_message = $result['message'];
            }
        } else {
            $result = createOrderFromItems($con, $user_id, $checkout_items, $pay_grand_total, $product_id, $combo_id, $combo_cart_ready, $delivery, 'cod', 'pending', 'pending');
            if ($result['success']) {
                $_SESSION['toast-type'] = 'success';
                $_SESSION['toast-msg'] = 'Order placed successfully!';
                header('Location:thankyou_order.php');
                exit();
            }
            $error_message = $result['message'];
        }
    }
}

$wallet_balance = getUserWalletBalance($con, $user_id);
$wallet_shortfall = max(0, $pay_grand_total - $wallet_balance);
$wallet_can_pay = $wallet_balance >= $pay_grand_total && $pay_grand_total > 0;
$stripe_is_available = STRIPE_ENABLED && STRIPE_PUBLISHABLE_KEY !== '' && STRIPE_SECRET_KEY !== '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        #global-toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 100000; display: flex; flex-direction: column; gap: 10px; }
        .global-toast { min-width: 250px; background: #333; color: #fff; padding: 15px 20px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 12px; font-size: 15px; font-weight: 500; transform: translateX(120%); transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); }
        .global-toast.show { transform: translateX(0); }
        .toast-success { background: #10b981; border-left: 5px solid #059669; }
        .toast-error { background: #ef4444; border-left: 5px solid #b91c1c; }
    </style>
</head>
<body class="checkout-page">
    <div class="payment-container">
        <header class="checkout-header">
            <h1>Secure Checkout</h1>
            <p>Complete your delivery details and choose payment method to place your order.</p>
        </header>

        <?php if (!empty($error_message)): ?>
            <div style="background:#fce8e6;color:#d93025;padding:12px 16px;border-radius:8px;margin-bottom:20px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="checkout-grid">
            <section class="checkout-left">
                <div class="checkout-card">
                    <h2>Delivery Details</h2>
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="full-name">Full Name</label>
                            <input type="text" id="full-name" name="full-name" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-field">
                            <label for="contact-number">Contact Number</label>
                            <input type="tel" id="contact-number" name="contact-number" placeholder="Enter your contact number" inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15" required>
                        </div>
                        <div class="form-field full">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" placeholder="Enter your delivery address" required></textarea>
                        </div>
                        <div class="form-field">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" placeholder="Enter your city" required>
                        </div>
                        <div class="form-field">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" placeholder="Enter your state" required>
                        </div>
                        <div class="form-field">
                            <label for="postal-code">Pin Code</label>
                            <input type="text" id="postal-code" name="postal-code" placeholder="Enter postal code" inputmode="numeric" pattern="[0-9]{4,10}" maxlength="10" required>
                        </div>
                    </div>
                </div>

                <div class="checkout-card payment-options">
                    <h2>Payment Method</h2>
                    <div class="payment-selection">
                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="stripe" onclick="showPaymentOption('stripe')" <?php echo $stripe_is_available ? '' : 'disabled'; ?>> Stripe
                        </label>
                        <div class="payment-method" id="stripe">
                            <h3>Pay with Stripe</h3>
                            <?php if ($stripe_is_available): ?>
                                <p>Secure payment using Stripe.</p>
                                <div id="card-element"></div>
                                <div id="card-errors" role="alert"></div>
                                <button type="button" id="stripe-submit-btn" class="payments_buttons">Pay Now with Stripe</button>
                            <?php else: ?>
                                <p style="color:#d93025;">Stripe is not available right now. Please choose Wallet or COD, or contact admin.</p>
                            <?php endif; ?>
                        </div>
                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="wallet" onclick="showPaymentOption('wallet')"> Wallet
                        </label>
                        <div class="payment-method" id="wallet">
                            <h3>Pay with Wallet</h3>
                            <p>Available Wallet Balance: <strong>₹ <?php echo number_format($wallet_balance, 2); ?></strong></p>
                            <?php if (!$wallet_can_pay): ?>
                                <p style="color:#d93025;">Add ₹ <?php echo number_format($wallet_shortfall, 2); ?> more to use wallet payment.</p>
                            <?php endif; ?>
                            <button type="submit" name="wallet-btn" class="payments_buttons" <?php echo $wallet_can_pay ? '' : 'disabled'; ?>>
                                Pay with Wallet
                            </button>
                        </div>
                        <label class="payment-choice">
                            <input type="radio" name="payment-method" value="cod" onclick="showPaymentOption('cod')"> Cash on Delivery (COD)
                        </label>
                        <div class="payment-method" id="cod">
                            <h3>Cash on Delivery (COD)</h3>
                            <button type="submit" name="cod-btn" class="payments_buttons">Confirm Order</button>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="checkout-right">
                <div class="checkout-card">
                    <h2>Order Summary</h2>
                    <div class="order-list">
                        <?php if (!empty($checkout_items)): ?>
                            <?php foreach ($checkout_items as $item): ?>
                                <?php
                                    $originalUnit = isset($item['original_unit_total']) ? (float) $item['original_unit_total'] : (float) $item['p_price'];
                                    $originalTotal = $originalUnit * (int) $item['buy_quantity'];
                                    $finalTotal = (float) $item['line_total'];
                                    $discountAmount = max(0, $originalTotal - $finalTotal);
                                    $isCombo = ($item['item_type'] ?? 'product') === 'combo';
                                ?>
                                <div class="payment-product-item">
                                    <img src="../upload_product_photos/<?php echo htmlspecialchars($item['p_img']); ?>" alt="Product Image">
                                    <div class="payment-product-info">
                                        <h3>
                                            <?php echo htmlspecialchars($item['p_name']); ?>
                                            <?php if ($isCombo): ?>
                                                <span style="font-size:11px;margin-left:6px;padding:2px 8px;border-radius:999px;background:#eef2ff;color:#3730a3;">COMBO</span>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if ($discountAmount > 0): ?>
                                            <p>Original Price: <span class="price-original">₹ <?php echo number_format($originalTotal, 2); ?></span></p>
                                            <p>Discount: -₹ <?php echo number_format($discountAmount, 2); ?></p>
                                        <?php endif; ?>
                                        <p>Size: <?php echo htmlspecialchars($item['p_size']); ?></p>
                                        <?php if ($isCombo && !empty($item['meta'])): ?>
                                            <p>Includes: <?php echo htmlspecialchars($item['meta']); ?></p>
                                        <?php endif; ?>
                                        <p>Quantity: <?php echo (int) $item['buy_quantity']; ?></p>
                                        <p>Total: ₹ <?php echo number_format($finalTotal, 2); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-order">Your cart is empty.</p>
                        <?php endif; ?>
                    </div>
                    <div class="payment-total">
                        <span>Total Amount</span>
                        <strong>₹ <?php echo number_format((float) $pay_grand_total, 2); ?></strong>
                    </div>
                </div>
            </aside>
        </form>
    </div>

    <div id="global-toast-container"></div>
    <script>
        const directProductId = '<?php echo $product_id ?: ''; ?>';
        const directComboId = '<?php echo $combo_id ?: ''; ?>';
        const stripeEnabled = <?php echo $stripe_is_available ? 'true' : 'false'; ?>;
        const stripePublishableKey = '<?php echo addslashes(STRIPE_PUBLISHABLE_KEY); ?>';

        function showToast(message, type = 'success') {
            const container = document.getElementById('global-toast-container');
            const toast = document.createElement('div');
            toast.className = `global-toast toast-${type}`;
            let icon = type === 'success' ? 'fa-check-circle' : 'fa-times-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 400); }, 3500);
        }

        <?php if (isset($_SESSION['toast-msg'])): ?>
            showToast("<?php echo addslashes($_SESSION['toast-msg']); ?>", "<?php echo $_SESSION['toast-type'] ?? 'success'; ?>");
            <?php unset($_SESSION['toast-msg'], $_SESSION['toast-type']); ?>
        <?php endif; ?>

        let stripe = null;
        let elements = null;
        let card = null;

        if (stripeEnabled && stripePublishableKey) {
            stripe = Stripe(stripePublishableKey);
            elements = stripe.elements();
            const rootStyles = getComputedStyle(document.documentElement);
            const brandColor = rootStyles.getPropertyValue('--brand').trim() || '#cbb90f';
            const bgColor = rootStyles.getPropertyValue('--bg1').trim() || '#18150d';

            const style = {
                base: {
                    color: bgColor,
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': { color: brandColor }
                },
                invalid: { color: '#fa755a', iconColor: '#fa755a' }
            };

            card = elements.create('card', {style: style});
            card.mount('#card-element');

            card.on('change', function(event) {
                const errorBox = document.getElementById('card-errors');
                if (errorBox) {
                    errorBox.textContent = event.error ? event.error.message : '';
                }
            });
        }

        function hideAllPaymentOptions() {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
        }

        function showPaymentOption(option) {
            hideAllPaymentOptions();
            const selectedOption = document.getElementById(option);
            if (selectedOption) {
                selectedOption.classList.add('active');
            }
        }

        const stripeSubmitBtn = document.getElementById('stripe-submit-btn');
        if (stripeSubmitBtn) stripeSubmitBtn.addEventListener('click', async function() {
            const btn = this;
            if (!stripeEnabled || !stripe || !card) {
                showToast('Stripe is not connected right now.', 'error');
                return;
            }

            const fullName = document.getElementById('full-name').value;
            const contactNumber = document.getElementById('contact-number').value;
            const address = document.getElementById('address').value;
            const city = document.getElementById('city').value;
            const state = document.getElementById('state').value;
            const postalCode = document.getElementById('postal-code').value;
            const normalizedContact = (contactNumber || '').replace(/\D+/g, '');
            const normalizedPostal = (postalCode || '').replace(/\D+/g, '');

            if (!fullName || !contactNumber || !address || !city || !state || !postalCode) {
                showToast('Please fill in all details.', 'error');
                return;
            }
            if (normalizedContact.length < 10) {
                showToast('Please enter a valid contact number.', 'error');
                return;
            }
            if (normalizedPostal.length < 4) {
                showToast('Please enter a valid pin code.', 'error');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Processing...';

            try {
                const payload = new URLSearchParams();
                payload.set('id', directProductId);
                payload.set('combo_id', directComboId);

                const response = await fetch('create_payment_intent.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: payload.toString()
                });
                const rawResponse = await response.text();
                let data = {};
                try {
                    data = JSON.parse(rawResponse);
                } catch (jsonError) {
                    data = { error: 'Invalid response from payment server. Please try again.' };
                }

                if (data.error) {
                    showToast(data.error, 'error');
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now with Stripe';
                    return;
                }

                const { paymentIntent, error } = await stripe.confirmCardPayment(data.client_secret, {
                    payment_method: { card: card, billing_details: { name: fullName } }
                });

                if (error) {
                    document.getElementById('card-errors').textContent = error.message;
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now with Stripe';
                    return;
                }

                if (paymentIntent.status === 'succeeded') {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'handle_stripe_payment.php';
                    const fields = {
                        'payment_intent_id': paymentIntent.id,
                        'full-name': fullName,
                        'contact-number': normalizedContact,
                        'address': address,
                        'city': city,
                        'state': state,
                        'postal-code': normalizedPostal,
                        'product_id': directProductId,
                        'combo_id': directComboId
                    };
                    for (const key in fields) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = fields[key];
                        form.appendChild(input);
                    }
                    document.body.appendChild(form);
                    form.submit();
                }
            } catch (error) {
                console.error(error);
                showToast('An error occurred. Please try again.', 'error');
                btn.disabled = false;
                btn.innerHTML = 'Pay Now with Stripe';
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const defaultOption = document.querySelector('input[name="payment-method"][value="stripe"]');
            const walletOption = document.querySelector('input[name="payment-method"][value="wallet"]');
            const codOption = document.querySelector('input[name="payment-method"][value="cod"]');

            if (stripeEnabled && defaultOption && !defaultOption.disabled) {
                defaultOption.checked = true;
                showPaymentOption('stripe');
            } else if (walletOption) {
                walletOption.checked = true;
                showPaymentOption('wallet');
            } else if (codOption) {
                codOption.checked = true;
                showPaymentOption('cod');
            }
        });
    </script>
</body>
</html>
