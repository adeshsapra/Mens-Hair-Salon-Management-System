<?php
include 'connect.php';
require_once '../stripe_config.php';
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function stripeTableExists(mysqli $con, string $table): bool
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

function buildCheckoutRedirect($productId, $comboId): string
{
    if ($productId) {
        return 'checkout.php?id=' . (int) $productId;
    }
    if ($comboId) {
        return 'checkout.php?combo_id=' . (int) $comboId;
    }
    return 'checkout.php';
}

function fetchStripeCheckoutItems($con, $userId, $productId = null, $comboId = null, $comboCartReady = false)
{
    $items = [];
    $grandTotal = 0.0;

    if ($productId) {
        $query = mysqli_query($con, "SELECT * FROM products WHERE p_id = {$productId} LIMIT 1");
        if ($query && $row = mysqli_fetch_assoc($query)) {
            $lineTotal = getDiscountedPrice($row['p_price'], $row['p_discount'] ?? 0);
            $items[] = [
                'item_type' => 'product',
                'p_id' => (int) $row['p_id'],
                'p_img' => $row['p_img'],
                'p_name' => $row['p_name'],
                'p_price' => $lineTotal,
                'p_size' => $row['p_size'],
                'buy_quantity' => 1,
                'line_total' => $lineTotal,
                'components' => []
            ];
            $grandTotal = $lineTotal;
        }
    } elseif ($comboId && $comboCartReady) {
        $combo_query = mysqli_query($con, "SELECT * FROM combos WHERE id = {$comboId} AND status = 1 LIMIT 1");
        if ($combo_query && $combo = mysqli_fetch_assoc($combo_query)) {
            $components = [];
            $parts = [];
            $components_query = mysqli_query(
                $con,
                "SELECT cp.product_id, cp.quantity, p.p_name, p.p_quantity
                 FROM combo_products cp
                 INNER JOIN products p ON p.p_id = cp.product_id
                 WHERE cp.combo_id = {$comboId}"
            );

            if ($components_query) {
                while ($component_row = mysqli_fetch_assoc($components_query)) {
                    $qty = (int) $component_row['quantity'];
                    $parts[] = $component_row['p_name'] . ' x' . $qty;
                    $components[] = [
                        'product_id' => (int) $component_row['product_id'],
                        'component_qty' => $qty,
                        'available_qty' => (int) $component_row['p_quantity']
                    ];
                }
            }

            $comboPrice = (float) $combo['price'];
            $items[] = [
                'item_type' => 'combo',
                'combo_id' => (int) $combo['id'],
                'p_id' => 0,
                'p_img' => !empty($combo['image']) ? $combo['image'] : 'default.jpeg',
                'p_name' => $combo['name'],
                'p_price' => $comboPrice,
                'p_size' => 'Combo Pack',
                'buy_quantity' => 1,
                'line_total' => $comboPrice,
                'components' => $components,
                'meta' => implode(', ', $parts)
            ];
            $grandTotal = $comboPrice;
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
                    'line_total' => $lineTotal,
                    'components' => []
                ];
                $grandTotal += $lineTotal;
            }
        }

        if ($comboCartReady) {
            $combo_rows = [];
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
                    if (isset($combo_row['status']) && (int) $combo_row['status'] !== 1) {
                        continue;
                    }
                    $combo_rows[] = $combo_row;
                    $combo_ids[] = (int) $combo_row['combo_id'];
                }
            }

            $components_map = [];
            if (!empty($combo_ids)) {
                $combo_sql = implode(',', array_unique($combo_ids));
                $components_query = mysqli_query(
                    $con,
                    "SELECT cp.combo_id, cp.product_id, cp.quantity, p.p_name, p.p_quantity
                     FROM combo_products cp
                     INNER JOIN products p ON p.p_id = cp.product_id
                     WHERE cp.combo_id IN ({$combo_sql})"
                );
                if ($components_query) {
                    while ($component_row = mysqli_fetch_assoc($components_query)) {
                        $combo_id = (int) $component_row['combo_id'];
                        if (!isset($components_map[$combo_id])) {
                            $components_map[$combo_id] = [];
                        }
                        $components_map[$combo_id][] = [
                            'product_id' => (int) $component_row['product_id'],
                            'component_qty' => (int) $component_row['quantity'],
                            'available_qty' => (int) $component_row['p_quantity'],
                            'name' => $component_row['p_name']
                        ];
                    }
                }
            }

            foreach ($combo_rows as $combo_row) {
                $combo_id = (int) $combo_row['combo_id'];
                $qty = (int) $combo_row['cc_quantity'];
                $line_total = (float) $combo_row['cc_total'];
                if ($line_total <= 0) {
                    $line_total = ((float) $combo_row['cc_price']) * $qty;
                }

                $components = isset($components_map[$combo_id]) ? $components_map[$combo_id] : [];
                $meta_parts = [];
                foreach ($components as $component) {
                    $meta_parts[] = $component['name'] . ' x' . (int) $component['component_qty'];
                }

                $items[] = [
                    'item_type' => 'combo',
                    'combo_id' => $combo_id,
                    'p_id' => 0,
                    'p_img' => $combo_row['cc_img'] ?: 'default.jpeg',
                    'p_name' => $combo_row['cc_name'],
                    'p_price' => (float) $combo_row['cc_price'],
                    'p_size' => 'Combo Pack',
                    'buy_quantity' => $qty,
                    'line_total' => $line_total,
                    'components' => $components,
                    'meta' => implode(', ', $meta_parts)
                ];
                $grandTotal += $line_total;
            }
        }
    }

    return ['items' => $items, 'grand_total' => round($grandTotal, 2)];
}

function stripeItemsHaveStock($con, $items)
{
    foreach ($items as $item) {
        $qty = (int) $item['buy_quantity'];
        if ($qty <= 0) {
            return false;
        }
        if (($item['item_type'] ?? 'product') === 'combo') {
            if (empty($item['components'])) {
                return false;
            }
            foreach ($item['components'] as $component) {
                $required = (int) $component['component_qty'] * $qty;
                if ((int) $component['available_qty'] < $required) {
                    return false;
                }
            }
        } else {
            $stockResult = mysqli_query($con, "SELECT p_quantity FROM products WHERE p_id = " . (int) $item['p_id'] . " LIMIT 1");
            if (!$stockResult || !($stockRow = mysqli_fetch_assoc($stockResult)) || (int) $stockRow['p_quantity'] < $qty) {
                return false;
            }
        }
    }
    return true;
}

if (!isset($_SESSION['user_id']) || !isset($_POST['payment_intent_id'])) {
    header('Location: checkout.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) && $_POST['product_id'] !== '' ? (int) $_POST['product_id'] : null;
$combo_id = isset($_POST['combo_id']) && $_POST['combo_id'] !== '' ? (int) $_POST['combo_id'] : null;
if ($product_id && $combo_id) {
    $combo_id = null;
}

$payment_intent_id = trim((string) $_POST['payment_intent_id']);
$delivery = [
    'full_name' => trim($_POST['full-name'] ?? ''),
    'contact_number' => trim($_POST['contact-number'] ?? ''),
    'address' => trim($_POST['address'] ?? ''),
    'city' => trim($_POST['city'] ?? ''),
    'state' => trim($_POST['state'] ?? ''),
    'postal_code' => trim($_POST['postal-code'] ?? '')
];

$combo_cart_ready = stripeTableExists($con, 'combo_cart') && stripeTableExists($con, 'combos') && stripeTableExists($con, 'combo_products');
$redirect_url = buildCheckoutRedirect($product_id, $combo_id);

if ($payment_intent_id === '' || in_array('', $delivery, true)) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Invalid payment data.';
    header('Location: ' . $redirect_url);
    exit();
}

$checkoutData = fetchStripeCheckoutItems($con, $user_id, $product_id, $combo_id, $combo_cart_ready);
$items = $checkoutData['items'];
$grand_total = $checkoutData['grand_total'];

if (empty($items) || $grand_total <= 0) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Cart is empty.';
    header('Location: ' . $redirect_url);
    exit();
}

if (!stripeItemsHaveStock($con, $items)) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Insufficient stock for one or more items.';
    header('Location: ' . $redirect_url);
    exit();
}

if (!STRIPE_ENABLED || STRIPE_SECRET_KEY === '' || STRIPE_PUBLISHABLE_KEY === '') {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Stripe account is not connected. Please contact admin.';
    header('Location: ' . $redirect_url);
    exit();
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $stripeIntent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
} catch (\Exception $e) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Unable to verify Stripe payment.';
    header('Location: ' . $redirect_url);
    exit();
}

if (!$stripeIntent || $stripeIntent->status !== 'succeeded') {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Stripe payment is not successful yet.';
    header('Location: ' . $redirect_url);
    exit();
}

$expectedAmount = (int) round(((float) $grand_total) * 100);
if ((int) $stripeIntent->amount !== $expectedAmount) {
    $_SESSION['toast-type'] = 'error';
    $_SESSION['toast-msg'] = 'Stripe payment amount mismatch. Please try again.';
    header('Location: ' . $redirect_url);
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
$intentSafe = mysqli_real_escape_string($con, (string) $stripeIntent->id);
$intentStatusSafe = mysqli_real_escape_string($con, (string) $stripeIntent->status);

mysqli_begin_transaction($con);

try {
    foreach ($items as $item) {
        $itemType = $item['item_type'] ?? 'product';
        $buyQty = (int) $item['buy_quantity'];
        $lineTotal = (float) $item['line_total'];
        $unitPrice = (float) $item['p_price'];
        $img = mysqli_real_escape_string($con, $item['p_img']);
        $name = mysqli_real_escape_string($con, $item['p_name']);
        $size = mysqli_real_escape_string($con, $item['p_size']);

        if ($itemType === 'combo') {
            foreach ($item['components'] as $component) {
                $componentProductId = (int) $component['product_id'];
                $required = (int) $component['component_qty'] * $buyQty;
                $updateStock = mysqli_query($con, "UPDATE products SET p_quantity = p_quantity - {$required} WHERE p_id = {$componentProductId} AND p_quantity >= {$required}");
                if (!$updateStock || mysqli_affected_rows($con) === 0) {
                    throw new Exception('Stock update failed for combo.');
                }
            }
        } else {
            $pId = (int) $item['p_id'];
            $updateStock = mysqli_query($con, "UPDATE products SET p_quantity = p_quantity - {$buyQty} WHERE p_id = {$pId} AND p_quantity >= {$buyQty}");
            if (!$updateStock || mysqli_affected_rows($con) === 0) {
                throw new Exception('Stock update failed.');
            }
        }

        $insertSale = mysqli_query(
            $con,
            "INSERT INTO product_sales (id, s_img, s_name, s_price, s_size, s_quantity, s_total, s_grand_total, s_date, s_status, s_time)
             VALUES ({$user_id}, '{$img}', '{$name}', {$unitPrice}, '{$size}', {$buyQty}, {$lineTotal}, {$grand_total}, '{$currentDate}', 'confirmed', '{$currentTime}')"
        );
        if (!$insertSale) {
            throw new Exception('Order creation failed.');
        }

        $saleId = (int) mysqli_insert_id($con);

        $insertPayment = mysqli_query(
            $con,
            "INSERT INTO payment (id, s_id, m_id, payment_for, payment_note, p_name, p_phno, p_address, p_city, p_state, p_pincode, p_method, p_amount, p_date, p_time, p_status, stripe_payment_intent_id, stripe_payment_status)
             VALUES ({$user_id}, {$saleId}, NULL, 'product', 'Product order #{$saleId}', '{$fullName}', '{$contactNumber}', '{$address}', '{$city}', '{$state}', '{$postalCode}', 'stripe', {$grand_total}, '{$currentDate}', '{$currentTime}', 'paid', '{$intentSafe}', '{$intentStatusSafe}')"
        );
        if (!$insertPayment) {
            throw new Exception('Payment record creation failed.');
        }

        mysqli_query($con, "INSERT INTO order_status_updates (s_id, status, update_date, update_time) VALUES ({$saleId}, 'confirmed', '{$currentDate}', '{$currentTime}')");
    }

    if ($product_id) {
        mysqli_query($con, "DELETE FROM product_cart WHERE id = {$user_id} AND p_id = {$product_id}");
    } elseif ($combo_id && $combo_cart_ready) {
        mysqli_query($con, "DELETE FROM combo_cart WHERE id = {$user_id} AND combo_id = {$combo_id}");
    } else {
        mysqli_query($con, "DELETE FROM product_cart WHERE id = {$user_id}");
        if ($combo_cart_ready) {
            mysqli_query($con, "DELETE FROM combo_cart WHERE id = {$user_id}");
        }
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
    header('Location: ' . $redirect_url);
    exit();
}
