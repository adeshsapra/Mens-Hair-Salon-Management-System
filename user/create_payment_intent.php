<?php
require_once '../connect.php';
require_once '../stripe_config.php';
require_once '../vendor/autoload.php';
session_start();

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['id']) ? intval($_POST['id']) : null;
$pay_grand_total = 0;

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
} else {
    $pay_product = mysqli_query($con, "SELECT SUM(c_total) AS grand_total FROM product_cart WHERE id = '$user_id'");
    $total_row = mysqli_fetch_assoc($pay_product);
    $pay_grand_total = $total_row && $total_row['grand_total'] ? $total_row['grand_total'] : 0;
}

// Amount in cents (₹ 1 = 100 cents)
$amount_in_cents = intval($pay_grand_total * 100);

if ($amount_in_cents <= 0) {
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amount_in_cents,
        'currency' => 'inr',
        'payment_method_types' => ['card'],
        'description' => 'Payment for order from Mens Hair Salon',
    ]);

    echo json_encode(['client_secret' => $paymentIntent->client_secret]);
} catch (\Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
}
?>
