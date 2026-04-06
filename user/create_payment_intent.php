<?php
require_once '../connect.php';
require_once '../stripe_config.php';
require_once '../vendor/autoload.php';
session_start();
header('Content-Type: application/json');

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$product_id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$combo_id = isset($_POST['combo_id']) ? (int) $_POST['combo_id'] : null;
if ($product_id && $combo_id) {
    $combo_id = null;
}

$pay_grand_total = 0;

function paymentTableExists(mysqli $con, string $table): bool {
    $table_safe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$table_safe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function getDiscountedPrice($price, $discountPercent) {
    if (empty($price)) return 0;
    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

if ($product_id) {
    $pay_product = mysqli_query($con, "SELECT p_price, p_discount FROM products WHERE p_id = '$product_id'");
    $fetch_pay_product = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $fetch_pay_product ? getDiscountedPrice($fetch_pay_product['p_price'], $fetch_pay_product['p_discount'] ?? 0) : 0;
} elseif ($combo_id && paymentTableExists($con, 'combos')) {
    $pay_combo = mysqli_query($con, "SELECT price FROM combos WHERE id = {$combo_id} AND status = 1 LIMIT 1");
    $fetch_combo = $pay_combo ? mysqli_fetch_assoc($pay_combo) : null;
    $pay_grand_total = $fetch_combo ? (float) $fetch_combo['price'] : 0;
} else {
    $pay_product = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = {$user_id}");
    $total_row = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $total_row && $total_row['grand_total'] ? $total_row['grand_total'] : 0;

    $combo_cart_ready = paymentTableExists($con, 'combo_cart');
    if ($combo_cart_ready) {
        $pay_combo_cart = mysqli_query($con, "SELECT SUM(cc_total) AS combo_total FROM combo_cart WHERE id = {$user_id}");
        $combo_row = $pay_combo_cart ? mysqli_fetch_assoc($pay_combo_cart) : null;
        $pay_grand_total += ($combo_row && $combo_row['combo_total']) ? (float) $combo_row['combo_total'] : 0;
    }
}

// Amount in cents (₹ 1 = 100 cents)
$amount_in_cents = (int) round(((float) $pay_grand_total) * 100);

if ($amount_in_cents <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

if (!STRIPE_ENABLED || STRIPE_PUBLISHABLE_KEY === '' || STRIPE_SECRET_KEY === '') {
    echo json_encode(['error' => 'Stripe is not connected right now. Please contact admin.']);
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount_in_cents,
        'currency' => 'inr',
        'payment_method_types' => ['card'],
        'description' => 'Payment for order from Mens Hair Salon',
        'metadata' => [
            'checkout_type' => 'product_order',
            'user_id' => (string) $user_id,
            'stripe_mode' => STRIPE_ACTIVE_MODE,
        ],
    ]);

    echo json_encode([
        'client_secret' => $paymentIntent->client_secret,
        'amount' => $amount_in_cents,
        'mode' => STRIPE_ACTIVE_MODE,
    ]);
} catch (\Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
