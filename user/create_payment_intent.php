<?php
require_once 'connect.php';
require_once '../stripe_config.php';
require_once '../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

function paymentTableExists(mysqli $con, string $table): bool
{
    $tableSafe = mysqli_real_escape_string($con, $table);
    $result = mysqli_query($con, "SHOW TABLES LIKE '{$tableSafe}'");
    return $result && mysqli_num_rows($result) > 0;
}

function getDiscountedPrice($price, $discountPercent): float
{
    if (empty($price)) {
        return 0.0;
    }

    $price = (float) $price;
    $discountPercent = max(0, min(100, (float) ($discountPercent ?? 0)));
    return round($price - (($price * $discountPercent) / 100), 2);
}

function calculateStripeCheckoutTotal(mysqli $con, int $userId, ?int $productId, ?int $comboId): float
{
    $grandTotal = 0.0;
    $comboCartReady = paymentTableExists($con, 'combo_cart') && paymentTableExists($con, 'combos');

    if ($productId) {
        $query = mysqli_query(
            $con,
            "SELECT p_price, p_discount FROM products WHERE p_id = {$productId} LIMIT 1"
        );
        $row = $query ? mysqli_fetch_assoc($query) : null;
        return $row ? getDiscountedPrice($row['p_price'] ?? 0, $row['p_discount'] ?? 0) : 0.0;
    }

    if ($comboId && $comboCartReady) {
        $query = mysqli_query(
            $con,
            "SELECT price FROM combos WHERE id = {$comboId} AND status = 1 LIMIT 1"
        );
        $row = $query ? mysqli_fetch_assoc($query) : null;
        return $row ? (float) ($row['price'] ?? 0) : 0.0;
    }

    $productCartQuery = mysqli_query(
        $con,
        "SELECT c_total, c_price, c_quantity
         FROM product_cart
         WHERE id = {$userId}"
    );

    if ($productCartQuery) {
        while ($row = mysqli_fetch_assoc($productCartQuery)) {
            $lineTotal = isset($row['c_total']) ? (float) $row['c_total'] : 0.0;
            if ($lineTotal <= 0) {
                $lineTotal = ((float) ($row['c_price'] ?? 0)) * (int) ($row['c_quantity'] ?? 0);
            }
            $grandTotal += $lineTotal;
        }
    }

    if ($comboCartReady) {
        $comboCartQuery = mysqli_query(
            $con,
            "SELECT cc.cc_total, cc.cc_price, cc.cc_quantity, c.status
             FROM combo_cart cc
             LEFT JOIN combos c ON c.id = cc.combo_id
             WHERE cc.id = {$userId}"
        );

        if ($comboCartQuery) {
            while ($row = mysqli_fetch_assoc($comboCartQuery)) {
                if (isset($row['status']) && (int) $row['status'] !== 1) {
                    continue;
                }

                $lineTotal = isset($row['cc_total']) ? (float) $row['cc_total'] : 0.0;
                if ($lineTotal <= 0) {
                    $lineTotal = ((float) ($row['cc_price'] ?? 0)) * (int) ($row['cc_quantity'] ?? 0);
                }
                $grandTotal += $lineTotal;
            }
        }
    }

    return round($grandTotal, 2);
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($userId <= 0) {
    echo json_encode(['error' => 'User not authenticated']);
    exit;
}

$productId = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
$comboId = isset($_POST['combo_id']) && $_POST['combo_id'] !== '' ? (int) $_POST['combo_id'] : null;
if ($productId && $comboId) {
    $comboId = null;
}

$grandTotal = calculateStripeCheckoutTotal($con, $userId, $productId, $comboId);
$amountInCents = (int) round($grandTotal * 100);

if ($amountInCents <= 0) {
    echo json_encode(['error' => 'Invalid amount. Please refresh checkout and try again.']);
    exit;
}

if (!STRIPE_ENABLED || STRIPE_PUBLISHABLE_KEY === '' || STRIPE_SECRET_KEY === '') {
    echo json_encode(['error' => 'Stripe is not connected right now. Please contact admin.']);
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $amountInCents,
        'currency' => 'inr',
        'payment_method_types' => ['card'],
        'description' => 'Product checkout payment',
        'metadata' => [
            'checkout_type' => 'product_order',
            'user_id' => (string) $userId,
            'product_id' => $productId ? (string) $productId : '',
            'combo_id' => $comboId ? (string) $comboId : '',
            'stripe_mode' => STRIPE_ACTIVE_MODE,
        ],
    ]);

    echo json_encode([
        'client_secret' => $paymentIntent->client_secret,
        'amount' => $amountInCents,
        'mode' => STRIPE_ACTIVE_MODE,
    ]);
} catch (\Throwable $exception) {
    http_response_code(400);
    echo json_encode(['error' => $exception->getMessage()]);
}

