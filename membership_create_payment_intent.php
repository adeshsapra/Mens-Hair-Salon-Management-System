<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/stripe_config.php';
require_once __DIR__ . '/vendor/autoload.php';
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    echo json_encode(['error' => 'Please sign in to continue.']);
    exit();
}

if (!STRIPE_ENABLED || STRIPE_SECRET_KEY === '' || STRIPE_PUBLISHABLE_KEY === '') {
    echo json_encode(['error' => 'Stripe is not connected right now. Please contact admin.']);
    exit();
}

$passKey = trim((string) ($_POST['pass_key'] ?? ''));
$billingPlan = trim((string) ($_POST['billing_plan'] ?? ''));

if (!in_array($passKey, ['royal', 'classic', 'standard'], true)) {
    echo json_encode(['error' => 'Invalid membership pass selected.']);
    exit();
}
if (!in_array($billingPlan, ['yearly', 'monthly'], true)) {
    echo json_encode(['error' => 'Invalid billing plan selected.']);
    exit();
}

$stmt = mysqli_prepare(
    $con,
    'SELECT mp_id, display_name, price, features_json FROM membership_plans WHERE pass_key = ? AND billing_plan = ? LIMIT 1'
);
if (!$stmt) {
    echo json_encode(['error' => 'Unable to load membership plan.']);
    exit();
}

mysqli_stmt_bind_param($stmt, 'ss', $passKey, $billingPlan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$plan = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

if (!$plan) {
    echo json_encode(['error' => 'Membership plan not found.']);
    exit();
}

$amountRupees = (float) $plan['price'];
$amountCents = (int) round($amountRupees * 100);
if ($amountCents <= 0) {
    echo json_encode(['error' => 'Invalid membership amount.']);
    exit();
}

$displayName = trim((string) ($plan['display_name'] ?? 'Membership Pass'));
$planId = (int) ($plan['mp_id'] ?? 0);
$checkoutName = ucfirst($billingPlan) . ' ' . $displayName;

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $intent = \Stripe\PaymentIntent::create([
        'amount' => $amountCents,
        'currency' => 'inr',
        'payment_method_types' => ['card'],
        'description' => 'Membership purchase: ' . $checkoutName,
        'metadata' => [
            'checkout_type' => 'membership',
            'user_id' => (string) $user_id,
            'pass_key' => $passKey,
            'billing_plan' => $billingPlan,
            'membership_plan_id' => (string) $planId,
            'stripe_mode' => STRIPE_ACTIVE_MODE,
        ],
    ]);

    $features = json_decode((string) ($plan['features_json'] ?? '[]'), true);
    if (!is_array($features)) {
        $features = [];
    }

    echo json_encode([
        'client_secret' => $intent->client_secret,
        'amount' => $amountCents,
        'display_name' => $displayName,
        'membership_plan_id' => $planId,
        'checkout_name' => $checkoutName,
        'features' => array_values($features),
        'mode' => STRIPE_ACTIVE_MODE,
    ]);
} catch (\Exception $e) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => $e->getMessage()]);
}
